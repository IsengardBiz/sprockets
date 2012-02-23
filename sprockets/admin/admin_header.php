<?php
/**
* Admin header file
*
* This file is included in all pages of the admin side and being so, it proceeds to a few
* common things.
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

include_once '../../../include/cp_header.php';

include_once ICMS_ROOT_PATH.'/modules/' . basename(dirname(dirname(__FILE__)))
	.'/include/common.php';
if( !defined("SPROCKETS_ADMIN_URL") ) define('SPROCKETS_ADMIN_URL', SPROCKETS_URL . "admin/");
include_once(SPROCKETS_ROOT_PATH . 'include/requirements.php');