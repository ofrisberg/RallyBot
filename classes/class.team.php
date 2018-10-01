<?php

class Team extends GPFunctions{
	
	public $id;
	public $name;
	public $token;
	public $ts_start;
	public $ts_finish;
	
	/* Construct the team */
	public function __construct($row) {
		$this->id = $row["t_id"];
		$this->start_position = $row["t_start_position"];
		$this->name = $row["t_name"];
		$this->token = $row["t_token"];
		$this->leader = $row["t_leader"];
		$this->phone = $row["t_phone"];
		$this->email = $row["t_email"];
		$this->nr_participants = $row["t_nr_participants"];
		$this->gasque = $row["t_gasque"];
		$this->ts_start = $row["t_ts_start"];
		$this->ts_start2 = $row["t_ts_start2"];
		$this->ts_finish = $row["t_ts_finish"];
		$this->ts_lunch_in = $row["t_ts_lunch_in"];
		$this->ts_lunch_out = $row["t_ts_lunch_out"];
		$this->corr_stal = intval($row["t_corr_stal"]);
		$this->corr_haftig = intval($row["t_corr_haftig"]);
	}
	public static function constructById($id) {
		global $DB;
		$id = $DB->real_escape_string($id);
		$row = $DB->query("SELECT * FROM r18_teams WHERE t_id='$id' LIMIT 1")->fetch_assoc();
		return new self($row);
	}
	public static function constructByToken($token) {
		global $DB;
		$token = $DB->real_escape_string($token);
		$row = $DB->query("SELECT * FROM r18_teams WHERE t_token='$token' LIMIT 1")->fetch_assoc();
		return new self($row);
	}
	
	/* Check if team exists by id */
	public static function existsById($id){
		global $DB;
		$id = $DB->real_escape_string($id);
		return ($DB->query("SELECT * FROM r18_teams WHERE t_id='$id'")->num_rows > 0);
	}
	
	/* Check if team exists by token */
	public static function existsByToken($token){
		global $DB;
		$token = $DB->real_escape_string($token);
		return ($DB->query("SELECT * FROM r18_teams WHERE t_token='$token'")->num_rows > 0);
	}
	
	/* Start timer on first unlock */
	public function start(){$this->setTsStart(date('Y-m-d H:i:s'));}
	
	/* Stop timer on last unlock */
	public function finish(){$this->setTsFinish(date('Y-m-d H:i:s'));}
	
	public function setTsStart($datetime){
		global $DB;
		return $DB->query("UPDATE r18_teams SET t_ts_start='$datetime' WHERE t_id='$this->id'");
	}
	public function setTsFinish($datetime){
		global $DB;
		return $DB->query("UPDATE r18_teams SET t_ts_finish='$datetime' WHERE t_id='$this->id'");
	}
	public function lunchCheckIn(){
		global $DB;
		$dt = date('Y-m-d H:i:s');
		return $DB->query("UPDATE r18_teams SET t_ts_lunch_in='$dt' WHERE t_id='$this->id'");
	}
	public function lunchCheckOut(){
		global $DB;
		$dt = date('Y-m-d H:i:s');
		return $DB->query("UPDATE r18_teams SET t_ts_lunch_out='$dt' WHERE t_id='$this->id'");
	}
	public function setCorrStal($nr){
		global $DB;
		return $DB->query("UPDATE r18_teams SET t_corr_stal='$nr' WHERE t_id='$this->id'");
	}
	public function setCorrHaftig($nr){
		global $DB;
		return $DB->query("UPDATE r18_teams SET t_corr_haftig='$nr' WHERE t_id='$this->id'");
	}
	
	
	public function __toString() {
		return $this->getId()." | ".$this->getName();
	}
	
	public function getUser(){
		global $DB;
		$sql = "SELECT * FROM r18_users WHERE t_id='".$this->getId()."' LIMIT 1";
		$query = $DB->query($sql);
		if($query->num_rows == 0){
			throw new Exception('Ingen ihopkopplad deltagare för lag '.$this->getId().' hittades');
		}else{
			return new User($query->fetch_assoc());
		}
	}
	
	public function hasUser(){
		global $DB;
		$sql = "SELECT * FROM r18_users WHERE t_id='".$this->getId()."' LIMIT 1";
		$query = $DB->query($sql);
		return ($query->num_rows == 1);
	}
	
	public function getId(){return $this->id;}
	public function getStartPosition(){return intval($this->start_position);}
	public function getName(){return $this->name;}
	public function getToken(){return trim($this->token);}
	public function getLeader(){return $this->leader;}
	public function getPhone(){return trim($this->phone);}
	public function getEmail(){return trim($this->email);}
	public function getNrParticipants(){return intval($this->nr_participants);}
	public function getGasque(){return intval($this->gasque);}
	public function getTsStart(){return $this->ts_start;}
	public function getTsStart2(){return $this->ts_start2;}
	public function getTsFinish(){return $this->ts_finish;}
	public function getTsLunchIn(){return $this->ts_lunch_in;}
	public function getTsLunchOut(){return $this->ts_lunch_out;}
	public function getCorrStal(){return $this->corr_stal;}
	public function getCorrHaftig(){return $this->corr_haftig;}
	public function hasStarted(){
		return $this->getTsStart() != "";
	}
	public function hasFinished(){
		return $this->getTsFinish() != "";
	}
	public function hasGasque(){
		return ($this->gasque==1);
	}
}



?>