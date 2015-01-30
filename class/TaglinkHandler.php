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
	
	////////////////////////////////////////////////////////
	//////////////////// PUBLIC METHODS ////////////////////
	////////////////////////////////////////////////////////

	/**
	 * Constructor
	 */
	public function __construct(& $db) {
		parent::__construct($db, 'taglink', 'taglink_id', 'tid', 'iid', 'sprockets');
	}
	
	/**
	 * Retrieve tag_ids for a SINGLE object (either tag or category label_type, but not both)
	 *
	 * Based on code from ImTagging: author marcan aka Marc-Andre Lanciault <marcan@smartfactory.ca>
	 *
	 * @param int $iid id of the related object
	 * @param object IcmsPersistableHandler
	 * @param int $label_type: 0 = tags, 1 = categories
	 * @return array $ret
	 */
    public function getTagsForObject($iid, &$handler, $label_type = '0') {
		$clean_iid = !empty($iid) ? (int)$iid : 0;
		$clean_moduleName = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($handler->_moduleName, 'str', 'noencode'));
		$clean_itemname = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($handler->_itemname, 'str', 'noencode'));
		if ($label_type == '1') {
			$clean_label_type = 1;
		} else {
			$clean_label_type = 0;
		}		
		
		return $this->_getTagsForObject($clean_iid, $clean_moduleName, $clean_itemname, $clean_label_type);
	}
	
	/**
	 * Retrieve tag IDs for MULTIPLE objects sorted into a multidimensional array
	 *  
	 * @param array $iids
	 * @param string $item
	 * @param id $module
	 * @return array
	 */
	public function getTagsForObjects($iids, $item, $module_id = FALSE) {
		$clean_iids = array();
		foreach ($iids as $iid) {
			$clean_iids[] = (int)$iid;
		}
		$clean_item = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($item, 'str', 'noencode'));
		$clean_module_id = !empty($module_id) ? (int)$module_id : 0;
		return $this->_getTagsForObjects($clean_iids, $clean_item, $clean_module_id);
	}
	
	/**
	 * Returns a list of module/object pairs that are clients of sprockets
	 * 
	 * @return array
	 */
	public function getClientObjects() {
		return $this->_getClientObjects();
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
	 * 
	 * @return array $content_object_array
	 */
	
	public function getTaggedItems($tag_id = FALSE, $module_id = FALSE, $item_type = FALSE,
			$start = 0, $limit = FALSE, $sort = 'DESC') {
		
		$clean_item_type = array();
		
		$clean_tag_id = !empty($tag_id) ? (int)$tag_id : 0;
		$clean_module_id = !empty($module_id) ? (int)$module_id : 0;
		if ($item_type) {
			$item_type_whitelist = array_keys($this->getClientObjects());
			$item_type = is_array($item_type) ? $item_type : array(0 => $item_type);
			foreach ($item_type as &$type) {
				if (in_array($type, $item_type_whitelist)) {
					$clean_item_type[] = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($type, 'str', 'noencode'));
				} else {
					unset($type);
				}
			}
		} else {
			$item_type = icms_getConfig('client_objects', 'sprockets');
		}
		$clean_start = !empty($start) ? (int)$start : 0;
		$clean_limit = !empty($limit) ? (int)$limit : 0;
		if ($sort == 'DESC') {
			$clean_sort = 'DESC';
		} else {
			$clean_sort = 'ASC';
		}
		
		return $this->_getTaggedItems($clean_tag_id, $clean_module_id, $clean_item_type,
				$clean_start, $clean_limit, $clean_sort);
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
	 * 
	 * @return array $content_object_array
	 */
	public function getUntaggedContent($module_id = FALSE, $item_type = FALSE, $start = FALSE,
			$limit = FALSE, $sort = 'DESC') {
		
		$clean_item_type = array();
		
		$clean_module_id = !empty($module_id) ? (int)$module_id : 0;
		if ($item_type) {
			$item_type_whitelist = array_keys($this->getClientObjects());
			$item_type = is_array($item_type) ? $item_type : array(0 => $item_type);
			foreach ($item_type as &$type) {
				if (in_array($type, $item_type_whitelist)) {
					$clean_item_type[] = icms::$xoopsDB->escape(icms_core_Datafilter::checkVar($type, 'str', 'noencode'));
				} else {
					unset($type);
				}
			}
		} else {
			$item_type = icms_getConfig('client_objects', 'sprockets');
		}
		$clean_start = !empty($start) ? (int)$start : 0;
		$clean_limit = !empty($limit) ? (int)$limit : 0;
		if ($sort == 'DESC') {
			$clean_sort = 'DESC';
		} else {
			$clean_sort = 'ASC';
		}
		if ($item_type) {
			$item_type_whitelist = array_keys($this->getClientObjects());
			$item_type = is_array($item_type) ? $item_type : array(0 => $item_type);
			foreach ($item_type as &$type) {
				if (in_array($type, $item_type_whitelist)) {
					$clean_item_type[] = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($type, 'str', 'noencode'));
				} else {
					unset($type);
				}
			}
		} else {
			$item_type = icms_getConfig('client_objects', 'sprockets');
		}
				
		return $this->_getUntaggedContent($clean_module_id, $clean_item_type, $clean_start, 
				$clean_limit, $clean_sort);
	}
	
	/**
	 * Saves tags for an object by creating taglinks.
	 * 
	 * If you are saving categories, you need to pass in the category key. Also note: If your object 
	 * has both tags and categories, then you need to call this method TWICE with different 
	 * label_type (0 = tag, 1 = category) in order to update them both. The 'untagged' option only 
	 * applies to tags (not available for categories).
	 *
	 * Based on code from ImTagging: author marcan aka Marc-Andre Lanciault <marcan@smartfactory.ca>
	 * 
	 * @param object $obj
	 * @param string $tag_var
	 */

	public function storeTagsForObject(&$obj, $tag_var = 'tag', $label_type = '0') {
		$clean_obj = is_object($obj) ? $obj : FALSE;
		$clean_tag_var = !empty($tag_var) ? icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($tag_var, 'str', 'noencode')) : 'tag';
		if ($label_type == '0') {
			$clean_label_type = 0;
		} else {
			$clean_label_type = 1;
		}
		$this->_storeTagsForObject(&$clean_obj, $clean_tag_var, $clean_label_type);
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
	public function deleteTagsForObject(&$obj, $label_type) {
		$clean_obj = is_object($obj) ? $obj : FALSE;
		if ($label_type == '1') {
			$clean_label_type = 1;
		} else {
			$clean_label_type = 0;
		}
		$this->_deleteTagsForObject(&$clean_obj, $clean_label_type);
	}
	
	/**
	 * Cleans up taglinks after an object is deleted
	 * 
	 * Based on code from ImTagging: author marcan aka Marc-AndrÃ© Lanciault <marcan@smartfactory.ca>
	 *
	 * @param object $obj
	 */

	public function deleteAllForObject(&$obj) {
		$clean_obj = is_object($obj) ? $obj : FALSE;
		$this->_deleteAllForObject(&$clean_obj);
	}
	
	/////////////////////////////////////////////////////////
	//////////////////// PRIVATE METHODS ////////////////////
	/////////////////////////////////////////////////////////
	
    private function _getTagsForObject($iid, $moduleName, $itemname, $label_type) {
		
		$tagList = $resultList = $ret = array();
		$moduleObj = icms_getModuleInfo($moduleName);
		$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');		
    	$criteria = new icms_db_criteria_Compo();
    	$criteria->add(new icms_db_criteria_Item('mid', $moduleObj->getVar('mid')));
    	$criteria->add(new icms_db_criteria_Item('item', $itemname));
    	$criteria->add(new icms_db_criteria_Item('iid', $iid));
    	$sql = 'SELECT DISTINCT `tid` FROM ' . icms::$xoopsDB->escape($this->table) . "";
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
		$criteria = icms_buildCriteria(array('label_type' => $label_type));
		$tagList = $sprockets_tag_handler->getList($criteria, TRUE);
		if (!$label_type) {
			$tagList[0] = 0;
		}
		
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
	
	private function _getTagsForObjects($iids, $item, $module_id) {
		
		$sql = $rows = '';
		$tag_ids = array();
		$iids = implode(',', $iids);
		
		$sql = "SELECT `iid`, `tid` FROM " . icms::$xoopsDB->escape($this->table) . " WHERE "
				. "`item` = '" . $item . "' AND "
				. "`iid` IN (" . $iids . ")";
		if ($module_id) {
			$sql .= " AND `mid` = '" . $module_id . "'";
		}
		$result = icms::$xoopsDB->query($sql);
		if (!$result) {
				echo 'Error1';
				exit;
		} else {
			$rows = $this->convertResultSet($result, FALSE, FALSE);
			foreach ($rows as $row) {
				if (!isset($tag_ids[$row['iid']])) {
					$tag_ids[$row['iid']] = array();
				}
				$tag_ids[$row['iid']][] = $row['tid'];
			}
		}
		return $tag_ids;
	}
	
	private function _getClientObjects() {
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
	
	private function _getTaggedItems($tag_id, $module_id, $item_type, $start, $limit, $sort) {
		
		$sql = $item_list = $items = '';
		$content_count = 0;
		$untagged_content = $nothing_to_display = 0;
		$content_id_array = $content_object_array = $content_array = $taglink_object_array 
			= $module_ids = $item_types = $module_array = $parent_id_buffer = $taglinks_by_module
			= $object_counts = $handlers = array();
			
		// If tag_id = 'untagged' set a flag to retrieve untagged content only
		if ($tag_id === 'untagged') {
			$untagged_content = TRUE;
		}
		
		// 1. Get a list of distinct item (object) types associated with the search parameters
		$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
		$sql = "SELECT " . icms::$xoopsDB->escape($this->table) . ".item, COUNT(*) FROM " 
				. icms::$xoopsDB->escape($this->table);
		if (!$untagged_content) {
				$sql .= " INNER JOIN " . icms::$xoopsDB->escape($sprockets_tag_handler->table) 
					. " ON " . icms::$xoopsDB->escape($this->table) . ".tid = " 
					. icms::$xoopsDB->escape($sprockets_tag_handler->table) . ".tag_id";
		}
		$sql .= " WHERE";
		if ($untagged_content || $tag_id || $module_id || $item_type) {
			if ($untagged_content) {
				$sql .= " `tid` = '0'";
			} elseif ($tag_id) {
				$sql .= " (`tid` = '" . $tag_id . "' AND `label_type` = '0')";
			} else {
				$sql .= " `label_type` = '0'";
			}
			if ($module_id) {
				if ($untagged_content || $tag_id) {
					$sql .= " AND";
				}
				$sql .= " `mid` = '" . $module_id . "'";
			}
			if ($item_type) {
					$sql .= " AND";
				if (count($item_type) == 1) {
					$sql .= ' `item` = "' . implode('","', $item_type) . '"';
				} else {
					$sql .= ' `item` IN ("' . implode('","', $item_type) . '")';
				}
			}
		}
		
		// If you want to retrieve untagged content, need to explictly request it
		// If you merely don't specify tag_id, this means retrieve all *tagged* content
		// The way it is structured, tag_id = 0 should *only* be run if untagged content requested
		// If there is simply no tag_id (but untagged not specified), then still need the join
		if (!$untagged_content && $tag_id == 0) {
			if ($untagged_content || $tag_id || $module_id || $item_type) {
				$sql .= " AND";
			}
			$sql .= " `tid` != '0'";
		}
		$sql .= " GROUP BY `item`";
		$result = icms::$xoopsDB->query($sql);
		if (!$result) {
				echo 'Error2';
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
					// NOTE: The next line causes a white screen when trying to re-enable a 
					// disabled module in the module admin page. Not sure why, since this code 
					// should not run if the module is inactive. Looks like the module status is 
					// evaluating as TRUE before it is fully operational. The bug does not seem 
					// to have any consequences however, page loads normally on refresh.
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
				$sql .= "(SELECT `item`, COUNT(DISTINCT `item`, `iid`)";
				$sql .= " FROM " . icms::$xoopsDB->escape($this->table) 
						. " INNER JOIN " . icms::$xoopsDB->escape($handlers[$itms]->table) . " ON "
						. icms::$xoopsDB->escape($this->table) . ".iid  = " 
						. icms::$xoopsDB->escape($handlers[$itms]->table) . "." . $itms . "_id";
				if (!$untagged_content) {
					$sql .= " INNER JOIN " . icms::$xoopsDB->escape($sprockets_tag_handler->table) 
						. " ON " . icms::$xoopsDB->escape($this->table) . ".tid = "
						. icms::$xoopsDB->escape($sprockets_tag_handler->table) . ".tag_id";
				}
				$sql .= " WHERE " . icms::$xoopsDB->escape($this->table) . ".iid  = " 
						. icms::$xoopsDB->escape($handlers[$itms]->table) . "." . $itms . "_id";
				$sql .= " AND " . icms::$xoopsDB->escape($this->table) . ".item = '" . $itms . "'";
				if ($untagged_content) {
					$sql .= " AND " . icms::$xoopsDB->escape($this->table) . ".tid = '0'";
				} elseif ($tag_id) {
					$sql .= " AND (" . icms::$xoopsDB->escape($this->table) . ".tid = " . "'"
						. $tag_id . "' AND " 
						. icms::$xoopsDB->escape($sprockets_tag_handler->table) . ".label_type = '0')";
				} else {
					$sql .= " AND " . icms::$xoopsDB->escape($sprockets_tag_handler->table)
					. ".label_type = '0'";
				}
				if ($module_id) {
					if ($untagged_content || $tag_id) {
						$sql .= " AND";
					}
					$sql .= " `mid` = '" . $module_id . "'";
				}
				
				$sql .= " AND `online_status` = '1') ";
				if ($i >0) {
					$sql .= " UNION ";
				}
			}
			
			/////////////////////////////////////////
			////////// Run the count query //////////
			/////////////////////////////////////////
			$result = icms::$xoopsDB->queryF($sql);
			if (!$result) {
					echo 'Error3';
					exit;
			} else {
				while ($row = icms::$xoopsDB->fetchArray($result)) {
					$content_count += $row['COUNT(DISTINCT `item`, `iid`)'];
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
					. icms::$xoopsDB->escape($handlers[$it]->table) . ".title,"
					. icms::$xoopsDB->escape($handlers[$it]->table) . ".description,"
					. "`creator`," // need to standardise this across modules, some use $user
					. "`counter`,"
					. icms::$xoopsDB->escape($handlers[$it]->table) . ".short_url,"
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
					. icms::$xoopsDB->escape($this->table) . ".mid,"
					. "`tid`";
				$sql .= " FROM " . icms::$xoopsDB->escape($this->table) . " INNER JOIN " 
						. icms::$xoopsDB->escape($handlers[$it]->table) . " ON "
						. icms::$xoopsDB->escape($this->table) . ".iid  = "
						. icms::$xoopsDB->escape($handlers[$it]->table) . "." . $it . "_id";
				if (!$untagged_content) {
					$sql .= " INNER JOIN " . icms::$xoopsDB->escape($sprockets_tag_handler->table)
					. " ON " . icms::$xoopsDB->escape($this->table) . ".tid = " 
					. icms::$xoopsDB->escape($sprockets_tag_handler->table) . ".tag_id";
				}
				$sql .= " WHERE " . icms::$xoopsDB->escape($this->table) . ".iid  = " 
						. icms::$xoopsDB->escape($handlers[$it]->table) . "." . $it . "_id";
				$sql .= " AND " . icms::$xoopsDB->escape($this->table) . ".item = '" . $it . "'";		
				if ($untagged_content) {
					$sql .= " AND " . icms::$xoopsDB->escape($this->table) . ".tid = '0'";
				} elseif ($tag_id) {
					$sql .= " AND (" . icms::$xoopsDB->escape($this->table) . ".tid = " . "'" 
						. $tag_id . "' AND " . icms::$xoopsDB->escape($sprockets_tag_handler->table)
						. ".label_type = '0')";
				} else {
					$sql .= " AND " . icms::$xoopsDB->escape($sprockets_tag_handler->table) 
						. ".label_type = '0'";
				}
				if ($module_id) {
					if ($untagged_content || $tag_id) {
						$sql .= " AND";
					}
					$sql .= " `mid` = '" . $module_id . "'";
				}
				$sql .= " AND `online_status` = '1'";
				if (!$tag_id) {
					$sql .= " GROUP BY `item`, `iid`";
				}
				$sql .= ")";
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
					echo 'Error4';
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
	
	private function _getUntaggedContent($module_id, $item_type, $start, $limit, $sort) {
		return $this->_getTaggedItems('untagged', $module_id, $item_type, $start, $limit, $sort);
	}
	
	private function _storeTagsForObject(&$obj, $tag_var, $label_type) {		
		// Remove existing taglinks prior to saving the updated set
		$this->_deleteTagsForObject($obj, $label_type);
		
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
	
	private function _deleteTagsForObject(&$obj, $label_type) {
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

	private function _deleteAllForObject(&$obj) {
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