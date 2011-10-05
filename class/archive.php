<?php

/**
 * Classes responsible for managing Archive objects and responding to OAIPMH requests
 *
 * A mimimal implementation of the Open Archives Initiative Protocol for Metadata Harvesting (OAIPMH)
 * Requests are received against the oaipmh_target.php file. Responses are XML streams as per the
 * OAIPMH specification, which defines a standard vocabulary and response format.
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2010
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		archive
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

// including the IcmsPersistabelSeoObject
include_once ICMS_ROOT_PATH . '/kernel/icmspersistableseoobject.php';
include_once(ICMS_ROOT_PATH . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/include/functions.php');

class SprocketsArchive extends IcmsPersistableSeoObject {

	/**
	 * Constructor
	 *
	 * @param object $handler ArchivePostHandler object
	 */
	public function __construct(& $handler) {
		global $icmsConfig;

		$this->IcmsPersistableObject($handler);

		$this->quickInitVar('archive_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('module_id', XOBJ_DTYPE_INT, true);
		$this->quickInitVar('enable_archive', XOBJ_DTYPE_INT, true, false, false, 1);
		$this->quickInitVar('metadata_prefix', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setMetadataPrefix());
		$this->quickInitVar('namespace', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setNamespace());
		$this->quickInitVar('granularity', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setGranularity());
		$this->quickInitVar('deleted_record', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setDeletedRecord());
		$this->quickInitVar('earliest_date_stamp', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setEarliestDateStamp());
		$this->quickInitVar('admin_email', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setAdminEmail());
		$this->quickInitVar('protocol_version', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setProtocolVersion());
		$this->quickInitVar('repository_name', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setRepositoryName());
		$this->quickInitVar('base_url', XOBJ_DTYPE_TXTBOX, false, false, false,
			$this->handler->setBaseUrl());
		$this->quickInitVar('compression', XOBJ_DTYPE_TXTBOX, true, false, false,
			$this->handler->setCompression());
		$this->initCommonVar('counter');
		$this->initCommonVar('dohtml');
		$this->initCommonVar('dobr');
		$this->initCommonVar('docxode');
				
		$this->setControl('module_id', array(
		'name' => 'select',
		'itemHandler' => 'archive',
		'method' => 'getModuleOptions',
		'module' => 'sprockets',
		'onSelect' => 'submit'));
		
		$this->setControl('enable_archive', 'yesno');

		$this->doMakeFieldreadOnly('metadata_prefix');
		$this->doMakeFieldreadOnly('namespace');
		$this->doMakeFieldreadOnly('granularity');
		$this->doMakeFieldreadOnly('deleted_record');
		$this->doMakeFieldreadOnly('earliest_date_stamp');
		$this->doMakeFieldreadOnly('protocol_version');
		$this->doMakeFieldreadOnly('base_url');
		$this->doMakeFieldreadOnly('compression');

		$this->IcmsPersistableSeoObject();
	}

	/**
	 * Overriding the IcmsPersistableObject::getVar method to assign a custom method on some
	 * specific fields to handle the value before returning it
	 *
	 * @param str $key key of the field
	 * @param str $format format that is requested
	 * @return mixed value of the field that is requested
	 */
	public function getVar($key, $format = 's') {
		if ($format == 's' && in_array($key, array ('repository_name','base_url', 'module_id',
			'enable_archive'))) {
			return call_user_func(array ($this,	$key));
		}
		return parent :: getVar($key, $format);
	}


	/**
	 * Ensures entities are escaped before sending to XML processor
	 *
	 * @return string
	 */
	public function repository_name() {
		$repositoryName = htmlspecialchars(html_entity_decode($this->getVar('repository_name', 'e'),
			ENT_QUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8');
		return $repositoryName;
	}

	/**
	 * Ensures entities are escaped before sending to XML processor
	 *
	 * @return string
	 */
	public function base_url() {
		$baseURL = htmlspecialchars(html_entity_decode($this->getVar('base_url', 'e'), ENT_QUOTES,
			'UTF-8'), ENT_NOQUOTES, 'UTF-8');
		return $baseURL;
	}

	
	public function module_id() {
		
		$module = $module_id = $module_handler = '';
		
		$module_id = $this->getVar('module_id', 'e');
		$module_handler = &xoops_gethandler('module');
		$module = $module_handler->get($module_id);

		return $module->dirname();
	}
	
	public function enable_archive() {
		$status = $this->getVar('enable_archive', 'e');

		$button = '<a href="' . ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/admin/archive.php?archive_id=' . $this->id() . '&amp;op=toggleStatus">';
		if ($status == 0) {
			$button .= '<img src="../images/button_cancel.png" alt="' . _CO_SPROCKETS_ARCHIVE_OFFLINE 
			. '" title="' . _CO_SPROCKETS_ARCHIVE_SWITCH_ONLINE . '" /></a>';
		} else {
			$button .= '<img src="../images/button_ok.png" alt="' . _CO_SPROCKETS_ARCHIVE_ONLINE
			. '" title="' . _CO_SPROCKETS_ARCHIVE_SWITCH_OFFLINE . '" /></a>';
		}
		return $button;
	}
	
	/**
	 * Generates a standard header for OAIPMH responses
	 *
	 * @return string
	 */
	public function oai_header() {
		$header = '';
		$timestamp = time();

		$timestamp = gmdate(DATE_ISO8601, $timestamp); // convert timestamp to UTC format
		$timestamp = str_replace('+0000', 'Z', $timestamp); // UTC designator 'Z' is OAI spec

		// build header

		$header .= '<?xml version="1.0" encoding="UTF-8" ?>';
		$header .= '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
            http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">';
		$header .= '<responseDate>' . $timestamp . '</responseDate>'; // must be UTC timestamp
		return $header;
	}

	/**
	 * Generates a standard footer for OAIPMH responses
	 *
	 * @return string
	 */
	public function oai_footer() {
		$footer ='</OAI-PMH>';
		return $footer;
	}

	////////// OPEN ARCHIVE INITIATIVE METHODS - MINIMAL IMPLEMENTATION AS PER THE GUIDELINES //////

	/**
	 * Returns basic information about the respository
	 *
	 * @return string
	 */
	public function identify() {
		// input validation: none required
		// throws: badArgument (how? no arguments are accepted so there is nothing to test for)
		$response = $deletedRecord = '';

		$response = $this->oai_header();
		$response .= '<request verb="Identify">' . $this->getVar('base_url') . '</request>' .
			'<Identify>' .
			'<repositoryName>' . $this->getVar('repository_name') . '</repositoryName>' .
			'<baseURL>' . $this->getVar('base_url') . '</baseURL>' .
			'<protocolVersion>' .  $this->getVar('protocol_version') . '</protocolVersion>' .
			'<adminEmail>' . $this->getVar('admin_email') . '</adminEmail>' .
			'<earliestDatestamp>' . $this->getVar('earliest_date_stamp') . '</earliestDatestamp>' .
			'<deletedRecord>' .  $this->getVar('deleted_record') . '</deletedRecord>' .
			'<granularity>' . $this->getVar('granularity') . '</granularity>' .
			'<compression>' . $this->getVar('compression') . '</compression>' .
			'</Identify>';
		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (spec/XML requirement), if not, convert it
		$response = $this->data_to_utf8($response);
		return $response;
	}
	
	/**
	 * Returns information about the available metadata formats this repository supports (only oai_dc)
	 *
	 * @param object $content_handler
	 * @param string $identifier
	 * @return string
	 */
	public function listMetadataFormats($content_handler, $identifier = null) {

		// accepts an optional identifier to enquire about formats available for a particular record
		// throws badArgument (how? there are no required arguments; if identifier is wrong the
		// the appropriate error = idDoesNotExist
		// throws noMetadataFormats (not necessary to implement, as oai_dc is hardwired and native
		// for everything)

		$response = '';
		$valid = true;

		$response = $this->oai_header();
		$response .= '<request verb="ListMetadataFormats"';
		if (!empty($identifier)) {
			$response .= ' identifier="' . $identifier . '"';
		}

		$response .= '>' . $this->getVar('base_url') . '</request>';

		// check if optional identifier is set, if so this request is regarding a particular record

		if (empty($identifier)) {

			// This archive only supports unqualified Dublin Core as its native format
			$response .= '<ListMetadataFormats>';
			$response .= '<metadataFormat>';
			$response .= '<metadataPrefix>' . $this->getVar('metadata_prefix') . '</metadataPrefix>';
			$response .= '<schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>';
			$response .= '<metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>';
			$response .= '</metadataFormat>';
			$response .= '</ListMetadataFormats>';
		} else { // an optional identifier has been provided, just check it exists
			
			$contentObj = '';
			$content_array = array();

			// only search for objects that are set as i) online and ii) federated
			$criteria = icms_buildCriteria(array('oai_identifier' => $identifier,
				'online_status' => '1', 'federated' => '1'));

			// this should return an array with only one publication object
			$content_array = $content_handler->getObjects($criteria);

			// extract the publication object
			$contentObj = array_shift($content_array);

			// if an object was in fact returned proceed to process
			if (!empty($contentObj)) {
				if ($contentObj->getVar('oai_identifier') == $identifier) {

					// This archive only supports unqualified Dublin Core as its native format
					$response .= '<ListMetadataFormats>';
					$response .= '<metadataFormat>';
					$response .= '<metadataPrefix>' . $this->getVar('metadata_prefix')
						. '</metadataPrefix>';
					$response .= '<schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>';
					$response .= '<metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>';
					$response .= '</metadataFormat>';
					$response .= '</ListMetadataFormats>';
				}
			} else {
				// otherwise throw idDoesNotExist (record doesn't exist, or is offline, or not federated)
				$response .= $this->throw_error('idDoesNotExist', 'Record identifier does not exist');
			}
		}
		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (required by XML), if not, convert it
		$response = $this->data_to_utf8($response);
		return $response;
	}

	/**
	 * Returns multiple records (headers only), supports selective harvesting based on time ranges
	 *
	 * @global mixed $xoopsDB
	 * @param object $content_handler
	 * @param string $metadataPrefix
	 * @param string $from
	 * @param string $until
	 * @param string $set
	 * @param string $resumptionToken
	 * @return string
	 */
	public function listIdentifiers($content_handler, $metadataPrefix = null, $from = null,
			$until = null, $set = null, $resumptionToken = null, $cursor = null) {

		$haveResults = false; // flag if any records were returned by query
		$rows = array();

		$response = $this->oai_header();
		
		// also modifies adds to $response
		$rows = $this->lookupRecords($content_handler, 'ListIdentifiers', $response,
				$metadataPrefix, $from, $until, $set, $resumptionToken, $cursor);

		// if an object was in fact returned proceed to process
		if (!empty($rows)) {
			$records = $datestamp = '';
			$haveResults = true;

			// generate the headers and spit out the xml
			foreach($rows as $content) {
				$datestamp = $this->timestamp_to_oaipmh_time($content['date']);
				$records .= '<header>';
				$records .= '<identifier>' . $content['oai_identifier'] . '</identifier>';
				$records .= '<datestamp>' . $datestamp . '</datestamp>';
				$records .= '</header>';
				unset($datestamp);
			}
		}
		if ($haveResults == true) {
			$response .= '<ListIdentifiers>' . $records . '</ListIdentifiers>';
		} else {
			$response .= $this->throw_error('noRecordsMatch', 'No records match the request '
				. 'parameters');
		}

		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (required by XML), if not, convert it
		$response = $this->data_to_utf8($response);
		return $response;
	}

	/**
	 * Returns the set structure of repository (sets are not supported in this implementation)
	 *
	 * @param string $resumptionToken
	 * @return string
	 */
	public function listSets($resumptionToken = null, $cursor = null) {
		// accepts optional resumptionToken
		// throws badArgument (no need to implement, as resumption tokens are not accepted)

		$response = '';

		$response = $this->oai_header();
		$response .= '<request verb="ListSets">' . $this->getVar('base_url') . '</request>';

		// this archive does not support sets or resumption tokens so the response is fixed
		if (!empty($resumptionToken)) {
			// throws badResumptionToken
			$response .= $this->throw_error('badResumptionToken', 'This archive does not support '
				. 'sets, therefore the resumption token is invalid.');
		}
		// throws noSetHierarchy
		$response .= $this->throw_error('noSetHierarchy', 'This archive does not support sets.');
		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (required by XML), if not, convert it
		$response = $this->data_to_utf8($response);
		return $response;
	}

	/**
	 * Returns a single complete record based on its unique oai_identifier
	 *
	 * @param object $content_handler
	 * @param string $identifier
	 * @param strimg $metadataPrefix
	 * @return string
	 */
	public function getRecord($content_handler, $identifier = null, $metadataPrefix = null) {
		$record = $response = $dc_identifier = '';
		$valid = true;
		$schema = 'oai-identifier.xsd';
		$haveResult = false;

		$response = $this->oai_header();
		$response .= '<request verb="GetRecord" identifier="' . $identifier
			. '" metadataPrefix="' . $metadataPrefix . '">' . $this->getVar('base_url')
			. '</request>';

		// input validation:
		if (empty($identifier) ) {
			// throws badArgument
			$valid = false;
			$response .= $this->throw_error('badArgument', 'Required argument missing: identifier');
		}

		if (empty($metadataPrefix)) {
			// throws badArgument
			$valid = false;
			$response .= $this->throw_error('badArgument',
				'Required arguments missing: metadataPrefix');
		} else {
			if ($metadataPrefix !== 'oai_dc') {
				// throws cannotDisseminateFormat
				$valid = false;
				$response .= $this->throw_error('cannotDisseminateFormat', 'This archive only '
					. 'supports unqualified Dublin Core metadata format');
			}
		}

		// lookup record
		if ($valid == true) {

			// only select records that are marked as online AND federated
			$criteria = icms_buildCriteria(array(
				'oai_identifier' => $identifier,
				'online_status' => '1', 'federated' => '1'));

			// this should return an array with only one publication object, because the
			// identifier is unique
			$content_array = $content_handler->getObjects($criteria);

			// extract the publication object
			$contentObj = array_shift($content_array);

			// if an object was in fact returned proceed to process
			if (!empty($contentObj)) {
				$haveResult = true;
				$content = $contentObj->toArray();
				
				// we need the date to remain as a timestamp though...so put it back!
				$content['date'] = $contentObj->getVar('date', 'e');

				// lookup human readable equivalents of the keys
				// the dc_identifer must be a URL pointing at the source repository record
				// this is necessary to give credit to the source repository, and to encourage
				// sharing of records - anyone clicking on an identifier link in an external archive
				// will be bounced back to the source archive
				$content = $this->convert_shared_fields($content, $contentObj);

				// format
				if ($content['format']) {
					$content['format'] = $contentObj->get_mimetype();
				}

				// rights
				if ($content['rights']) {
					$content['rights'] = strip_tags($contentObj->getVar('rights'));
				}
				
				// subject (tags)
				$sprocketsModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));
				if ($sprocketsModule) {
					$content['subject'] = array();
					$sprockets_tag_handler = icms_getModuleHandler('tag',
						$sprocketsModule->dirname(), 'sprockets');
					
					$contentObj->loadTags();
					foreach ($contentObj->getVar('tag') as $tag_id) {
						$content['subject'][] = $sprockets_tag_handler->getTagName($tag_id);
					}
				}

				$response .= '<GetRecord>';

				// this populates the record in oai_dc xml
				$response .= $this->record_to_xml($content);
				$response .= '</GetRecord>';
			}
			if ($haveResult == false) {
				// throws idDoesNotExist
				$response .= $this->throw_error('idDoesNotExist', 'Record ID does not exist, or '
					. 'has not been selected for federation');
			}
		}
		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (required by XML), if not, convert it
		$response = $this->data_to_utf8($response);
		return $response;
	}

	/**
	 * Returns multiple records (harvest entire repository, or within specified time range)
	 *
	 * @global mixed $xoopsDB
	 * @param object $content_handler
	 * @param string $metadataPrefix
	 * @param string $from
	 * @param string $until
	 * @param string $set
	 * @param string $resumptionToken
	 * @return string
	 */

	public function listRecords($content_handler, $metadataPrefix = null, $from = null,
		$until = null, $set = null, $resumptionToken = null, $cursor = null) {

		$haveResults = false; // flags if any records were returned by query
		$contentArray = array();
		
		$response = $this->oai_header();
		// also modifies adds to $response
		$contentArray = $this->lookupRecords($content_handler, 'ListRecords', $response,
			$metadataPrefix, $from,	$until, $set, $resumptionToken, $cursor);

		// if there are some contents
		if (!empty($contentArray)) {
			$records = $sql = $rows = '';
			$haveResults = true;
			$contentObjArray = $rightsObjArray = $formatObjArray = array();
			$sprocketsModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));

			// prepare lookup arrays for converting soundtrack keys to human readable values
			// doing this outside of the main loop avoids massive numbers of redundant queries
			// objects use their ids as keys in the arrays for easy lookup

			$sprockets_rights_handler = icms_getModuleHandler('rights', $sprocketsModule->dirname(),
				'sprockets');

			$rightsObjArray = $sprockets_rights_handler->getObjects(null, true);
			$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');			
			$mimetypeObjArray = $system_mimetype_handler->getObjects(null, true);

			// include the build in mimetype lookup list
			$mimetype_list = include ICMS_ROOT_PATH . '/class/mimetypes.inc.php';

			// process each publication and generate XML output
			foreach($contentArray as $contentObj) {

				$content = $contentObj->toArray();
				
				// we need the date to remain as a timestamp though...so put it back!
				$content['date'] = $contentObj->getVar('date', 'e');

				// convert fields to human readable
				$content = $this->convert_shared_fields($content, $contentObj);

				// format
				if ($content['format']) {
					$mimetypeObj = $mimetypeObjArray[$content['format']];
					$extension = $mimetypeObj->getVar('extension', 'e');
					$content['format'] = $mimetype_list[$extension];
				}

				// rights
				if (!empty($content['rights'])) {
					$content['rights'] = strip_tags($content['rights']);
				}
	
				$records .= $this->record_to_xml($content);
			}
		}
		if ($haveResults == true) {
			$response .= '<ListRecords>' . $records . '</ListRecords>';
		}
		
		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (required by XML), if not, convert it
		$response = $this->data_to_utf8($response);
		
		return $response;
	}

	/**
	 * Returns a fixed response (error message) to any non-recognised verb parameter
	 *
	 * @return string
	 */
	public function BadVerb() {
		$response = '';

		$response = $this->oai_header();
		$response .= '<request>' . $this->getVar('base_url') . '</request>';
		$response .= $this->throw_error('badVerb', 'Bad verb, request not compliant with '
			. 'OAIPMH specification');
		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (required by XML), if not, convert it
		$response = $this->data_to_utf8($response);
		return $response;
	}

	////////// END OPEN ARCHIVES INITIATIVE API //////////

	// UTILITIES

		/**
		 * Retrieves content objects from the database on behalf of GetRecord() and ListRecords()
		 *
		 * @global mixed $xoopsDB
		 * @param <type> $content_handler
		 * @param <type> $requestVerb
		 * @param <type> $response
		 * @param <type> $metadataPrefix
		 * @param <type> $from
		 * @param <type> $until
		 * @param <type> $set
		 * @param <type> $resumptionToken
		 * @return <type>
		 */
		public function lookupRecords($content_handler, $requestVerb, &$response,
				$metadataPrefix = null, $from = null, $until = null, $set = null,
				$resumptionToken = null, $cursor = null) {
			
		$sprocketsConfig = icms_getModuleConfig(basename(dirname(dirname(__FILE__))));

		$valid = true; // if any part of the request is invalid, this will be set to false => exit
		$response .= '<request verb="' . $requestVerb . '" metadataPrefix="' . $metadataPrefix . '"';

		if (!empty($from)) {
			$response .= ' from="' . $from . '"';
		}

		if (!empty($until)) {
			$response .= ' until="' . $until . '"';
		}

		if (!empty($set)) {
			$response .= ' set="' . $set . '"';
		}

		if (!empty($resumptionToken)) {
			$response .= ' resumptionToken="' . $resumptionToken . '"';
		}
		$response .= '>' . $this->getVar('base_url') . '</request>';

		// VALIDATE INPUT

		// this archive does not support resumption tokens
		if (!empty($resumptionToken)) {
			// throws badResumptionToken
			$valid = false;
			$response .= $this->throw_error('badResumptionToken', 'This archive does not support '
				. 'resumption tokens, you get it all in one hit or not at all.');
		}
		if (!empty($set)) {
			// throws noSetHierarchy
			$valid = false;
			$response .= $this->throw_error('noSetHierarchy', 'This archive does not support sets');
		}

		if (empty($metadataPrefix)) {
			$valid = false;
			$response .= $this->throw_error('badArgument', 'Missing required argument: '
				. 'metadataPrefix');
		} else {
			if ($metadataPrefix !== 'oai_dc') {
				$valid = false;
				$response .= $this->throw_error('cannotDisseminateFormat', 'This archive only '
					. 'supports unqualified Dublin Core metadata format');
			}
		}

		// validate from
		if (!empty($from)) {
			$valid_timestamp = '';
			$from = str_replace('Z', '', $from);
			$from = str_replace('T', ' ', $from);

			$valid_timestamp = $this->validate_datetime($from);

			if ($valid_timestamp == false) {
				$valid = $false;
				$response .= $this->throw_error('badArgument', 'Invalid datetime: from');
			} else {
				$valid_timestamp = $time = '';
				$time = $from;
				$valid_timestamp = $this->not_before_earliest_datestamp($time);
				if ($valid_timestamp == false) {
					$valid = false;
					$response .= $this->throw_error('badArgument', 'Invalid datetime: from '
						. 'precedes earliest datestamp, your harvester should check this with an '
						. 'Identify request');
				}
			}
		}

		// validate until
		if (!empty($until)) {
			$until = str_replace('Z', '', $until);
			$until = str_replace('T', ' ', $until);
			$valid_timestamp = $this->validate_datetime($until);
			if ($valid_timestamp == false) {
				$valid = $false;
				$response .= $this->throw_error('badArgument', 'Invalid datetime: until');
			} else {
				$valid_timestamp = $time = '';
				$time = $until;
				$valid_timestamp = $this->not_before_earliest_datestamp($time);
				if ($valid_timestamp == false) {
					$valid = false;
					$response .= $this->throw_error('badArgument', 'Invalid datetime: until '
						. 'precedes earliest datestamp, your harvester should check this with an '
						. 'Identify request');
				}
			}
		}

		// check that from precedes until
		if (!empty($from) && !empty($until)) {
			$valid_timestamp = '';
			$valid_timestamp = $this->from_precedes_until($from, $until);
			if ($valid_timestamp == false) {
				$valid = false;
				$response .= $this->throw_error('badArgument', 'Invalid datetime: until parameter '
					. 'precedes from parameter');
			}
		}

		// lookup all records within the specified time range / cursor offset limit
		// if there is a $resumptionToken, need to look at the cursor position to see where to start
		if ($valid == true) {
			$from = strtotime($from);
			$until = strtotime($until);
			$sql = $rows = $fields = '';
			global $xoopsDB;

			if ($requestVerb == 'ListRecords') {
				$fields = '*';
			} else {
				$fields = '`oai_identifier`,`date`';
			}

			/*
			 * Build two sql statements, one to count the number of records that would be returned 
			 * by these criteria, and another to return the records (but only a subset if the 
			 * total number exceeds the cursor offset preference value). Reuse some of the code.
			 */
						
			$sql = "SELECT " . $fields . " from " . $content_handler->table . " WHERE";
			$count_sql = "SELECT count(*) from " . $content_handler->table . " WHERE";
			
			if (!empty($from) || !empty($until)) {
				if (!empty($from)) {
					$shared_sql .= " `date` >= '" . $from . "'";
				}
				if (!empty($from) && !empty($until)) {
					$shared_sql .= " AND";
				}
				if (!empty ($until)) {
					$shared_sql .= " `date` <= '" . $until . "'";
				}
				$shared_sql .= " AND";
			}
			
			$shared_sql .= " `federated` = '1' AND `online_status` = '1' ";
			$shared_sql .= " ORDER BY `date` DESC";
			
			$count_sql .= $shared_sql;
			
			/*
			 * Calculate the $completeListSize. If it exceeds the cursor offset preference, then a 
			 * partial result set will be returned and we need to set a resumption token to enable  
			 * client to re-issue the request and pick up at the point where it left off. This is a 
			 * throttling mechanism to reduce DB load and script time outs that you might experience 
			 * if you have a large collection. If the number of records is less than the offset 
			 * value, no resumptionToken is needed and it is not set.
			 */
			$completeListSize = array_shift($this->handler->query($count_sql));

			if (($completeListSize['count(*)'] - $cursor) > 
					$sprocketsConfig['resumption_token_cursor_offset'] && $valid) {
				
				$token = array();
				
				// increment the cursor
				$next_cursor = $cursor + $sprocketsConfig['resumption_token_cursor_offset'];
				
				// pack the state variables into an array and serialise/urlencode it
				if ($requestVerb) {
					$token['verb'] = $requestVerb;
				}
				if ($metadataPrefix) {
					$token['metadataPrefix'] = $metadataPrefix;
				}
				if ($from) {
					$token['from'] = $from;
				}
				if ($until) {
					$token['until'] = $until;
				}
				if ($set) {
					$token['set'] = $set;
				}
				if ($next_cursor) {
					$token['cursor'] = $next_cursor;
				}
				$token = urlencode(serialize($token));
			}
			
			/*
			 * Retrieve the records
			 */
			
			$sql .= $shared_sql . " LIMIT " . $cursor . ", "
					. $sprocketsConfig['resumption_token_cursor_offset'];

			$contentArray = array();

			if ($requestVerb == 'ListRecords') {
				$contentArray = $content_handler->getObjects(null, true, true, $sql);
			} else {
				$contentArray = $this->handler->query($sql);
			}

			// if an object was in fact returned proceed to process
			if (empty($contentArray)) {
				
				// throw noRecordsMatch
				$response .= $this->throw_error('noRecordsMatch', 'No records match the request '
					. 'parameters');
			} elseif ($token) {
				
				// pass the resumption token back to $response, if there is one
				$response .= '<resumptionToken completeListSize="'
						. $completeListSize['count(*)']	. '" cursor="' . $next_cursor . '">' . $token 
						. '</resumptionToken>';	
			}
			return $contentArray;
		}
	}

	/**
	 * Converts common fields to human readable
	 *
	 * @param mixed array $content
	 * @param obj $contentObj
	 * @return mixed Array $content
	 */
	public function convert_shared_fields($content, $contentObj) {

		// dc_identifier - a URL back to the original resource / source archive
		$content['identifier'] = $content['itemUrl'];

		// timestamp
		$content['submission_time'] = strtotime($content['submission_time']);

		// creator
		if ($content['creator']) {
			$creators = $contentObj->getVar('creator', 'e');
			$creators = explode('|', $creators);
			$content['creator'] = $creators;
		}

		// language - ISO 639-1 two letter codes
		if ($content['language']) {
			$content['language'] = $contentObj->getVar('language', 'e');
		}

		return $content;
	}

	/**
	 * Utility function for displaying error messages to bad OAIPMH requests
	 *
	 * @param string $error
	 * @param string $message
	 * @return string
	 */
	public function throw_error($error, $message) {

		$response = '';

		switch ($error) {
			case "badArgument":
				$response = '<error code="badArgument">' . $message . '</error>';
				break;

			case "cannotDisseminateFormat":
				$response = '<error code="cannotDisseminateFormat">' . $message . '</error>';
				break;

			case "idDoesNotExist":
				$response = '<error code="idDoesNotExist">' . $message . '</error>';
				break;

			case "badResumptionToken":
				$response = '<error code="badResumptionToken">' . $message . '</error>';
				break;

			case "noSetHierarchy":
				$response = '<error code="noSetHierarchy">' . $message . '</error>';
				break;

			case "noMetadataFormats":
				$response = '<error code="noMetadataFormats">' . $message . '</error>';
				break;

			case "noRecordsMatch":
				$response = '<error code="noRecordsMatch">' . $message . '</error>';
				break;

			case "badVerb":
				$response = '<error code="badVerb">' . $message . '</error>';
				break;
		}
		$response = $this->data_to_utf8($response);
		return $response;
	}

	/**
	 * Template for converting a single database record to OAIPMH spec XML
	 *
	 * Generates the output for each record.
	 */

	/**
	 * Converts a single record into OAIPMH spec XML
	 *
	 * @param array $record
	 * @return string 
	 */
	public function record_to_xml($record) {

		// initialise
		$xml = $datestamp = '';
		$dublin_core_fields = array(
			'title',
			'identifier',
			'creator',
			'date',
			'type',
			'format',
			'relation',
			'description',
			'subject',
			'language',
			'publisher',
			'coverage',
			'rights',
			'source');

		// adjust the datestamp to match the OAI spec
		//$datestamp = $record['submission_time'];
		$datestamp = $this->timestamp_to_oaipmh_time($record['date']);

		// add a trailing space before closing paragraph tags to separate sentences when tags removed
		$record['description'] = str_replace('.<', '. <', $record['description']);

		// remove any html tags from the description field of the record
		$record['description'] = trim(strip_tags($record['description']));

		// encode entities before sending to XML processing
		foreach ($record as $key => &$value) {
				$value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'),
						ENT_NOQUOTES, 'UTF-8');
		}
		
		// build and populate template
		$xml .= '<record>';
		$xml .= '<header>';
		$xml .= '<identifier>' . $record['oai_identifier'] . '</identifier>';
		$xml .= '<datestamp>' . $datestamp . '</datestamp>';
		$xml .= '</header>';
		$xml .= '<metadata>';
		$xml .= '<oai_dc:dc
			xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
			xmlns:dc="http://purl.org/dc/elements/1.1/"
			xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
			xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/
			http://www.openarchives.org/OAI/2.0/oai_dc.xsd">';

		////////// iterate through optional and repeatable Dublic Core fields //////////

		foreach($dublin_core_fields as $dc_field) {
			$dc_value = '';
			$dc_value = $record[$dc_field];
			if (!empty($dc_value)) {
				if (is_array($dc_value)) {
					foreach($dc_value as $subvalue) {
						$subvalue = htmlspecialchars(html_entity_decode($subvalue, ENT_QUOTES,
							'UTF-8'), ENT_NOQUOTES, 'UTF-8');
						$xml .= '<dc:' . $dc_field . '>' . $subvalue . '</dc:' . $dc_field . '>';
					}
				} else {
					$dc_value = htmlspecialchars(html_entity_decode($dc_value, ENT_QUOTES, 'UTF-8'),
						ENT_NOQUOTES, 'UTF-8');
					$xml .= '<dc:' . $dc_field . '>' . $dc_value . '</dc:' . $dc_field . '>';
				}
			}
		}
		$xml .= '</oai_dc:dc>';
		$xml .= '</metadata>';
		$xml .= '</record>';
		return $xml;
	}

	/**
	 * Checks that a requested time range does not occur before the repository's earliest timestamp
	 *
	 * @param string $time
	 * @return bool
	 */

	public function not_before_earliest_datestamp($time) {
		$request_date_stamp = $time;
		$earliest_date_stamp = $this->getEarliestDateStamp();
		$request_date_stamp = str_replace('Z', '', $request_date_stamp);
		$request_date_stamp = str_replace('T', ' ', $request_date_stamp);
		$request_date_stamp = strtotime($request_date_stamp);
		$earliest_date_stamp = str_replace('Z', '', $earliest_date_stamp);
		$earliest_date_stamp = str_replace('T', ' ', $earliest_date_stamp);
		$earliest_date_stamp = strtotime($earliest_date_stamp);

		if ($request_date_stamp >= $earliest_date_stamp) {
			$validity = true;
		} else {
			$validity = false;
		}
		return $validity;
	}
	
	/**
	 * Retrieves the earliest content object associated with this Archive
	 * 
	 * @return string
	 */
	public function getEarliestDateStamp() {
		$earliest_date_stamp = $this->getVar('earliest_date_stamp', 'e');
		$earliest_date_stamp = $this->timestamp_to_oaipmh_time($earliest_date_stamp);
		return $earliest_date_stamp;
	}
	

	// validate datetime syntax, also checks data does not exceed reasonable values

	/**
	 * Validates the datetime syntax, also checks that data does not exceed reasonable values
	 *
	 * @param string $time
	 * @return bool
	 */
	public function validate_datetime($time) {
		$valid = true;

		if (preg_match("/^([1-3][0-9]{3,3})-(0?[1-9]|1[0-2])-(0?[1-9]|[1-2][0-9]|3[0-1])\s([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9])$/", $time)) {
			// do nothing
		} else {
			$valid = false;
		}
		
		////////// EXPLANATION OF THE DATETIME VALIDATION REGEX //////////
		//
		// This is effectively the same as the readable expression:
		// (1000-3999)-(1-12)-(1-31) (00-24):(00-59):(00-59)
		//
		// Broken down:
		// Year: ([1-3][0-9]{3,3}) Matches 1000 to 3999, easily changed.
		// Month: (0?[1-9]|1[0-2]) Matches 1 to 12
		// Day: (0?[1-9]|[1-2][0-9]|3[0-1]) Matches 1 to 31
		// Hour: ([0-1][0-9]|2[0-4]) Matches 00 to 24
		// Minute: ([0-5][0-9]) Matches 00 to 59
		// Second: ([0-5][0-9]) Same as above.
		//
		// Notes:
		// The "?" allows for the preceding digit to be optional,
		// ie: "2008-1-22" and "2008-01-22" are both valid.
		// The "^" denies input before the year, so " 2008" or "x2008" is invalid.
		// The "$" works to deny ending input.
		//
		// From: http://www.webdeveloper.com/forum/showthread.php?t=178277
		//
		////////////////////////////////////////////////////////////////

		return $valid;
	}
	
	/**
	 * Checks that the OAIPMH $from parameter precedes the $until parameter
	 *
	 * Used by ListIdentifiers() and ListRecords()
	 *
	 * @param string $from
	 * @param string $until
	 * @return boolean
	 */
	public function from_precedes_until ($from, $until) {

		$valid = false;
		$from_datetime = $until_datetime = '';

		// convert to unix timestamps for easy comparison
		$from_datetime = strtotime($from);
		$until_datetime = strtotime($until);

		if ($from_datetime < $until_datetime) {
			$valid = true;
		}
		
		return $valid;
	}

	/**
	 * Forces the XML response to be sent in UTF8, converts it in some other character set.
	 *
	 * @param <type> $data
	 * @return <type>
	 */
	public function data_to_utf8($data) {
		$converted = '';

		if (_CHARSET !== 'utf-8') {
			$charset = strtoupper(_CHARSET);
			$converted = iconv($charset, 'UTF-8', $data);
		} else {
			return $data;
		}
	}

	/**
	 * Converts a timestamp into the OAIPMH datetime format
	 *
	 * @param string $timestamp
	 * @return string
	 */
	public function timestamp_to_oaipmh_time($timestamp) {
		$format = 'Y-m-d\TH:i:s\Z';
		$oai_date_time = date($format, $timestamp);
		return $oai_date_time;
	}
}

