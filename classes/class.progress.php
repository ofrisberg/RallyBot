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
		$this->nr_helps_physical = intval($row["r_help_physical"]);
		$this->ts_unlock = $row["r_ts_unlock"];
		$this->unlock_physical = intval($row["r_unlock_physical"]);
		$this->lat = $row["p_lat"];
		$this->lng = $row["p_lng"];
	}
	
	public static function constructByTeamAndStation($team,$station) {
		global $DB;
		$t_id = $DB->real_escape_string($team->getId());
		$s_id = $DB->real_escape_string($station->getId());
		
		$query = $DB->query("SELECT * FROM r18_progress WHERE t_id='$t_id' AND s_id='$s_id' LIMIT 1");
		if($query->num_rows == 0){
			throw new Exception('Internt fel, era framsteg har inte installerats korrekt');
		}else{
			return new self($query->fetch_assoc());
		}
	}
	
	/* Set help punishment score */
	public function setHelp($nr){
		global $DB;
		$nr = intval($nr);
		return $DB->query("UPDATE r18_progress SET r_help='$nr' WHERE t_id='$this->t_id' AND s_id='$this->s_id' LIMIT 1");
	}
	
	/* Set physical help punishment score */
	public function setHelpPhysical($nr){
		global $DB;
		$nr = intval($nr);
		return $DB->query("UPDATE r18_progress SET r_help_physical='$nr' WHERE t_id='$this->t_id' AND s_id='$this->s_id' LIMIT 1");
	}
	
	/* Set unlock time (from admin page) */
	public function setTsUnlock($datetime){
		global $DB;
		return $DB->query("UPDATE r18_progress SET r_ts_unlock='$datetime' WHERE t_id='$this->t_id' AND s_id='$this->s_id' LIMIT 1");
	}
	
	/* Set physical unlock (1/0) */
	public function setUnlockPhysical($nr){
		global $DB;
		$nr = intval($nr);
		return $DB->query("UPDATE r18_progress SET r_unlock_physical='$nr' WHERE t_id='$this->t_id' AND s_id='$this->s_id' LIMIT 1");
	}
	
	/* Unlock */
	public function unlock($lat=0,$lng=0){
		global $DB;
		$datetime = date('Y-m-d H:i:s');
		return $DB->query("UPDATE r18_progress SET r_ts_unlock='$datetime',p_lat='$lat',p_lng='$lng' WHERE t_id='$this->t_id' AND s_id='$this->s_id' LIMIT 1");
	}
	
	/* Insert empty progress before rally */
	public static function insertEmpty($t_id,$s_id){
		global $DB;
		$t_id = $DB->real_escape_string($t_id);
		$s_id = $DB->real_escape_string($s_id);
		$sql = "INSERT INTO r18_progress (t_id,s_id,r_help) VALUES ('$t_id','$s_id','0')";
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
		$time = substr($this->getTsUnlock(),11,5);
		if($time == ""){$time='<b>LOCKED</b>';}
		$out = "$time: ";//t_id: ".$this->getTeamId().". 
		if($this->getTeamId() % 2 == 1){
			$out .= "c_s_id: ".(11-intval($this->getStationId())).". ";
		}else{
			$out .= "s_id: ".$this->getStationId().". ";
		}
		$out .= "nrHelps: ".$this->getNrHelps().". ";
		$out .= "nrHelpsPhysical: ".$this->getNrHelpsPhysical().". ";
		$out .= "unlockPhysical: ".$this->getUnlockPhysical().". ";
		//$out .=  "(".$this->getLatitude().", ".$this->getLongitude().")";
		return $out;
	}

	
	public function getTeamId(){return $this->t_id;}
	public function getStationId(){return $this->s_id;}
	public function getNrHelps(){return $this->nr_helps;}
	public function getNrHelpsPhysical(){return $this->nr_helps_physical;}
	public function getTsUnlock(){return $this->ts_unlock;}
	public function getUnlockPhysical(){return $this->unlock_physical;}
	public function getLatitude(){return $this->lat;}
	public function getLongitude(){return $this->lng;}
}



?>