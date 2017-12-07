<?php
/**
 * @author Wolfgang Pinitsch <andone@mfga.at>
 * 
 * @copyright	Copyright (C) 2005-2016 joomleague.at. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\Dispatcher;
use Joomla\CMS\Table\Table;

// create a link like https://opentranslators.transifex.com/projects/p/joomleague/language/en_GB/
function createTXLink($lang) {
	return '<a href="https://opentranslators.transifex.com/projects/p/joomleague/language/'.$lang.'/" target="_blank">'.$lang.'</a>';
}

class com_joomleagueInstallerScript
{
	private function _install($update=false, $parent) {
		$maxExecutionTime = $maxInputTime = 900;
		if ((int)ini_get('max_execution_time') < $maxExecutionTime){
			@set_time_limit($maxExecutionTime);
		}
		if ((int)ini_get('max_input_time') < $maxInputTime){
			@set_time_limit($maxInputTime);
		}
		$this->install_admin_rootfolder	= JPATH::clean($parent->getParent()->getPath('source').'/administrator');
		$this->install_rootfolder 		= $parent->getParent()->getPath('source');
		$this->debug = false;
		$time_start = microtime(true);
		

		$img_jlg = '<img src="../media/com_joomleague/jl_images/joomleague_logo.png">';
		$img_com = '<img src="../media/com_joomleague/jl_images/ext_com.png">';
		$img_mod = '<img src="../media/com_joomleague/jl_images/ext_mod.png">';
        $img_plg = '<img src="../media/com_joomleague/jl_images/ext_plugin.png">';
        $img_esp = '<img src="../media/com_joomleague/jl_images/ext_esp.png">';
        ?>
		<ul class="nav nav-tabs" id="joomleague-install-tabs">
        	<li class="active">
        		<a data-toggle="tab" href="#jlgcomponent"><?php echo $img_com.' '.JText::_('COM_JOOMLEAGUE_INSTALL_COMPONENT'); ?></a>
        	</li>
        	<li>
        		<a data-toggle="tab" href="#jlgdatabase"><?php echo $img_esp.' '.JText::_('COM_JOOMLEAGUE_INSTALL_DATABASE'); ?></a>
    		</li>
        	<li>
        		<a data-toggle="tab" href="#jlgmodules"><?php echo $img_mod.' '.JText::_('COM_JOOMLEAGUE_INSTALL_MODULES'); ?></a>
    		</li>
    		<li>
        		<a data-toggle="tab" href="#jlgplugins"><?php echo $img_plg.' '.JText::_('COM_JOOMLEAGUE_INSTALL_PLUGINS'); ?></a>
    		</li>
    	</ul>		
		<?php 
		
		$selector = 'joomleague';
		echo JHtml::_('bootstrap.startTabSet', $selector, array('active' => 'jlgcomponent')); 
		echo JHtml::_('bootstrap.addTab', $selector, 'jlgcomponent', 'jlgcomponent');
		
		if($update) {
			echo '<h1>'.JText::_('COM_JOOMLEAGUE_INSTALL_JOOMLEAGUE_UPDATE').'</h1>';
		} else {
			echo '<h1>'.JText::_('COM_JOOMLEAGUE_INSTALL_JOOMLEAGUE_INSTALL').'</h1>';
		}

		echo $img_jlg;
		echo JHtml::_('bootstrap.endTab');
		
		include_once($this->install_admin_rootfolder.'/components/com_joomleague/models/databasetools.php');
		echo JHtml::_('bootstrap.addTab', $selector, 'jlgdatabase', 'jlgdatabase');
		if($update) {
			self::updateDatabase($selector);
		}
		echo JHtml::_('bootstrap.endTab');
		
		if($update) {
			echo JHtml::_('bootstrap.addTab', $selector, 'jlgcomponent', 'Pictures');
			JoomleagueModelDatabaseTools::migratePicturePath($selector);
			echo JHtml::_('bootstrap.endTab');
				
			echo JHtml::_('bootstrap.addTab', $selector, 'jlgcomponent', 'Eventtypes Suspensions');
			JoomleagueModelDatabaseTools::updateEventtypeSuspensions($selector);
			echo JHtml::_('bootstrap.endTab');
				
		}
		
		echo JHtml::_('bootstrap.addTab', $selector, 'jlgcomponent', 'Images');
		self::createImagesFolder($selector);
		echo JHtml::_('bootstrap.endTab');
		
		echo JHtml::_('bootstrap.addTab', $selector, 'jlgcomponent', 'Languages');
		self::installComponentLanguages($selector);
		echo JHtml::_('bootstrap.endTab');

		echo JHtml::_('bootstrap.addTab', $selector, 'jlgcomponent', 'Install');
		include_once($this->install_rootfolder.'/components/com_joomleague/joomleague.core.php');
		include_once($this->install_admin_rootfolder.'/components/com_joomleague/assets/updates/jl_install.php');
		echo JHtml::_('bootstrap.endTab');
		
		echo JHtml::_('bootstrap.addTab', $selector, 'jlgmodules', 'Modules');
		self::installModules();
		echo JHtml::_('bootstrap.endTab');
		
		echo JHtml::_('bootstrap.addTab', $selector, 'jlgplugins', 'Plugins');
		self::installPlugins($selector);
		echo JHtml::_('bootstrap.endTab');
		
		//todo: create a default set of permissions
		//echo JHtml::_('bootstrap.addTab', $selector, 'permissions', 'Permissions');
		//self::installPermissions();
		//echo JHtml::_('bootstrap.endTab');
		?>
		<hr />
		<br>Click on <a href="index.php?option=com_installer&view=discover&task=discover.refresh">Discover-&gt;Refresh</a> to discover new or updated Modules and Plugins.
	<?php
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		echo '<hr />';
		echo '<br>Overall Duration: '.round($time).'s<br>';
		echo JHtml::_('bootstrap.endTabSet');
	}

	/**
	 * method to install the component languages
	 *
	 * @return void
	 */
	public function installComponentLanguages()
	{
		$time_start = microtime(true);
		$arrAdminLanguages = array(); 
		$arrLanguages = array(); 
		echo 'All language translations are powered by <a href="https://opentranslators.transifex.com/projects/p/joomleague/">Transifex</a>';
		
		$src = $this->install_admin_rootfolder.'/components/com_joomleague/';
		$dest = JPATH_ADMINISTRATOR.'/language';
		if($this->debug) {
			echo '<br>copy ' . $src.'/language' . ' -> ' . JPATH_ADMINISTRATOR.'/language';
		}
		JFolder::copy($src.'/language', JPATH_ADMINISTRATOR.'/language', '', true);
		
		$languages = JFolder::folders($src.'/language');
		foreach ($languages as $lang)
		{
			$arrAdminLanguages[] = str_replace('-', '_', $lang);
		}
		
		$src = $this->install_rootfolder.'/components/com_joomleague/';
		$dest = JPATH_SITE.'/';
		if($this->debug) {
			echo '<br>copy ' . $src.'/language' . ' -> ' . JPATH_SITE.'/language';
		}
		JFolder::copy($src.'/language', JPATH_SITE.'/language', '', true);
		$languages = JFolder::folders($src.'/language');
		foreach ($languages as $lang)
		{
			$arrLanguages[] = str_replace('-', '_', $lang);
		}
		
		echo '<br><br>Available backend translations: ';
		if(count($arrAdminLanguages)) {
			echo implode(', ', array_unique($arrAdminLanguages, SORT_STRING));
		} else {
			echo 'none';
		}
		echo ' - <span style="color:green">'.JText::_('Success').'</span><br>';

		echo '<br>Available frontend translations: ';
		if(count($arrLanguages)) {
			echo implode(', ', array_map('createTXLink', array_unique($arrLanguages, SORT_STRING)));
		} else {
			echo 'none';
		}
		echo ' - <span style="color:green">'.JText::_('Success').'</span><br>';
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		echo 'Duration: '.round($time).'s<br>';
	}

	/**
	 * method to install the modules
	 *
	 * @return void
	 */
	public function installModules()
	{
		$time_start = microtime(true);
		$arrAdminModules = array(); 
		$arrModules = array(); 
		$src=$this->install_admin_rootfolder.'/components/com_joomleague/modules';
		if(JFolder::exists($src)) {
			$dest=JPATH_ADMINISTRATOR.'/modules';
			$modules = JFolder::folders($src);
			foreach ($modules as $module)
			{
				$arrAdminModules[$module] = array();
				if(JFolder::exists($src.'/'.$module.'/language')) {
					$langs = JFolder::folders($src.'/'. $module . '/language');
					foreach ($langs as $lang)
					{
						$arrAdminModules[$module][] = str_replace('-', '_', $lang);
					}
					JFolder::copy($src.'/'.$module.'/language', JPATH_ADMINISTRATOR.'/language', '', true);
				}
			}
			JFolder::copy($src, $dest, '', true);
		} else {
			echo "No administration Module(s) copied<br>";
		}
		
		$src = $this->install_rootfolder.'/components/com_joomleague/modules';
		if(JFolder::exists($src)) {
			$dest=JPATH_SITE.'/modules';
			$modules = JFolder::folders($src);
			foreach ($modules as $module)
			{
				$arrModules[$module] = array();
				if(JFolder::exists($src.'/'.$module.'/language')) {
					$langs = JFolder::folders($src.'/'. $module . '/language');
					foreach ($langs as $lang)
					{
						$arrModules[$module][] = str_replace('-', '_', $lang);
					}
					JFolder::copy($src.'/'.$module.'/language', JPATH_SITE.'/language', '', true);
				}
			}
			JFolder::copy($src, $dest, '', true);
			$selector = "joomleagueaccordionadminmodules";
			echo JHtml::_('bootstrap.startAccordion', $selector);
			$text =  JText::_('COM_JOOMLEAGUE_INSTALL_ADMINISTRATOR_MODULES');
			echo JHtml::_('bootstrap.addSlide', $selector, $text, 'adminslide');
				
			$m=0;
			foreach($arrAdminModules as $k => $mod)
			{
				$text = JText::_('COM_JOOMLEAGUE_INSTALL_ADMINISTRATOR_MODULE_'.strtoupper($k)); 
				echo JHtml::_('bootstrap.addSlide', $selector, $text, 'adminslide'.$m++);
				echo 'Available translations: ';
				if(isset($arrModules[$k]) && count($arrModules[$k])) {
					echo implode(', ', array_unique($arrModules[$k], SORT_STRING));
				} else {
					echo 'none';
				}
				echo ' - <span style="color:green">'.JText::_('Success').'</span>';
				echo JHtml::_('bootstrap.endSlide');
			}
			
			echo JHtml::_('bootstrap.endSlide');
			echo JHtml::_('bootstrap.endAccordion');
			
			$selector = "joomleagueaccordionsitemodules";
			echo JHtml::_('bootstrap.startAccordion', $selector);
			$text =  JText::_('COM_JOOMLEAGUE_INSTALL_SITE_MODULES');
			echo JHtml::_('bootstrap.addSlide', $selector, $text, 'siteslide');
				
			$m=0;
			foreach($arrModules as $k => $mod)
			{
				$text = JText::_('COM_JOOMLEAGUE_INSTALL_SITE_MODULE_'.strtoupper($k)); 
				echo JHtml::_('bootstrap.addSlide', $selector, $text, 'siteslide'.$m++);
				echo 'Available translations: ';
				if(isset($arrModules[$k]) && count($arrModules[$k])) {
					echo implode(', ', array_unique($arrModules[$k], SORT_STRING));
				} else {
					echo 'none';
				}
				echo ' - <span style="color:green">'.JText::_('Success').'</span>';
				echo JHtml::_('bootstrap.endSlide');
			}
			echo JHtml::_('bootstrap.endSlide');
			echo JHtml::_('bootstrap.endAccordion');
		} else {
			echo "No Module(s) copied<br>";
		}
		self::_addJoomLeagueBugtrackerModule();

		$time_end = microtime(true);
		$time = $time_end - $time_start;
		echo 'Duration: '.round($time).'s<br>';
	}

	/**
	 * method to install the plugins
	 *
	 * @return void
	 */
	public function installPlugins()
	{
		$time_start = microtime(true);
		$arrPlugins = array(); 
		$src = $this->install_rootfolder.'/components/com_joomleague/plugins';
		if(JFolder::exists($src)) {
			$dest=JPATH_SITE.'/plugins';
			$groups = JFolder::folders($src);
			foreach ($groups as $group)
			{
				$plugins = JFolder::folders($src.'/'.$group);
				foreach ($plugins as $plugin)
				{
					$arrPlugins[$group.'/'.$plugin] = array();
					if(JFolder::exists($src.'/'.$group.'/'.$plugin.'/language')) 
					{
						$langs = JFolder::folders($src.'/'.$group.'/'.$plugin.'/language');
						foreach ($langs as $lang)
						{
							$arrPlugins[$group.'/'.$plugin][] = $lang;
						}
						JFolder::copy($src.'/'.$group.'/'.$plugin.'/language', JPATH_ADMINISTRATOR.'/language', '', true);
					}
				}
			}
			JFolder::copy($src, $dest, '', true);
			
			$selector = "joomleagueaccordionplugins";
			echo JHtml::_('bootstrap.startAccordion', $selector);
			$text =  JText::_('COM_JOOMLEAGUE_INSTALL_PLUGINS');
			echo JHtml::_('bootstrap.addSlide', $selector, $text, 'pluginsslide');
				
			$p=0;
			foreach($arrPlugins as $k => $plg) 
			{
				$text = JText::_('COM_JOOMLEAGUE_INSTALL_PLUGIN_'.str_replace('/', '_', strtoupper($k))); 
				echo JHtml::_('bootstrap.addSlide', $selector, $text, 'pluginsslide'.$p++);
				
				echo 'Available translations: ';
				if(isset($arrPlugins[$k]) && count($arrPlugins[$k])) {
					echo implode(', ', array_unique($arrPlugins[$k], SORT_STRING));
				} else {
					echo 'none';
				}
				echo ' - <span style="color:green">'.JText::_('Success').'</span>';
				echo JHtml::_('bootstrap.endSlide');
			}
			
			echo JHtml::_('bootstrap.endSlide');
			echo JHtml::_('bootstrap.endAccordion');
		} else {
			echo 'No Plugin(s) copied<br>';
		}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		echo '<br>Duration: '.round($time) . 's';
	}
	
	public static function installPermissions()
	{
		$time_start = microtime(true);
		jimport('joomla.access.rules');
		$app = Factory::getApplication();
	
		// Get the root rules
		$root = Table::getInstance('asset');
		$root->loadByName('root.1');
		$root_rules = new Access($root->rules);
	
		// Define the new rules
		$ACL_PERMISSIONS = '{"core.admin":[],"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"settings.edit":[],"settings.save":[]}';
		$new_rules = new Access($ACL_PERMISSIONS);
	
		// Merge the rules into default rules and save it
		$root_rules->merge($new_rules);
		$root->rules = (string)$root_rules;
		if ( $root->store() ) {
			echo 'Installed ACL Permissions';
			echo ' - <span style="color:green">'.JText::_('Success').'</span><br />';
		}
		else {
			echo ' - <span style="color:red">'.JText::_('Failed').'</span><br />';
		}
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		echo 'Duration: '.round($time).'s<br>';
	}
	
	
	public function updateDatabase() {
		$time_start = microtime(true);

		echo 'Updating Database';
		echo ' - <span style="color:green">'.JText::_('Success').'</span>';
		echo JHtml::_('bootstrap.startAccordion','details',array(
						'allowAllClose' => true,
						'startTransition' => true,
						true));
		echo JHtml::_('bootstrap.addSlide', 'Details', 'panel-details');

		echo '<div style="width:100%; height: 200px; overflow: auto">';
		JoomleagueModelDatabaseTools::ImportTables();
		echo '</div>';
		echo JHtml::_('bootstrap.endSlide');
		echo JHtml::_('bootstrap.endAccordion');
		echo '<br />';
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		echo '<br>Duration: '.round($time).'s<br>';
	}
	
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	public function install($parent)
	{
		if(self::_versionCompare()) {
			self::_install(false, $parent);
		}
	}

	private function _versionCompare () {
		if (version_compare(phpversion(), '5.3.0', '<')===true) {
			echo  '<div style="font:12px/1.35em arial, helvetica, sans-serif;"><div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;"><h3 style="margin:0; font-size:1.7em; font-weight:normal; text-transform:none; text-align:left; color:#2f2f2f;">Whoops, it looks like you have an invalid PHP version.</h3></div><p>JoomLeague requires PHP 5.2.4 or newer.</p><p>PHP4 is no longer supported by its developers and your webhost almost certainly offers PHP5.  Please contact your webhost for advice on how to enable PHP5 on your website.</p></div>';
			return false;
		}
		return true;
	}
	 
	/**
	 * method to update the component
	 *
	 * @return void
	 */
	public function update($parent)
	{
		if(self::_versionCompare()) { 
			self::_install(true, $parent);
		}
	}

	public function postflight($route, $adapter) {
		//-----------------------------------------------------
		//Table `#__extensions` Bugfix needed due a wrong client_id for the update system
		//-----------------------------------------------------
		$db = Factory::getDbo();
		$query="UPDATE `#__extensions` SET client_id=0 WHERE name='joomleague'";
		$db->setQuery($query);
		try
					{
						$db->execute();
					}
					catch (RuntimeException $e)
					{
						throw new Exception($e->getMessage());
					}
		
	}
	
	public function uninstall($adapter)
	{
		$params = ComponentHelper::getParams('com_joomleague');
		//Also uninstall db tables of JoomLeague?
		$uninstallDB = $params->get('cfg_drop_joomleague_tables_when_uninstalled',0); 
		
		if ($uninstallDB)
		{
			echo JText::_('Also removing database tables of JoomLeague');
			include_once(JPATH_ADMINISTRATOR.'/components/com_joomleague/models/databasetools.php');
			JoomleagueModelDatabaseTools::dropJoomLeagueTables();
		}
		else
		{
			echo JText::_('Database tables of JoomLeague are not removed');
		}
		?>
		<div class="header">JoomLeague has been removed from your system!</div>
		<p>To completely remove Joomleague from your system, be sure to also
			uninstall the JoomLeague modules, plugins and languages.</p>

		<?php
		return true;
	}
	
	public function createImagesFolder()
	{
		$time_start = microtime(true);
		echo JText::_('Creating new Image Folder structure');
		$src = JPath::clean($this->install_rootfolder.'/media/com_joomleague/database');
		$dest = JPath::clean(JPATH_ROOT.'/images/com_joomleague/database');
	
		if(JFolder::exists($src)) {
			$ret = JFolder::copy($src, $dest, '', true);
		}
		JFile::copy(JPATH_ROOT.'/media/index.html', JPATH_ROOT.'/images/com_joomleague/index.html', '', true);
		$folders = JFolder::folders($dest,'.',true);
		foreach ($folders as $folder) {
			$src = JPath::clean(JPATH_ROOT.'/media/com_joomleague/'.$folder);
			if(JFolder::exists($src)) {
				$to = JPath::clean($dest.'/'.$folder);
				if(!JFolder::exists($to)) {
					$ret = JFolder::move($src, $to);
				} else {
					$ret = JFolder::copy($src, $to, '', true);
					$ret = JFolder::delete($src);
				}
			}
		}
		//$from = JPath::clean(JPATH_ROOT.'/media/com_joomleague/database');
		//$ret = JFolder::delete($from);
		echo ' - <span style="color:green">'.JText::_('Success').'</span>';
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		echo '<br>Duration: '.round($time).'s<br>';
	}
	
	private function _addJoomLeagueBugtrackerModule() {
		$title = 'JoomLeague Bugtracker';
		$tblModules = Table::getInstance('module');
		$tblModules->load(array('title'=>$title));
		$tblModules->title			= $title;
		$tblModules->module			= 'mod_feed';
		$tblModules->position 		= 'cpanel';
		$tblModules->published 		= 1;
		$tblModules->client_id 		= 1;
		$tblModules->ordering 		= 1;
		$tblModules->access 		= 3;
		$tblModules->language 		= '*';
		$tblModules->publish_up		= '2000-00-00 00:00:00';
		$tblModules->params 		= '{"rssurl":"http:\/\/tracker.joomleague.at\/projects\/joomleague\/issues.atom?c%5B%5D=project&c%5B%5D=tracker&c%5B%5D=parent&c%5B%5D=status&c%5B%5D=subject&c%5B%5D=assigned_to&c%5B%5D=fixed_version&c%5B%5D=due_date&f%5B%5D=updated_on&f%5B%5D=status_id&f%5B%5D=&group_by=&key=ad01ef2fceecf0c7c51812792f3b9cd54612ef15&op%5Bstatus_id%5D=%3D&op%5Bupdated_on%5D=%3E%3Ct-&set_filter=1&utf8=%E2%9C%93&v%5Bstatus_id%5D%5B%5D=3&v%5Bstatus_id%5D%5B%5D=5&v%5Bstatus_id%5D%5B%5D=6&v%5Bupdated_on%5D%5B%5D=30","rssrtl":"0","rsstitle":"1","rssdesc":"1","rssimage":"1","rssitems":"10","rssitemdesc":"1","word_count":"100","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900"}';
	
		if (!$tblModules->store()) {
			echo $tblModules->getError().'<br />';
		}
		$db = Factory::getDbo();
		$query = 'INSERT IGNORE INTO #__modules_menu (moduleid,menuid) VALUES ('.$tblModules->id.',0)';
		$db->setQuery($query);
		try
					{
						$db->execute();
					}
					catch (RuntimeException $e)
					{
						throw new Exception($e->getMessage());
					}
		
			
		// Initialise variables
		$conf = Factory::getConfig();
		//$dispatcher = Dispatcher::getInstance();
			
		$options = array(
				'defaultgroup' => ($tblModules->module) ? $tblModules->module : (isset($this->option) ? $this->option : Factory::getApplication()->input->get('option')),
				'cachebase' => ($tblModules->client_id) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get('cache_path', JPATH_SITE . '/cache'));
			
		$cache = Cache::getInstance('callback', $options);
		$cache->clean();
	}
	
}