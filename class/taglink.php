<?php

/**
* Classes responsible for managing Sprockets taglink objects
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

// including the IcmsPersistabelSeoObject
include_once ICMS_ROOT_PATH . '/kernel/icmspersistableobject.php';
include_once(ICMS_ROOT_PATH . '/modules/sprockets/include/functions.php');

class SprocketsTaglink extends IcmsPersistableObject {

	/**
	 * Constructor
	 *
	 * @param object $handler SprocketsPostHandler object
	 */
	public function __construct(& $handler) {
		global $icmsConfig;

		$this->IcmsPersistableObject($handler);

		$this->quickInitVar('taglink_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('tid', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('mid', XOBJ_DTYPE_INT, false);
		$this->quickInitVar('item', XOBJ_DTYPE_TXTBOX, false);
		$this->quickInitVar('iid', XOBJ_DTYPE_INT, true);

	}

	/**
	 * Overriding the IcmsPersistableObject::getVar method to assign a custom method on some
	 * specific fields to handle the value before returning it
	 *
	 * @param str $key key of the field
	 * @param str $format format that is requested
	 * @return mixed value of the field that is requested
	 */
	
	function getVar($key, $format = 's') {
		if ($format == 's' && in_array($key, array ())) {
			return call_user_func(array ($this,	$key));
		}
		return parent :: getVar($key, $format);
	}
	
	/**
	 * Returns a tag object associated with this taglink
	 *
	 * @return object $tagObj
	 */
	public function getTag() {
		
		$sprockets_tag_handler = icms_getModuleHandler('tag', basename(dirname(dirname(__FILE__))),
			'sprockets');
		
		$tagObj = $sprockets_tag_handler->get($this->id());
		
		return $tagObj;
	}
	
	public function getTagId() {
		
		return $this->getVar('tid', 'e');
	}

	/**
	 * Returns the item (object) name associated with this taglink, eg 'article'
	 *
	 * @return string
	 */
	
	public function getItem() {
		
		return $this->getVar('item', 'e');
	}

	/**
	 * Returns the item_id for the linked object
	 *
	 * @return int
	 */
	
	public function getItemId() {

		return $this->getVar('iid', 'e');
	}
	

	/**
	 * Returns the module object for the linked object
	 *
	 * @return object $module
	 */
	
	public function getModuleObject() {
		
		$module_handler = &xoops_gethandler('module');
		$module = &$module_handler->get($this->getVar('mid', 'e'));

		return $module;
	}

	/**
	 * Returns the linked object associated with this taglink
	 *
	 * @return object $contentObj
	 */
	
	public function getLinkedObject() {

		$item = $this->getItem();
		$module = $this->getModuleObject();
		$content_handler = icms_getModuleHandler($this->getItem(), $module->dirname(),
			$module->dirname());
		
		$contentObj = $content_handler->get($this->getItemId());
		
		return $contentObj;
	}
}

class SprocketsTaglinkHandler extends IcmsPersistableObjectHandler {

	/**
	 * Constructor
	 */
	public function __construct(& $db) {
		$this->IcmsPersistableObjectHandler($db, 'taglink', 'taglink_id', 'tid', 'iid', 'sprockets');
	}
	
	/**
	 * Returns a distinct list of module IDs that are currently referenced by taglinks
	 *
	 * @global object $xoopsDB
	 * @return array $module_list
	 */
	
	public function getTaglinkModules() {
		
		global $xoopsDB;
		
		$module_list = array();
		$module_handler = &xoops_gethandler( 'module' );
		
		$criteria = new CriteriaCompo();
		$criteria->add(new Criteria('label_type', '1', '!='));
		
		$sql = "SELECT DISTINCT `mid` FROM " . $this->table;
		
		$rows = $this->query($sql, $criteria);
		foreach ($rows as $key => $mid) {			
			$module_list[$mid] = &$module_handler->get($mid);
		}
		
		return $module_list;
	}

	/**
	 * Returns a distinct list of item types that are currently referenced by taglinks
	 *
	 * @global object $xoopsDb
	 * @return array $item_list
	 */
	
	public function getTaglinkItems() {
		
		global $xoopsDb;
		
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

    	$criteria = new CriteriaCompo();
    	$criteria->add(new Criteria('mid', $moduleObj->mid()));
    	$criteria->add(new Criteria('item', $handler->_itemname));
    	$criteria->add(new Criteria('iid', $iid));
    	$sql = 'SELECT tid FROM ' . $this->table;
    	$rows = $this->query($sql, $criteria);
    	$ret = array();
    	foreach($rows as $row) {
    		$ret[] = $row['tid'];
    	}
    	return $ret;
    }
	
