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
		
		$rows = $this->query($sql, $criteria);
		foreach ($rows as $key => $item) {
			$item_list[] = $item;
		}
		
		return $item_list;
	}
	
	/**
	 * retrieve tag_ids for an object
	 *
	 * Based on code from ImTagging: author marcan aka Marc-André Lanciault <marcan@smartfactory.ca>
	 *
	 * @param int $iid id of the related object
	 * @param object IcmsPersistableHandler
	 * @return array $ret
	 */
    public function getTagsForObject($iid, &$handler) {
		
		$moduleObj = icms_getModuleInfo($handler->_moduleName);

    	$criteria = new icms_db_criteria_Compo();
    	$criteria->add(new icms_db_criteria_Item('mid', $moduleObj->getVar('mid')));
    	$criteria->add(new icms_db_criteria_Item('item', $handler->_itemname));
    	$criteria->add(new icms_db_criteria_Item('iid', $iid));
    	$sql = 'SELECT tid FROM ' . $this->table;
    	$rows = $this->query($sql, $criteria);
    	$ret = array();
    	foreach($rows as $row) {
    		$ret[] = $row['tid'];
    	}
    	return $ret;
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
	 * @param string $item_type
	 * @param int $start
	 * @param int $limit
	 * @param string $sort
	 * @param string $order
	 * @return array $content_object_array
	 */
	
	public function getTaggedItems($tag_id = FALSE, $module_id = FALSE, $item_type = FALSE,
			$start = FALSE, $limit = FALSE, $sort = 'taglink_id', $order = 'DESC') {

		global $sprocketsConfig;
		
		$content_id_array = $content_object_array = $taglink_object_array = $module_ids
			= $item_types = $module_array = $parent_id_buffer = $taglinks_by_module = array();
		
		// get content objects as per the supplied parameters
		$criteria = new icms_db_criteria_Compo();
		
		if ($tag_id) {
			$criteria->add(new icms_db_criteria_Item('tid', $tag_id));
		}
		
		if ($module_id) {
			$criteria->add(new icms_db_criteria_Item('mid', $module_id));
		}
		
		if ($item_type) {
			$criteria->add(new icms_db_criteria_Item('item', $item_type));
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
		
		$taglink_object_array = $this->getObjects($criteria);
		
		// Get the required module object if parameter supplied, or build an array of module IDs from the taglinks
		if($module_id) {
			$module_ids[] = $module_id;
		} else {
			foreach ($taglink_object_array as $key => $taglink) {
				$module_ids[] = $taglink->getVar('mid');
			}
			$module_ids = array_unique($module_ids);
		}	
		
		// Retrieve the module objects and create a subarray for each with its mid as key, to hold its taglinks
		foreach ($module_ids as $key => $mid) {
			$module_handler = icms::handler('icms_module');
			$module_array[$mid] = $module_handler->get($mid);
		}

		// IMPORTANT!! Sort the taglinks to facilitate processing: taglinks_by_module[module_id][item][iid]
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

		// sort the combined module content by date
		$sorted = $unsorted = array();

		foreach ($content_object_array as $key => $contentObj) {
			$unsorted[$key] = $contentObj->getVar('date', 'e');
		}
		asort($unsorted);
		foreach ($unsorted as $key => $value) {
			$sorted[$key] = $content_object_array[$key];
		}
		$content_object_array = $sorted;

		return $content_object_array;
	}

	/**
	 * Saves tags for an object by creating taglinks. NB: If you are saving categories, you need to
	 * pass in the category key. Also note: If your object has both tags and categories, then you 
	 * need to call this method TWICE with different label_type (0 = tag, 1 = category) in order to 
	 * update them both.
	 *
	 * Based on code from ImTagging: author marcan aka Marc-André Lanciault <marcan@smartfactory.ca>
	 * 
	 * @param object $obj
	 * @param string $tag_var
	 */

	public function storeTagsForObject(& $obj, $label_type, $tag_var='tag') {
		/**
		 * @todo: check if tags have changed and if so don't do anything
		 */
		
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
				$taglinkObj = $this->create();
				$taglinkObj->setVar('mid', $moduleObj->getVar('mid'));
				$taglinkObj->setVar('item', $obj->handler->_itemname);
				$taglinkObj->setVar('iid', $obj->id());
				$taglinkObj->setVar('tid', $tag);
				$this->insert($taglinkObj);
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
	public function deleteTagsForObject(& $obj, $label_type = '0')
	{
		$criteria = $sprockets_tag_handler = $sprockets_taglink_handler = $moduleObj = '';
		$tagObjArray = $taglinkObjArray = $taglinks_for_deletion = array();
		
		// Taglinks know the tag id, module id, item id and item type.
		$sprockets_tag_handler = icms_getModuleHandler('tag', basename(dirname(dirname(__FILE__))), 
				'sprockets');
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', basename(dirname(dirname(__FILE__))),
				'sprockets');
		
		// Read a buffer of ALL tags OR categories on the system (one or the other)
		$criteria = icms_buildCriteria(array('label_type' => $label_type));
		$tagObjArray = $sprockets_tag_handler->getObjects($criteria, TRUE);
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
			if (array_key_exists($taglink->getVar('tid'), $tagObjArray))
			{
				$taglinks_for_deletion[] = $taglink->getVar('taglink_id');
			}
		}
		
		// Delete marked taglinks
		if (!empty($taglinks_for_deletion))
		{
			$taglinks_for_deletion = '(' . implode($taglinks_for_deletion) . ')';
			$criteria = new icms_db_criteria_Compo();
			$criteria->add(new icms_db_criteria_Item('taglink_id', $taglinks_for_deletion, 'IN'));
			$this->deleteAll($criteria);
		}
	}

	// This function based on code from ImTagging

	/**
	 * Cleans up taglinks after an object is deleted
	 * 
	 * Based on code from ImTagging: author marcan aka Marc-André Lanciault <marcan@smartfactory.ca>
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