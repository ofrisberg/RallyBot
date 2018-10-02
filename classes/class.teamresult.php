<?php

class TeamResult extends Team{
	
	public function __construct($row) {
		parent::__construct($row);
		$this->result = $this->computeResult();
		
	}
	
	public function computeResult(){
		$res = 0;
		$res += $this->getStartFinishDiffMinutes();
		$res -= $this->getCorrStal();
		$res -= $this->getCorrHaftig();
		$res += 105*$this->getNrLocked();
		$res += $this->getHelpBan();
		$res += $this->missedLunchBan();
		return $res;
	}
	
	public function missedLunchBan(){
		if($this->getTsLunchOut() == ""){
			return 45;
		}
		return 0;
	}
	
	public function getStartFinishDiffMinutes(){
		$from_time = strtotime($this->getTsStart2());
		$to_time = strtotime($this->getTsFinish());
		return round(abs($to_time - $from_time) / 60,0);
	}
	
	public function getHelpBan(){
		global $DB;
		$prs = [];
		$query = $DB->query("SELECT * FROM r18_progress WHERE t_id='".$this->getId()."' ORDER BY s_id");
		while($row = $query->fetch_assoc()){
			$prs[] = new Progress($row);
		}
		$tot_ban = 0;
		foreach($prs as $pr){
			$tmp_help = $pr->getNrHelps();
			if($tmp_help < $pr->getNrHelpsPhysical()){
				$tmp_help = $pr->getNrHelpsPhysical();
			}
			if($tmp_help < 4){
				$tot_ban += 10*$tmp_help;
			}else{
				$tot_ban += 75;
			}
		}
		return $tot_ban;
		
	}
	
	public function getNrLocked(){
		global $DB;
		$sql = "SELECT * FROM r18_progress WHERE t_id='".$this->getId()."' AND (r_ts_unlock IS NOT NULL OR r_unlock_physical='1')";
		return (10-$DB->query($sql)->num_rows);
	}
	
}