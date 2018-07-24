<?php

class Progress extends GPFunctions{
	
	public $t_id;
	public $s_id;
	public $nr_helps;
	public $ts_unlock;
	
	/* Construct the progress */
	public function __construct($row) {
		$this->t_id = $row["t_id"];
		$this->s_id = $row["s_id"];
		$this->nr_helps = intval($row["r_help"]);
		$this->ts_unlock = $row["r_ts_unlock"];
	}
	
	public static function constructByTeamAndStation($team,$station) {
		global $DB;
		$t_id = $DB->real_escape_string($team->getId());
		$s_id = $DB->real_escape_string($station->getId());
		
		$query = $DB->query("SELECT * FROM r18_progress WHERE t_id='$t_id' AND s_id='$s_id' LIMIT 1");
		if($query->num_rows == 0){ //team has not unlocked
			if(!self::insert($team->getId(),$station->getId())){
				throw new Exception('Internt fel, kunde inte skapa progress');
			}
			return self::constructByTeamAndStation($team,$station);
		}else{
			return new self($query->fetch_assoc());
		}
	}
	
	/* Increase help punishment score */
	public function increaseHelp(){
		global $DB;
		return $DB->query("UPDATE r18_progress SET r_help=r_help+1 WHERE t_id='$this->t_id' AND s_id='$this->s_id' LIMIT 1");
	}
	
	/* Set help punishment score (from admin page) */
	public function setHelp($nr){
		global $DB;
		$nr = intval($nr);
		return $DB->query("UPDATE r18_progress SET r_help='$nr' WHERE t_id='$this->t_id' AND s_id='$this->s_id' LIMIT 1");
	}
	
	/* Set unlock time (from admin page) */
	public function setTsUnlock($datetime){
		global $DB;
		return $DB->query("UPDATE r18_progress SET r_ts_unlock='$datetime' WHERE t_id='$this->t_id' AND s_id='$this->s_id' LIMIT 1");
	}
	
	/* Insert new progress */
	public static function insert($t_id,$s_id){
		global $DB;
		$t_id = $DB->real_escape_string($t_id);
		$s_id = $DB->real_escape_string($s_id);
		$datetime = date('Y-m-d H:i:s');
		$sql = "INSERT INTO r18_progress (t_id,s_id,r_help,r_ts_unlock) VALUES ('$t_id','$s_id','0','$datetime')";
		return $DB->query($sql);
	}
	
	/* Check if progress exists */
	public static function exists($team,$station){
		global $DB;
		$t_id = $DB->real_escape_string($team->getId());
		$s_id = $DB->real_escape_string($station->getId());
		return ($DB->query("SELECT * FROM r18_progress WHERE t_id='$t_id' AND s_id='$s_id'")->num_rows > 0);
	}
	
	public function __toString() {
		 return "t_id: ".$this->getTeamId().". s_id: ".$this->getStationId().". nrHelps: ".$this->getNrHelps().". ".$this->getTsUnlock();
	}

	
	public function getTeamId(){return $this->t_id;}
	public function getStationId(){return $this->s_id;}
	public function getNrHelps(){return $this->nr_helps;}
	public function getTsUnlock(){return $this->ts_unlock;}
}



?>