class SprocketsArchiveHandler extends IcmsPersistableObjectHandler {

	/**
	 * Constructor
	 */
	public function __construct(& $db) {
		$this->IcmsPersistableObjectHandler($db, 'archive', 'archive_id', 'repository_name',
			'base_url', 'sprockets');
	}
	
	// INITIALISE DEFAULT ARCHIVE VALUES BECAUSE MOST OF THESE ARE FIXED

	/**
	 * Returns options to select which module this archive represents
	 *
	 * @return int
	 */
	
	public function getModuleOptions() {
		$newsModule = icms_getModuleInfo('news');
		$podcastModule = icms_getModuleInfo('podcast');
		$libraryModule = icms_getModuleInfo('library');
		
		$module_options = array();
		
		// need to add something to check that only one archive object is created per module
		
		if ($newsModule) {
			$module_options[$newsModule->mid()] = $newsModule->name();
		}

		if ($podcastModule) {
			$module_options[$podcastModule->mid()] = $podcastModule->name();
		}
		
		if ($libraryModule) {
			$module_options[$libraryModule->mid()] = $libraryModule->name();
		}
		
		return $module_options;
	}
	
	/**
	 * Returns names of compatible modules installed on the system
	 * 
	 * @return int
	 */
	
	public function getModuleNames() {
		$newsModule = icms_getModuleInfo('news');
		$podcastModule = icms_getModuleInfo('podcast');
		$libraryModule = icms_getModuleInfo('library');
		
		$module_names = array();
		
		// need to add something to check that only one archive object is created per module
		
		if ($newsModule) {
			$module_names[$newsModule->mid()] = 'news';
		}

		if ($podcastModule) {
			$module_names[$podcastModule->mid()] = 'podcast';
		}
		
		if ($libraryModule) {
			$module_names[$libraryModule->mid()] = 'library';
		}
		
		return $module_names;
	}
	
