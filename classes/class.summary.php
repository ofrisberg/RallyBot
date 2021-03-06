<?php

class Summary extends GPFunctions{
	
	private $t_id_min;
	private $t_id_max;
	
	public function __construct() {
		$this->t_id_min = 0;
		$this->t_id_max = 99;
	}
	
	private function getSQLWhere(){
		return '(t_id >= '.$this->t_id_min.' AND t_id <='.$this->t_id_max.')';
	}
	
	public function nrCoupled() {
		return $this->query("SELECT count(*) as nr FROM r18_users WHERE t_id IS NOT NULL AND ".$this->getSQLWhere());
	}
	
	public function nrStarted() {
		return $this->query("SELECT count(*) as nr FROM r18_teams WHERE t_ts_start IS NOT NULL AND ".$this->getSQLWhere());
	}
	
	public function nrUnlocks() {
		return $this->query("SELECT count(*) as nr FROM r18_progress WHERE r_ts_unlock IS NOT NULL AND ".$this->getSQLWhere());
	}
	
	public function nrHelps() {
		return $this->query("SELECT sum(r_help) as nr FROM r18_progress WHERE ".$this->getSQLWhere());
	}
	
	public function nrLunchIn() {
		return $this->query("SELECT count(*) as nr FROM r18_teams WHERE t_ts_lunch_in IS NOT NULL AND ".$this->getSQLWhere());
	}
	
	public function nrLunchOut() {
		return $this->query("SELECT count(*) as nr FROM r18_teams WHERE t_ts_lunch_out IS NOT NULL AND ".$this->getSQLWhere());
	}
	
	public function nrAnswers() {
		return $this->query("SELECT count(*) as nr FROM r18_answers WHERE ".$this->getSQLWhere());
	}
	
	public function nrFinished() {
		return $this->query("SELECT count(*) as nr FROM r18_teams WHERE t_ts_finish IS NOT NULL AND ".$this->getSQLWhere());
	}
	
	public function getHelpStatsByStation(){
		global $DB;
		$arr = [];
		for($i=1; $i <= 10; $i++){
			$query = $DB->query("SELECT r_help,r_help_physical FROM r18_progress WHERE s_id='$i' AND t_id<100");
			$arr[$i] = 0;
			if($query->num_rows > 0){
				while($row = $query->fetch_assoc()){
					$tmp = intval($row[r_help]);
					if($tmp < intval($row[r_help_physical])){
						$tmp = intval($row[r_help_physical]);
					}
					$arr[$i] += $tmp;
				}
			}
		}
		return $arr;
	}
	
	public function query($sql){
		global $DB;
		return intval($DB->query($sql)->fetch_assoc()["nr"]);
	}
	
}