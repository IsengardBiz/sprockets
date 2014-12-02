<?php

/**
 * New categorised content (recent items from across modules) block file
 *
 * This file holds the functions needed to edit/view the 'recent content' block
 *
 * @copyright	http://smartfactory.ca The SmartFactory
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		marcan aka Marc-Andre Lanciault <marcan@smartfactory.ca>
 * @author		Madfish <simon@isengard.biz>
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

/**
 * Prepares recent teasers block for display
 *
 * @param array $options
 * @return string
 */
function sprockets_content_teasers_cats_show($options) {
	include_once(ICMS_ROOT_PATH . '/modules/sprockets/include/common.php');
	
	$block = '';
	
	// Check Sprockets is installed and active (otherwise do nothing)
	if (icms_get_module_status("sprockets"))
	{
		
	}
	
	return $block;
}

function sprockets_content_teasers_cats_edit($options) {
	include_once(ICMS_ROOT_PATH . '/modules/' . basename(dirname(dirname(__FILE__)))
		. '/include/common.php');
	
	$form = '';
	
	return $form;
}