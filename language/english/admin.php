<?php
/**
* English language constants used in admin section of the module
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/
if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

// Requirements
define("_AM_SPROCKETS_REQUIREMENTS", "Sprockets Requirements");
define("_AM_SPROCKETS_REQUIREMENTS_INFO", "We've reviewed your system, unfortunately it doesn't
	meet all the requirements needed for Sprockets to function. Below are the requirements needed.");
define("_AM_SPROCKETS_REQUIREMENTS_ICMS_BUILD", "Sprockets requires at least ImpressCMS 1.1.1 RC 1.");
define("_AM_SPROCKETS_REQUIREMENTS_SUPPORT", "Should you have any question or concerns, please
	visit our forums at
	<a href='http://community.impresscms.org'>http://community.impresscms.org</a>.");

// Tag
define("_AM_SPROCKETS_TAGS", "Tags");
define("_AM_SPROCKETS_TAGS_DSC", "All tags in the module");
define("_AM_SPROCKETS_TAG_CREATE", "Add a tag");
define("_AM_SPROCKETS_CATEGORY_CATEGORIES", "Categories");
define("_AM_SPROCKETS_CATEGORY_CREATE", "Add a global category");
define("_AM_SPROCKETS_CATEGORY_MODULE_CREATE", "Add a category");
define("_AM_SPROCKETS_TAG", "Tag");
define("_AM_SPROCKETS_TAG_CREATE_INFO", "Fill-out the following form to create a new tag.");
define("_AM_SPROCKETS_TAG_EDIT", "Edit this tag");
define("_AM_SPROCKETS_TAG_EDIT_INFO", "Fill-out the following form in order to edit this tag.");
define("_AM_SPROCKETS_TAG_MODIFIED", "The tag was successfully modified.");
define("_AM_SPROCKETS_TAG_CREATED", "The tag has been successfully created.");
define("_AM_SPROCKETS_TAG_VIEW", "Tag info");
define("_AM_SPROCKETS_TAG_VIEW_DSC", "Here is the info about this tag.");
define("_AM_SPROCKETS_TAG_YES", "Yes");
define("_AM_SPROCKETS_TAG_NO", "No");
define("_AM_SPROCKETS_TAG_RSS_ENABLED", "RSS feed enabled");
define("_AM_SPROCKETS_TAG_RSS_DISABLED", "RSS feed disabled");
define("_AM_SPROCKETS_TAG_RSS_FEED", "RSS feed?");
define("_AM_SPROCKETS_TAG_NAVIGATION_ENABLED", "Navigation element enabled");
define("_AM_SPROCKETS_TAG_NAVIGATION_DISABLED", "Navigation element disabled");

// Category
define("_AM_SPROCKETS_CATEGORY_EDIT", "Edit");
define("_AM_SPROCKETS_CATEGORY_DELETE", "Delete");

// Taglink
define("_AM_SPROCKETS_TAGLINKS", "Taglinks");
define("_AM_SPROCKETS_TAGLINKS_DSC", "All taglinks in the module");
define("_AM_SPROCKETS_TAGLINK_CREATE", "Add a taglink");
define("_AM_SPROCKETS_TAGLINK", "Taglink");
define("_AM_SPROCKETS_TAGLINK_CREATE_INFO", "Fill-out the following form to create a new taglink.");
define("_AM_SPROCKETS_TAGLINK_EDIT", "Edit this taglink");
define("_AM_SPROCKETS_TAGLINK_EDIT_INFO", "Fill-out the following form in order to edit this
	taglink.");
define("_AM_SPROCKETS_TAGLINK_MODIFIED", "The taglink was successfully modified.");
define("_AM_SPROCKETS_TAGLINK_CREATED", "The taglink has been successfully created.");
define("_AM_SPROCKETS_TAGLINK_VIEW", "Taglink info");
define("_AM_SPROCKETS_TAGLINK_VIEW_DSC", "Here is the info about this taglink.");

// Rights
define("_AM_SPROCKETS_RIGHTS", "Rights");
define("_AM_SPROCKETS_RIGHTS_DSC", "All rights in the module");
define("_AM_SPROCKETS_RIGHTS_CREATE", "Add a license (rights)");
define("_AM_SPROCKETS_RIGHTS_CREATE_INFO", "Fill-out the following form to create a new rights.");
define("_AM_SPROCKETS_RIGHTS_EDIT", "Edit this rights");
define("_AM_SPROCKETS_RIGHTS_EDIT_INFO", "Fill-out the following form in order to edit this rights.");
define("_AM_SPROCKETS_RIGHTS_MODIFIED", "The rights was successfully modified.");
define("_AM_SPROCKETS_RIGHTS_CREATED", "The rights has been successfully created.");
define("_AM_SPROCKETS_RIGHTS_VIEW", "Rights info");
define("_AM_SPROCKETS_RIGHTS_VIEW_DSC", "Here is the info about this rights.");

// Archive
define("_AM_SPROCKETS_ARCHIVES", "Archives");
define("_AM_SPROCKETS_ARCHIVES_DSC", "All archives in the module");
define("_AM_SPROCKETS_ARCHIVE_CREATE", "Add an archive");
define("_AM_SPROCKETS_ARCHIVE", "Archive");
define("_AM_SPROCKETS_ARCHIVE_CREATE_INFO", "Fill-out the following form to create a new archive.");
define("_AM_SPROCKETS_ARCHIVE_EDIT", "Edit this archive");
define("_AM_SPROCKETS_ARCHIVE_EDIT_INFO", "Fill-out the following form in order to edit this
    archive.");
define("_AM_SPROCKETS_ARCHIVE_MODIFIED", "The archive was successfully modified.");
define("_AM_SPROCKETS_ARCHIVE_CREATED", "The archive has been successfully created.");
define("_AM_SPROCKETS_ARCHIVE_VIEW", "Archive info");
define("_AM_SPROCKETS_ARCHIVE_VIEW_DSC", "Here is the info about this archive.");
define("_AM_SPROCKETS_ARCHIVE_NO_ARCHIVE","<strong>Archive status: <span style=\"color:#red;\">None.
    </span></strong> Create an Archive object below if you want to enable the Open Archives Initiative
    Protocol for Metadata Harvesting.<br />");
define("_AM_SPROCKETS_ARCHIVE_ONLINE", "<strong>Archive status: <span style=\"color:#green;\">Enabled.
    </span></strong> Sprockets has permission to serve metadata in response to incoming OAIPMH
    requests.");
define("_AM_SPROCKETS_ARCHIVE_OFFLINE","<strong>Archive status: <span style=\"color:#red;\"> Offline.
    </span></strong> You must enable archive functionality in module preferences if you want
    Sprockets to serve metadata in response to incoming OAIPMH requests.");
define("_AM_SPROCKETS_ARCHIVE_ENABLED", "Open Archives functionality enabled, incoming OAIPMH 
	requests will be served");
define("_AM_SPROCKETS_ARCHIVE_DISABLED", "Open Archives functionality disabled, incoming OAIPMH 
	requests will be refused");

// CSS editor
define("_AM_SPROCKETS_EDIT_CSS", "Edit CSS");
define("_AM_SPROCKETS_CSS_SELECT_THEME", "Select theme to edit");
define("_AM_SPROCKETS_CSS_AVAILABLE_STYLE_SHEETS", "Available style sheets");
define("_AM_SPROCKETS_CSS_EDITING", "Editing: ");
define("_AM_SPROCKETS_UPDATE_FILE", "Update file");
define("_AM_SPROCKETS_CANCEL", "Cancel");
define("_AM_SPROCKETS_CSS_SAVE_SUCCESSFUL", "CSS save successful.");
define("_AM_SPROCKETS_CSS_SAVE_FAILED", "Warning: CSS save failed.");

// warnings
define("_AM_SPROCKETS_CATEGORY_DELETE_CAUTION", "Do you really want to delete this category? 
	Subcategories will also be deleted (content items are not affected).");