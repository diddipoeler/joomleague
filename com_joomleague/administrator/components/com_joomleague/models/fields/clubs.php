<?php
/**
 * Joomleague
 *
 * @copyright	Copyright (C) 2007-2015 joomleague.at. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link		http://www.joomleague.at
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

class JFormFieldClubs extends FormField
{

	protected $type = 'clubs';
	
	function getInput() {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$lang = Factory::getLanguage();
		$extension = "com_joomleague";
		$source = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $extension);
		$lang->load($extension, JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension, $source, null, false, false)
		||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($extension, $source, $lang->getDefault(), false, false);
		
		$query->select('c.id, c.name')
		->from('#__joomleague_club c')
		->order('name');
		$db->setQuery( $query );
		$clubs = $db->loadObjectList();
		$mitems = array(HTMLHelper::_('select.option', '', JText::_('COM_JOOMLEAGUE_GLOBAL_SELECT')));

		foreach ( $clubs as $club ) {
			$mitems[] = HTMLHelper::_('select.option',  $club->id, '&nbsp;'.$club->name. ' ('.$club->id.')' );
		}
		
		$output= HTMLHelper::_('select.genericlist',  $mitems, $this->name, 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $this->value, $this->id );
		return $output;
	}
}
 