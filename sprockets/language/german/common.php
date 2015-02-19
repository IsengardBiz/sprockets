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

if (!defined("ICMS_ROOT_PATH")) die("ICMS Hauptverzeichnis nicht definiert");

// tag
define("_CO_SPROCKETS_TAG_TITLE", "Titel");
define("_CO_SPROCKETS_TAG_TITLE_DSC", " Name des tags (Subjekt).");
define("_CO_SPROCKETS_TAG_DESCRIPTION", "Beschreibung");
define("_CO_SPROCKETS_TAG_DESCRIPTION_DSC", " Beschreibung des Tags (optional).");
define("_CO_SPROCKETS_TAG_ICON", "Tag icon");
define("_CO_SPROCKETS_TAG_ICON_DSC", "Sie können ein Logo bzw. Icon für das Tag hochladen.");
define("_CO_SPROCKETS_TAG_PARENT_ID", "Eltern Kategorie");
define("_CO_SPROCKETS_TAG_PARENT_ID_DSC", "Bitte bestimmen Sie die übergeordnete Kategorie, wenn es sich um eine Unterkategorie handelt. 
	Eine Kategorie kann nicht selbst ihre eigene Oberkategorie sein.");
define("_CO_SPROCKETS_TAG_MID", "Modul");
define("_CO_SPROCKETS_TAG_RSS", "RSS feed");
define("_CO_SPROCKETS_TAG_RSS_DSC", "Aktivieren Sie einen einheitlichen RSS feed für dieses Tag, welches Inhalte 
	von ALLEN kompatieblen Modulen dieser Seite zusammen fasst.");
define("_CO_SPROCKETS_TAG_SWITCH_ONLINE", "Auf online stellen");
define("_CO_SPROCKETS_TAG_SWITCH_OFFLINE", "Auf offline stellen");
define("_CO_SPROCKETS_TAG_ONLINE", "Online");
define("_CO_SPROCKETS_TAG_OFFLINE", "Offline");
define("_CO_SPROCKETS_TAG_RSS_ENABLED", "Aktivieren");
define("_CO_SPROCKETS_TAG_RSS_DISABLED", "Deaktivieren");
define("_CO_SPROCKETS_TAG_TAG", "Tag");
define("_CO_SPROCKETS_TAG_CATEGORY", "Kategorie");
define("_CO_SPROCKETS_TAG_BOTH", "Beide");
define("_CO_SPROCKETS_TAG_ALL_TAGS", "-- Alle Tags --");
define("_CO_SPROCKETS_TAG_NAVIGATION_ELEMENT", "Navigation Element?");
define("_CO_SPROCKETS_TAG_NAVIGATION_ELEMENT_DSC", "Einschalten, damit dieses Tag in den ausgewählten Blöcken eingebunden werden kann, um es in der Seitennavigation zu nutzen. 
Tun Sie dies für Tags, welche Sie als Schlüsselnavigation für die Benutzer verwenden wollen. 
		Alle Tags werden weiterhin im Administrationsbereich verwendbar sein, 
		so dass sie damit Blockinhalte filtern können (usw.).");
define("_CO_SPROCKETS_TAG_YES", "Ja");
define("_CO_SPROCKETS_TAG_NO", "Nein");
define("_CO_SPROCKETS_TAG_NAVIGATION_ENABLE", "Aktivieren als Navigationselement");
define("_CO_SPROCKETS_TAG_NAVIGATION_DISABLE", "Deaktivieren als Navigationselement");
define("_CO_SPROCKETS_TAG_NAVIGATION_ENABLED", "Navigationselement aktiviert");
define("_CO_SPROCKETS_TAG_NAVIGATION_DISABLED", "Navigationselement deaktiviert");

// taglink
define("_CO_SPROCKETS_TAGLINK_TID", "Tag ID");
define("_CO_SPROCKETS_TAGLINK_TID_DSC", " ID des verlinkten Tags-Object.");
define("_CO_SPROCKETS_TAGLINK_MID", "Module ID");
define("_CO_SPROCKETS_TAGLINK_MID_DSC", " ID des Modules, zudem der verlinkte Inhalt gehört.");
define("_CO_SPROCKETS_TAGLINK_ITEM", "Item");
define("_CO_SPROCKETS_TAGLINK_ITEM_DSC", " ");
define("_CO_SPROCKETS_TAGLINK_IID", "Item ID");
define("_CO_SPROCKETS_TAGLINK_IID_DSC", " ID des verlinkten Inhalts-Item.");

// rights
define("_CO_SPROCKETS_RIGHTS_TITLE", "Titel");
define("_CO_SPROCKETS_RIGHTS_TITLE_DSC", " Name der geistigen Eigentumsrechte-Lizens.");
define("_CO_SPROCKETS_RIGHTS_DESCRIPTION", "Beschreibung");
define("_CO_SPROCKETS_RIGHTS_DESCRIPTION_DSC", " Eine Zusammenfassung der geistigen Eigentumsrechte-Lizens. 
		It is recommended to link to the full terms and conditions using the
	identifier field.");
define("_CO_SPROCKETS_RIGHTS_IDENTIFIER", "Link");
define("_CO_SPROCKETS_RIGHTS_IDENTIFIER_DSC", " A link to the full terms and conditions of this
	intellectual property rights license.");
define("_CO_SPROCKETS_RIGHTS_FULL_TERMS", "Die Gesamten Lizenzbestimmungen anzeigen.");

// RSS
define("_CO_SPROCKETS_RSS", "RSS");
define("_CO_SPROCKETS_NEW", "Neuer Inhalt");
define("_CO_SPROCKETS_NEW_DSC", "Die neusten Informationen von allen Bereichen der Seite.");
define("_CO_SPROCKETS_ALL", "Alle Inhalte");

define("_CO_SPROCKETS_NEWS_ALL", "Alle News");
define("_CO_SPROCKETS_SUBSCRIBE_RSS", "In den Newsfeed eintragen");
define("_CO_SPROCKETS_SUBSCRIBE_RSS_ON", "In den Newsfeed eintragen für: ");

// archive
define("_CO_SPROCKETS_ARCHIVE_MODULE_ID", "Module");
define("_CO_SPROCKETS_ARCHIVE_MODULE_ID_DSC", "Wählen Sie das Module, welches durch dieses Archiv representiert werden soll.");
define("_CO_SPROCKETS_ARCHIVE_ENABLE_ARCHIVE", "Archive aktivieren?");
define("_CO_SPROCKETS_ARCHIVE_ENABLE_ARCHIVE_DSC", "Das Archiv wird keine ankommenden OAIPMH anfragen entgegennehmen, 
		bis diese Funktion aktiviert wurde");
define("_CO_SPROCKETS_ARCHIVE_ENABLED", "Archive online");
define("_CO_SPROCKETS_ARCHIVE_ENABLED_DSC", "Schalten Sie dieses Archiv online (Ja), oder offline (Nein).");
define("_CO_SPROCKETS_ARCHIVE_TARGET_MODULE", "Zielmodul");
define("_CO_SPROCKETS_ARCHIVE_TARGET_MODULE_DSC", "Wählen Sie ein Modul aus, welches Sie für den OAIPMH
    (federation) service aktivieren wollen.");
define("_CO_SPROCKETS_ARCHIVE_METADATA_PREFIX", "Metadata Prefix");
define("_CO_SPROCKETS_ARCHIVE_METADATA_PREFIX_DSC", " Zeigt die XML-Metadata-Schematas an, 
		welche von diesem Archiv unterstützt werden. Zur Zeit wird nur der Dublin Core unterstützt (oai_dc).");
define("_CO_SPROCKETS_ARCHIVE_NAMESPACE", "Namensraum");
define("_CO_SPROCKETS_ARCHIVE_NAMESPACE_DSC", "Wird benutzt, um einzigartige Identifizierer für die Datensätze zu erstellen. 
    Standardmäßig wird die Domain der Seite verwendet. Changing this is not recommended as it helps people
    identify your archive as the source of a record that has been shared with other archives.");
define("_CO_SPROCKETS_ARCHIVE_GRANULARITY", "Detailgenauigkeit");
define("_CO_SPROCKETS_ARCHIVE_GRANULARITY_DSC", " Die Detailgenauigkeit von Zeitstempeln. Das OAIPMH lässt 
    zwei verschiedene Detailgenauigkeiten zu, diese Implementierung unterstützt die genaueste Darstellung 
    (YYYY-MM-DDThh:mm:ssZ).");
define("_CO_SPROCKETS_ARCHIVE_DELETED_RECORD", "Deleted record support");
define("_CO_SPROCKETS_ARCHIVE_DELETED_RECORD_DSC", " Does the archive support tracking of deleted
    records? This implementation does not currently support deleted records.");
define("_CO_SPROCKETS_ARCHIVE_EARLIEST_DATE_STAMP", "Frühester Zeitstempel");
define("_CO_SPROCKETS_ARCHIVE_EARLIEST_DATE_STAMP_DSC", " Der Zeitstempel für die älteste Erfassung 
		eines Archivs.");
define("_CO_SPROCKETS_ARCHIVE_ADMIN_EMAIL", "Admin E-Mail");
define("_CO_SPROCKETS_ARCHIVE_ADMIN_EMAIL_DSC", " Die E-Mail-Adresse des Administrators des
    Archives. Be aware that this address is reported in response to incoming OAIPMH requests.");
define("_CO_SPROCKETS_ARCHIVE_PROTOCOL_VERSION", "Protokoll Version");
define("_CO_SPROCKETS_ARCHIVE_PROTOCOL_VERSION_DSC", " The OAIPMH protocol version implemented by
    this repository. Zur Zeit wird nur Version 2.0 unterstützt.");
define("_CO_SPROCKETS_ARCHIVE_REPOSITORY_NAME", "Archive Name");
define("_CO_SPROCKETS_ARCHIVE_REPOSITORY_NAME_DSC", " Name deines Archivs.");
define("_CO_SPROCKETS_ARCHIVE_BASE_URL", "Base URL");
define("_CO_SPROCKETS_ARCHIVE_BASE_URL_DSC", " Die Ziel-URL an welche ankommende OAIPMH Anfragen für
    dein Archive gesendet werden sollen.");
define("_CO_SPROCKETS_ARCHIVE_COMPRESSION", "Komprimierung");
define("_CO_SPROCKETS_ARCHIVE_COMPRESSION_DSC", " Schlüsselt auf, welche Kompressionsarten für dieses Archiv unterstützt werden. 
		Momentan wird nur gzip unterstützt.");
define("_CO_SPROCKETS_ARCHIVE_ABOUT_THIS_ARCHIVE", "Unsere Punlikations Sammlung ist ein Open Archive");
define("_CO_SPROCKETS_ARCHIVE_OAIPMH_TARGET", "Diese Website implementiert das 
    <a href=\"http://www.openarchives.org/pmh/\">Open Archives Initiative Protocol for Metadata
    Harvesting</a> (OAIPMH). Compliant harvesters can access our publication metadata from the
    OAIPMH target below. OAIPMH queries should be directed to the Base URL specified below.");
define("_CO_SPROCKETS_ARCHIVE_NOT_AVAILABLE", "Sorry, die Open Archive funktionalität ist 
		momentan ausgeschaltet.");
define("_CO_SPROCKETS_ARCHIVE_NOT_CONFIGURED", "Sprockets ist gerade so eingestellt, 
		dass ankommende OAIPMH-Anfragen abgewiesen werden, Entschuldigung");
define("_CO_SPROCKETS_ARCHIVE_ENABLED_YES", "Ja");
define("_CO_SPROCKETS_ARCHIVE_ENABLED_NO", "Nein");
define("_CO_SPROCKETS_ARCHIVE_SWITCH_ONLINE", "Online schalten");
define("_CO_SPROCKETS_ARCHIVE_SWITCH_OFFLINE", "Offline schalten");
define("_CO_SPROCKETS_ARCHIVE_ONLINE", "Online");
define("_CO_SPROCKETS_ARCHIVE_OFFLINE", "Offline");

// errors
define("_CO_SPROCKETS_ONLY_ONE_ARCHIVE", "Es ist nur ein Archiv je Modul erlaubt.");

// warnings
define("_CO_SPROCKETS_ONLY_ONE_ARCHIVE_PER_MODULE", "<strong>Hinweis</strong>: Nur ein Archivobjekt 
		ist je Zielmodul erlaubt.");


// OAI info for client modules OAI page

define("_CO_SPROCKETS_IMPLEMENTS_OAI", "This website implements the <a href=\"http://www.openarchives.org/pmh/\">
	Open Archives Initiative Protocol for Metadata Harvesting</a> (OAIPMH). Compliant harvesters 
	can access our publication metadata from the OAIPMH target below. OAIPMH queries should be 
	directed to the Base URL specified below.");
define("_CO_SPROCKETS_REPOSITORY_NAME", "Repository Name");
define("_CO_SPROCKETS_METADATA_PREFIX", "Metadata prefix");
define("_CO_SPROCKETS_GRANULARITY", "Granualarity");
define("_CO_SPROCKETS_DELETED_RECORD", "Deleted record support");
define("_CO_SPROCKETS_EARLIEST_DATE_STAMP", "Earliest date stamp");
define("_CO_SPROCKETS_ADMIN_EMAIL", "Admin email");
define("_CO_SPROCKETS_PROTOCOL_VERSION", "Protocol version");
define("_CO_SPROCKETS_BASE_URL", "Base URL");
define("_CO_SPROCKETS_COMPRESSION", "Compression");
define("_CO_SPROCKETS_ARCHIVE_NOT_ENABLED", "Sorry, open archives initiative functionality is not presently enabled for this module.");