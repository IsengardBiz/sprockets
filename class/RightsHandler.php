<?php

/**
* Class representing a Rights Handler object
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

class SprocketsRightsHandler extends icms_ipf_Handler {

	/**
	 * Constructor
	 */
	public function __construct(& $db) {
		
		parent::__construct($db, 'rights', 'rights_id', 'title', 'description',
			'sprockets');
	}

	/*
     * Returns a list of Rights (array)
	*/
	public function getRights() {
		return $this->getList();
	}
	
	/**
	 * Returns an array of rights, optionally with links
	 * 
	 * Use to build buffer to reduce DB lookups when parsing multiple objects
	 *
	 * @param bool $with_links
	 * @return array 
	 */
	public function get_rights_buffer($with_links = FALSE) {
		
		$rights_object_array = $rights_buffer = array();
		
		$rights_object_array = $this->getObjects();
		
		if ($with_links) {
			foreach ($rights_objects as $rights) {
				$rights_buffer[$rights->id()] = $rights->getItemLink();
			}
		} else {
			foreach ($rights_objects as $rights) {
				$rights_buffer[$rights->id()] = $rights->getVar('title');
			}
		}
		
		return $rights_buffer;
	}
}