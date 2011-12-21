<?php

/**
* Classes responsible for managing Sprockets rights objects
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

class SprocketsRights extends IcmsPersistableSeoObject {

	/**
	 * Constructor
	 *
	 * @param object $handler SprocketsPostHandler object
	 */
	public function __construct(& $handler) {
		global $icmsConfig;

		$this->IcmsPersistableObject($handler);

		$this->quickInitVar('rights_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('title', XOBJ_DTYPE_TXTBOX, true);
		$this->quickInitVar('description', XOBJ_DTYPE_TXTAREA, false);
		$this->quickInitVar('identifier', XOBJ_DTYPE_TXTBOX, false);
		$this->initCommonVar('dohtml');
		$this->initCommonVar('dobr');

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
		if ($format == 's' && in_array($key, array ())) {
			return call_user_func(array ($this,	$key));
		}
		return parent :: getVar($key, $format);
	}
}

class SprocketsRightsHandler extends IcmsPersistableObjectHandler {

	/**
	 * Constructor
	 */
	public function __construct(& $db) {
		$this->IcmsPersistableObjectHandler($db, 'rights', 'rights_id', 'title', 'description',
			'sprockets');
	}

	/*
     * Returns a list of Rights (array)
	*/
	public function getRights() {
		return $this->getList();
	}
	
	/**
	 * Returns a select box of available tags
	 *
	 * @param int $selected
	 * @param string $zero_option_message
	 * @return string $form
	 */
	
	public function getTagSelectBox($selected = null, $zero_option_message = '---',
			$navigation_elements_only = true) {
		
		$form = $criteria = '';
		$tagList = array();

		if ($navigation_elements_only) {
			$criteria = icms_buildCriteria(array('navigation_element' => true));
		}

		$tagList = array(0 => $zero_option_message) + $this->getList($criteria);

		$form = '<div><form name="tag_selection_form" action="article.php" method="get">';
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
	 * Returns an array of rights, optionally with links
	 * 
	 * Use to build buffer to reduce DB lookups when parsing multiple objects
	 *
	 * @param bool $with_links
	 * @return array 
	 */
	public function get_rights_buffer($with_links = false) {
		
		$rights_object_array = $rights_buffer = array();
		
		$rights_object_array = $this->getObjects();
		
		if ($with_links) {
			foreach ($rights_objects as $rights) {
				$rights_buffer[$rights->id()] = $rights->getItemLink();
			}
		} else {
			foreach ($rights_objects as $rights) {
				$rights_buffer[$rights->id()] = $rights->title();
			}
		}
		
		return $rights_buffer;
	}
}