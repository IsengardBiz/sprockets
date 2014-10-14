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
	
	public function getTaglinkItems($tag_id = FALSE) {
		
		$tid = isset($tag_id) ? intval($tag_id) : FALSE;
		$item_list = array();
		$criteria = '';
		
		$sql = "SELECT DISTINCT `item` FROM " . $this->table;
		if ($tid) {
			$sql .= " WHERE `tid` = '" . $tid . "'";
		}
		$rows = $this->query($sql, $criteria);
		foreach ($rows as $key => $item) {
			$item_list[] = $item['item'];
		}

		return $item_list;
	}
	
	/**
	 * Retrieve tag_ids for an object (either tag or category label_type, but not both)
	 *
	 * Based on code from ImTagging: author marcan aka Marc-Andre Lanciault <marcan@smartfactory.ca>
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
	 * Note: The first element in the returned array is a COUNT of the total number of available
	 * results, which is used to construct pagination controls. The calling code needs to remove
	 * this element before attempting to process the content objects
	 * 
	 * Note: This method should ONLY be used to retrieve content from multiple modules 
	 * simultaneously. Individual modules can retrieve / process their own results much more 
	 * efficiently using their own methods or a standard IPF call.
	 *
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
			$start = FALSE, $limit = FALSE, $sort = 'DESC') {
		
		$sql = $item_list = $items = '';
		$content_count = 0;
		$nothing_to_display = FALSE;
		$content_id_array = $content_object_array = $taglink_object_array = $module_ids
			= $item_types = $module_array = $parent_id_buffer = $taglinks_by_module
			= $object_counts = array();
		$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
		
		// Parameters to public methods should be sanitised
		$tag_id = isset($tag_id) ? intval($tag_id) : 0;
		$module_id = isset($module_id) ? intval($module_id) : 0;
		$item_type_whitelist = icms_getConfig('client_objects', 'sprockets');
		$item_type = is_array($item_type) ? $item_type : array();
		foreach ($item_type as &$type) {
			if (in_array($type, $item_type_whitelist)) { // What if item type is just one ?
				unset($type);
			}
		}
		$start = isset($start) ? intval($start) : 0;
		$limit = isset($limit) ? intval($limit) : 0;
		if ($sort != 'ASC') {
			$sort = 'DESC';
		}
		
		// Get handlers for objects that are a) designated clients of sprockets and b) where the 
		// module is installed and activated
		$handlers = $sprockets_tag_handler->getClientObjectHandlers();
		
		// Get a list of taglinks broken down by object types. NOTE: If there are a very large 
		// number of taglinks, resource usage might be very high.
		//
		// One possibility to increase efficiency might be to:
		// 
		// 1. Just get a list of distinct items associated with the tag in question. That will 
		// tell you which module object tables need to be searched.
		// 
		// 2. Run a cross-module query directly using multiple joins (this will eliminate the need
		// to build a list of taglink_ids). This will allow the result set to be retrieved and 
		// sorted in one go, dramatically reducing the number of queries required for the operation.
		// However, it will be a (relatively) complex query and I suppose it could impose a heavy
		// load by itself?
		
		// 1. Get a list of distinct item (object) types associated with the search parameters
		$sql = "SELECT `item`, COUNT(*) FROM " . $this->table;
		if ($tag_id || $module_id || $item_type) {
			$sql .= " WHERE";
			if ($tag_id) {
				$sql .= " `tid` = " . $tag_id;
			}
			if ($module_id) {
				if ($tag_id) {
					$sql .= " AND";
				}
					$sql .= " `mid` = " . $module_id;
			}
		}
		if ($item_type) {
			if (is_array($item_type)) {
				$item_type = '("' . implode('","', $item_type) . '")';
			} else {
				$item_type = '("' . $item_type . '")';
			}				
			if ($tag_id || $module_id) {
				$sql .= " AND";
			}
			$sql .= " `item` IN " . $item_type;
		}
		$sql .= " GROUP BY `item`";
		$result = icms::$xoopsDB->query($sql);
		if (!$result) {
				echo 'Error';
				exit;
		} else {
			while ($row = icms::$xoopsDB->fetchArray($result)) {
				$content_count += $row['COUNT(*)'];
				$items[] = $row['item'];
			}
		}
		
		// 2. Use the item list to check client modules are active and load required handlers
		if ($items) {
			$item_whitelist = $sprockets_tag_handler->getClientObjects();
			foreach ($items as $key => $item) {
				if (in_array($item, $item_whitelist)) {
					// Check module is available/activated, remove anything that isn't
					if (icms_get_module_status($item_whitelist[$item])) {
						$handlers[$item] = icms_getModuleHandler($item, $item_whitelist[$item], 
							$item_whitelist[$item]);
					} else {
						unset($items[$key]);
					}
				}
			}
		} else {
			$nothing_to_display == TRUE;
		}
		
		
		// 3. Count total result set and retrieve the subset of results actually required
		
		
		
		// MAYBE USE ->QUERY RATHER THAN getObjects()? The SQL is ok and returns the expected
		// results, however getObjects returns a load of gibberish.
		$taglink_object_array = $this->getObjects($criteria, FALSE, FALSE, $sql);
		$content_count = count($taglink_object_array);
		echo 'Results returned: ' . count($taglink_object_array);
		
		
		// THIS CODE WORKS - COMMENTED OUT TO TEST A MORE EFFICIENT METHOD - DO NOT DELETE!
		/*
		if ($tag_id || $module_id || $item_type || $start || $limit || $sort || $order) {
			$criteria = new icms_db_criteria_Compo();
			if ($tag_id) {
				$criteria->add(new icms_db_criteria_Item('tid', $tag_id));
			}
			if ($module_id) {
				$criteria->add(new icms_db_criteria_Item('mid', $module_id));
			}
			if ($item_type) {
				if (is_array($item_type)) {
					$item_type = '("' . implode('","', $item_type) . '")';
					$criteria->add(new icms_db_criteria_Item('item', $item_type, "IN"));
				} else {
					$criteria->add(new icms_db_criteria_Item('item', $item_type));
				}
			}
		}
		$taglink_object_array = $this->getObjects($criteria);
	
		// Get the required module object if parameter supplied, or build an array of module IDs
		// from the taglinks
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
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('isactive', 1));
		$criteria->add(new icms_db_criteria_Item('mid', $module_ids, "IN"));
		$module_array = $module_handler->getObjects($criteria, TRUE);
		
		// Retrieve the module objects, create a subarray for each with its mid as key, to hold 
		// its taglinks, and load the relevant language file
		foreach ($module_array as $mid => $modObj) {
			$taglinks_by_module[$mid] = array();
			icms_loadLanguageFile($modObj->getVar('dirname'), 'common');
		}

		// IMPORTANT!! Sort the taglinks to facilitate processing: taglinks_by_module[module_id][item][iid]
		foreach ($taglink_object_array as $key => $taglink) {
			if (!array_key_exists($taglink->getVar('item'), $taglinks_by_module[$taglink->getVar('mid')])) {
				$taglinks_by_module[$taglink->getVar('mid')][$taglink->getVar('item')] = array();
			}
			$taglinks_by_module[$taglink->getVar('mid')][$taglink->getItem()]
					[$taglink->getVar('taglink_id')] = $taglink;
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
				$criteria->setSort('date');
				$criteria->setOrder($sort);
				// The next two lines are probably not going to work on a per-module basis, because
				// we do not know the position of any given module's contents until they have been 
				// compared against the dates of results from all other modules. It's ok for the 
				// most recent results, but once you start requesting earlier pagination subsets
				// you are going to run into inconsistencies, because you are sorting independent 
				// subsets of results, not all results. Basically, the only way around this is to 
				// do the query via a huge join, where date fields can be examined across all object
				// tables simultaneously. This will also save a ton of queries.
				$criteria->setOrder($start);
				$criteria->setLimit($limit);

				// Retrieve the content objects
				$content_objects = $handlers[$module_key]->getObjects($criteria);
				
				// Concatenate the content objects to form combined (multi-module) results
				$content_object_array = array_merge($content_object_array, $content_objects);
			}
		}
				
		// Sort the results by date field
		function sortByDateDescending($a, $b) {
			if ($a->getVar('date', 'e') == $b->getVar('date', 'e')) {
				return 0;
			}
			return ($a->getVar('date', 'e') < $b->getVar('date', 'e')) ? 1 : -1;
		}
		
		function sortByDateAscending($a, $b) {
			if ($a->getVar('date', 'e') == $b->getVar('date', 'e')) {
				return 0;
			}
			return ($a->getVar('date', 'e') < $b->getVar('date', 'e')) ? -1 : 1;
		}

		if ($sort == 'DESC') {
			usort($content_object_array, "sortByDateDescending");
		} elseif ($sort == 'ASC') {
			usort($content_object_array, "sortByDateAscending");
		}
		*/
		
		// Count the total result set to allow construction of pagination controls
		$content_count = count($content_object_array);
		
		// Truncate the results to the desired quantity (very inefficient, needs improving)
		$content_object_array = array_slice($content_object_array, $start, $limit);
		
		// Prepend the $count of results
		array_unshift($content_object_array, $content_count);
		
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