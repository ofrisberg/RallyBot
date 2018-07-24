<?php

interface iGPFunctions{
	public function generateChars($length); /* @return string */
	public function generateChar(); /* @return char */
	public function validLength($str,$min,$max); /* @return boolean */
	public function validEmail($str); /* @return boolean */
	public function validPhone($str); /* @return boolean */
	public function validJson($str); /* @return boolean */
	public function logError($str);
	public function cleanUrl($str);  /* @return string */
	public function cleanText($str); /* @return string */
	public function jsonHeader(); /* @return string */
	public function jsonExit($arr);
	public function inSweden($lat,$lng); /* @return boolean */
	public function timeAgo($datetime); /* @return string */
	public function timeToDate($datetime); /* @return string */
	public function mb_ucfirst($str); /* @return string */
}


class GPFunctions implements iGPFunctions{
	
	function __construct() {}
	
	public function generateChars($length){
		$token = "";
		for($i = 0; $i < $len; $i++){
			$token .= $this->generateLetter();
		}
		return $token;
	}
	
	public function generateChar(){
		$nr = rand(0,35); //not csprng
		if($nr > 9){
			$nr = chr(87+$nr);
		}
		return $nr;
	}
	
	public function validLength($str,$min,$max){
		if(strlen($str)< $min || strlen($str) > $max){
			return false;
		}
		return true;
	}
	
	public function validEmail($str){
		return filter_var($str, FILTER_VALIDATE_EMAIL);
	}
	
	public function validPhone($str){
		return preg_match('/^\+467[0-9]{8}$/',$str,$matches);
	}
	
	public function validJson($str){
		json_decode($str);
		return (json_last_error() == JSON_ERROR_NONE);
	}
	
	public function logError($str){
		error_log(date('Y-m-d H:i:s')." | ".$str, 3, "error.log");
	}
	
	public function cleanUrl($str){
		$replace = array(); 
		$delimiter = '-';
		
		if(!empty($replace) ) {
			$str = str_replace((array)$replace, ' ', $str);
		}

		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
		$clean = preg_replace("/[^a-zA-Z0-9/_|+ -]/", '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[/_|+ -]+/", $delimiter, $clean);

		return $clean;
	}
	
	public function cleanText($str){
		$str = trim($str);
		$str = stripslashes($str);
		return $str;
	}
	
	public function jsonHeader(){
		return "Content-Type: application/json; charset=UTF-8";
	}
	
	public function jsonExit($arr){
		$json = json_encode($arr);
		echo $json;
		exit();
	}
	
	public function inSweden($lat,$lng){
		return ($lat < 69 && $lat > 55 && $lng > 11 && $lng < 24);
	}
	
	public function timeAgo($datetime){
		if (date('Y-m-d') == date('Y-m-d', strtotime($datetime))) {
			$when = "Idag " . date('H:i', strtotime($datetime));
		} else if (date('Y-m-d', strtotime(date('Y-m-d') . ' -1 days')) == date('Y-m-d', strtotime($datetime))) {
			$when = "Igår " . date('H:i', strtotime($datetime));
		} else {
			$when = date('Y-m-d', strtotime($datetime));
		}
		return $when;
	}
	
	public function timeToDate($datetime){
		return substr($datetime,0,10);
	}
	
	public function mb_ucfirst($str) {
		$e = 'utf-8';
		if (function_exists('mb_strtoupper') && function_exists('mb_substr') && !empty($str)) {
			$str = mb_strtolower($str, $e);
			$upper = mb_strtoupper($str, $e);
			preg_match('#(.)#us', $upper, $matches);
			$str = $matches[1] . mb_substr($str, 1, mb_strlen($str, $e), $e);
		} else {
			$str = ucfirst($str);
		}
		return $str;
	}
	
	
	
	
}



?>