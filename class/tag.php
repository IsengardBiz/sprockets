<?php

/**
* Classes responsible for managing Sprockets tag objects
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
include_once ICMS_ROOT_PATH . '/kernel/icmspersistableseoobject.php';
include_once(ICMS_ROOT_PATH . '/modules/sprockets/include/functions.php');

class SprocketsTag extends IcmsPersistableSeoObject {
	/**
	 * Constructor
	 *
	 * @param object $handler SprocketsPostHandler object
	 */
	public function __construct(& $handler) {
		global $icmsConfig, $sprocketsConfig;

		$this->IcmsPersistableObject($handler);

		$this->quickInitVar('tag_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('label_type', XOBJ_DTYPE_INT, true, false, false, 0);
		$this->quickInitVar('title', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('parent_id', XOBJ_DTYPE_INT, false, false, false, 0);
		$this->quickInitVar('description', XOBJ_DTYPE_TXTAREA, false);
		$this->quickInitVar('icon', XOBJ_DTYPE_IMAGE, false);
		$this->quickInitVar('navigation_element', XOBJ_DTYPE_INT, false, false, false, 1);
		$this->quickInitVar('rss', XOBJ_DTYPE_INT, true, false, false, 1);

		$this->setControl('label_type', array(
			'name' => 'select',
			'itemHandler' => 'tag',
			'method' => 'getLabelTypeOptions',
			'module' => 'sprockets',
			'onSelect' => 'submit'));
		
		// this control is a category tree select box
        $this->setControl('parent_id', array(
			'type' => 'select',
			'itemHandler' => 'tag',
			'method' => 'getCategoryOptions',
			'module' => 'sprockets'));
		
		// Add WYSIWYG editor to description field
		$this->setControl('description', 'dhtmltextarea');
		$this->setControl('navigation_element', 'yesno');
		$this->setControl('rss', 'yesno');
		$this->setControl('icon', array('name' => 'image'));
		$url = ICMS_URL . '/uploads/' . basename(dirname(dirname(__FILE__))) . '/';
		$path = ICMS_ROOT_PATH . '/uploads/' . basename(dirname(dirname(__FILE__))) . '/';
		$this->setImageDir($url, $path);

		$this->IcmsPersistableSeoObject();
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
		if ($format == 's' && in_array($key, array ('label_type', 'parent_id', 'icon',
			'navigation_element', 'rss'))) {
			return call_user_func(array ($this,	$key));
		}
		return parent :: getVar($key, $format);
	}

	/**
	 * Converts label_type to a human readable value (tag, category or both)
	 *
	 * @return string
	 */

	public function label_type() {

		$label_type = $this->getVar('label_type', 'e');
		
		switch ($label_type) {

			case "0":
				return _CO_SPROCKETS_TAG_TAG;
			case "1":
				return _CO_SPROCKETS_TAG_CATEGORY;
			case "2":
				return _CO_SPROCKETS_TAG_BOTH;
		}
	}
	
	public function parent_id() {
		
		$parent_id = $this->getVar('parent_id', 'e');
		
		if ($parent_id) {
			
			$sprockets_tag_handler = icms_getModuleHandler('tag', basename(dirname(dirname(__FILE__))),
					'sprockets');

			$parentObj = $sprockets_tag_handler->get($parent_id);

			return $parentObj->title();
		}
		return false;
	}
	
	/**
	 * Converts navigation_element into a human readable icon (yes/no)
	 *
	 * @return string
	 */
	public function navigation_element() {
		
		$navigation_element = $button = '';
		
		$navigation_element = $this->getVar('navigation_element', 'e');
		$button = '<a href="' . ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/admin/tag.php?tag_id=' . $this->id() . '&amp;op=toggleNavigationElement">';
		if ($navigation_element == false) {
			$button .= '<img src="../images/button_cancel.png" alt="'
				. _CO_SPROCKETS_TAG_NAVIGATION_DISABLED 
				. '" title="' . _CO_SPROCKETS_TAG_NAVIGATION_ENABLE . '" /></a>';
		} else {
			$button .= '<img src="../images/button_ok.png" alt="'
				. _CO_SPROCKETS_TAG_NAVIGATION_ENABLED
				. '" title="' . _CO_SPROCKETS_TAG_NAVIGATION_DISABLE . '" /></a>';
		}
		return $button;
	}

	/**
	 * Converts the icon name into a html <image /> tag for display
	 *
	 * @return mixed
	 */

	public function icon() {
		$icon = $this->getVar('icon', 'e');
		$title = $this->getVar('title', 'e');
		$path = $this->getImageDir();
		if (!empty($icon)) {
			return '<img src="' . $path . $icon . '" alt="' . $this->getVar('title', 'e')
			. '" title="' . $this->getVar('title', 'e') . '" />';
		} else {
			return false;
		}
	}
	
	/**
	 * Returns an icon indicating RSS feed status (online/offline) with link toggle
	 *
	 * @return string 
	 */
	public function rss() {
		$status = $this->getVar('rss', 'e');

		$button = '<a href="' . ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/admin/tag.php?tag_id=' . $this->id() . '&amp;op=toggleStatus">';
		if ($status == false) {
			$button .= '<img src="../images/button_cancel.png" alt="' . _CO_SPROCKETS_TAG_OFFLINE 
			. '" title="' . _CO_SPROCKETS_TAG_SWITCH_ONLINE . '" /></a>';
		} else {
			$button .= '<img src="../images/button_ok.png" alt="' . _CO_SPROCKETS_TAG_ONLINE
			. '" title="' . _CO_SPROCKETS_TAG_SWITCH_OFFLINE . '" /></a>';
		}
		return $button;
	}
	
	/**
	 * Returns a html snippet containing an RSS icon and link to the feed URL for a given tag
	 * 
	 * @param	int $clean_tag_id
	 * @return	string 
	 */
	public function getRssFeedLink() {
			
		$rss_snippet = '<a href="' . ICMS_URL .
			'/modules/' . basename(dirname(dirname(__FILE__)))
			. '/rss.php?tag_id=' . $this->id() . '" title="' . _CO_SPROCKETS_SUBSCRIBE_RSS_ON 
			. $this->getVar('title', 'e') . '"><img src="' . ICMS_URL . '/modules/' 
			. basename(dirname(dirname(__FILE__))) . '/images/rss.png" alt="' . _CO_SPROCKETS_RSS
			. '" /></a>';
		
		return $rss_snippet;		
	}
}
	
class SprocketsTagHandler extends IcmsPersistableObjectHandler {

	/**
	 * Constructor
	 */
	public function __construct(& $db) {
		$this->IcmsPersistableObjectHandler($db, 'tag', 'tag_id', 'title', 'description',
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
		$criteria = new CriteriaCompo();
		
		// select those that have category status (label_type = 1 (category) or 2 (both))
		$criteria->add(new Criteria('label_type', 0, '!='));
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
			$navigation_elements_only = true) {
		
		$form = $criteria = '';
		$tagList = array();

		if ($navigation_elements_only) {
			$criteria = icms_buildCriteria(array('navigation_element' => true));
		}

		$tagList = array(0 => $zero_option_message) + $this->getList($criteria);

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
		
		$criteria = new CriteriaCompo();
		$criteria->add(new Criteria('tag_id', $parent_id_string, 'IN'));
		
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
		
		$criteria = new CriteriaCompo();
		//$criteria->add(new Criteria('label_type', '1', '!='));
		$criteria->add(new Criteria('rss', '1'));
		
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
			$criteria = new CriteriaCompo();
			
			// get handlers
			$sprockets_tag_handler = icms_getModuleHandler('tag',
				basename(dirname(dirname(__FILE__))), 'sprockets');
			$sprockets_taglink_handler = icms_getModuleHandler('taglink',
				basename(dirname(dirname(__FILE__))), 'sprockets');
			
			// read out all categories into an array
			$criteria->add(new Criteria('label_type', 0, '!='));
			$allCategoryArray = $sprockets_tag_handler->getObjects($criteria);
			
			// check if there are any categories that identify this tag as their parent. If there 
			// are, that indicates that this tag was converted from a category, and some cleanup is 
			// in order
			
			$criteria->add(new Criteria('parent_id', $obj->id()));
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
				$criteria = new CriteriaCompo();
				$criteria->add(new Criteria('tag_id', $markedForDeletion, 'IN'));
				$sprockets_tag_handler->deleteAll($criteria);
				
				// delete 'category only' related taglinks
				$criteria = new CriteriaCompo();
				$criteria->add(new Criteria('tid', $markedForDeletion, 'IN'));
				$sprockets_taglink_handler->deleteAll($criteria);

				// update 'category/tag' labels to be just tags
				if (!empty($markedForUpdate)) {
					
					//icms::$xoopsDB;
					global $xoopsDB;
					
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
		$criteria = new CriteriaCompo();
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
		$criteria->add(new Criteria('label_type', 0, '!='));
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

		$criteria->add(new Criteria('tag_id', $markedForDeletion, 'IN'));
		$sprockets_tag_handler->deleteAll($criteria);

		// delete associated taglinks
		$criteria = new CriteriaCompo();
		$criteria->add(new Criteria('tid', $markedForDeletion, 'IN'));
		$sprockets_taglink_handler->deleteAll($criteria);
		
		return true;
	}
}