	/**
	 * Returns the only metadataprefix supported by this repository (oai_dc)
	 *
	 * @return string
	 */
	public function setMetadataPrefix() {
		return 'oai_dc';
	}

	/**
	 * One of several functions used to build a unique identifier for each record
	 *
	 * @return string
	 */
	public function setNamespace() {
		$namespace = ICMS_URL;
		$namespace = str_replace('http://', '', $namespace);
		$namespace = str_replace('https://', '', $namespace);
		$namespace = str_replace('www.', '', $namespace);
		return $namespace;
	}

	/**
	 * Returns the timestamp granularity supported by this repository in OAIPMH datetime format
	 *
	 * This implementation supports seconds-level granularity, which is the maximum.
	 *
	 * @return string
	 */
	public function setGranularity() {
		return 'YYYY-MM-DDThh:mm:ssZ';
	}

	/**
	 * Returns whether this repository supports deleted record tracking (no)
	 *
	 * @return string
	 */
	public function setDeletedRecord() {
		return 'no';
	}

	/**
	 * Sets the earliest datestamp attribute for this repository, using the Unix epoch as default
	 *
	 * If there are records in the repository, the oldest datestamp will be reported as that of
	 * the oldest record. For safety reasons, this will include offline and non-federated records
	 * so if a records online or federation status changes, nothing will be broken. If there are
	 * no records, the beginning of the Unix epoch will be used as the earliest datestamp value.
	 *
	 * @return string
	 */
	public function setEarliestDatestamp() {
		return '1970-01-01T00:00:00Z';
	}

