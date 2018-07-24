<?php

class Station extends GPFunctions{
	
	private $id;
	private $rebus;
	private $help1;
	private $help2;
	private $help3;
	private $facit;
	private $token;
	private $lat;
	private $lng;
	
	/* Construct the station */
	public function __construct($row) {
		$this->id = $row["s_id"];
		$this->rebus = $row["s_rebus"];
		$this->help1 = $row["s_help1"];
		$this->help2 = $row["s_help2"];
		$this->help3 = $row["s_help3"];
		$this->facit = $row["s_facit"];
		$this->token = $row["s_token"];
		$this->lat = $row["s_lat"];
		$this->lng = $row["s_lng"];
	}
	public static function constructById($id) {
		global $DB;
		$id = $DB->real_escape_string($id);
		$query = $DB->query("SELECT * FROM r18_stations WHERE s_id='$id' LIMIT 1");
		if($query->num_rows == 0){throw new Exception('Fel rebusnummer');}
		return new self($query->fetch_assoc());
	}
	public static function constructByToken($token) {
		global $DB;
		$token = $DB->real_escape_string($token);
		$query = $DB->query("SELECT * FROM r18_stations WHERE s_token='$token' LIMIT 1");
		if($query->num_rows == 0){throw new Exception('Fel rebuskod');}
		return new self($query->fetch_assoc());
	}
	
	public static function constructByCoords($lat,$lng) {
		global $DB;
		$sql = "SELECT * FROM r18_stations ORDER BY ((s_lat-$lat)*(s_lat-$lat)) + ((s_lng - $lng)*(s_lng - $lng)) ASC LIMIT 1";
		$query = $DB->query($sql);
		if($query->num_rows == 0){throw new Exception('Internt latlng-fel');}
		return new self($query->fetch_assoc());
	}
	
	public function getDistance($lat,$lng){
		$latFrom = deg2rad($this->lat);
		$lonFrom = deg2rad($this->lng);
		$latTo = deg2rad($lat);
		$lonTo = deg2rad($lng);
		$earthRadius = 6371000;

		$latDelta = $latTo - $latFrom;
		$lonDelta = $lonTo - $lonFrom;

		$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
		$distance = $angle * $earthRadius;
		return round($distance,0);
	}
	
	public static function getMaxId(){
		global $DB;
		$sql = "SELECT s_id FROM r18_stations ORDER BY s_id DESC LIMIT 1";
		return $DB->query($sql)->fetch_assoc()[s_id];
	}
	
	public function getId(){return $this->id;}
	public function getToken(){return $this->token;}
	public function getHelp1(){return $this->help1;}
	public function getHelp2(){return $this->help2;}
	public function getHelp3(){return $this->help3;}
	public function getFacit(){return $this->facit;}
	public function getLat(){return $this->lat;}
	public function getLng(){return $this->lng;}
}



?>