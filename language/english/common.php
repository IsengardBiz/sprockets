<?php
/**
* English language constants commonly used in the module
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

// tag
define("_CO_SPROCKETS_TAG_TITLE", "Title");
define("_CO_SPROCKETS_TAG_TITLE_DSC", " Name of this tag (subject).");
define("_CO_SPROCKETS_TAG_DESCRIPTION", "Description");
define("_CO_SPROCKETS_TAG_DESCRIPTION_DSC", " Description of this tag (optional).");
define("_CO_SPROCKETS_TAG_ICON", "Tag icon");
define("_CO_SPROCKETS_TAG_ICON_DSC", "You can upload a logo or icon to use with this tag.");
define("_CO_SPROCKETS_TAG_PARENT_ID", "Parent category");
define("_CO_SPROCKETS_TAG_PARENT_ID_DSC", "If this is a subcategory, specify its immediate parent. 
	A category cannot be assigned as its own parent.");
define("_CO_SPROCKETS_TAG_MID", "Module");
define("_CO_SPROCKETS_TAG_RSS", "RSS feed");
define("_CO_SPROCKETS_TAG_RSS_DSC", "Enable a unified RSS feed for this tag, drawing on content 
	from ALL compatible modules on the site.");
define("_CO_SPROCKETS_TAG_SWITCH_ONLINE", "Switch online");
define("_CO_SPROCKETS_TAG_SWITCH_OFFLINE", "Switch offline");
define("_CO_SPROCKETS_TAG_ONLINE", "Online");
define("_CO_SPROCKETS_TAG_OFFLINE", "Offline");
define("_CO_SPROCKETS_TAG_RSS_ENABLED", "Enabled");
define("_CO_SPROCKETS_TAG_RSS_DISABLED", "Disabled");
define("_CO_SPROCKETS_TAG_TAG", "Tag");
define("_CO_SPROCKETS_TAG_CATEGORY", "Category");
define("_CO_SPROCKETS_TAG_BOTH", "Both");
define("_CO_SPROCKETS_TAG_ALL_TAGS", "-- All tags --");
define("_CO_SPROCKETS_TAG_NAVIGATION_ELEMENT", "Navigation element?");
define("_CO_SPROCKETS_TAG_NAVIGATION_ELEMENT_DSC", "Enable to include this tag in select
	boxes used for page navigation. Do this for tags that you want to use as key navigation elements 
	one the user side of your site. All tags will still be available on the admin side so that you 
	can still use the others to filter block content etc.");
define("_CO_SPROCKETS_TAG_YES", "Yes");
define("_CO_SPROCKETS_TAG_NO", "No");
define("_CO_SPROCKETS_TAG_NAVIGATION_ENABLE", "Enable as navigation element");
define("_CO_SPROCKETS_TAG_NAVIGATION_DISABLE", "Disable as navigation element");
define("_CO_SPROCKETS_TAG_NAVIGATION_ENABLED", "Navigation element enabled");
define("_CO_SPROCKETS_TAG_NAVIGATION_DISABLED", "Navigation element disabled");

// taglink
define("_CO_SPROCKETS_TAGLINK_TID", "Tag ID");
define("_CO_SPROCKETS_TAGLINK_TID_DSC", " ID of the linked tag object.");
define("_CO_SPROCKETS_TAGLINK_MID", "Module ID");
define("_CO_SPROCKETS_TAGLINK_MID_DSC", " ID of the module the linked content object belongs to.");
define("_CO_SPROCKETS_TAGLINK_ITEM", "Item");
define("_CO_SPROCKETS_TAGLINK_ITEM_DSC", " ");
define("_CO_SPROCKETS_TAGLINK_IID", "Item ID");
define("_CO_SPROCKETS_TAGLINK_IID_DSC", " ID of the linked content item.");

// rights
define("_CO_SPROCKETS_RIGHTS_TITLE", "Title");
define("_CO_SPROCKETS_RIGHTS_TITLE_DSC", " Name of this intellectual property rights license.");
define("_CO_SPROCKETS_RIGHTS_DESCRIPTION", "Description");
define("_CO_SPROCKETS_RIGHTS_DESCRIPTION_DSC", " A summary description of this intellectual 
	property rights license. It is recommended to link to the full terms and conditions using the
	identifier field.");
define("_CO_SPROCKETS_RIGHTS_IDENTIFIER", "Link");
define("_CO_SPROCKETS_RIGHTS_IDENTIFIER_DSC", " A link to the full terms and conditions of this
	intellectual property rights license.");
define("_CO_SPROCKETS_RIGHTS_FULL_TERMS", "View the full terms of this license.");

// RSS
define("_CO_SPROCKETS_RSS", "RSS");
define("_CO_SPROCKETS_NEW", "New content");
define("_CO_SPROCKETS_NEW_DSC", "The latest information from all areas of our site.");
define("_CO_SPROCKETS_ALL", "All content");

define("_CO_SPROCKETS_NEWS_ALL", "All news");
define("_CO_SPROCKETS_SUBSCRIBE_RSS", "Subscribe to our newsfeed");
define("_CO_SPROCKETS_SUBSCRIBE_RSS_ON", "Subscribe to our newsfeed on: ");

// archive
define("_CO_SPROCKETS_ARCHIVE_MODULE_ID", "Module");
define("_CO_SPROCKETS_ARCHIVE_MODULE_ID_DSC", "Select the module represented by this archive.");
define("_CO_SPROCKETS_ARCHIVE_ENABLE_ARCHIVE", "Enable this archive?");
define("_CO_SPROCKETS_ARCHIVE_ENABLE_ARCHIVE_DSC", "The archive will not accept incoming OAIPMH 
	requests unless it is enabled");
define("_CO_SPROCKETS_ARCHIVE_ENABLED", "Archive online");
define("_CO_SPROCKETS_ARCHIVE_ENABLED_DSC", "Toggle this archive online (yes) or offline (no).");
define("_CO_SPROCKETS_ARCHIVE_TARGET_MODULE", "Target module");
define("_CO_SPROCKETS_ARCHIVE_TARGET_MODULE_DSC", "Select the module you wish to enable the OAIPMH
    (federation) service for.");
define("_CO_SPROCKETS_ARCHIVE_METADATA_PREFIX", "Metadata prefix");
define("_CO_SPROCKETS_ARCHIVE_METADATA_PREFIX_DSC", " Indicates the XML metadata schemes supported
    by this archive. Presently only Dublin Core is supported (oai_dc).");
define("_CO_SPROCKETS_ARCHIVE_NAMESPACE", "Namespace");
define("_CO_SPROCKETS_ARCHIVE_NAMESPACE_DSC", "Used to construct unique identifiers for records. 
    Default is to use your domain name. Changing this is not recommended as it helps people
    identify your archive as the source of a record that has been shared with other archives.");
define("_CO_SPROCKETS_ARCHIVE_GRANULARITY", "Granularity");
define("_CO_SPROCKETS_ARCHIVE_GRANULARITY_DSC", " The granularity of datestamps. The OAIPMH permits 
    two levels of granularity, this implementation supports the most fine grained option
    (YYYY-MM-DDThh:mm:ssZ).");
define("_CO_SPROCKETS_ARCHIVE_DELETED_RECORD", "Deleted record support");
define("_CO_SPROCKETS_ARCHIVE_DELETED_RECORD_DSC", " Does the archive support tracking of deleted
    records? This implementation does not currently support deleted records.");
define("_CO_SPROCKETS_ARCHIVE_EARLIEST_DATE_STAMP", "Earliest date stamp");
define("_CO_SPROCKETS_ARCHIVE_EARLIEST_DATE_STAMP_DSC", " The datestamp for the oldest record in
    your archive.");
define("_CO_SPROCKETS_ARCHIVE_ADMIN_EMAIL", "Admin email");
define("_CO_SPROCKETS_ARCHIVE_ADMIN_EMAIL_DSC", " The email address for the administrator of this
    archive. Be aware that this address is reported in response to incoming OAIPMH requests.");
define("_CO_SPROCKETS_ARCHIVE_PROTOCOL_VERSION", "Protocol version");
define("_CO_SPROCKETS_ARCHIVE_PROTOCOL_VERSION_DSC", " The OAIPMH protocol version implemented by
    this repository. Currently only version 2.0 is supported.");
define("_CO_SPROCKETS_ARCHIVE_REPOSITORY_NAME", "Archive name");
define("_CO_SPROCKETS_ARCHIVE_REPOSITORY_NAME_DSC", " The name of your archive.");
define("_CO_SPROCKETS_ARCHIVE_BASE_URL", "Base URL");
define("_CO_SPROCKETS_ARCHIVE_BASE_URL_DSC", " The target URL to which incoming OAIPMH requests for
    your archive should be sent.");
define("_CO_SPROCKETS_ARCHIVE_COMPRESSION", "Compression");
define("_CO_SPROCKETS_ARCHIVE_COMPRESSION_DSC", " Indicates what types of compression are supported
    by this archive. Presently only gzip is supported.");
define("_CO_SPROCKETS_ARCHIVE_ABOUT_THIS_ARCHIVE", "Our publication collection is an Open Archive");
define("_CO_SPROCKETS_ARCHIVE_OAIPMH_TARGET", "This website implements the 
    <a href=\"http://www.openarchives.org/pmh/\">Open Archives Initiative Protocol for Metadata
    Harvesting</a> (OAIPMH). Compliant harvesters can access our publication metadata from the
    OAIPMH target below. OAIPMH queries should be directed to the Base URL specified below.");
define("_CO_SPROCKETS_ARCHIVE_NOT_AVAILABLE", "Sorry, Open Archive functionality is not enabled at
    this time.");
define("_CO_SPROCKETS_ARCHIVE_NOT_CONFIGURED", "Sprockets is currently configured to refuse incoming
    OAIPMH requests, sorry");
define("_CO_SPROCKETS_ARCHIVE_ENABLED_YES", "Yes");
define("_CO_SPROCKETS_ARCHIVE_ENABLED_NO", "No");
define("_CO_SPROCKETS_ARCHIVE_SWITCH_ONLINE", "Switch online");
define("_CO_SPROCKETS_ARCHIVE_SWITCH_OFFLINE", "Switch offline");
define("_CO_SPROCKETS_ARCHIVE_ONLINE", "Online");
define("_CO_SPROCKETS_ARCHIVE_OFFLINE", "Offline");

// errors
define("_CO_SPROCKETS_ONLY_ONE_ARCHIVE", "Only one archive is permitted for each client module.");

// warnings
define("_CO_SPROCKETS_ONLY_ONE_ARCHIVE_PER_MODULE", "<strong>Please note</strong>: Only one archive 
	object is permitted per target module.");
//define("", "");