	/**
	 * Returns a list of content objects associated with a specific tag, module, item type
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
	
	public function getTaggedItems($tag_id = false, $module_id = false, $item_type = false,
			$start = false, $limit = false, $sort = 'taglink_id', $order = 'DESC') {

		global $sprocketsConfig;
		
		$content_id_array = $content_object_array = $taglink_object_array = $module_ids
			= $item_types = $module_array = $parent_id_buffer = array();
		
		// get content objects as per the supplied parameters
		$criteria = new CriteriaCompo;
		
		if ($tag_id) {
			$criteria->add(new Criteria('tid', $tag_id));
		}
		
		if ($module_id) {
			$criteria->add(new Criteria('mid', $module_id));
		}
		
		if ($item_type) {
			$criteria->add(new Criteria('item', $item_type));
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
		
		// get the required module objects
		
		if($module_id) {
			$module_ids[] = $module_id;
		} else {
			foreach ($taglink_object_array as $key => $taglink) {
				$module_ids[] = $taglink->getVar('mid');
			}
			$module_ids = array_unique($module_ids);
		}
		if ($item_type) {
			$item_types[] = $item_type;
		} else {
			foreach ($taglink_object_array as $taglink) {
				$item_types[] = $taglink->getItem();
			}
			$item_types = array_unique($item_types);
		}
		
		foreach ($module_ids as $key => $mid) {
			$module_handler = &xoops_gethandler('module');
			$module_array[$mid] = &$module_handler->get($mid);
			$taglinks_by_module[$mid] = array();
		}
		
		// sort the taglinks to facilitate processing: taglinks_by_module[module_id][item][iid]
		foreach ($taglink_object_array as $key => $taglink) {
			if (!array_key_exists($taglink->getItem(), $taglinks_by_module[$taglink->getVar('mid')])) {
				$taglinks_by_module[$mid][$taglink->getItem()] = array();
			}
			$taglinks_by_module[$taglink->getVar('mid')][$taglink->getItem()][$taglink->id()] = $taglink;
		}
		
		// get handler for each module/item type, build query string and retrieve content objects	
		foreach ($module_array as $key => $moduleObj) {
			
			foreach ($taglinks_by_module[$key] as $module_key => $item_array) {
				
				$item_id = $item_string = '';
				$content_objects = array();
				$criteria = new CriteriaCompo();

				foreach ($item_array as $item_key => $taglink) {
						
					$id_string[] = $taglink->getVar('iid');
				}

				$item_id = $module_key . '_id';
				$id_string = "(" . implode(",", $id_string) . ")";

				$criteria->add(new Criteria($item_id, $id_string, 'IN'));
				$criteria->add(new Criteria('online_status', '1'));

				$content_handler = icms_getModuleHandler($module_key, $moduleObj->dirname(),
						$moduleObj->dirname());
	
				$content_objects = $content_handler->getObjects($criteria);

				$content_object_array = $content_object_array + $content_objects;
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
	 * Saves tags for an object by creating taglinks
	 *
	 * Based on code from ImTagging: author marcan aka Marc-André Lanciault <marcan@smartfactory.ca>
	 * 
	 * @param object $obj
	 * @param string $tag_var
	 */

	public function storeTagsForObject(&$obj, $tag_var='tag') {
		/**
		 * @todo: check if tags have changed and if so don't do anything
		 */

		// delete all current tags linked to this object
		$this->deleteAllForObject($obj);

		$moduleObj = icms_getModuleInfo($obj->handler->_moduleName);
		$tag_array = $obj->getVar($tag_var);
		
		if (count($tag_array) > 0) {

			foreach($tag_array as $tag) {
				$taglinkObj = $this->create();
				$taglinkObj->setVar('mid', $moduleObj->mid());
				$taglinkObj->setVar('item', $obj->handler->_itemname);
				$taglinkObj->setVar('iid', $obj->id());
				$taglinkObj->setVar('tid', $tag);
				$this->insert($taglinkObj);
			}
		}
    }

	// this funcbased on code from ImTagging

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
    	$mid = $moduleObj->mid();

		$criteria = new CriteriaCompo();
		$criteria->add(new Criteria('mid', $mid));
		$criteria->add(new Criteria('item', $obj->handler->_itemname));
		$criteria->add(new Criteria('iid', $obj->id()));

		$this->deleteAll($criteria);
	}
}