<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class statslist extends Front_Controller {

	//--------------------------------------------------------------------

	public function index() 
	{
		$this->lang->load('statslist');
		
		$settings = $this->settings_lib->find_all();
		
		$this->load->model('ootp_web_toolkit/leagues_model');
		$this->load->model('ootp_web_toolkit/teams_model');
		
		$years = $this->leagues_model->get_all_seasons($settings['ootp.league_id']);
		
		if ($this->input->post('submit')) 
		{
			$team_id = ($this->input->post('team_id') ? $this->input->post('team_id') : false);
			$year = ($this->input->post('year')? $this->input->post('year') : false);
			
			if ($team_id !== false)
			{
				$league_year = false;
				if ($year !== false) 
				{
					$league_year = $year;
				}
				else 
				{
					$currDate = strtotime($this->leagues_model->get_league_date('current',$settings['ootp.league_id']));
					$startDate = strtotime($this->leagues_model->get_league_date('start',$settings['ootp.league_id']));
					if ($currDate <= $startDate) 
					{
						$league_year = (intval($years[0]));
					}
					else 
					{
						$league_year = date('Y',$currDate);
					}
				}
				$this->load->model('ootp_web_toolkit/players_model');
                $records = array (
					'Batting'=>$this->players_model->get_current_stats(2,$settings['ootp.league_id'], $league_year, $team_id),
					'Pitching'=>$this->players_model->get_current_stats(1,$settings['ootp.league_id'], $league_year, $team_id)
				);
				Template::set('records',$records);
				Template::set('league_year',$league_year);
				Template::set('team_id',$team_id);
				Template::set('team_details',$this->teams_model->select('team_id, name, nickname, logo_file')->find($team_id));
			}
		}
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->helper('ootp_web_toolkit/general');
        Template::set('teams',$this->teams_model->get_teams_array(($settings['statslist.limit_to_primary'] == 1) ? $settings['ootp.league_id'] : false));
		Template::set('years',$years);
		Template::set('settings',$settings);
		Template::set_view('statslist/index');
		Template::render();
	}
}