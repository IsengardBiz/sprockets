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
  'version'=> '2.01',
  'description'=> _MI_SPROCKETS_MD_DESC,
  'author'=> "Madfish (Simon Wilkinson)",
  'credits'=> "Skeleton code generated with ImBuilding. Module icon by David Lanham http://dlanham.com/",
  'help'=> "",
  'license'=> "GNU General Public License (GPL)",
  'official'=> 0,
  'dirname'=> basename(dirname(__FILE__ )),

/**  Images information  */
  'iconsmall'=> "images/icon_small.png",
  'iconbig'=> "images/icon_big.png",
  'image'=> "images/icon_big.png", /* for backward compatibility */

/**  Development information */
  'status_version'=> "2.01",
  'status'=> "Beta",
  'date'=> "30/8/2013",
  'author_word'=> "This version of Sprockets is compatible with ImpressCMS V1.3.x.",

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
  'file' => 'sprockets_content_teasers.php',
  'name' => _MI_SPROCKETS_CONTENT_TEASERS,
  'description' => _MI_SPROCKETS_CONTENT_TEASERSDSC,
  'show_func' => 'sprockets_content_teasers_show',
  'edit_func' => 'sprockets_content_teasers_edit',
	// 0 items to show | 1 tags | 2 objects | 3 image position | 4 image size | 5 display mode | 6 dynamic tagging
  'options' => '5|All|All|1|150|1|0',
  'template' => 'sprockets_content_teasers.html');

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
  'description' => 'Tag admin index');

$modversion['templates'][]= array(
  'file' => 'sprockets_text.html',
  'description' => 'Subtemplate for text resources');

$modversion['templates'][]= array(
  'file' => 'sprockets_sound.html',
  'description' => 'Subtemplate for sound resources');

$modversion['templates'][]= array(
  'file' => 'sprockets_image.html',
  'description' => 'Subtemplate for image and video resources');

$modversion['templates'][]= array(
  'file' => 'sprockets_tag.html',
  'description' => 'Tag index');

$modversion['templates'][]= array(
  'file' => 'sprockets_admin_taglink.html',
  'description' => 'Taglink admin index');

$modversion['templates'][]= array(
  'file' => 'sprockets_admin_rights.html',
  'description' => 'Rights admin index');

$modversion['templates'][]= array(
  'file' => 'sprockets_rights.html',
  'description' => 'Rights index');

$modversion['templates'][]= array(
  'file' => 'sprockets_admin_archive.html',
  'description' => 'Archive admin index');

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

// Thumbnail size for teaser images in the cross-module content on tag.php
$modversion['config'][] = array(
	'name' => 'thumbnail_width',
	'title' => '_MI_SPROCKETS_THUMBNAIL_WIDTH',
	'description' => '_MI_SPROCKETS_THUMBNAIL_WIDTHDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '150');

$modversion['config'][] = array(
	'name' => 'thumbnail_height',
	'title' => '_MI_SPROCKETS_THUMBNAIL_HEIGHT',
	'description' => '_MI_SPROCKETS_THUMBNAIL_HEIGHTDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '150');

// Size for image resources in the cross-module content content on tag.php
$modversion['config'][] = array(
	'name' => 'image_width',
	'title' => '_MI_SPROCKETS_IMAGE_WIDTH',
	'description' => '_MI_SPROCKETS_IMAGE_WIDTHDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '400');

$modversion['config'][] = array(
	'name' => 'image_height',
	'title' => '_MI_SPROCKETS_IMAGE_HEIGHT',
	'description' => '_MI_SPROCKETS_IMAGE_HEIGHTDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '400');

// Client OBJECTS to include in the cross-module content on tag.php or in the cross-content block.
// Need one entry for every object type.
$client_objects = array(
	'_MI_SPROCKETS_CMS_PAGES' => 'start',
	'_MI_SPROCKETS_CATALOGUE_ITEMS' => 'item',
	'_MI_SPROCKETS_EVENTS' => 'event',
	'_MI_SPROCKETS_LIBRARY_PUBLICATIONS' => 'publication',
	'_MI_SPROCKETS_NEWS_ARTICLES' => 'article',
	'_MI_SPROCKETS_PARTNERS' => 'partner',
	'_MI_SPROCKETS_PODCAST_PROGRAMMES' => 'programme',
	'_MI_SPROCKETS_PODCAST_SOUNDTRACKS' => 'soundtrack',
	'_MI_SPROCKETS_PROJECTS' => 'project'
);

$modversion['config'][] = array(
	'name' => 'client_objects',
	'title' => '_MI_SPROCKETS_ALLOWED_CLIENT_OBJECTS',
	'description' => '_MI_SPROCKETS_ALLOWED_CLIENT_OBJECTS_DSC',
	'formtype' => 'select_multi',
	'valuetype' => 'array',
	'options' => $client_objects);

$modversion['config'][] = array(
	'name' => 'date_format',
	'title' => '_MI_SPROCKETS_DATE_FORMAT',
	'description' => '_MI_SPROCKETS_DATE_FORMAT_DSC',
	'formtype' => 'textbox',
	'valuetype' => 'text',
	'default' =>  'j/n/Y');

$modversion['config'][] = array(
	'name' => 'pagination_limit',
	'title' => '_MI_SPROCKETS_PAGINATION',
	'description' => '_MI_SPROCKETS_PAGINATION_DSC',
	'formtype' => 'textbox',
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