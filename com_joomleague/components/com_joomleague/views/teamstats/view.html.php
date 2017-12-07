<?php 
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

/**
 * View-Teamstats
 */
class JoomleagueViewTeamStats extends JLGView
{
	public function display($tpl = null)
	{
		$this->model = $this->getModel();
		$this->overallconfig = $this->model->getOverallConfig();
		if (!isset($this->overallconfig['seperator']))
		{
			$this->overallconfig['seperator'] = ':';
		}
		$this->config = $this->model->getTemplateConfig($this->getName());
		$this->tableconfig = $this->model->getTemplateConfig('ranking');
		$this->eventsconfig = $this->model->getTemplateConfig('eventsranking');
		$flashconfig = $this->model->getTemplateConfig('flash');
		$this->project = $this->model->getProject();
		if (isset($this->project))
		{
			$this->actualround = $this->model->getCurrentRound();
			$this->team = $this->model->getTeam();
			$this->highest_home = $this->model->getHighestHome();
			$this->highest_away = $this->model->getHighestAway();
			$this->highestdef_home = $this->model->getHighestDefHome();
			$this->highestdef_away = $this->model->getHighestDefAway();
			$this->totalshome = $this->model->getSeasonTotalsHome();
			$this->totalsaway = $this->model->getSeasonTotalsAway();
			$this->matchdaytotals = $this->model->getMatchDayTotals();
			$this->totalrounds = $this->model->getTotalRounds();
			$this->totalattendance = $this->model->getTotalAttendance();
			$this->bestattendance = $this->model->getBestAttendance();
			$this->worstattendance = $this->model->getWorstAttendance();
			$this->averageattendance = $this->model->getAverageAttendance();
			$this->chart_url = $this->model->getChartURL();
			$this->nogoals_against = $this->model->getNoGoalsAgainst();
			$this->logo = $this->model->getLogo();
			$this->results = $this->model->getResults();

			$this->_setChartdata(array_merge($flashconfig, $this->config));
		}
		$this->setTitlePage();

		parent::display($tpl);
	}

	private function setTitlePage()
	{
		// Set page title
		$titleInfo = JoomleagueHelper::createTitleInfo(JText::_('COM_JOOMLEAGUE_TEAMSTATS_PAGE_TITLE'));
		if (!empty($this->team))
		{
			$titleInfo->team1Name = $this->team->name;
		}
		if (!empty($this->project))
		{
			$titleInfo->projectName = $this->project->name;
			$titleInfo->leagueName = $this->project->league_name;
			$titleInfo->seasonName = $this->project->season_name;
		}
		$app = Factory::getApplication();
		$input = $app->input;
		$division = $this->model->getDivision($input->getInt('division', 0));
		if (!empty($division) && $division->id != 0)
		{
			$titleInfo->divisionName = $division->name;
		}
		$this->pagetitle = JoomleagueHelper::formatTitle($titleInfo, $this->config['page_title_format']);
		$document = Factory::getDocument();
		$document->setTitle($this->pagetitle);
	}

	function averageValue($value, $numberOfItems)
	{
		return !empty($numberOfItems) ? round($value / $numberOfItems, 2) : 0;
	}

	/**
	 * assign the chartdata object for open flash chart library
	 * @param $config
	 * @return unknown_type
	 */
	function _setChartdata($config)
	{
		require_once JLG_PATH_SITE.'/assets/classes/open-flash-chart/open-flash-chart.php';

		$data = $this->get('ChartData');

		// Calculate Values for Chart Object
		$forSum = array();
		$againstSum = array();
		$matchDayGoalsCount = array();
		$matchDayGoalsCount[] = 0;
		$round_labels = array();
		foreach($data as $rw)
		{
			if (!$rw->goalsfor)
			{
				$rw->goalsfor = 0;
			}
			if (!$rw->goalsagainst)
			{
				$rw->goalsagainst = 0;
			}
			$forSum[]     = intval($rw->goalsfor);
			$againstSum[] = intval($rw->goalsagainst);

			// check, if both results are missing and avoid drawing the flatline of '0' goals for not played games yet
			if (!$rw->goalsfor && !$rw->goalsagainst)
			{
				$matchDayGoalsCount[] = 0;
			}
			else
			{
				$matchDayGoalsCount[] = intval($rw->goalsfor + $rw->goalsagainst);
			}
			$round_labels[] = $rw->roundcode;
		}
		
		$chart = new open_flash_chart();
		$chart->set_bg_colour($config['bg_colour']);

		$barfor = new $config['bartype_1']();
		$barfor->set_values($forSum);
		$barfor->set_tooltip(JText::_('COM_JOOMLEAGUE_TEAMSTATS_GOALS_FOR'). ': #val#');
		$barfor->set_colour($config['bar1']);
		$barfor->set_on_show(new bar_on_show($config['animation_1'], $config['cascade_1'], $config['delay_1']));
		$barfor->set_key(JText::_('COM_JOOMLEAGUE_TEAMSTATS_GOALS_FOR'), 12);

		$baragainst = new $config['bartype_2']();
		$baragainst->set_values($againstSum);
		$baragainst->set_tooltip(  JText::_('COM_JOOMLEAGUE_TEAMSTATS_GOALS_AGAINST'). ': #val#');
		$baragainst->set_colour($config['bar2']);
		$baragainst->set_on_show(new bar_on_show($config['animation_2'], $config['cascade_2'], $config['delay_2']));
		$baragainst->set_key(JText::_('COM_JOOMLEAGUE_TEAMSTATS_GOALS_AGAINST'), 12);

		$chart->add_element($barfor);
		$chart->add_element($baragainst);

		// total
		$d = new $config['dotstyle_3']();
		$d->size((int)$config['line3_dot_strength']);
		$d->halo_size(1);
		$d->colour($config['line3']);
		$d->tooltip(JText::_('COM_JOOMLEAGUE_TEAMSTATS_TOTAL2').' #val#');

		$line = new line();
		$line->set_default_dot_style($d);
		$line->set_values(array_slice($matchDayGoalsCount,1));
		$line->set_width((int) $config['line3_strength']);
		$line->set_key(JText::_('COM_JOOMLEAGUE_TEAMSTATS_TOTAL'), 12);
		$line->set_colour($config['line3']);
		$line->on_show(new line_on_show($config['l_animation_3'], $config['l_cascade_3'], $config['l_delay_3']));
		$chart->add_element($line);
		
		$x = new x_axis();
		$x->set_colours($config['x_axis_colour'], $config['x_axis_colour_inner']);
		$x->set_labels_from_array($round_labels);
		$chart->set_x_axis($x);
		$x_legend = new x_legend(JText::_('COM_JOOMLEAGUE_TEAMSTATS_ROUNDS'));
		$x_legend->set_style('{font-size: 15px; color: #778877}');
		$chart->set_x_legend($x_legend);

		$y = new y_axis();
		$y->set_range(0, max($matchDayGoalsCount)+2, $config['y_axis_steps']);
		$y->set_colours($config['y_axis_colour'], $config['y_axis_colour_inner']);
		$chart->set_y_axis($y);
		$y_legend = new y_legend(JText::_('COM_JOOMLEAGUE_TEAMSTATS_GOALS'));
		$y_legend->set_style('{font-size: 15px; color: #778877}');
		$chart->set_y_legend($y_legend);

		$this->chartdata = $chart;
	}
}