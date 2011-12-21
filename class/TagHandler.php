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
    	$icms_persistable_registry = IcmsPersistableRegistry::getInstance();
    	$tagObj = $icms_persistable_registry->getSingleObject('tag', $tag_id, 'sprockets');
    	if ($tagObj && !$tagObj->isNew()) {
    		return $tagObj->getVar('title');
    	} else {
    		return false;
    	}
    }

	/**
	 * Returns a list of tags with tag_id as key
	 *
	 * @return array
	 */

	public function getTags() {
		$tagList = $this->getList();
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
		
		$categoryTree = $parentCategoryOptions = '';
		$categoryObjArray = array();
		$criteria = new icms_db_criteria_Compo();
		
		// select those that have category status (label_type = 1 (category) or 2 (both))
		$criteria->add(new icms_db_criteria_Item('label_type', 0, '!='));
		$categoryObjArray = $this->getObjects($criteria);
		
		//IcmsPersistableTree(&$objectArr, $myId, $parentId, $rootId = null)
		$categoryTree = new IcmsPersistableTree($categoryObjArray, 'tag_id', 'parent_id');
		$parentCategoryOptions = $categoryTree->makeSelBox('parent', 'title');

		return $parentCategoryOptions;
	}

	/**
	 * Returns a list of label type options (tag, category, both) used to build select box
	 *
	 * @return array
	 */

	public function getLabelTypeOptions() {
		return array(0 => _CO_SPROCKETS_TAG_TAG, 1 => _CO_SPROCKETS_TAG_CATEGORY, 
			2 => _CO_SPROCKETS_TAG_BOTH);
	}

	/**
	 * Returns a select box of available tags
	 *
	 * @param string $action page to load on submit
	 * @param int $selected
	 * @param string $zero_option_message
	 * @return string $form
	 */
	
	public function getTagSelectBox($action, $selected = null, $zero_option_message = '---',
			$navigation_elements_only = true, $module_id = null, $item = null) {

		$form = $criteria = '';
		$tagList = $tag_ids = array();
		
		if ($navigation_elements_only) {
			$criteria = icms_buildCriteria(array('navigation_element' => TRUE));
		}
		
		$tagList = array(0 => $zero_option_message) + $this->getList($criteria);
		
		if ($module_id) {
			// Only display tags that contain content relevant to this module. Note: Tags
			// containing offline content will still be displayed. This can change later if
			// there is agreement on standardising existing module fields with Sprockets
			$sprockets_taglink_handler = icms_getModuleHandler('taglink', 
					basename(dirname(dirname(__FILE__))), 'sprockets');
			
			$query = $rows = $tag_ids = '';
			$query = "SELECT DISTINCT `tid` FROM " . $sprockets_taglink_handler->table
					. " WHERE `mid` = '" . $module_id . "'";
			if ($item) {
					$query .= " AND `item` = '" . $item . "'";
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
					$tagList = array_intersect($tagList, $tag_ids);
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
			
			return false;
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
	public function get_parent_id_buffer($tag_object_array, $with_links = false) {
		
		$parent_id_string = '';
		$parent_objects = $parent_buffer = array();
		
		foreach ($tag_object_array as $key => $tagObj) {
			
			$parent_objects[] = $tagObj->getVar('parent_id', 'e');  
		}
		
		$parent_id_string = "(" . implode(',', $parent_objects);
		
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('tag_id', $parent_id_string, 'IN'));
		
		if ($with_links) {
			$parent_objects = $this->getObjects($criteria, true, true);
			foreach ($parent_objects as $parent) {
				$parent_buffer[$parent->id()] = $parent->getItemLink();
			}
		} else {
			$parent_objects = $this->getList($criteria);
		}
		
		return $parent_buffer;
	}

	/**
	 * Adds a label_type filter to the admin side tag table
	 *
	 * @return array
	 */

	public function label_type_filter() {
		return $this->getLabelTypeOptions();
	}
	
	public function navigation_element_filter() {
		return array(0 => _CO_SPROCKETS_TAG_NO, 1 => _CO_SPROCKETS_TAG_YES);
	}
	
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
		
		$tag_object_array = $this->getObjects($criteria, true, true);
		
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
		if ($obj->getVar($field, 'e') == true) {
			$obj->setVar($field, 0);
			$status = 0;
		} else {
			$obj->setVar($field, 1);
			$status = 1;
		}
		$this->insert($obj, true);
		
		return $status;
	}
		
	/**
	 * Performs maintenance on categories and taglinks if a tag is changed
	 *
	 * @param object $obj SprocketsTag object
	 * @return bool
	 */
	protected function afterSave(& $obj) {
		
		/**
		 * Category to tag conversion: If a category/both is converted to a 'tag only' it is removed
		 * from the category tree. Any child categories and related taglinks are therefore deleted.
		 * The way to test if conversion has occurred is to check if other categories have this tag
		 * set as their parent_id
		 */
		
		// check if this label is a tag-only type (label_type = 0)
		if ($obj->getVar('label_type', 'e') == '0') {
			
			// initialise
			$firstChildCategoryArray = $allChildCategories = $allCategoryArray = $markedForDeletion 
				= $markedForUpdate = array();
			$categoryTree = $sql = '';
			$criteria = new icms_db_criteria_Compo();
			
			// get handlers
			$sprockets_tag_handler = icms_getModuleHandler('tag',
				basename(dirname(dirname(__FILE__))), 'sprockets');
			$sprockets_taglink_handler = icms_getModuleHandler('taglink',
				basename(dirname(dirname(__FILE__))), 'sprockets');
			
			// read out all categories into an array
			$criteria->add(new icms_db_criteria_Item('label_type', 0, '!='));
			$allCategoryArray = $sprockets_tag_handler->getObjects($criteria);
			
			// check if there are any categories that identify this tag as their parent. If there 
			// are, that indicates that this tag was converted from a category, and some cleanup is 
			// in order
			
			$criteria->add(new icms_db_criteria_Item('parent_id', $obj->id()));
			$firstChildCategoryArray = $sprockets_tag_handler->getObjects($criteria);
			
			if (count($firstChildCategoryArray) > 0) {
				
				// get a category tree
				include_once ICMS_ROOT_PATH . '/modules/sprockets/include/angry_tree.php';
				$categoryTree = new IcmsPersistableTree($allCategoryArray, 'tag_id', 'parent_id');
				
				// compile a list of subordinate categories that need to be deleted or updated
				$allChildCategories = $categoryTree->getAllChild($obj->id());
				foreach ($allChildCategories as $key => $child) {
					
					// sort the children into i) 'category only' and ii) 'category/tag' labels
					if ($child->getVar('label_type', 'e') == 1) {
						$markedForDeletion[] = $child->id();
					} elseif ($child->getVar('label_type', 'e') == 2) {
						$markedForUpdate[] = $child->id();
					}
				}
				
				$markedForDeletion = "('" . implode("','", $markedForDeletion) . "')";
				$markedForUpdate = "('" . implode("','", $markedForUpdate) . "')";

				// delete 'category only' subcategories
				$criteria = new icms_db_criteria_Compo();
				$criteria->add(new icms_db_criteria_Item('tag_id', $markedForDeletion, 'IN'));
				$sprockets_tag_handler->deleteAll($criteria);
				
				// delete 'category only' related taglinks
				$criteria = new icms_db_criteria_Compo();
				$criteria->add(new icms_db_criteria_Item('tid', $markedForDeletion, 'IN'));
				$sprockets_taglink_handler->deleteAll($criteria);

				// update 'category/tag' labels to be just tags
				if (!empty($markedForUpdate)) {
					
					$sql = "UPDATE " . $sprockets_tag_handler->table
						. " SET `label_type` = '0', `parent_id` = '0' WHERE `tag_id` IN "
						. $markedForUpdate;
					$result = $sprockets_tag_handler->db->query($sql);
					if (!$result) {
						$obj->setErrors($sprockets_tag_handler->db->error());
						return false;
					}
				}
			}
		}
		
		return true;
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
		 * Deleting a parent category also kills the category subtree underneath it. Items that
		 * are both tags and categories will be remarked as tags only. Content is not affected.
		 * Any item that no longer has a tag or category become part of the 'untagged' collection.
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
		
		return true;
	}
}