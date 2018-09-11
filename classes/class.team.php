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
		$this->ts_start = $row["t_ts_start"];
		$this->ts_finish = $row["t_ts_finish"];
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
	
	public function __toString() {
		return $this->getId()." | ".$this->getName();
	}
	
	public function getId(){return $this->id;}
	public function getStartPosition(){return intval($this->start_position);}
	public function getName(){return $this->name;}
	public function getTsStart(){return $this->ts_start;}
	public function getTsFinish(){return $this->ts_finish;}
	public function hasStarted(){
		return $this->getTsStart() != "";
	}
	public function hasFinished(){
		return $this->getTsFinish() != "";
	}
}



?>