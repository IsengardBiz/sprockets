<?php

/**
* Class respresenting Sprockets tag objects
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

class SprocketsTag extends icms_ipf_seo_Object {
	/**
	 * Constructor
	 *
	 * @param object $handler SprocketsPostHandler object
	 */
	public function __construct(& $handler) {
		global $icmsConfig, $sprocketsConfig;

		icms_ipf_object::__construct($handler);

		$this->quickInitVar('tag_id', XOBJ_DTYPE_INT, TRUE);
		$this->quickInitVar('label_type', XOBJ_DTYPE_INT, TRUE, FALSE, FALSE, 0);
		$this->quickInitVar('mid', XOBJ_DTYPE_INT, FALSE);
		$this->quickInitVar('title', XOBJ_DTYPE_TXTBOX, TRUE);
		$this->quickInitVar('parent_id', XOBJ_DTYPE_INT, FALSE, FALSE, FALSE, 0);
		$this->quickInitVar('description', XOBJ_DTYPE_TXTAREA, FALSE);
		$this->quickInitVar('icon', XOBJ_DTYPE_IMAGE, FALSE);
		$this->quickInitVar('navigation_element', XOBJ_DTYPE_INT, FALSE, FALSE, FALSE, 1);
		$this->quickInitVar('rss', XOBJ_DTYPE_INT, TRUE, FALSE, FALSE, 1);
		
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
		if ($format == 's' && in_array($key, array ('label_type', 'parent_id', 'mid', 'icon',
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
	
	/**
	 * Converts parent_id into a human readable string
	 *
	 * @return string
	 */
	public function parent_id() {
		
		$parent_id = $this->getVar('parent_id', 'e');
		
		if ($parent_id) {
			
			$sprockets_tag_handler = icms_getModuleHandler('tag', basename(dirname(dirname(__FILE__))),
					'sprockets');

			$parentObj = $sprockets_tag_handler->get($parent_id);

			return $parentObj->getVar('title');
		}
		return FALSE;
	}
	
	/**
	 * Converts mid into a human readable icon (yes/no)
	 *
	 * @return string
	 */
	public function mid()
	{
		$mid = $this->getVar('mid', 'e');
		$moduleObj = $moduleName = '';
		
		if ($mid)
		{
			// Get the module object and retrieve its name
			$module_handler = icms::handler('icms_module');
			$moduleObj = $module_handler->get($mid);
			$moduleName = $moduleObj->getVar('name');
		}
		return $moduleName;
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
		if ($navigation_element == FALSE) {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_cancel.png" alt="'
				. _CO_SPROCKETS_TAG_NAVIGATION_DISABLED 
				. '" title="' . _CO_SPROCKETS_TAG_NAVIGATION_ENABLE . '" /></a>';
		} else {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_ok.png" alt="'
				. _CO_SPROCKETS_TAG_NAVIGATION_ENABLED . '" title="'
				. _CO_SPROCKETS_TAG_NAVIGATION_DISABLE . '" /></a>';
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
			return FALSE;
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
		if ($status == FALSE) {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_cancel.png" alt="' 
				. _CO_SPROCKETS_TAG_OFFLINE . '" title="' . _CO_SPROCKETS_TAG_SWITCH_ONLINE . '" /></a>';
		} else {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_ok.png" alt="' 
				. _CO_SPROCKETS_TAG_ONLINE . '" title="' . _CO_SPROCKETS_TAG_SWITCH_OFFLINE . '" /></a>';
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
	
	/**
	 * Alters the category object admin links to point at the category admin page
	 */
	public function category_admin_titles( $moduleDirectory = "sprockets" )
	{
		$title = $this->getVar('title', 'e');
		$title = '<a href="' . ICMS_URL . '/modules/' . $moduleDirectory
			. '/admin/category.php?tag_id=' . $this->getVar("tag_id") . '">'
			. $this->getVar("title", "e") . '</a>';
	
		return $title;
	}
	
	/**
	 * Alters the category object navigation_element icon links to point at the category admin page
	 *
	 * @return string
	 */
	public function category_admin_navigation_element( $moduleDirectory = "sprockets" ) {
		
		$navigation_element = $button = '';
		
		$navigation_element = $this->getVar('navigation_element', 'e');
		$button = '<a href="' . ICMS_URL . '/modules/' . $moduleDirectory
				. '/admin/category.php?tag_id=' . $this->id() . '&amp;op=toggleNavigationElement">';
		if ($navigation_element == FALSE) {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_cancel.png" alt="'
				. _CO_SPROCKETS_TAG_NAVIGATION_DISABLED 
				. '" title="' . _CO_SPROCKETS_TAG_NAVIGATION_ENABLE . '" /></a>';
		} else {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_ok.png" alt="'
				. _CO_SPROCKETS_TAG_NAVIGATION_ENABLED . '" title="'
				. _CO_SPROCKETS_TAG_NAVIGATION_DISABLE . '" /></a>';
		}
		return $button;
	}
	
	/**
	 * Alters the category object navigation_element icon links to point at the category admin page
	 *
	 * @return string
	 */
	public function category_admin_rss( $moduleDirectory = "sprockets" )
	{
		$status = $this->getVar('rss', 'e');

		$button = '<a href="' . ICMS_URL . '/modules/' . $moduleDirectory
				. '/admin/category.php?tag_id=' . $this->id() . '&amp;op=toggleStatus">';
		if ($status == FALSE) {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_cancel.png" alt="' 
				. _CO_SPROCKETS_TAG_OFFLINE . '" title="' . _CO_SPROCKETS_TAG_SWITCH_ONLINE . '" /></a>';
		} else {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_ok.png" alt="' 
				. _CO_SPROCKETS_TAG_ONLINE . '" title="' . _CO_SPROCKETS_TAG_SWITCH_OFFLINE . '" /></a>';
		}
		return $button;
	}
	
	/**
	 * Displays a custom edit action button, which links back to the CATEGORY admin page
	 * 
	 * @return mixed
	 */
	public function edit_category_action()
	{
		$button = '';	
		$button = '<a href="category.php?tag_id=' . $this->getVar('tag_id') . '&amp;op=mod">
			<img src="' . ICMS_IMAGES_SET_URL . '/actions/edit.png" alt="' 
			. _AM_SPROCKETS_CATEGORY_EDIT . '" title="' . _AM_SPROCKETS_CATEGORY_EDIT . '" /></a>';

		return $button;
	}
	
	/**
	 * Displays a custom delete action button, which links back to the CATEGORY admin page
	 * 
	 * @return mixed
	 */
	public function delete_category_action()
	{
		$button = '';
		$button = '<a href="category.php?tag_id=' . $this->getVar('tag_id') . '&amp;op=del">
			<img src="' . ICMS_IMAGES_SET_URL . '/actions/editdelete.png" alt="' 
			. _AM_SPROCKETS_CATEGORY_DELETE . '" title="' . _AM_SPROCKETS_CATEGORY_DELETE . '" /></a>';

		return $button;
	}
}