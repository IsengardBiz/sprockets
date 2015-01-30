<?php

/**
 * Class representing a Sprockets Archive object, which responds to OAIPMH requests
 *
 * A mimimal implementation of the Open Archives Initiative Protocol for Metadata Harvesting (OAIPMH)
 * Requests are received against the oaipmh_target.php file. Responses are XML streams as per the
 * OAIPMH specification, which defines a standard vocabulary and response format. See the Open Archives
 * website for more information, and for a copy of the spec.
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2010
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		archive
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

class SprocketsArchive extends icms_ipf_seo_Object {

	/**
	 * Constructor
	 *
	 * @param object $handler ArchivePostHandler object
	 */
	public function __construct(& $handler) {
		
		global $icmsConfig;

		parent::__construct($handler);

		$this->quickInitVar('archive_id', XOBJ_DTYPE_INT, TRUE);
		$this->quickInitVar('module_id', XOBJ_DTYPE_INT, TRUE);
		$this->quickInitVar('enable_archive', XOBJ_DTYPE_INT, TRUE, FALSE, FALSE, 1);
		$this->quickInitVar('metadata_prefix', XOBJ_DTYPE_TXTBOX, TRUE, FALSE, FALSE,
			$this->handler->setMetadataPrefix());
		$this->quickInitVar('namespace', XOBJ_DTYPE_TXTBOX, TRUE, FALSE, FALSE,
			$this->handler->setNamespace());
		$this->quickInitVar('granularity', XOBJ_DTYPE_TXTBOX, TRUE, FALSE, FALSE,
			$this->handler->setGranularity());
		$this->quickInitVar('deleted_record', XOBJ_DTYPE_TXTBOX, TRUE, FALSE, FALSE,
			$this->handler->setDeletedRecord());
		$this->quickInitVar('earliest_date_stamp', XOBJ_DTYPE_TXTBOX, TRUE, FALSE, FALSE,
			$this->handler->setEarliestDateStamp());
		$this->quickInitVar('admin_email', XOBJ_DTYPE_TXTBOX, TRUE, FALSE, FALSE,
			$this->handler->setAdminEmail());
		$this->quickInitVar('protocol_version', XOBJ_DTYPE_TXTBOX, TRUE, FALSE, FALSE,
			$this->handler->setProtocolVersion());
		$this->quickInitVar('repository_name', XOBJ_DTYPE_TXTBOX, TRUE, FALSE, FALSE,
			$this->handler->setRepositoryName());
		$this->quickInitVar('base_url', XOBJ_DTYPE_TXTBOX, FALSE, FALSE, FALSE,
			$this->handler->setBaseUrl());
		$this->quickInitVar('compression', XOBJ_DTYPE_TXTBOX, TRUE, FALSE, FALSE,
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

	////////////////////////////////////////////////////////
	//////////////////// PUBLIC METHODS ////////////////////
	////////////////////////////////////////////////////////
	
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
		return $this->_repository_name();
	}
	
	/**
	 * Ensures entities are escaped before sending to XML processor
	 *
	 * @return string
	 */
	
	public function base_url() {
		return $this->_base_url();
	}
	
	/**
	 * Returns the directory name for the module this archive services
	 * 
	 * @return string
	 */
	
	public function module_id() {
		return $this->_module_id();
	}
	
	/**
	 * Returns a button to enable or disable an archive object
	 */
	
	public function enable_archive() {
		return $this->_enable_archive();
	}
	
	/**
	 * Generates a standard header for OAIPMH responses
	 *
	 * @return string
	 */
	public function oai_header() {
		return $this->_oai_header();
	}
	
	/**
	 * Generates a standard footer for OAIPMH responses
	 *
	 * @return string
	 */
	public function oai_footer() {
		return $this->_oai_footer();
	}
	
	/**
	 * Returns basic information about the respository
	 *
	 * @return string
	 */
	public function identify() {
		return $this->_identify();
	}
	
	/**
	 * Returns information about the available metadata formats this repository supports (only oai_dc)
	 *
	 * @param object $content_handler
	 * @param string $identifier
	 * @return string
	 */
	public function listMetadataFormats($content_handler, $identifier = null) {
		if (is_object($content_handler)) {
			$clean_handler = $content_handler;
		} else {
			exit;
		}
		if ($identifier) {
			$clean_identifier = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($identifier, 'str', 'noencode'));
		} else {
			$clean_identifier = null;
		}
		return $this->_listMetadataFormats($clean_handler, $clean_identifier);
	}
	
	/**
	 * Returns multiple records (headers only), supports selective harvesting based on time ranges
	 *
	 * @param object $content_handler
	 * @param string $metadataPrefix
	 * @param string $from
	 * @param string $until
	 * @param string $set
	 * @param string $resumptionToken
	 * @return string
	 */
	public function listIdentifiers($content_handler, $metadataPrefix = null, $from = null,
			$until = null, $set = null, $resumptionToken = null, $cursor = 0) {
		
		if (is_object($content_handler)) {
			$clean_handler = $content_handler;
		} else {
			exit;
		}
		if ($metadataPrefix) {
			$clean_metadataPrefix = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($metadataPrefix, 'str', 'noencode'));
		} else {
			$clean_metadataPrefix = null;
		}
		if ($from) {
			$clean_from = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($from, 'str', 'noencode'));
		} else {
			$clean_from = null;
		}
		if ($until) {
			$clean_until = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($until, 'str', 'noencode'));
		} else {
			$clean_until = null;
		}
		if ($set) {
			$clean_set = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($set, 'str', 'noencode'));
		} else {
			$clean_set = null;
		}
		if ($resumptionToken) {
			$clean_resumptionToken = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($resumptionToken, 'str', 'noencode'));
		} else {
			$clean_resumptionToken = null;
		}
		$clean_cursor = isset($cursor) ? (int)$cursor : 0;

		return $this->_listIdentifiers($clean_handler, $clean_metadataPrefix, $clean_from, 
				$clean_until, $clean_set, $clean_resumptionToken, $clean_cursor);
	}
	
	/**
	 * Returns the set structure of repository (sets are not supported in this implementation)
	 *
	 * @param string $resumptionToken
	 * @return string
	 */
	public function listSets($resumptionToken = null, $cursor = 0) {
		if ($resumptionToken) {
			$clean_resumptionToken = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($resumptionToken, 'str', 'noencode'));
		} else {
			$clean_resumptionToken = null;
		}
		$clean_cursor = isset($cursor) ? (int)$cursor : 0;
		
		return $this->_listSets($clean_resumptionToken, $clean_cursor);
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
			if (isset($content_handler) && is_object($content_handler)) {
				$clean_content_handler = $content_handler;
			} else {
				exit;
			}
			if ($identifier) {
				$clean_identifier = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($identifier, 'str', 'noencode'));
			} else {
				$clean_identifier = null;
			}
			if ($metadataPrefix) {
				$clean_metadataPrefix = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($metadataPrefix, 'str', 'noencode'));
			} else {
				$clean_metadataPrefix = null;
			}
		return $this->_getRecord($clean_content_handler, $clean_identifier, $clean_metadataPrefix);
	}
	
	/**
	 * Returns multiple records (harvest entire repository, or within specified time range)
	 *
	 * @param object $content_handler
	 * @param string $metadataPrefix
	 * @param string $from
	 * @param string $until
	 * @param string $set
	 * @param string $resumptionToken
	 * @return string
	 */

	public function listRecords($content_handler, $metadataPrefix = null, $from = null,
		$until = null, $set = null, $resumptionToken = null, $cursor = 0) {
		
		if (isset($content_handler) && is_object($content_handler)) {
			$clean_content_handler = $content_handler;
		} else {
			exit;
		}
		if ($metadataPrefix) {
			$clean_metadataPrefix = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($metadataPrefix, 'str', 'noencode'));
		} else {
			$clean_metadataPrefix = null;
		}
		if ($from) {
			$clean_from = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($from, 'str', 'noencode'));
		} else {
			$clean_from = null;
		}
		if ($until) {
			$clean_until = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($until, 'str', 'noencode'));
		} else {
			$clean_until = null;
		}
		if ($set) {
			$clean_set = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($set, 'str', 'noencode'));
		} else {
			$clean_set = null;
		}
		if ($resumptionToken) {
			$clean_resumptionToken = icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($resumptionToken, 'str', 'noencode'));
		} else {
			$clean_resumptionToken = null;
		}
		$clean_cursor = isset($cursor) ? (int)$cursor : 0;
		
		return $this->_listRecords($clean_content_handler, $clean_metadataPrefix, $clean_from,
				$clean_until, $clean_set, $clean_resumptionToken, $clean_cursor);
	}
	
	/**
	 * Returns a fixed response (error message) to any non-recognised verb parameter
	 *
	 * @return string
	 */
	public function BadVerb() {
		return $this->_BadVerb();
	}
	
	// UTILITIES

	/**
	 * Converts common fields to human readable
	 *
	 * @param mixed array $content
	 * @param obj $contentObj
	 * @return mixed Array $content
	 */
	public function convert_shared_fields($content, $contentObj) {
		return $this->_convert_shared_fields($content, $contentObj);
	}
	
	/**
	 * Utility function for displaying error messages to bad OAIPMH requests
	 *
	 * @param string $error
	 * @param string $message
	 * @return string
	 */
	public function throw_error($error, $message) {
		$clean_error = !empty($error) ? icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($error, 'str', 'noencode')) : FALSE;
		$clean_message = !empty($message) ? icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($message, 'str', 'noencode')) : FALSE;
		return $this->_throw_error($clean_error, $clean_message);
	}
	
	/**
	 * Converts a single record into OAIPMH spec XML
	 *
	 * @param array $record
	 * @return string 
	 */
	public function record_to_xml($record) {
		return $this->_record_to_xml($record);
	}
	
	/**
	 * Checks that a requested time range does not occur before the repository's earliest timestamp
	 *
	 * @param string $time
	 * @return bool
	 */
	public function not_before_earliest_datestamp($time) {
		$clean_time = !empty($time) ? icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($time, 'str', 'noencode')) : FALSE;
		return $this->_not_before_earliest_datestamp($clean_time);
	}
	
	/**
	 * Retrieves the earliest content object associated with this Archive
	 * 
	 * @return string
	 */
	public function getEarliestDateStamp() {
		return $this->_getEarliestDateStamp();
	}
	
	/**
	 * Validates the datetime syntax, also checks that data does not exceed reasonable values
	 *
	 * @param string $time
	 * @return bool
	 */
	public function validate_datetime($time) {
		$clean_time = !empty($time) ? icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($time, 'str', 'noencode')) : FALSE;
		return $this->_validate_datetime($clean_time);
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
		$clean_from = !empty($from) ? icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($from, 'str', 'noencode')) : FALSE;
		$clean_until = !empty($until) ? icms::$xoopsDB->escape(icms_core_DataFilter::checkVar($until, 'str', 'noencode')) : FALSE;
		return $this->_from_precedes_until($clean_from, $clean_until);
	}
	
	/**
	 * Forces the XML response to be sent in UTF8, converts it in some other character set.
	 *
	 * @param mixed $data
	 * @return mixed
	 */
	public function data_to_utf8($data) {
		return $this->_data_to_utf8($data);
	}
	
	/**
	 * Converts a timestamp into the OAIPMH datetime format
	 *
	 * @param string $timestamp
	 * @return string
	 */
	public function timestamp_to_oaipmh_time($timestamp) {
		$clean_timestamp = (int)$timestamp;
		return $this->_timestamp_to_oaipmh_time($clean_timestamp);
	}

	/////////////////////////////////////////////////////////
	//////////////////// PRIVATE METHODS ////////////////////
	/////////////////////////////////////////////////////////

	private function _repository_name() {
		$repositoryName = htmlspecialchars(html_entity_decode($this->getVar('repository_name', 'e'),
			ENT_QUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8');
		return $repositoryName;
	}

	private function _base_url() {
		$baseURL = htmlspecialchars(html_entity_decode($this->getVar('base_url', 'e'), ENT_QUOTES,
			'UTF-8'), ENT_NOQUOTES, 'UTF-8');
		return $baseURL;
	}

	private function _module_id() {
		
		$module = $module_id = $module_handler = '';
		
		$module_id = $this->getVar('module_id', 'e');
		$module_handler = icms::handler('icms_module');
		$module = $module_handler->get($module_id);

		return $module->getVar('dirname');
	}
	
	private function _enable_archive() {
		$status = $this->getVar('enable_archive', 'e');

		$button = '<a href="' . ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/admin/archive.php?archive_id=' . $this->id() . '&amp;op=toggleStatus">';
		if ($status == 0) {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_cancel.png" alt="' 
				. _CO_SPROCKETS_ARCHIVE_OFFLINE . '" title="' . _CO_SPROCKETS_ARCHIVE_SWITCH_ONLINE 
				. '" /></a>';
		} else {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_ok.png" alt="' 
			. _CO_SPROCKETS_ARCHIVE_ONLINE . '" title="' . _CO_SPROCKETS_ARCHIVE_SWITCH_OFFLINE 
			. '" /></a>';
		}
		return $button;
	}
	
	private function _oai_header() {
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

	private function _oai_footer() {
		$footer ='</OAI-PMH>';
		return $footer;
	}

	////////// OPEN ARCHIVE INITIATIVE METHODS - MINIMAL IMPLEMENTATION AS PER THE GUIDELINES //////

	private function _identify() {
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
	
	private function _listMetadataFormats($content_handler, $identifier) {

		// accepts an optional identifier to enquire about formats available for a particular record
		// throws badArgument (how? there are no required arguments; if identifier is wrong the
		// the appropriate error = idDoesNotExist
		// throws noMetadataFormats (not necessary to implement, as oai_dc is hardwired and native
		// for everything)

		$response = '';
		$valid = TRUE;

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

	private function _listIdentifiers($content_handler, $metadataPrefix, $from, $until, $set,
			$resumptionToken, $cursor) {
		
		$haveResults = FALSE; // flag if any records were returned by query
		$rows = array();

		$response = $this->oai_header();
		
		// also modifies adds to $response
		$rows = $this->_lookupRecords($content_handler, 'ListIdentifiers', $response,
				$metadataPrefix, $from, $until, $set, $resumptionToken, $cursor);

		// if an object was in fact returned proceed to process
		if (!empty($rows)) {
			$records = $datestamp = '';
			$haveResults = TRUE;

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
		if ($haveResults == TRUE) {
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

	private function _listSets($resumptionToken, $cursor) {
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

	private function _getRecord($content_handler, $identifier, $metadataPrefix) {
		$record = $response = $dc_identifier = '';
		$valid = TRUE;
		$schema = 'oai-identifier.xsd';
		$haveResult = FALSE;

		$response = $this->oai_header();
		$response .= '<request verb="GetRecord" identifier="' . $identifier
			. '" metadataPrefix="' . $metadataPrefix . '">' . $this->getVar('base_url')
			. '</request>';

		// input validation:
		if (empty($identifier) ) {
			// throws badArgument
			$valid = FALSE;
			$response .= $this->throw_error('badArgument', 'Required argument missing: identifier');
		}

		if (empty($metadataPrefix)) {
			// throws badArgument
			$valid = FALSE;
			$response .= $this->throw_error('badArgument',
				'Required arguments missing: metadataPrefix');
		} else {
			if ($metadataPrefix !== 'oai_dc') {
				// throws cannotDisseminateFormat
				$valid = FALSE;
				$response .= $this->throw_error('cannotDisseminateFormat', 'This archive only '
					. 'supports unqualified Dublin Core metadata format');
			}
		}

		// lookup record
		if ($valid == TRUE) {

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
				$haveResult = TRUE;
				$content = $contentObj->toArray();
				
				// we need the date to remain as a timestamp though...so put it back!
				//$content['date'] = $contentObj->getVar('date', 'e');

				// lookup human readable equivalents of the keys
				// the dc_identifer must be a URL pointing at the source repository record
				// this is necessary to give credit to the source repository, and to encourage
				// sharing of records - anyone clicking on an identifier link in an external archive
				// will be bounced back to the source archive
				$content = $this->convert_shared_fields($content, $contentObj);

				// format
				if ($content['format']) {
					$extension = $contentObj->getVar('format', 'e');
					$content['format'] = $this->handler->get_mimetype($extension);
					//$content['format'] = $contentObj->get_mimetype();
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
						$sprocketsModule->getVar('dirname'), 'sprockets');
					
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
			if ($haveResult == FALSE) {
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

	private function _listRecords($content_handler, $metadataPrefix, $from, $until, $set,
			$resumptionToken, $cursor) {

		$haveResults = FALSE; // flags if any records were returned by query
		$contentArray = array();
		
		$response = $this->oai_header();
		// also modifies adds to $response
		$contentArray = $this->_lookupRecords($content_handler, 'ListRecords', $response,
			$metadataPrefix, $from,	$until, $set, $resumptionToken, $cursor);

		// if there are some contents
		if (!empty($contentArray)) {
			$records = $sql = $rows = '';
			$haveResults = TRUE;
			$contentObjArray = $rightsObjArray = $formatObjArray = array();
			$sprocketsModule = icms_getModuleInfo('sprockets');

			// prepare lookup arrays for converting object keys to human readable values
			// doing this outside of the main loop avoids massive numbers of redundant queries
			// objects use their ids as keys in the arrays for easy lookup

			$sprockets_rights_handler = icms_getModuleHandler('rights', 
					$sprocketsModule->getVar('dirname'), 'sprockets');

			$rightsObjArray = $sprockets_rights_handler->getObjects(null, TRUE);
			$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');			
			$mimetypeObjArray = $system_mimetype_handler->getObjects(null, TRUE);

			// include the build in mimetype lookup list
			$mimetype_list = icms_Utils::mimetypes();

			// process each publication and generate XML output
			foreach($contentArray as $contentObj) {
				
				$content = $contentObj->toArray();
				
				// we need the date to remain as a timestamp though...so put it back!
				//$content['date'] = $contentObj->getVar('date', 'e');

				// convert fields to human readable
				$content = $this->convert_shared_fields($content, $contentObj);				

				// format
				if ($content['format']) {
					$format_extension = $contentObj->getVar('format');
					$format_extension = ltrim($format_extension, '.');
					$content['format'] = $mimetype_list[$format_extension];
				}

				// rights
				if (!empty($content['rights'])) {
					$content['rights'] = strip_tags($content['rights']);
				}
				$records .= $this->record_to_xml($content);
			}
		}
		if ($haveResults == TRUE) {
			$response .= '<ListRecords>' . $records . '</ListRecords>';
		}
		
		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (required by XML), if not, convert it
		$response = $this->data_to_utf8($response);
		
		return $response;
	}

	private function _BadVerb() {
		$response = '';

		$response = $this->oai_header();
		$response .= '<request>' . $this->getVar('base_url') . '</request>';
		$response .= $this->throw_error('badVerb', 'Bad verb, request not compliant with '
			. 'OAIPMH specification'); // Do not move to language file, this is a specification response
		$response .= $this->oai_footer();

		// check if the character encoding is UTF-8 (required by XML), if not, convert it
		$response = $this->data_to_utf8($response);
		return $response;
	}

	////////// END OPEN ARCHIVES INITIATIVE API //////////

	// UTILITIES

	/**
	 * Retrieves content objects from the database on behalf of ListIdentifiers() and ListRecords()
	 *
	 * Note that ListIdentifiers() and ListRecords() are responsible for sanitising input to this 
	 * method
	 * 
	 * @param object $content_handler
	 * @param string $requestVerb
	 * @param string $response
	 * @param string $metadataPrefix
	 * @param string $from
	 * @param string $until
	 * @param string $set
	 * @param string $resumptionToken
	 * @return array mixed
	 */
	private function _lookupRecords($content_handler, $requestVerb, &$response, $metadataPrefix,
			$from, $until, $set, $resumptionToken, $cursor) {
			
		$sprocketsConfig = icms_getModuleConfig(basename(dirname(dirname(__FILE__))));

		$valid = TRUE; // if any part of the request is invalid, this will be set to FALSE => exit
		$response .= '<request verb="' . $requestVerb . '" metadataPrefix="' . $metadataPrefix . '"';

		if (!empty($clean_from)) {
			$response .= ' from="' . $clean_from . '"';
		}

		if (!empty($clean_until)) {
			$response .= ' until="' . $clean_until . '"';
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
			$valid = FALSE;
			$response .= $this->throw_error('badResumptionToken', 'This archive does not support '
				. 'resumption tokens, you get it all in one hit or not at all.');
		}
		if (!empty($set)) {
			// throws noSetHierarchy
			$valid = FALSE;
			$response .= $this->throw_error('noSetHierarchy', 'This archive does not support sets');
		}

		if (empty($metadataPrefix)) {
			$valid = FALSE;
			$response .= $this->throw_error('badArgument', 'Missing required argument: '
				. 'metadataPrefix');
		} else {
			if ($metadataPrefix !== 'oai_dc') {
				$valid = FALSE;
				$response .= $this->throw_error('cannotDisseminateFormat', 'This archive only '
					. 'supports unqualified Dublin Core metadata format');
			}
		}

		// validate from
		if (!empty($clean_from)) {
			$valid_timestamp = '';
			$clean_from = str_replace('Z', '', $clean_from);
			$clean_from = str_replace('T', ' ', $clean_from);

			$valid_timestamp = $this->validate_datetime($clean_from);

			if ($valid_timestamp == FALSE) {
				$valid = $FALSE;
				$response .= $this->throw_error('badArgument', 'Invalid datetime: from');
			} else {
				$valid_timestamp = $time = '';
				$time = $clean_from;
				$valid_timestamp = $this->not_before_earliest_datestamp($time);
				if ($valid_timestamp == FALSE) {
					$valid = FALSE;
					$response .= $this->throw_error('badArgument', 'Invalid datetime: from '
						. 'precedes earliest datestamp, your harvester should check this with an '
						. 'Identify request');
				}
			}
		}

		// validate until
		if (!empty($clean_until)) {
			$clean_until = str_replace('Z', '', $clean_until);
			$clean_until = str_replace('T', ' ', $clean_until);
			$valid_timestamp = $this->validate_datetime($clean_until);
			if ($valid_timestamp == FALSE) {
				$valid = $FALSE;
				$response .= $this->throw_error('badArgument', 'Invalid datetime: until');
			} else {
				$valid_timestamp = $time = '';
				$time = $clean_until;
				$valid_timestamp = $this->not_before_earliest_datestamp($time);
				if ($valid_timestamp == FALSE) {
					$valid = FALSE;
					$response .= $this->throw_error('badArgument', 'Invalid datetime: until '
						. 'precedes earliest datestamp, your harvester should check this with an '
						. 'Identify request');
				}
			}
		}

		// check that from precedes until
		if (!empty($clean_from) && !empty($clean_until)) {
			$valid_timestamp = '';
			$valid_timestamp = $this->from_precedes_until($clean_from, $clean_until);
			if ($valid_timestamp == FALSE) {
				$valid = FALSE;
				$response .= $this->throw_error('badArgument', 'Invalid datetime: until parameter '
					. 'precedes from parameter');
			}
		}

		// lookup all records within the specified time range / cursor offset limit
		// if there is a $resumptionToken, need to look at the cursor position to see where to start
		if ($valid == TRUE) {
			$clean_from = strtotime($clean_from);
			$clean_until = strtotime($clean_until);
			$sql = $rows = $fields = '';

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
			
			if (!empty($clean_from) || !empty($clean_until)) {
				if (!empty($clean_from)) {
					$shared_sql .= " `date` >= '" . $clean_from . "'";
				}
				if (!empty($clean_from) && !empty($clean_until)) {
					$shared_sql .= " AND";
				}
				if (!empty ($clean_until)) {
					$shared_sql .= " `date` <= '" . $clean_until . "'";
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
				if ($clean_from) {
					$token['from'] = $clean_from;
				}
				if ($clean_until) {
					$token['until'] = $clean_until;
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
				$contentArray = $content_handler->getObjects(null, TRUE, TRUE, $sql);
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
						. $completeListSize['count(*)']	. '" cursor="' . $next_cursor . '">' 
						. $token . '</resumptionToken>';	
			}
			return $contentArray;
		}
	}

	private function _convert_shared_fields($content, $contentObj) {
		
		// oai_identifier
		$content['oai_identifier'] = $contentObj->getVar('oai_identifier', 'e');

		// dc_identifier - a URL back to the original resource / source archive
		$content['identifier'] = $content['itemUrl'];

		// timestamp
		$content['submission_time'] = $contentObj->getVar('submission_time', 'e');

		// creator
		if ($content['creator']) {
			$creators = array();
			$creators = $contentObj->getVar('creator', 'e');
			$creators = explode('|', $creators);
			$content['creator'] = $creators;
		}
		
		// source - construct link by convention as getVar() override with link is to useful to dump
		if ($content['source']) {
			$moduleName = $contentObj->handler->_moduleName;
			$objectName = $contentObj->handler->_itemname;
			$content['source'] = ICMS_URL . '/modules/' . $moduleName . '/' . $objectName . '.php?' 
					. $objectName . '_id=' . $contentObj->getVar('source', 'e');
		}
		

		// language - ISO 639-1 two letter codes
		if ($content['language']) {
			$content['language'] = $contentObj->getVar('language', 'e');
		}

		return $content;
	}

	private function _throw_error($error, $message) {

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

	private function _record_to_xml($record) {
	
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
		$datestamp = $this->timestamp_to_oaipmh_time($record['submission_time']);

		// add a trailing space before closing paragraph tags to separate sentences when tags removed
		$record['description'] = str_replace('.<', '. <', $record['description']);

		// remove any html tags from the description field of the record
		$record['description'] = trim(strip_tags($record['description']));

		// encode entities before sending to XML processing
		foreach ($record as $key => &$value) {
			if (is_array($value)) {
				foreach ($value as $subvalue) {
					$subvalue = htmlspecialchars(html_entity_decode($subvalue, ENT_QUOTES, 'UTF-8'),
						ENT_NOQUOTES, 'UTF-8');
				}
			} else {
				$value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'),
						ENT_NOQUOTES, 'UTF-8');
			}
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

	private function _not_before_earliest_datestamp($time) {
		$request_date_stamp = $time;
		$earliest_date_stamp = $this->getEarliestDateStamp();
		$request_date_stamp = str_replace('Z', '', $request_date_stamp);
		$request_date_stamp = str_replace('T', ' ', $request_date_stamp);
		$request_date_stamp = strtotime($request_date_stamp);
		$earliest_date_stamp = str_replace('Z', '', $earliest_date_stamp);
		$earliest_date_stamp = str_replace('T', ' ', $earliest_date_stamp);
		$earliest_date_stamp = strtotime($earliest_date_stamp);

		if ($request_date_stamp >= $earliest_date_stamp) {
			$validity = TRUE;
		} else {
			$validity = FALSE;
		}
		return $validity;
	}
	
	private function _getEarliestDateStamp() {
		$earliest_date_stamp = $this->getVar('earliest_date_stamp', 'e');
		$earliest_date_stamp = $this->timestamp_to_oaipmh_time($earliest_date_stamp);
		return $earliest_date_stamp;
	}

	private function _validate_datetime($time) {
		$valid = TRUE;

		if (preg_match("/^([1-3][0-9]{3,3})-(0?[1-9]|1[0-2])-(0?[1-9]|[1-2][0-9]|3[0-1])\s([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9])$/", $time)) {
			// do nothing
		} else {
			$valid = FALSE;
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
	
	private function _from_precedes_until ($from, $until) {

		$valid = FALSE;
		$from_datetime = $until_datetime = '';

		// convert to unix timestamps for easy comparison
		$from_datetime = strtotime($from);
		$until_datetime = strtotime($until);

		if ($from_datetime < $until_datetime) {
			$valid = TRUE;
		}
		
		return $valid;
	}

	private function _data_to_utf8($data) {
		$converted = '';

		if (_CHARSET !== 'utf-8') {
			$charset = strtoupper(_CHARSET);
			$converted = iconv($charset, 'UTF-8', $data);
		} else {
			return $data;
		}
	}

	private function _timestamp_to_oaipmh_time($timestamp) {
		$format = 'Y-m-d\TH:i:s\Z';
		$oai_date_time = date($format, $timestamp);
		return $oai_date_time;
	}
}