	/**
	 * Returns the repository's admin email address, as per the OAIPMH spec requirements
	 *
	 * @global mixed $icmsConfig
	 *
	 * @return string
	 */
	public function setAdminEmail() {
		global $icmsConfig;
		return $icmsConfig['adminmail'];
	}

	/**
	 * Returns the OAIPMH version in use by this repository (2.0, the current version)
	 *
	 * @return string
	 */
	public function setProtocolVersion() {
		return '2.0';
	}

	/**
	 * Returns the name of the repository, default value is the site name in global preferences.
	 *
	 * A different respository name can be set within the Archive object.
	 *
	 * @global mixed $icmsConfig
	 *
	 * @return string
	 */
	public function setRepositoryName() {
		global $icmsConfig;
		$repository_name = $icmsConfig['sitename'] . ' - ' . $icmsConfig['slogan'];
		return $repository_name;
	}

	/**
	 * Returns the base URL, which is the URL against which OAIPMH requests should be sent
	 *
	 * @param string $directory
	 *
	 * @return string
	 */
	public function setBaseUrl($directory = false) {

		$base_url = '';

		// with a new archive, $directory will not be set and should default to the first
		// value in the select box. This is ugly but it works.
		if (!$directory) {
			$module_options = $this->getModuleOptions();
			$directory = strtolower(array_shift($module_options));
		}
		$base_url = ICMS_URL . '/modules/' . $directory . '/oaipmh_target.php';
		
		return $base_url;
	}

