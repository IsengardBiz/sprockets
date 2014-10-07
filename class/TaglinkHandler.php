<?php

/**
* Class representing Sprockets taglink handler objects
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

class SprocketsTaglinkHandler extends icms_ipf_Handler {

	/**
	 * Constructor
	 */
	public function __construct(& $db) {
		parent::__construct($db, 'taglink', 'taglink_id', 'tid', 'iid', 'sprockets');
	}
	
	/**
	 * Returns a distinct list of module IDs that are currently referenced by taglinks
	 *
	 * @return array $module_list
	 */
	
	public function getTaglinkModules() {
		
		$module_list = array();
		$module_handler = icms::handler('icms_module');
		
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('label_type', '1', '!='));
		
		$sql = "SELECT DISTINCT `mid` FROM " . $this->table;
		$sql = mysql_real_escape_string($sql);
		
		$rows = $this->query($sql, $criteria);
		foreach ($rows as $key => $mid) {			
			$module_list[$mid] = $module_handler->get($mid);
		}
		
		return $module_list;
	}

	/**
	 * Returns a distinct list of item types that are currently referenced by taglinks
	 *
	 * @return array $item_list
	 */
	
	public function getTaglinkItems() {
		
		$item_list = array();
		$criteria = '';
		
		$sql = "SELECT DISTINCT `item` FROM " . $this->table;
		$sql = mysql_real_escape_string($sql);
		
		$rows = $this->query($sql, $criteria);
		foreach ($rows as $key => $item) {
			$item_list[] = $item;
		}
		
		return $item_list;
	}
	
	/**
	 * Retrieve tag_ids for an object (either tag or category label_type, but not both)
	 *
	 * Based on code from ImTagging: author marcan aka Marc-AndrÃ© Lanciault <marcan@smartfactory.ca>
	 *
	 * @param int $iid id of the related object
	 * @param object IcmsPersistableHandler
	 * @param int $label_type: 0 = tags, 1 = categories
	 * @return array $ret
	 */
    public function getTagsForObject($iid, &$handler, $label_type = '0') {
		
		$tagList = $resultList = $ret = array();
		$moduleObj = icms_getModuleInfo($handler->_moduleName);
		$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
		
		// Sanitise parameters used in queries
		$clean_iid = isset($iid) ? intval($iid) : 0;
		$clean_label_type = isset($label_type) ? intval($label_type): 0 ;		
		
    	$criteria = new icms_db_criteria_Compo();
    	$criteria->add(new icms_db_criteria_Item('mid', $moduleObj->getVar('mid')));
    	$criteria->add(new icms_db_criteria_Item('item', $handler->_itemname));
    	$criteria->add(new icms_db_criteria_Item('iid', $clean_iid));
    	$sql = 'SELECT DISTINCT `tid` FROM ' . $this->table;
		$sql = mysql_real_escape_string($sql);
    	$rows = $this->query($sql, $criteria);
    	$ret = array();
    	foreach($rows as $row) {
    		$ret[] = $row['tid'];
    	}
		
		// Need to discard either the categories or tags from the results, depending on the 
		// label_type that was requested:
		// Read a reference buffer of all tags with key as ID
		$sprockets_tag_handler = icms_getModuleHandler('tag', basename(dirname(dirname(__FILE__))),
				'sprockets');
		$criteria = icms_buildCriteria(array('label_type' => $clean_label_type));
		$tagList = $sprockets_tag_handler->getList($criteria, TRUE);
		
		// For each of the object's tags, check if the array_key_exists in the reference $tagList.
		// If so, then it is the right kind of tag and can be appended to the results:
		foreach ($ret as $key => $value)
		{
			if (array_key_exists($value, $tagList))
			{
				$resultList[] = $value;
			}
		}
		
    	return $resultList;
    }
	
	/**
	 * Returns a list of content objects associated with a specific tag, module or item type
	 *
	 * Can draw content from across compatible modules simultaneously. Used to build unified RSS
	 * feeds and tag pages. Always use this method with a limit and as many parameters as possible
	 * in order to simplify the results and avoid slow queries (for example, if you neglect to 
	 * specify a module_id it will run queries on all compatible modules).
	 *
	 * @global array $sprocketsConfig
	 * @param int $tag_id
	 * @param int $module_id
	 * @param array $item_type // list of object types as strings
	 * @param int $start
	 * @param int $limit
	 * @param string $sort
	 * @param string $order
	 * @return array $content_object_array
	 */
	
	public function getTaggedItems($tag_id = FALSE, $module_id = FALSE, $item_type = FALSE,
			$start = FALSE, $limit = FALSE, $sort = 'taglink_id', $order = 'DESC') {

		global $sprocketsConfig;
		
		$sql = '';
		$content_id_array = $content_object_array = $taglink_object_array = $module_ids
			= $item_types = $module_array = $parent_id_buffer = $taglinks_by_module = array();
		
		// Parameters to public methods should be sanitised
		$tag_id = isset($tag_id) ? intval($tag_id) : 0;
		$module_id = isset($module_id) ? intval($module_id) : 0;
		$item_type_whitelist = array_keys(sprockets_get_object_options());
		$item_type = is_array($item_type) ? $item_type : array();
		foreach ($item_type as &$type) {
			if (in_array($type, $item_type_whitelist)) {
				unset($type);
			}
		}
		$start = isset($start) ? intval($start) : 0;
		$limit = isset($limit) ? intval($limit) : 0;
		$sort_whitelist = array('taglink_id', 'tid', 'mid', 'item', 'iid');
		$sort = in_array($sort, $sort_whitelist) ? $sort : 'taglink_id';
		if ($order != 'ASC') {
			$order = 'DESC';
		}
		
		// Set up the query (had to do it this way as could not get setGroupby() to function??
		$sql = "SELECT * FROM " . $this->table . " GROUP BY `mid`, `iid`";	
		
		// Set optional criteria, must be via 'having' as cannot use 'where' with a group by
		$criteria = new icms_db_criteria_Compo();
		
		if ($tag_id || $module_id || $item_type)
		{
			$sql .= " HAVING";
			
			if ($tag_id) {
				$sql .= " `tid` = " . $tag_id;
				if ($module_id || $item_type) {
					$sql .= " AND";
				}
			}

			if ($module_id) {
				$sql .= " `mid` = " . $module_id;
				if ($item_type) {
					$sql .= " AND";
				}
			}

			if ($item_type && is_array($item_type)) {
				$item_type = "('" . implode("', '", $item_type) . "')";
				$sql .= " `item` IN " . $item_type;
			}
		}
				
		if ($start) {
			$criteria->setStart($start);
		}
		
		if ($limit) {
			$criteria->setLimit($limit);
		}
		
		if ($sort) {
			$criteria->setSort($sort);
		}
		
		if ($order) {
			$criteria->setOrder($order);
		}
		
		$taglink_object_array = $this->getObjects($criteria, TRUE, TRUE, $sql);
		
		// Get the required module object if parameter supplied, or build an array of module IDs from the taglinks
		if($module_id) {
			$module_ids[] = $module_id;
		} else {
			foreach ($taglink_object_array as $key => $taglink) {
				$module_ids[] = $taglink->getVar('mid');
			}
			$module_ids = array_unique($module_ids);
		}
		
		// Fetch all modules at once to reduce query load. Only active, relevant (taglinks exist)
		// modules will be returned.
		$module_handler = icms::handler('icms_module');
		$module_ids = '(' . implode(',', $module_ids) . ')';
		$criteria = new CriteriaCompo();
		$criteria->add(new icms_db_criteria_Item('isactive', 1));
		$criteria->add(new icms_db_criteria_Item('mid', $module_ids, "IN"));
		$module_array = $module_handler->getObjects($criteria, TRUE);
		
		// Retrieve the module objects and create a subarray for each with its mid as key, to hold its taglinks
		foreach ($module_array as $mid => $modObj) {
			$taglinks_by_module[$mid] = array(); // Fix bug
		}

		// IMPORTANT!! Sort the taglinks to facilitate processing: taglinks_by_module[module_id][item][iid]
		// But this will screw up the sorting by taglink_id!
		foreach ($taglink_object_array as $key => $taglink) {
			if (!array_key_exists($taglink->getItem(), $taglinks_by_module[$taglink->getVar('mid')])) {
				$taglinks_by_module[$taglink->getVar('mid')][$taglink->getItem()] = array();
			}
			$taglinks_by_module[$taglink->getVar('mid')][$taglink->getItem()][$taglink->getVar('taglink_id')] = $taglink;
		}
		
		// Get handler for each module/item type, build query string and retrieve content objects	
		// For each module...
		foreach ($module_array as $key => $moduleObj) {
			
			// For each item type (eg. article, project, partner)...
			foreach ($taglinks_by_module[$key] as $module_key => $item_array) {
				$item_id = $item_string = '';
				$id_string = $content_objects = array();
				$criteria = new icms_db_criteria_Compo();

				// Prepare a string of item IDs as search criteria
				foreach ($item_array as $item_key => $taglink) {
					$id_string[] = $taglink->getVar('iid');
				}
				$item_id = $module_key . '_id';
				$id_string = "(" . implode(",", $id_string) . ")";
				$criteria->add(new icms_db_criteria_Item($item_id, $id_string, 'IN'));
				$criteria->add(new icms_db_criteria_Item('online_status', '1'));

				// Retrieve the content objects
				$content_handler = icms_getModuleHandler($module_key, $moduleObj->getVar('dirname'),
						$moduleObj->getVar('dirname'));
				$content_objects = $content_handler->getObjects($criteria);
				
				// Concatenate the content objects to form combined (multi-module) results
				$content_object_array = array_merge($content_object_array, $content_objects);
			}
		}

		// Sort the combined module content by date (not working?)
		$tmp = array();
		foreach($content_object_array as $key => &$obj) {
			$tmp[] = &$obj->getVar('date', 'e');
		}
		array_multisort($tmp, SORT_DESC, $content_object_array);
		
		return $content_object_array;
	}
	
	/**
	 * Saves tags for an object by creating taglinks. NB: If you are saving categories, you need to
	 * pass in the category key. Also note: If your object has both tags and categories, then you 
	 * need to call this method TWICE with different label_type (0 = tag, 1 = category) in order to 
	 * update them both.
	 *
	 * Based on code from ImTagging: author marcan aka Marc-AndrÃ© Lanciault <marcan@smartfactory.ca>
	 * 
	 * @param object $obj
	 * @param string $tag_var
	 */

	public function storeTagsForObject(& $obj, $tag_var = 'tag', $label_type = '0')
	{		
		// Remove existing taglinks prior to saving the updated set
		$this->deleteTagsForObject($obj, $label_type);
		
		// Make sure this is an array (select control returns string, selectmulti returns array)
		$tag_array = $obj->getVar($tag_var);
		if (!is_array($tag_array) && !empty($tag_array))
		{
			$tag_array = array($tag_array);
		}
		
		$moduleObj = icms_getModuleInfo($obj->handler->_moduleName);

		if (count($tag_array) > 0) {

			foreach($tag_array as $tag) {
				// Do not allow the select box zero message to be stored as a tag!
				if ($tag != '0')
				{
					$taglinkObj = $this->create();
					$taglinkObj->setVar('mid', $moduleObj->getVar('mid'));
					$taglinkObj->setVar('item', $obj->handler->_itemname);
					$taglinkObj->setVar('iid', $obj->id());
					$taglinkObj->setVar('tid', $tag);
					$this->insert($taglinkObj);
				}
			}
		}
    }
	
	/**
	 * Deletes either the category- or tag-related taglinks of an object prior to updating it.
	 * 
	 * When updating an objects tags or categories, it is necessary to delete the old ones before
	 * saving the new, modified set. This creates a problem: Since tags/categories are managed 
	 * separately (because some objects may have both), you can't just delete them all because they 
	 * reside in the same database table. You need to delete EITHER the tags OR the categories.
	 * Otherwise updating your tags will kill all your categories, and vice-versa. So call this
	 * method with the appropriate label_type and it will selectively delete them.
	 * 
	 * @param obj $obj
	 * @param int $label_type (0 = tags, 1 = categories) 
	 */
	public function deleteTagsForObject(& $obj, $label_type)
	{
		$criteria = $sprockets_tag_handler = $sprockets_taglink_handler = $moduleObj = '';
		$tagList = $taglinkObjArray = $taglinks_for_deletion = array();
		
		// Taglinks know the tag id, module id, item id and item type.
		$sprockets_tag_handler = icms_getModuleHandler('tag', basename(dirname(dirname(__FILE__))), 
				'sprockets');
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', basename(dirname(dirname(__FILE__))),
				'sprockets');
		
		// Read a buffer of ALL tags OR categories on the system (one or the other)
		$criteria = icms_buildCriteria(array('label_type' => $label_type));
		$tagList = $sprockets_tag_handler->getList($criteria, TRUE);
		unset($criteria);
		
		// Read a buffer of ALL taglinks for THIS OBJECT
		$moduleObj = icms_getModuleInfo($obj->handler->_moduleName);
		$criteria = icms_buildCriteria(array(
			'mid' => $moduleObj->getVar('mid'), 
			'iid' => $obj->id(), 
			'item' => $obj->handler->_itemname));
		$taglinkObjArray = $sprockets_taglink_handler->getObjects($criteria, TRUE);
		
		// Check if each taglink tid is one of the target tag/category IDs. If so, mark the taglink_id for deletion
		foreach ($taglinkObjArray as $taglink)
		{
			if (array_key_exists($taglink->getVar('tid'), $tagList))
			{
				$taglinks_for_deletion[] = $taglink->getVar('taglink_id');
			}
		}
		
		// Delete marked taglinks
		if (!empty($taglinks_for_deletion))
		{
			$taglinks_for_deletion = '(' . implode(',', $taglinks_for_deletion) . ')';
			$criteria = new icms_db_criteria_Compo();
			$criteria->add(new icms_db_criteria_Item('taglink_id', $taglinks_for_deletion, 'IN'));
			$this->deleteAll($criteria);
		}
	}

	// This function based on code from ImTagging

	/**
	 * Cleans up taglinks after an object is deleted
	 * 
	 * Based on code from ImTagging: author marcan aka Marc-AndrÃ© Lanciault <marcan@smartfactory.ca>
	 *
	 * @param object $obj
	 */

	public function deleteAllForObject(&$obj) {
		/**
		 * @todo: add $moduleObj as a static var
		 */
		$moduleObj = icms_getModuleInfo($obj->handler->_moduleName);
    	$mid = $moduleObj->getVar('mid');

		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('mid', $mid));
		$criteria->add(new icms_db_criteria_Item('item', $obj->handler->_itemname));
		$criteria->add(new icms_db_criteria_Item('iid', $obj->id()));

		$this->deleteAll($criteria);
	}
}