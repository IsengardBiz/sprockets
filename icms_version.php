<?php
/**
* Sprockets version infomation
*
* This file holds the configuration information of this module
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

/**  General Information  */
$modversion = array(
  'name'=> _MI_SPROCKETS_MD_NAME,
  'version'=> '1.1',
  'description'=> _MI_SPROCKETS_MD_DESC,
  'author'=> "Madfish (Simon Wilkinson)",
  'credits'=> "Skeleton code generated with ImBuilding.",
  'help'=> "",
  'license'=> "GNU General Public License (GPL)",
  'official'=> 0,
  'dirname'=> basename(dirname(__FILE__ )),

/**  Images information  */
  'iconsmall'=> "images/icon_small.png",
  'iconbig'=> "images/icon_big.png",
  'image'=> "images/icon_big.png", /* for backward compatibility */

/**  Development information */
  'status_version'=> "1.1",
  'status'=> "Beta",
  'date'=> "10/10/2011",
  'author_word'=> "",

/** Contributors */
  'developer_website_url' => "https://www.isengard.biz",
  'developer_website_name' => "Isengard.biz",
  'developer_email' => "simon@isengard.biz");

$modversion['people']['developers'][] = "Madfish (Simon Wilkinson)";

/** Manual */
$modversion['manual']['wiki'][] = "<a href='http://wiki.impresscms.org/index.php?title=Sprockets' target='_blank'>English</a>";

$modversion['warning'] = _CO_ICMS_WARNING_BETA;

/** Administrative information */
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = "admin/index.php";
$modversion['adminmenu'] = "admin/menu.php";

/** Database information */
$modversion['object_items'][1] = 'tag';
$modversion['object_items'][] = 'taglink';
$modversion['object_items'][] = 'rights';
$modversion['object_items'][] = 'archive';

$modversion["tables"] = icms_getTablesArray($modversion['dirname'], $modversion['object_items']);

/** Install and update informations */
$modversion['onInstall'] = "include/onupdate.inc.php";
$modversion['onUpdate'] = "include/onupdate.inc.php";

/** Search information */
$modversion['hasSearch'] = 0;

/** Menu information */
$modversion['hasMain'] = 1;

$modversion['blocks'][1] = array(
  'file' => 'sprockets_content_recent.php',
  'name' => _MI_SPROCKETS_CONTENT_RECENT,
  'description' => _MI_SPROCKETS_CONTENT_RECENTDSC,
  'show_func' => 'sprockets_content_recent_show',
  'edit_func' => 'sprockets_content_recent_edit',
// date|tag|date format|max title length|spotlight|spotlight id|spotlight module name|spotlight type
  'options' => '5|All|j/n/Y|90|0|0|0|0',
  'template' => 'sprockets_content_recent.html');

/** Templates information */
$modversion['templates'][1] = array(
  'file' => 'sprockets_header.html',
  'description' => 'Module Header');

$modversion['templates'][] = array(
  'file' => 'sprockets_footer.html',
  'description' => 'Module Footer');

$modversion['templates'][] = array(
	'file' => 'sprockets_requirements.html',
	'description' => 'Requirements warning');

$modversion['templates'][] = array(
	'file' => 'sprockets_rss.html',
	'description' => 'Unified RSS feed, supports enclosures.');

$modversion['templates'][]= array(
  'file' => 'sprockets_admin_tag.html',
  'description' => 'Tag Admin Index');

$modversion['templates'][]= array(
  'file' => 'sprockets_tag.html',
  'description' => 'Tag Index');

$modversion['templates'][]= array(
  'file' => 'sprockets_admin_taglink.html',
  'description' => 'Taglink Admin Index');

$modversion['templates'][]= array(
  'file' => 'sprockets_admin_rights.html',
  'description' => 'Rights Admin Index');

$modversion['templates'][]= array(
  'file' => 'sprockets_rights.html',
  'description' => 'Rights Index');

$modversion['templates'][]= array(
  'file' => 'sprockets_admin_archive.html',
  'description' => 'Archive Admin Index');

/** Preferences information */

$modversion['config'][1] = array(
  'name' => 'display_breadcrumb',
  'title' => '_MI_SPROCKETS_DISPLAY_BREADCRUMB',
  'description' => '_MI_SPROCKETS_DISPLAY_BREADCRUMB_DSC',
  'formtype' => 'yesno',
  'valuetype' => 'int',
  'default' =>  '1');

$modversion['config'][] = array(
  'name' => 'number_rss_items',
  'title' => '_MI_SPROCKETS_NUMBER_RSS_ITEMS',
  'description' => '_MI_SPROCKETS_NUMBER_RSS_ITEMS_DSC',
  'formtype' => 'text',
  'valuetype' => 'int',
  'default' =>  '10');

$modversion['config'][] = array(
  'name' => 'resumption_token_cursor_offset',
  'title' => '_MI_SPROCKETS_RESUMPTION_TOKEN_CURSOR_OFFSET',
  'description' => '_MI_SPROCKETS_RESUMPTION_TOKEN_CURSOR_OFFSET_DSC',
  'formtype' => 'text',
  'valuetype' => 'int',
  'default' =>  '100');

$modversion['config'][] = array(
  'name' => 'resumption_token_expiration',
  'title' => '_MI_SPROCKETS_RESUMPTION_TOKEN_EXPIRATION',
  'description' => '_MI_SPROCKETS_RESUMPTION_TOKEN_EXPIRATION_DSC',
  'formtype' => 'text',
  'valuetype' => 'int',
  'default' =>  '1800');

$modversion['config'][] = array(
  'name' => 'resumption_token_throttle',
  'title' => '_MI_SPROCKETS_RESUMPTION_TOKEN_THROTTLE',
  'description' => '_MI_SPROCKETS_RESUMPTION_TOKEN_THROTTLE_DSC',
  'formtype' => 'text',
  'valuetype' => 'int',
  'default' =>  '60');

/** Comments information */
$modversion['hasComments'] = 0;

/** Notification information */
$modversion['hasNotification'] = 0;