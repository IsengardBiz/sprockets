<?php

/**
* Generating an RSS feed for a tag or category, draws content from across modules
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

/** Include the module's header for all pages */
include_once 'header.php';
include_once ICMS_ROOT_PATH.'/header.php';

/*
 * Encodes entities to ensure that feed content matches RSS specification
 *
 * @param	string $field
 * @return	string $field
 */
function encode_entities($field) {
	$field = htmlspecialchars(html_entity_decode($field, ENT_QUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8');
	return $field;
}

global $sprocketsConfig;
$clean_tag_id = $sort_order = '';
$tags_with_rss = $content_item_array = array();

$clean_tag_id = isset($_GET['tag_id']) ? intval($_GET['tag_id']) : FALSE;

if ($clean_tag_id) {

	include_once ICMS_ROOT_PATH . '/modules/' . basename(dirname(__FILE__)) . '/class/icmsfeed.php';

	$unified_feed = new IcmsFeed();
	$sprocketsModule = icms_getModuleInfo(basename(dirname(__FILE__)));

	// get handlers
	$sprockets_taglink_handler = icms_getModuleHandler('taglink', $sprocketsModule->getVar('dirname'),
		'sprockets');
	$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'),
		'sprockets');
	
	// generate a tag-specific RSS feed, drawing on content from all compatible modules
	$tags_with_rss = $sprockets_tag_handler->getTagsWithRss();
	
	if (key_exists($clean_tag_id, $tags_with_rss)) {

		// get the tag object
		$tagObj = $sprockets_tag_handler->get($clean_tag_id);

		// remove html tags and problematic characters to meet RSS spec
		$site_name = encode_entities($icmsConfig['sitename']);
		$tag_title = encode_entities($tagObj->getVar('title'));
		$tag_description = strip_tags($tagObj->getVar('description'));
		$tag_description = encode_entities($tag_description);

		$unified_feed->title = $site_name . ' - ' . $tag_title;
		$unified_feed->url = ICMS_URL;
		$unified_feed->description = $tag_description;
		$unified_feed->language = _LANGCODE;
		$unified_feed->charset = _CHARSET;
		$unified_feed->category = $sprocketsModule->getVar('name');

		// if there's a tag icon, use it as the feed image
		if ($tagObj->getVar('icon', 'e')) {
			$url = $tagObj->getImageDir() . $tagObj->getVar('icon', 'e');
		} else {
			$url = ICMS_URL . 'images/logo.gif';
		}

		$unified_feed->image = array('title' => $unified_feed->title, 'url' => $url, 
				'link' => $unified_feed->url);
		$unified_feed->width = 144;
		$unified_feed->atom_link = '"' . SPROCKETS_URL . 'rss.php?tag_id=' . $tagObj->id() . '"';

		// get the content objects for this tag's feed
		// $tag_id = FALSE, $module_id = FALSE, $item_type = FALSE, $start = FALSE, $limit = FALSE,
		// $sort = 'DESC')
		$content_item_array = $sprockets_taglink_handler->getTaggedItems($clean_tag_id, FALSE,
				FALSE, FALSE, icms::$module->config['number_rss_items']);
		
		// Unset the first result as it contains a count value that we do not need in this case
		unset($content_item_array[0]);

		// Prepare an array of content items (these are NOT objects, array of required fields only)
		foreach($content_item_array as $contentItem) {

			// encode content fields to ensure feed is compliant with the RSS spec
			// Isengard convention: Multiple creators are pipe-delimited
			if ($contentItem['creator']) {
				$creator = $contentItem['creator'];
				$creator = explode('|', $creator);
				foreach ($creator as &$individual) {
					$individual = encode_entities($individual);
				}
			} else {
				$creator = encode_entities();
			}
			// Strip the filtered by HTML purifier notices before they get encoded
			$contentItem['description'] = str_replace('<!-- filtered with htmlpurifier -->', '',
					$contentItem['description']);
			$contentItem['description'] = str_replace('<!-- input filtered -->', '',
					$contentItem['description']);
			$description = encode_entities($contentItem['description']);
			$title = encode_entities($contentItem['title']);
			$link = encode_entities($contentItem['itemUrl']);

			$unified_feed->feeds[] = array (
				'title' => $title,
				'link' => $link,
				'description' => $description,
				'author' => $creator,
				// pubdate must be a RFC822-date-time EXCEPT with 4-digit year or won't validate
				'pubdate' => date(DATE_RSS, $contentItem['date']),
				'guid' => $link,
				'category' => $tag_title
				);
		}

		$unified_feed->render();

	} else {
		// if $clean_tag_id is not set there is nothing to do
		exit;
	}
}
