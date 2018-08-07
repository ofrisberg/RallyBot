<?php

class User extends GPFunctions{
	
	public $id;
	public $t_id;
	public $ts_insert;
	
	/* Construct the user */
	public function __construct($row) {
		$this->id = $row["u_id"];
		$this->state = $row["u_state"];
		$this->t_id = $row["t_id"];
		$this->ts_insert = $row["u_ts_insert"];
	}
	public static function constructById($id) {
		global $DB;
		$id = $DB->real_escape_string($id);
		$row = $DB->query("SELECT * FROM r18_users WHERE u_id='$id' LIMIT 1")->fetch_assoc();
		return new self($row);
	}
	
	/* Check if user exists (has sent message before) */
	public static function existsById($id){
		global $DB;
		$id = $DB->real_escape_string($id);
		return ($DB->query("SELECT * FROM r18_users WHERE u_id='$id'")->num_rows > 0);
	}
	
	/* Register a new user */
	public static function insert($id){
		global $DB;
		$id = $DB->real_escape_string($id);
		$datetime = date('Y-m-d H:i:s');
		$sql = "INSERT INTO r18_users (u_id,u_ts_insert) VALUES ('$id','$datetime')";
		return $DB->query($sql);
	}
	
	/* Check if user has team */
	public function hasTeam(){
		return ($this->t_id != "");
	}
	
	/* Connect user with a team */
	public function setTeamId($tid){
		global $DB;
		$tid = $DB->real_escape_string($tid);
		$query = $DB->query("UPDATE r18_users SET t_id='$tid' WHERE u_id='$this->id' LIMIT 1");
		if($query){
			$this->t_id = $tid;
		}
		return $query;
	}
	
	/* Disconnect user with team */
	public function disconnectTeam(){
		global $DB;
		$query = $DB->query("UPDATE r18_users SET t_id=NULL WHERE u_id='$this->id' LIMIT 1");
		if($query){$this->t_id = "";}
		return $query;
	}
	
	/* Set user state
	* 0 - Talking to bot
	* 1 - Waiting for ja/nej reply
	* 2 - Talking to rallykå */
	public function setState($state){
		global $DB;
		$query = $DB->query("UPDATE r18_users SET u_state='$state' WHERE u_id='$this->id' LIMIT 1");
		if($query){$this->state = $state;}
		return $query;
	}
	
	public function isReplyingYesNo(){return $this->state == '1';}
	public function isTalkingToRallyka(){return $this->state == '2';}
	
	public function getId(){return $this->id;}
	public function getTeamId(){return $this->t_id;}
}



?>