	/**
	 * Returns the compression scheme(s) supported by this repository (only gzip)
	 *
	 * @return string
	 */
	public function setCompression() {
		return 'gzip';
	}

	/**
	 * Converts a timestamp to the OAIPMH datetime format as per the spec
	 * 
	 * @param string $timestamp
	 * @return string
	 */
	public function timestamp_to_oaipmh_time($timestamp) {
		$format = 'Y-m-d\TH:i:s\Z';
		$oai_date_time = date($format, $timestamp);
		return $oai_date_time;
	}
	
	/**
	 * Toggles a yes/no field on or offline
	 *
	 * @param int $id
	 * @param str $field
	 * @return int $status
	 */
	public function toggleStatus($id, $field) {
		
		$status = $obj = '';
		
		$obj = $this->get($id);
		if ($obj->getVar($field, 'e') == 1) {
			$obj->setVar($field, 0);
			$status = 0;
		} else {
			$obj->setVar($field, 1);
			$status = 1;
		}
		$this->insert($obj, true);
		
		return $status;
	}

	/**
	 * Prevents more than one archive object being created per client module (only one is needed)
	 *
	 * @param object $obj
	 * @return boolean
	 */
	public function beforeSave(& $obj) {

		// check if an archive already exists for this module

		$module_id = '';
		$archive_object_array = $module_id_array = array();
		$valid_target = true;

		$module_id = $obj->getVar('module_id', 'e');
		$archive_object_array = $this->getObjects($criteria = null, true, true);

		foreach ($archive_object_array as $archive) {
			$module_id_array[$archive->id()] = $archive->getVar('module_id', 'e');
		}

		if (in_array($module_id, $module_id_array)) {

			if ($obj->isNew()) {

				// a new archive cannot target the same module as an existing one
				
				$valid_target = false;

			} else {

				// this is an existing archive object, check if current target is already selected
				if (array_key_exists($obj->id(), $module_id_array)) {
					
					// unset this archive object from the ID array
					unset($module_id_array[$obj->id()]);

					// the module ID should not match any of the remaining values
					if (in_array($module_id, $module_id_array)) {

						$valid_target = false;
					}
					
				} else {

					// we cannot change the target module if it is being handled by another archive
					$valid_target = false;
				}

			}
		}

		if ($valid_target) {

			return true;

		} else {

			$obj->setErrors(_CO_SPROCKETS_ONLY_ONE_ARCHIVE);
			return $valid_target;
		}
	}
}