<?php
/**
 * Joomleague
 *
 * @copyright	Copyright (C) 2006-2015 joomleague.at. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link		http://www.joomleague.at
 */
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Treetonodes Controller
 */
class JoomleagueControllerTreetonodes extends JLGControllerAdmin
{

	public function __construct($config = array())
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$nid = $input->get('nid',array(),'array');
		ArrayHelper::toInteger($nid);

		if($nid)
		{
			$app->setUserState('com_joomleaguenode_id',$nid[0]);
		}

		$tid = $input->get('tid',array(),'array');
		ArrayHelper::toInteger($tid);
		if($tid)
		{
			$app->setUserState('com_joomleaguetreeto_id',$tid[0]);
		}

		parent::__construct($config);

		// Register Extra tasks
		// $this->registerTask('edit', 'display');
		$this->registerTask('apply','save');
	}

	/**
	 *
	 */
	public function displayObs($cachable = false,$urlparams = false)
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$option = $input->getCmd('option');
		$document = Factory::getDocument();
		$model = $this->getModel('treetonodes');
		$viewType = $document->getType();
		$view = $this->getView('treetonodes',$viewType);
		$view->setModel($model,true); // true is for the default model;

		$projectws = $this->getModel('project');
		$projectws->setId($app->getUserState($option . 'project',0));
		$view->setModel($projectws);

		$tid = $input->get('tid',array(),'array');

		if($tid)
		{
			// set Treeto_id
			ArrayHelper::toInteger($tid);
			$app->setUserState($option . 'treeto_id',$tid[0]);
		}
		$treetows = $this->getModel('treeto');
		$treetows->setId($app->getUserState($option . 'treeto_id'));
		$view->setModel($treetows);

		$task = $this->getTask();

		switch($task)
		{
			case 'edit':
				{
					$model = $this->getModel('treetonode');
					$viewType = $document->getType();
					$view = $this->getView('treetonode',$viewType);
					$view->setModel($model,true); // true is for the default
					                               // model;
					$view->setModel($projectws);

					$input->set('hidemainmenu',false);
					$input->set('layout','form');
					$input->set('view','treetonode');
					$input->set('edit',true);

					$model = $this->getModel('treetonode');
					$model->checkout();
				}
				break;
		}
		parent::display();
	}

	/**
	 *
	 */
	public function removenode()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$option = $input->getCmd('option');
		$post = $input->post->getArray();
		$post['treeto_id'] = $app->getUserState($option . 'treeto_id',0);

		$model = $this->getModel('treetonodes');
		if($model->setRemoveNode($post))
		{
		    $this->setMessage(Text::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CTRL_REMOVENODE'),'notice');
		}
		else
		{
		    $this->setMessage(Text::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CTRL_ERROR_REMOVENODE'),'error');
		}
		$link = 'index.php?option=com_joomleague&view=treetos';
		$this->setRedirect($link,$msg);
	}

	/**
	 *
	 */
	public function unpublishnode()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$post = $input->post->getArray();
		$model = $this->getModel('treetonode');
		if($model->setUnpublishNode())
		{
		    $this->setMessage(Text::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CTRL_UNPUBLISHNODE'),'notice');
		}
		else
		{
		    $this->setMessage(Text::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CTRL_ERROR_UNPUBLISHNODE'),'error');
		}
		$link = 'index.php?option=com_joomleague&view=treetonodes';
		$this->setRedirect($link,$msg);
	}

	/**
	 * save the checked nodes inside the trees
	 */
	public function saveshortleaf()
	{
		// Check for token
		Session::checkToken() or jexit(Text::_('COM_JOOMLEAGUE_GLOBAL_INVALID_TOKEN'));

		$app = Factory::getApplication();
		$input = $app->input;
		$option = $input->getCmd('option');
		$cid = $input->get('cid',array(),'array');
		ArrayHelper::toInteger($cid);
		$post = $input->post->getArray();
		$post['treeto_id'] = $app->getUserState($option . 'treeto_id',0);
		$model = $this->getModel('treetonodes');

		if($model->storeshortleaf($cid,$post))
		{
		    $this->setMessage(Text::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CTRL_SAVED'),'notice');
		}
		else
		{
		    $this->setMessage(Text::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CTRL_ERROR_SAVED') . $model->getError(),'error');
		}
		$link = 'index.php?option=com_joomleague&view=treetonodes';
		$this->setRedirect($link,$msg);
	}

	/**
	 *
	 */
	public function savefinishleaf()
	{
		// Check for token
		Session::checkToken() or jexit(Text::_('COM_JOOMLEAGUE_GLOBAL_INVALID_TOKEN'));

		$app = Factory::getApplication();
		$input = $app->input;
		$option = $input->getCmd('option');
		$post = $input->post->getArray();
		$post['treeto_id'] = $app->getUserState($option . 'treeto_id',0);

		$model = $this->getModel('treetonodes');
		if($model->storefinishleaf($post))
		{
		    $this->setMessage(Text::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CTRL_LEAFS_SAVED'),'notice');
		}
		else
		{
		    $this->setMessage(Text::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CTRL_LEAFS_ERROR_SAVED'),'error');
		}
		$link = 'index.php?option=com_joomleague&view=treetonodes';
		$this->setRedirect($link,$msg);
	}

	/**
	 * save the checked nodes inside the trees
	 */
	public function saveshort()
	{
		// Check for token
		Session::checkToken() or jexit(Text::_('COM_JOOMLEAGUE_GLOBAL_INVALID_TOKEN'));

		$app = Factory::getApplication();
		$input = $app->input;
		$option = $input->getCmd('option');
		$cid = $input->get('cid',array(),'array');
		ArrayHelper::toInteger($cid);
		$post = $input->post->getArray();

		$model = $this->getModel('treetonodes');
		if($model->storeshort($cid,$post))
		{
		    $this->setMessage(Text::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CTRL_SAVED'),'notice');
		}
		else
		{
		    $this->setMessage(Text::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CTRL_ERROR_SAVED') . $model->getError(),'error');
		}
		$link = 'index.php?option=com_joomleague&view=treetonodes';
		$this->setRedirect($link,$msg);
	}

	/**
	 *
	 */
	public function save()
	{
		// Check for token
		Session::checkToken() or jexit(Text::_('COM_JOOMLEAGUE_GLOBAL_INVALID_TOKEN'));

		$app = Factory::getApplication();
		$input = $app->input;
		$option = $input->getCmd('option');
		$post = $input->post->getArray();

		$model = $this->getModel('treetonode');
		if($model->store($post))
		{
		    $this->setMessage(Text::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CTRL_SAVED'),'notice');
		}
		else
		{
		    $this->setMessage(Text::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CTRL_ERROR_SAVED') . $model->getError(),'error');
		}
		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();

		$task = $this->getTask();

		if($task == 'save')
		{
			$link = 'index.php?option=com_joomleague&view=treetonodes';
		}
		else
		{
			$link = 'index.php?option=com_joomleague&view=treetonodes&task=treetonode.edit&id[]=' . $post['id'];
		}
		$this->setRedirect($link,$msg);
	}

	/**
	 * assign (empty)match to node from editmatches view
	 */
	public function assignmatch()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$option = $input->getCmd('option');
		$post = $input->post->getArray();
		$post['project_id'] = $app->getUserState($option . 'project',0);
		$post['node_id'] = $app->getUserState($option . 'node_id',0);

		$model = $this->getModel('treetonode');
		if($model->store($post))
		{
		    $this->setMessage(Text::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CTRL_ADD_MATCH'),'notice');
		}
		else
		{
		    $this->setMessage(Text::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_CTRL_ERROR_ADD_MATCH') . $model->getError(),'error');
		}
		$link = 'index.php?option=com_joomleague&view=matches';
		$this->setRedirect($link,$msg);
	}
}
