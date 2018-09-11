<?php

class Slack{
	
	public $url;
	
	public function __construct() {
		$this->url = $GLOBALS['CFG']['SLACK']['url'];
	}
	
	public function send($msg){
		$ch = curl_init($this->url);
		
		$data = json_encode([
			"text" => $msg,
		]);
		
		$headers = [];
		$headers[] = 'Content-Type: application/json';
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
		
		$result = curl_exec($ch);
		
		curl_close($ch);
		//print_r($result);
	}
	
	
	
}