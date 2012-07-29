<?php

/**
* Classes responsible for managing Sprockets Tag Handler objects
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

class SprocketsTagHandler extends icms_ipf_Handler {

	/**
	 * Constructor
	 */
	public function __construct(& $db) {
		parent::__construct($db, 'tag', 'tag_id', 'title', 'description',
			'sprockets');
	}

	/**
	 * Returns the name of a tag
	 *
	 * @param int $tag_id
	 * @return string
	 */

	function getTagName($tag_id) {
    	$icms_persistable_registry_handler = icms_ipf_registry_Handler::getInstance();
    	$tagObj = $icms_persistable_registry_handler->getSingleObject('tag', $tag_id, 'sprockets');
    	if ($tagObj && !$tagObj->isNew()) {
    		return $tagObj->getVar('title');
    	} else {
    		return FALSE;
    	}
    }

	/**
	 * Returns a list of tags with tag_id as key
	 *
	 * @return array
	 */

	public function getTags() {
		$criteria = icms_buildCriteria(array('label_type' => '0'));
		$tagList = array(0 => '---') + $this->getList($criteria);
		asort($tagList);
		return $tagList;
	}

	/**
	 * Returns a select box containing the category tree
	 *
	 * @param int $selected
	 * @return string $parentCategoryOptions
	 */

	public function getCategoryOptions($selected = '') {
		include_once ICMS_ROOT_PATH . '/modules/sprockets/include/angry_tree.php';
		
		/////////////////////////////////////////////////
		//////////////////// CAUTION ////////////////////
		/////////////////////////////////////////////////
		
		// Detect the module that this method is *called* from, so that module-specific category
		// trees can be constructed. Module ID (mid) is used as a criteria to filter the results. 
		// It relies on the fact that the global $icmsModule contains data about the current 
		// (ie. calling) module, rather than the resident module for the code. Seems to work - but 
		// need to keep an eye on this for a while to make sure it is robust.
		
		global $icmsModule;
		$module_id = $icmsModule->getVar('mid');
		$dirname = $icmsModule->getVar('dirname');
		
		/////////////////////////////////////////////////
		/////////////////////////////////////////////////
		
		$categoryTree = $parentCategoryOptions = '';
		$categoryObjArray = array();
		
		// Select those that have category status (label_type = 1) and optionally filter by module.
		// If the calling module is actually Sprockets (which manages global categories), then do 
		// not use module ID as a filter, because global categories (those generated within the 
		// Sprockets module itself) are not assigned a module ID. This is because if you are ever
		// forced to reinstall Sprockets, it will probably have a different module ID the second 
		// time and none of your global categories would be detected.
		
		if ($dirname == 'sprockets')
		{
			$module_id = 'NULL';
		}
		$criteria = icms_buildCriteria(array('label_type' => '1', 'mid' => $module_id));
		$categoryObjArray = $this->getObjects($criteria);
		
		//IcmsPersistableTree(&$objectArr, $myId, $parentId, $rootId = null)
		$categoryTree = new IcmsPersistableTree($categoryObjArray, 'tag_id', 'parent_id');
		$parentCategoryOptions = $categoryTree->makeSelBox('parent', 'title');

		return $parentCategoryOptions;
	}

	/**
	 * Returns a select box of available tags, optionally filtered by module (ie. tags in use)
	 *
	 * @param string $action page to load on submit
	 * @param int $selected
	 * @param string $zero_option_message
	 * @return string $form
	 */
	
	public function getTagSelectBox($action, $selected = null, $zero_option_message = '---',
			$navigation_elements_only = TRUE, $module_id = null, $item = null) {

		$form = $criteria = '';
		$tagList = $tag_ids = array();
		
		// Sanitise parameters used to build query in case a client module passes in bad data
		$clean_module_id = isset($module_id) ? intval($module_id): 0 ;
		$clean_item = mysql_real_escape_string($item);
		
		if ($navigation_elements_only) {
			$criteria = icms_buildCriteria(array('label_type' => '0', 'navigation_element' => '1'));
		} else {
			$criteria = icms_buildCriteria(array('label_type' => '0'));
		}
		
		$tagList = $this->getList($criteria);
		
		if ($module_id) {

			// Only display tags that contain content relevant to this module. Note: Tags
			// containing offline content will still be displayed. This can change later if
			// there is agreement on standardising existing module fields with Sprockets
			$sprockets_taglink_handler = icms_getModuleHandler('taglink', 
					basename(dirname(dirname(__FILE__))), 'sprockets');
			
			$query = $rows = $tag_ids = '';
			$query = "SELECT DISTINCT `tid` FROM " . $sprockets_taglink_handler->table
					. " WHERE `mid` = '" . $clean_module_id . "'";
			if ($clean_item) {
					$query .= " AND `item` = '" . $clean_item . "'";
			}
			
			$result = icms::$xoopsDB->query($query);
			if (!$result) {
				echo 'Error';
				exit;

			} else {
				$rows = $sprockets_taglink_handler->convertResultSet($result);
				foreach ($rows as $key => $row) {
					$tag_ids[] = $row->getVar('tid');
				}
				// remove empty tags
				if (empty($tag_ids)) {
					$tagList = '';
				} else {
					// Flip the array so we can use the IDs to check for matching keys in the master $tagList
					$tagList =  array(0 => $zero_option_message) + array_intersect_key($tagList, array_flip($tag_ids));
				}
			}
		}		

		if (!empty($tagList)) {
			$form = '<div><form name="tag_selection_form" action="' . $action . '" method="get">';
			$form .= '<select name="tag_id" id="tag_id" onchange="this.form.submit()">';
			foreach ($tagList as $key => $value) {
				if ($key == $selected) {
				$form .= '<option value="' . $key . '" selected="selected">' . $value . '</option>';
				} else {
					$form .= '<option value="' . $key . '">' . $value . '</option>';
				}
			}
			$form .= '</select></form></div>';

			return $form;
			
		} else {

			return FALSE;
		}
	}
	
	/**
	 * Returns a select box of available categories, optionally filtered by module
	 *
	 * @param string $action page to load on submit
	 * @param int $selected
	 * @param string $zero_option_message
	 * @return string $form
	 */
	
	public function getCategorySelectBox($action, $selected = null, $zero_option_message = '---',
			$module_id = null, $item = null) 
	{	
		$clean_module_id = isset($module_id) ? intval($module_id): null ;
		$clean_item = mysql_real_escape_string($item);
		
		$categoryList = $this->getCategoryOptions();
		if (!empty($categoryList)) {
			$form = '<div><form name="tag_selection_form" action="' . $action . '" method="get">';
			$form .= '<select name="tag_id" id="tag_id" onchange="this.form.submit()">';
			foreach ($categoryList as $key => $value) {
				if ($key == $selected) {
				$form .= '<option value="' . $key . '" selected="selected">' . $value . '</option>';
				} else {
					$form .= '<option value="' . $key . '">' . $value . '</option>';
				}
			}
			$form .= '</select><input type="hidden" value="1" name="label_type" /></form></div>';

			return $form;
			
		} else {

			return FALSE;
		}
	}
	
	/**
	 * Checks if the given tag (category) is a parent
	 * @param type $id
	 * @return type 
	 */
	public function check_is_parent($id) {
		$is_parent = FALSE;
		$criteria = icms_buildCriteria(array('parent_id' => $id, 'label_type' => '1'));
		$is_parent = $this->getList($criteria);
		if ($is_parent) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns an array of parent category titles for a given list of tag objects, optionally with links
	 * 
	 * Use to build buffer to reduce DB lookups when parsing multiple tag objects
	 *
	 * @param array $tag_object_array
	 * @return array 
	 */
	public function get_parent_id_buffer($tag_object_array, $with_links = FALSE) {
		
		$parent_id_string = '';
		$parent_objects = $parent_buffer = array();
		
		foreach ($tag_object_array as $key => $tagObj) {
			
			$parent_objects[] = $tagObj->getVar('parent_id', 'e');  
		}
		
		$parent_id_string = "(" . implode(',', $parent_objects);
		
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('tag_id', $parent_id_string, 'IN'));
		
		if ($with_links) {
			$parent_objects = $this->getObjects($criteria, TRUE, TRUE);
			foreach ($parent_objects as $parent) {
				$parent_buffer[$parent->id()] = $parent->getItemLink();
			}
		} else {
			$parent_objects = $this->getList($criteria);
		}
		
		return $parent_buffer;
	}

	/**
	 * Allows the category admin page to be filtered by module
	 * 
	 * @return array
	 */
	public function module_filter()
	{
		$moduleList = array();
		
		// Get a unique list of module IDs that have created categories
		/*$icmsDB = icms::$xoopsDB;
		$sprockets_tag_handler = icms_getModuleHandler('tag', basename(dirname(dirname(__FILE__))), 'sprockets');		
		$sql = "SELECT DISTINCT `mid` from " . $sprockets_tag_handler->table . " WHERE `label_type` = '1'";
		$result = icms::$xoopsDB->query($sql);
		if (!$result)
		{
			echo 'Error: Module filter failed';
			exit;
		}
		else
		{
			$rows = $sprockets_tag_handler->convertResultSet($result);
			foreach ($rows as $key => $value)
			{
				$moduleList[] = $value->getVar('mid');
			}
			$moduleList = " (" . implode(',', $moduleList) . ") ";
		}*/
		
		// Get a list of modules installed on the system, module id as key
		$module_handler = icms::handler('icms_module');
		//$criteria = new icms_db_criteria_Compo();
		//$criteria->add(new icms_db_criteria_Item('mid', $moduleList, 'IN'));
		$moduleList = $module_handler->getList($criteria = NULL, FALSE);
				
		// Return the list
		return $moduleList;
	}
	
	/**
	 * Allows the tag/category admin page to be filtered by those with/without navigation element status
	 * 
	 * @return array
	 */
	public function navigation_element_filter() {
		return array(0 => _CO_SPROCKETS_TAG_NO, 1 => _CO_SPROCKETS_TAG_YES);
	}
	
	/**
	 * Allows the tag/category admin page to be filtered by those with/without rss feeds
	 * 
	 * @return array
	 */
	public function rss_filter() {
		return array(0 => _CO_SPROCKETS_TAG_RSS_DISABLED, 1 => _CO_SPROCKETS_TAG_RSS_ENABLED);
	}
	
	/**
	 * Returns an array of tag objects that have RSS feeds enabled
	 *
	 * @return array $tag_object_array
	 */

	public function getTagsWithRss() {
		
		$criteria = new icms_db_criteria_Compo();
		//$criteria->add(new icms_db_criteria_Item('label_type', '1', '!='));
		$criteria->add(new icms_db_criteria_Item('rss', '1'));
		
		$tag_object_array = $this->getObjects($criteria, TRUE, TRUE);
		
		return $tag_object_array;
	}
	
	/**
	 * Toggles a yes/no field on or offline
	 *
	 * @param int $id
	 * @param str $field
	 * @return int $status
	 */
	public function toggleStatus($id, $field) {
		
		$status = $obj = '';
		
		$obj = $this->get($id);
		if ($obj->getVar($field, 'e') == TRUE) {
			$obj->setVar($field, 0);
			$status = 0;
		} else {
			$obj->setVar($field, 1);
			$status = 1;
		}
		$this->insert($obj, TRUE);
		
		return $status;
	}
		
	/**
	 * Currently not in use.
	 *
	 * @param object $obj SprocketsTag object
	 * @return bool
	 */
	protected function afterSave(& $obj) {
		return TRUE;
	} 

	/**
	 * Cleans up after category deletion: Deletes child categories and associated taglink objects
	 *
	 * @param object $obj
	 * @return bool
	 */

	protected function afterDelete(&$obj) {
		
		include_once ICMS_ROOT_PATH . '/modules/sprockets/include/angry_tree.php';
		
		// initialise
		$tag_id = $categoryTree = $markedForDeletion = '';
		$categoryObjArray = $allChildCategories = array();
		$criteria = new icms_db_criteria_Compo();
		$sprockets_tag_handler = icms_getModuleHandler('tag',
			basename(dirname(dirname(__FILE__))), 'sprockets');
		$sprockets_taglink_handler = icms_getModuleHandler('taglink',
			basename(dirname(dirname(__FILE__))), 'sprockets');

		/*
		 * Deleting a parent category also kills the category subtree underneath it. Content is not
		 * affected. Any item that no longer has a tag or category become part of the 'untagged'
		 * collection.
		 */
		
		// exclude labels that are only tags (not categories)
		$criteria->add(new icms_db_criteria_Item('label_type', 0, '!='));
		$categoryObjArray = $sprockets_tag_handler->getObjects($criteria);

		// get a category tree
		$categoryTree = new IcmsPersistableTree($categoryObjArray, 'tag_id', 'parent_id');

		$markedForDeletion = '(' . $obj->id();
		
		// check if the category has any children
		$allChildCategories = $categoryTree->getAllChild($obj->id());
		if (count($allChildCategories) > 0) {

			// build a list of category IDs scheduled for deletion
			foreach ($allChildCategories as $child) {
				$markedForDeletion .= ',' . $child->id();
			}
		}
		
		// delete the category and subtree if there is one
		$markedForDeletion .= ')';

		$criteria->add(new icms_db_criteria_Item('tag_id', $markedForDeletion, 'IN'));
		$sprockets_tag_handler->deleteAll($criteria);

		// delete associated taglinks
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('tid', $markedForDeletion, 'IN'));
		$sprockets_taglink_handler->deleteAll($criteria);
		
		return TRUE;
	}
}