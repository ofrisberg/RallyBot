<?php



class Answer extends GPFunctions{
	
	/* Construct the answer */
	public function __construct($row) {
		$this->q_id = $row['q_id'];
		$this->t_id = $row['t_id'];
		$this->answer = $row['answer'];
	}
	
	public static function constructById($q_id,$t_id) {
		global $DB;
		$t_id = $DB->real_escape_string($t_id);
		$q_id = $DB->real_escape_string($q_id);
		$row = $DB->query("SELECT * FROM r18_answers WHERE t_id='$t_id' AND q_id='$q_id' LIMIT 1")->fetch_assoc();
		return new self($row);
	}
	
	/* Check if answer exists */
	public static function existsById($q_id,$t_id){
		global $DB;
		$t_id = $DB->real_escape_string($t_id);
		$q_id = $DB->real_escape_string($q_id);
		return ($DB->query("SELECT * FROM r18_answers WHERE t_id='$t_id' AND q_id='$q_id' LIMIT 1")->num_rows > 0);
	}
	
	/* Insert a new answer */
	public static function insert($q_id,$t_id,$answer){
		global $DB;
		$q_id = $DB->real_escape_string($q_id);
		$t_id = $DB->real_escape_string($t_id);
		$answer = $DB->real_escape_string($answer);
		$sql = "INSERT INTO r18_answers (q_id,t_id,answer) VALUES ('$q_id','$t_id','$answer')";
		return $DB->query($sql);
	}
	
	/* Update a old answer */
	public function update($answer){
		global $DB;
		$q_id = $DB->real_escape_string($this->q_id);
		$t_id = $DB->real_escape_string($this->t_id);
		$answer = $DB->real_escape_string($answer);
		$sql = "UPDATE r18_answers SET answer='$answer' WHERE q_id='$q_id' AND t_id='$t_id'";
		return $DB->query($sql);
	}
	
	
	
}