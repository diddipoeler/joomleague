<?php
/**
 * Joomleague - JComments plugin
 * 
 * @copyright	Copyright (C) 2013-2015 joomleague.at. All rights reserved.
 * @copyright	Copyright (C) 2008-2013 Julien Vonthron. All rights reserved.
 * @license		GNU/GPL, see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;

class plgContentJoomleague_Comments extends CMSPlugin {

	public $params = null;
	
	public function __construct(&$subject, $config = array())
	{
		$app = Factory::getApplication();
		$jcomments_exists = file_exists(JPATH_SITE.'/components/com_jcomments/jcomments.php');
		
		if (!$jcomments_exists && $app->isClient('site')) {
			return false;
		}
		
		parent::__construct($subject);
		
		// Get the parameters.
		if (isset($config['params']))
		{
			if ($config['params'] instanceof Registry)
			{
				$this->params = $config['params'];
			}
			else
			{
				$this->params = new Registry;
				$this->params->loadString($config['params']);
			}
		}
		
		// load language file for frontend
		CMSPlugin::loadLanguage('plg_joomleague_comments', JPATH_ADMINISTRATOR);
	}

	/**
	 * adds comments to match reports
	 * @param object match
	 * @param string title
	 * @return boolean true on success
	 */
	public function onMatchReportComments(&$match, $title, &$html)
	{
		// load plugin params info
		$separate_comments 	= $this->params->get('separate_comments', 0);

		if ($separate_comments) {
			$comments = JPATH_SITE.'/components/com_jcomments/jcomments.php';
			if (file_exists($comments))
			{
				require_once($comments);
				$html = '<div class="jlgcomments">'.JComments::show($match->id, 'com_joomleague_matchreport', $title).'</div>';
				return true;
			}
			return false;
		}
	}

	/**
	 * adds comments to match preview
	 * @param object match
	 * @param string title
	 * @return boolean true on success
	 */
	public function onNextMatchComments(&$match, $title, &$html)
	{
		// load plugin params info
		$separate_comments 	= $this->params->get('separate_comments', 0);

		if ($separate_comments) {
			$comments = JPATH_SITE.'/components/com_jcomments/jcomments.php';
			if (file_exists($comments))
			{
				require_once($comments);
				$html = '<div class="jlgcomments">'.JComments::show($match->id, 'com_joomleague_nextmatch', $title).'</div>';
				return true;
			}
			return false;
		}
	}

	/**
	 * adds comments to a match (independent if they were made before or after the match)
	 * @param object match
	 * @param string title
	 * @return boolean true on success
	 */
	public function onMatchComments(&$match, $title, &$html)
	{
		// load plugin params info
		$separate_comments 	= $this->params->get('separate_comments',0);
		
		if ($separate_comments == 0) {

			$comments = JPATH_SITE.'/components/com_jcomments/jcomments.php';
			if (file_exists($comments))
			{
				require_once($comments);
				$html = '<div class="jlgcomments">'.JComments::show($match->id, 'com_joomleague', $title).'</div>';
				return true;
			}
			return false;
		}
	}
}
