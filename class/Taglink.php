<?php

/**
* Class respresenting Sprockets taglink objects
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

class SprocketsTaglink extends icms_ipf_Object {

	/**
	 * Constructor
	 *
	 * @param object $handler SprocketsPostHandler object
	 */
	public function __construct(& $handler) {
		global $icmsConfig;

		parent::__construct($handler);

		$this->quickInitVar('taglink_id', XOBJ_DTYPE_INT, TRUE);
		$this->quickInitVar('tid', XOBJ_DTYPE_INT, TRUE);
		$this->quickInitVar('mid', XOBJ_DTYPE_INT, FALSE);
		$this->quickInitVar('item', XOBJ_DTYPE_TXTBOX, TRUE);
		$this->quickInitVar('iid', XOBJ_DTYPE_INT, TRUE);
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

	/**
	 * Returns the module object for the linked object
	 *
	 * @return object $module
	 */
	
	public function getModuleObject() {
		
		$module_handler = icms::handler('icms_module');
		$module = $module_handler->getByDirname($this->getVar('mid', 'e'));

		return $module;
	}

	/**
	 * Returns the linked object associated with this taglink (online objects only)
	 *
	 * @return object $contentObj
	 */
	
	public function getLinkedObject() {

		$item = $this->getVar('item', 'e');
		$module = $this->getModuleObject();
		$content_handler = icms_getModuleHandler($this->getVar('item', 'e'), $module->getVar('dirname'),
			$module->getVar('dirname'));
		
		$contentObj = $content_handler->get($this->getItemId());
		if ($contentObj->getVar('online_status', 'e') == 0)
		{
			$contentObj = null;
		}
		
		return $contentObj;
	}
}