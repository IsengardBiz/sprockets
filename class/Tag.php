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

		parent::__construct($handler);

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

			return $parentObj->getVar('title');
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