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
if (!defined("ICMS_ROOT_PATH")) die("ICMS Hauptverzeichnis nicht definiert");

// Requirements
define("_AM_SPROCKETS_REQUIREMENTS", "Sprockets Voraussetzungen");
define("_AM_SPROCKETS_REQUIREMENTS_INFO", "Ein Test ergab, dass leider nicht alle Systemvoraussetzungen erfüllt sind, 
		um Sprockets reibungslos zu verwenden. Die untenstehenden Anweisungen sind nicht erfüllt.");
define("_AM_SPROCKETS_REQUIREMENTS_ICMS_BUILD", "Sprockets benötigt mindestens ImpressCMS 1.1.1 RC 1.");
define("_AM_SPROCKETS_REQUIREMENTS_SUPPORT", "Sollten sie irgendwelche Fragen oder Besognisse haben, 
		besuchen sie bitte unser Forum: 
	<a href='http://www.impresscms.de/modules/newbb/'>http://impresscms.de</a>.");

// Tag
define("_AM_SPROCKETS_TAGS", "Tags");
define("_AM_SPROCKETS_TAGS_DSC", "Alle Tags des Modules");
define("_AM_SPROCKETS_TAG_CREATE", "Hinzufügen eines Tags");
define("_AM_SPROCKETS_TAG", "Tag");
define("_AM_SPROCKETS_TAG_CREATE_INFO", "Bitte füllen Sie das folgende Formular aus, um ein neues Tag zu erstellen.");
define("_AM_SPROCKETS_TAG_EDIT", "Tag bearbeiten");
define("_AM_SPROCKETS_TAG_EDIT_INFO", "Bitte füllen Sie das folgende Formular aus, um dieses Tag zu bearbeiten.");
define("_AM_SPROCKETS_TAG_MODIFIED", "Das Tag wurde erfolgreich modifiziert.");
define("_AM_SPROCKETS_TAG_CREATED", "Das Tag wurde erfolgreich erstellt.");
define("_AM_SPROCKETS_TAG_VIEW", "Tag Informationen");
define("_AM_SPROCKETS_TAG_VIEW_DSC", "Hier stehen die Informationen zum Tag.");
define("_AM_SPROCKETS_TAG_YES", "Ja");
define("_AM_SPROCKETS_TAG_NO", "Nein");
define("_AM_SPROCKETS_TAG_RSS_ENABLED", "RSS feed einschalten");
define("_AM_SPROCKETS_TAG_RSS_DISABLED", "RSS feed ausschalten");
define("_AM_SPROCKETS_TAG_RSS_FEED", "RSS feed?");
define("_AM_SPROCKETS_TAG_NAVIGATION_ENABLED", "Navigationselemente einschalten");
define("_AM_SPROCKETS_TAG_NAVIGATION_DISABLED", "Navigationselemente ausschalten");

// Taglink
define("_AM_SPROCKETS_TAGLINKS", "Taglinks");
define("_AM_SPROCKETS_TAGLINKS_DSC", "Alle Taglinks des Modules");
define("_AM_SPROCKETS_TAGLINK_CREATE", "Hinzufügen eines Taglinks");
define("_AM_SPROCKETS_TAGLINK", "Taglink");
define("_AM_SPROCKETS_TAGLINK_CREATE_INFO", "Bitte füllen Sie das folgende Formular aus, um einen neuen Taglink zu erstellen.");
define("_AM_SPROCKETS_TAGLINK_EDIT", "Taglink bearbeiten");
define("_AM_SPROCKETS_TAGLINK_EDIT_INFO", "Bitte füllen Sie das folgende Formular aus, um diesen 
		Taglink zu bearbeiten.");
define("_AM_SPROCKETS_TAGLINK_MODIFIED", "Der Taglink wurde erfolgreich modifiziert.");
define("_AM_SPROCKETS_TAGLINK_CREATED", "Der Taglink wurde erfolgreich erstellt.");
define("_AM_SPROCKETS_TAGLINK_VIEW", "Taglink Informationen");
define("_AM_SPROCKETS_TAGLINK_VIEW_DSC", "Hier stehen die Informationen zum Taglink.");

// Rights
define("_AM_SPROCKETS_RIGHTS", "Berechtigungen");
define("_AM_SPROCKETS_RIGHTS_DSC", "Alle Berechtigungen des Modules");
define("_AM_SPROCKETS_RIGHTS_CREATE", "Hinzufügen einer Lizenz (Berechtigung)");
define("_AM_SPROCKETS_RIGHTS_CREATE_INFO", "Bitte füllen Sie das folgende Formular aus, um neue Berechtigungen zu erstellen.");
define("_AM_SPROCKETS_RIGHTS_EDIT", "Berechtugungen bearbeiten");
define("_AM_SPROCKETS_RIGHTS_EDIT_INFO", "Bitte füllen Sie das folgende Formular aus, um die Berechtigung zu bearbeiten.");
define("_AM_SPROCKETS_RIGHTS_MODIFIED", "Die Berechtigung wurde erfolgreich modifiziert.");
define("_AM_SPROCKETS_RIGHTS_CREATED", "Die Berechtigung wurde erfolgreich erstellt.");
define("_AM_SPROCKETS_RIGHTS_VIEW", "Bechrechtigungs Informationen");
define("_AM_SPROCKETS_RIGHTS_VIEW_DSC", "Hier stehen die Informationen zu den Berechtigungen.");

// Archive
define("_AM_SPROCKETS_ARCHIVES", "Archive");
define("_AM_SPROCKETS_ARCHIVES_DSC", "Alle Archive des Modules");
define("_AM_SPROCKETS_ARCHIVE_CREATE", "Hinzufügen eines Archives");
define("_AM_SPROCKETS_ARCHIVE", "Archive");
define("_AM_SPROCKETS_ARCHIVE_CREATE_INFO", "Bitte füllen Sie das folgende Formular aus, um ein neues Archiv zu erstellen.");
define("_AM_SPROCKETS_ARCHIVE_EDIT", "Archiv bearbeiten");
define("_AM_SPROCKETS_ARCHIVE_EDIT_INFO", "Bitte füllen Sie das folgende Formular aus, 
		um das Archiv zu bearbeiten.");
define("_AM_SPROCKETS_ARCHIVE_MODIFIED", "Das Archiv wurde erfolgreich modifiziert.");
define("_AM_SPROCKETS_ARCHIVE_CREATED", "Das Archiv wurde erfolgreich erstellt.");
define("_AM_SPROCKETS_ARCHIVE_VIEW", "Archive Informationen");
define("_AM_SPROCKETS_ARCHIVE_VIEW_DSC", "Hier stehen die Informationen zum Archiv.");
define("_AM_SPROCKETS_ARCHIVE_NO_ARCHIVE","<strong>Archive Status: <span style=\"color:#red;\">None.
    </span></strong> Erstellen Sie unten ein Archivobject, wenn Sie das Open Archives Initiative
    Protocol for Metadata Harvesting einschalten wollen.<br />");
define("_AM_SPROCKETS_ARCHIVE_ONLINE", "<strong>Archive Status: <span style=\"color:#green;\">Aktiviert.
    </span></strong> Sprockets hat die Berechtigung to serve metadata in response to incoming OAIPMH
    requests.");
define("_AM_SPROCKETS_ARCHIVE_OFFLINE","<strong>Archiv Status: <span style=\"color:#red;\"> Offline.
    </span></strong> Sie sollten die Archivefunktionen in den Moduleeinstellungen ändern, wenn Sie möchten, 
		dass Sprockets to serve metadata in response to incoming OAIPMH requests.");
define("_AM_SPROCKETS_ARCHIVE_ENABLED", "Open Archives functionality enabled, incoming OAIPMH 
	requests will be served");
define("_AM_SPROCKETS_ARCHIVE_DISABLED", "Open Archives functionality disabled, incoming OAIPMH 
	requests will be refused");

// CSS editor
define("_AM_SPROCKETS_EDIT_CSS", "CSS bearbeiten");
define("_AM_SPROCKETS_CSS_SELECT_THEME", "Wählen Sie ein Theme aus, um es zu bearbeiten");
define("_AM_SPROCKETS_CSS_AVAILABLE_STYLE_SHEETS", "Verfügbare Style-Sheets");
define("_AM_SPROCKETS_CSS_EDITING", "Bearbeiten: ");
define("_AM_SPROCKETS_UPDATE_FILE", "Datei aktualisieren");
define("_AM_SPROCKETS_CANCEL", "Abbrechen");
define("_AM_SPROCKETS_CSS_SAVE_SUCCESSFUL", "CSS wurde gespeichert.");
define("_AM_SPROCKETS_CSS_SAVE_FAILED", "Warnung: Speichern der CSS fehlgeschlagen.");

// warnings
define("_AM_SPROCKETS_CATEGORY_DELETE_CAUTION", "Wollen Sie diese Kategorie wirklich löschen? 
	Unterkategorien werden ebenfalls gelöscht (Dies betrifft jedoch nicht die Inhalte).");