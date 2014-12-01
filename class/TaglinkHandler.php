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
		// If so, then it is the right kind of tag and can be appended to the results. However, if
		// there are NO tags, or if there is only one tag and tid = 0 then it's untagged content
		// (only applies to tags, as there is no untagged functionality for categories)
		foreach ($ret as $key => $value) {
			if (array_key_exists($value, $tagList))	{
				$resultList[] = $value;
			}
		}
		if (empty($resultList)) {
			$resultList[0] = 0;
		}

    	return $resultList;
    }
	
	/**
	 * Returns a list of module/object pairs that are clients of sprockets
	 * 
	 * @return array
	 */
	public function getClientObjects() {
		return array(
			'article' => 'news',
			'programme' => 'podcast',
			'soundtrack' => 'podcast',
			'publication' => 'library',
			'item' => 'catalogue',
			'partner' => 'partners',
			'project' => 'projects',
			'start' => 'cms'
			);
	}
	
	/**
	 * Returns a list of content items (as arrays) associated with a specific tag, module or item type
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
	 * @param mixed $tag_id // Can be an int (tag ID) or 'untagged' to retrieve untagged content
	 * @param int $module_id
	 * @param array $item_type // list of object types as strings
	 * @param int $start
	 * @param int $limit
	 * @param string $sort
	 * @return array $content_object_array
	 */
	
	public function getTaggedItems($tag_id = FALSE, $module_id = FALSE, $item_type = FALSE,
			$start = FALSE, $limit = FALSE, $sort = 'DESC') {
		
		$sql = $item_list = $items = '';
		$content_count = 0;
		$untagged_content = $nothing_to_display = FALSE;
		$content_id_array = $content_object_array = $content_array = $taglink_object_array 
			= $module_ids = $item_types = $module_array = $parent_id_buffer = $taglinks_by_module
			= $object_counts = $handlers = array();
		
		// If tag_id = 'untagged' set a flag to retrieve untagged content
		if ($tag_id == 'untagged') {
			$untagged_content = TRUE;
		}
		
		// Parameters to public methods should be sanitised
		$tag_id = isset($tag_id) ? intval($tag_id) : 0;
		$module_id = isset($module_id) ? intval($module_id) : 0;
		$item_type_whitelist = icms_getConfig('client_objects', 'sprockets');
		if ($item_type) {
			$item_type = is_array($item_type) ? $item_type : array(0 => $item_type);
			foreach ($item_type as &$type) {
				if (!in_array($type, $item_type_whitelist)) {
					unset($type);
				}
			}
		}
		$start = isset($start) ? intval($start) : 0;
		$limit = isset($limit) ? intval($limit) : 0;
		if ($sort != 'ASC') {
			$sort = 'DESC';
		}
		
		// 1. Get a list of distinct item (object) types associated with the search parameters
		$sql = "SELECT `item`, COUNT(*) FROM " . $this->table;
		if ($untagged_content || $tag_id || $module_id || $item_type) {
			$sql .= " WHERE";
			if ($untagged_content) {
				$sql .= " `tid` = '0'";
			} elseif ($tag_id) {
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
				$items[] = $row['item'];
			}
		}
			
		// 2. Use the item list to check client modules are active and load required handlers
		// Note that $items is based on whitelisted user input or on results from the database,
		// so the only thing that needs to be checked is whether the relevant modules are installed 
		// and activated
		if ($items) {
			$clientObjects = $this->getClientObjects();
			foreach ($items as $key => &$itm) {
				if (icms_get_module_status($clientObjects[$itm])) {
					$handlers[$itm] = icms_getModuleHandler($itm, $clientObjects[$itm], 
						$clientObjects[$itm]);
				} else {
					unset($items[$key]);
				}
			}
		} else {
			$nothing_to_display = TRUE;
		}
		
		// 3. Get a count of the total number of search results available. Unfortunately, this is an
		// expensive thing to do in a cross-module table search (one query per object type). If
		// online_status were recorded in the taglinks table this could be reduced to a single 
		// query (counting the number of taglinks). A better approach might be to treat
		// sprockets_taglinks as a shared table, that all Gone Native modules will try to set up 
		// if it doesn't exist. That way client modules can start documenting untagged content
		// even before Sprockets is installed.
		if ($items) {
			$sql = '';
			$i = count($items);
			foreach ($items as $itms) {
				$i--;
				$sql .= "(SELECT `item`, COUNT(*)";
				$sql .= " FROM " . $this->table . " INNER JOIN " . $handlers[$itms]->table . " ON "
						. $this->table . ".iid  = " . $handlers[$itms]->table . "." . $itms . "_id";
				$sql .= " WHERE " . $this->table . ".iid  = " 
						. $handlers[$itms]->table . "." . $itms . "_id";
				$sql .= " AND " . $this->table . ".item = '" . $itms . "'";

				if ($untagged_content || $tag_id || $module_id) {
					$sql .= " AND";
					if ($untagged_content) {
						$sql .= " `tid` = '0'";
					} elseif ($tag_id) {
						$sql .= " `tid` = " . "'" . $tag_id . "'";
					}
					if ($module_id) {
						if ($tag_id) {
							$sql .= " AND";
						}
							$sql .= " `mid` = '" . $module_id . "'";
					}
				}
				$sql .= " AND `online_status` = '1') ";
				if ($i >0) {
					$sql .= " UNION ";
				}
			}
			// Run the count query
			$result = icms::$xoopsDB->queryF($sql);
			if (!$result) {
					echo 'Error';
					exit;
			} else {
				while ($row = icms::$xoopsDB->fetchArray($result)) {
					$content_count += $row['COUNT(*)'];
				}
			}
		}
		
		// 4. Retrieve the subset of results actually required, using as few resources as possible.
		// A sub-query is run on each object table (unavoidable). The results are combined through
		// a UNION of common fields (easy, since Gone Native modules use standard Dublin Core field
		// names). NB: The LIMIT is applied to the COMBINED result set of all subqueries linked by
		// the union, so sorting of results happens ACROSS all tables simultaneously. Cool, huh?
		if ($items) {
			$sql = '';
			$i = count($items);
			foreach ($items as $key => $it) {
				$i--;
				$sql .= "(SELECT "
					. "`item`,"
					. "`title`,"
					. "`description`,"
					. "`creator`," // need to standardise this across modules, some use $user
					. "`counter`,"
					. "`short_url`,"
					. "`date`,"
					. "`type`,";
				// Remap non-standard field names. Need to standardise these across client modules
				switch ($it) {
					case "programme":
						$sql .= "`cover` as `image`,";
						break;
					case "partner":
					case "project":
					case "start":
						$sql .= "`logo` as `image`,";
						break;
					case "soundtrack":
						$sql .= "`poster_image` as `image`,";
						break;
					default:
						$sql .= "`image`,";
						break;
				}
				$sql .= "`taglink_id`,"
					. "`iid`,"
					. "`mid`,"
					. "`tid`";
				$sql .= " FROM " . $this->table . " INNER JOIN " . $handlers[$it]->table . " ON "
						. $this->table . ".iid  = " . $handlers[$it]->table . "." . $it . "_id";
				$sql .= " WHERE " . $this->table . ".iid  = " 
						. $handlers[$it]->table . "." . $it . "_id";
				$sql .= " AND " . $this->table . ".item = '" . $it . "'";

				if ($untagged_content || $tag_id || $module_id) {
					$sql .= " AND";
					if ($untagged_content) {
						$sql .= " `tid` = '0'";
					} elseif ($tag_id) {
						$sql .= " `tid` = " . "'" . $tag_id . "'";
					}
					if ($module_id) {
						if ($tag_id) {
							$sql .= " AND";
						}
							$sql .= " `mid` = '" . $module_id . "'";
					}
				}
				$sql .= " AND `online_status` = '1') ";
				if ($i >0) {
					$sql .= " UNION ";
				}
			}
			$sql .= " ORDER BY `date` " . $sort;
			if ($start || $limit) {
				$sql .= " LIMIT " . $start . "," . $limit . " ";
			}
			// Run the query
			$result = icms::$xoopsDB->queryF($sql);
			if (!$result) {
					echo 'Error';
					exit;
			} else {
				while ($row = icms::$xoopsDB->fetchArray($result)) {
					$content_array[] = $row;
				}
			}
		} else {
			$nothing_to_display = TRUE;
		}

		// Prepend the $count of results
		array_unshift($content_array, $content_count);
		
		return $content_array;
	}
	
	/**
	 * Returns a list of content items (as arrays) that has been marked as untagged, and that are
	 * optionally associated with a specific tag, module or item type
	 *
	 * Can draw content from across compatible modules simultaneously. Used to build unified RSS
	 * feeds and content pages. Always use this method with a limit and as many parameters as possible
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
	 * @return array $content_object_array
	 */
	public function getUntaggedContent($module_id = FALSE, $item_type = FALSE, $start = FALSE,
			$limit = FALSE, $sort = 'DESC') {
			return $this->getTaggedItems('untagged', $module_id, $item_type, $start, $limit, $sort);
	}
	
	/**
	 * Saves tags for an object by creating taglinks.
	 * 
	 * If you are saving categories, you need to pass in the category key. Also note: If your object 
	 * has both tags and categories, then you need to call this method TWICE with different 
	 * label_type (0 = tag, 1 = category) in order to update them both. The 'untagged' option only 
	 * applies to tags (not available for categories).
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
		
		$count = count($tag_array);
		$moduleObj = icms_getModuleInfo($obj->handler->_moduleName);
		
		// If there are NO tags, or ONLY the 0 element tag ('---'), flag as untagged content
		if ($label_type == 0 && ($count == 0 || ($count == 1 && $tag_array[0] == 0))) {
			$taglinkObj = $this->create();
			$taglinkObj->setVar('mid', $moduleObj->getVar('mid'));
			$taglinkObj->setVar('item', $obj->handler->_itemname);
			$taglinkObj->setVar('iid', $obj->id());
			$taglinkObj->setVar('tid', 0);
			$this->insert($taglinkObj);
		} else {
			// Save the tags via taglinks
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
		
		// Check if each taglink tid is one of the target tag/category IDs. If so, mark the 
		// taglink_id for deletion
		foreach ($taglinkObjArray as $taglink)
		{
			if (array_key_exists($taglink->getVar('tid'), $tagList)
					|| ($taglink->getVar('tid') == 0 && $label_type == 0))
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