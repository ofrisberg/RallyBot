<?php

class Message extends GPFunctions{
	
	public $id;
	public $u_id;
	public $text;
	public $dir2;
	public $ts_insert;
	
	/* Construct the team */
	public function __construct($row) {
		$this->id = $row["m_id"];
		$this->u_id = $row["u_id"];
		$this->text = $row["m_text"];
		$this->ts_insert = $row["m_ts_insert"];
		$this->dir = $row["m_dir"];
	}
	public static function constructById($id) {
		global $DB;
		$id = $DB->real_escape_string($id);
		$row = $DB->query("SELECT * FROM r18_messages WHERE m_id='$id' LIMIT 1")->fetch_assoc();
		return new self($row);
	}
	
	/* Save a new message */
	public static function insert($u_id,$text,$dir,$t_id){
		global $DB;
		$u_id = $DB->real_escape_string($u_id);
		$t_id = $DB->real_escape_string($t_id);
		$text = $DB->real_escape_string($text);
		$datetime = date('Y-m-d H:i:s');
		$sql = "INSERT INTO r18_messages (u_id,m_text,m_dir,m_ts_insert,t_id) VALUES ('$u_id','$text','$dir','$datetime','$t_id')";
		return $DB->query($sql);
	}
	
	public function __toString() {
		$time = substr($this->getTsInsert(),11,5);
		$sign = "<span style='font-size:10px;'><br/>".$this->getId()." | ".$this->getUserId()."<br/></span>";
		if($this->dir == "to"){
			return "$time: <span style='font-style:italic;'>".htmlentities($this->getText())."</span> $sign";
		}
		return "$time: ".htmlentities($this->getText())." $sign";
	}

	public function getId(){return $this->id;}
	public function getText(){return $this->text;}
	public function getTsInsert(){return $this->ts_insert;}
	public function getUserId(){return $this->u_id;}
}



?>