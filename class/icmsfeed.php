<?php
/**
 *
 * Module RSS Feed Class - hacked to enable W3C validation and to allow for enclosures
 *
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package              core
 * @since		1.1
 * @author		Ignacio Segura, "Nachenko"
 * @author		Madfish <simon@isengard.biz>
 * @version		$Id: icmsfeed.php Madfish 19/11/2010 $
 */

if (!defined('ICMS_ROOT_PATH')) {
	exit();
}

class IcmsFeed {

	public $title;
	public $url;
	public $description;
	public $language;
	public $charset;
	public $category;
	public $pubDate;
	public $webMaster;
	public $generator;
	public $copyright;
	public $lastbuild;
	public $channelEditor;
	public $width;
	public $height;
	public $ttl;
	public $image = array ();
	public $atom_link;

	/**
	 * Constructor
	 */
	public function IcmsFeed () {
		global $icmsConfig;
		$this->title = $icmsConfig['sitename'];
		$this->url = ICMS_URL;
		$this->description = $icmsConfig['slogan'];
		$this->language = _LANGCODE;
		$this->charset = _CHARSET;
		$this->pubDate = date(_DATESTRING, time());

		// need to add time zone offset to comply with RFC822-date-times or the feed won't validate
		$timezone = $icmsConfig['default_TZ'];

		// add a leading zero if offset is < 10 hours
		if (abs($timezone) < 10) {
			if ($timezone < 0) {
				$timezone = str_replace('-', '-0', $timezone);
			} else {
				$timezone = '0' . $timezone;
			}
		}
		// remove the decimal point if present and add either two trailing zeros or 30
		if (strpos($timezone, '.5')) {
			$timezone = str_replace('.5', '30', $timezone);
		} else {
			$timezone = $timezone . '00';
		}
		
		// add a + sign if the time zone offset is positive
		if ($timezone >= 0) {
			$timezone = '+' . $timezone;
		}
		$this->lastbuild = formatTimestamp(time(), 'D, d M Y H:i:s') . ' ' . $timezone;

		$this->webMaster = $icmsConfig['adminmail'];
		$this->channelEditor = $icmsConfig['adminmail'];
		$this->generator = XOOPS_VERSION;
		$this->copyright = 'Copyright ' . formatTimestamp(time(), 'Y') . ' '
			. $icmsConfig['sitename'];
		$this->width = 200;
		$this->height = 50;
		$this->ttl = 60;
		$this->image = array(
			'title' => $this->title,
			'url' => ICMS_URL.'/images/logo.gif',
		);
		$this->feeds = array();
	}

	/**
	 * Render the feed and display it directly
	 */
	public function render() {
		
		icms::$logger->disableLogger();

		header('Content-Type: application/rss+xml; charset='._CHARSET);
		$xoopsOption['template_main'] = "db:sprockets_rss.html";
		
		$tpl = new icms_view_Tpl();

		$tpl->assign('channel_title', $this->title);
		$tpl->assign('channel_link', $this->url);
		$tpl->assign('channel_desc', $this->description);
		$tpl->assign('channel_category', $this->category);
		$tpl->assign('channel_generator', $this->generator);
		$tpl->assign('channel_language', $this->language);
		$tpl->assign('channel_lastbuild', $this->lastbuild);
		$tpl->assign('channel_copyright', $this->copyright);
		$tpl->assign('channel_width', $this->width);
		$tpl->assign('channel_height', $this->height);
		$tpl->assign('channel_ttl', $this->ttl);
		$tpl->assign('image_url', $this->image['url']);
		$tpl->assign('atom_link', $this->atom_link);
		foreach ($this->feeds as $feed) {
			$tpl->append('items', $feed);
		}
		$tpl->display('db:sprockets_rss.html');
	}
}