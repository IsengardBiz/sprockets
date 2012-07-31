<?php
/**
* English language constants related to module information
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		sprockets
* @version		$Id$
*/

if (!defined("ICMS_ROOT_PATH")) die("ICMS Hauptverzeichnis nicht definiert");

// Module Info
define("_MI_SPROCKETS_MD_NAME", "Sprockets");
define("_MI_SPROCKETS_MD_DESC", "Module zur Bereitstellung nützlicher Dienste für IPF-Module.");
define("_MI_SPROCKETS_TAGS", "Tags");
define("_MI_SPROCKETS_TAGLINKS", "Tag Links");
define("_MI_SPROCKETS_CATEGORIES", "Globale Kategorien");
define("_MI_SPROCKETS_RIGHTS", "Rechte");
define("_MI_SPROCKETS_ARCHIVE", "Archive");
define("_MI_SPROCKETS_TEMPLATES", "Templates");
define("_MI_SPROCKETS_CSS_EDITOR", "CSS Editor");

// preferences
define("_MI_SPROCKETS_DISPLAY_BREADCRUMB", "Breadcrumb anzeigen?");
define("_MI_SPROCKETS_DISPLAY_BREADCRUMB_DSC", "Anzeigen, oder verstecken, der horizontalen Breadcrumb-Navigation über dem Modul.");
define("_MI_SPROCKETS_NUMBER_RSS_ITEMS", "Anzahl der Items in den RSS feeds?");
define("_MI_SPROCKETS_NUMBER_RSS_ITEMS_DSC", "Empfohlen werden zwischen 5-10 Items. Diese Abfragen verursachen hohe Serverlasen, also passen Sie die Werte vorsichtig an.");
define("_MI_SPROCKETS_RESUMPTION_TOKEN_CURSOR_OFFSET", "OAIMPH resumption token offset (records).");
define("_MI_SPROCKETS_RESUMPTION_TOKEN_CURSOR_OFFSET_DSC", "The maximum number of records to return 
	in response to an OAIPMH ListRecords or ListIdentifiers request. Such requests generate a large
	number of queries. If your OAI repository has a large number of records (hundreds or thousands),
	it is best to return a partial result set in order to prevent script timeouts and database 
	overload. The response will include a 'resumption token' that allows the client to re-issue the 
	request starting at the point where it left off. The entire repository can thus be harvested 
	in a series of smaller transactions without overloading the server. Sites on shared web hosting 
	should keep this value low (<100), sites with dedicated servers can consider increasing it. To 
	disable partial harvesting (ie. return all records), set this value to 0.");
define("_MI_SPROCKETS_RESUMPTION_TOKEN_EXPIRATION", "OAIPMH resumption token expiration (seconds).");
define("_MI_SPROCKETS_RESUMPTION_TOKEN_EXPIRATION_DSC", "The time that a resumption 
	token will remain valid. Expired tokens are refused. 30 minutes (1800 seconds) is a reasonable 
	value.");
define("_MI_SPROCKETS_RESUMPTION_TOKEN_THROTTLE", "OAIPMH throttle (seconds).");
define("_MI_SPROCKETS_RESUMPTION_TOKEN_THROTTLE_DSC", "To reduce server load from OAIPMH ListRecords 
	and ListIdentifiers requests you can ask clients to wait for a while before submitting 
	their next partial harvest request. Requests made within the moratorium period will be ignored 
	and clients will be given aa HTTP 503 status code with a Retry-after value set. One minute is 
	a reasonable value. If your server is heavily loaded, increase this value. You can consider 
	reducing it a bit if you have dedicated server resources. Set it at zero to disable the 
	throttle");

// blocks
define("_MI_SPROCKETS_CONTENT_RECENT", "Neuster Inhalt");
define("_MI_SPROCKETS_CONTENT_RECENTDSC", "Zeige die neusten Inhalte der kompatiblen Module an.");