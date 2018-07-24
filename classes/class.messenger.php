<?php

class Messenger {
	
    public $inp;
    public $sender;
    public $message;
    public $error = '';
    public $quick_resp;
    public $imageUrl;
	public $IO;
	public $lat;
	public $lng;

    public function __construct($inp) {
        global $DB;
        $this->inp = $inp;
		$this->lat = 0;
		$this->lng = 0;
        if (isset($this->inp['entry'][0]['messaging'][0]['sender']['id'])) {
            $this->sender = $this->inp['entry'][0]['messaging'][0]['sender']['id'];
        } else {
            $this->error = 'Ingen avsändare';
        }
        if (isset($this->inp['entry'][0]['messaging'][0]['message']['text'])) {
            $this->message = $this->inp['entry'][0]['messaging'][0]['message']['text'];
            $this->message = trim(mb_strtoupper($this->message), "?.,!");
        } else if (isset($this->inp['entry'][0]['messaging'][0]['message']['attachments'][0]['type'])) {
            $attatch = $this->inp['entry'][0]['messaging'][0]['message']['attachments'][0];
            if ($attatch['type'] == 'image') {
                $this->imageUrl = $attatch['payload']['url'];
            }else if($attatch['type'] == 'location'){
				if(preg_match('/^(.*) Location$/iu',$attatch['title'],$matches)){
					$this->lat = $attatch['payload']['coordinates']['lat'];
					$this->lng = $attatch['payload']['coordinates']['long'];
				}else{
					$this->message = 'WRONG_LOCATION';
				}
			}else {
                $this->error = 'Fel attatch';
            }
        } else {
            $this->error = 'Inget meddelande';
        }
    }
	
	/* get first and last name */
	//https://graph.facebook.com/v2.6/1795019383892333?fields=first_name,last_name&access_token=
	
	public function setIO($io){$this->IO = $io;}
	public function getSenderId(){ return $this->sender; }
	public function getTextMessage(){ return $this->message; }
	
    public function getIOReply() {
        foreach ($this->IO as $io) {
            if (in_array($this->message, explode("|",$io["input"]))) {
                return $io["output"];
            }
        }
        return '';
    }

    public function reply($msg) {
        self::send($msg, $this->sender);
    }

    public static function send($msg, $recipient) {
        $arr = [
            "recipient" => [
                "id" => $recipient,
            ],
            "message" => [
                "text" => $msg,
            ]
        ];
        self::curl($arr);
    }

    public function replyImage($image) {
        self::sendImage($image, $this->sender);
    }

    public static function sendImage($image, $recipient) {
        $arr = [
            "recipient" => [
                "id" => $recipient,
            ],
            "message" => [
                "attachment" => [
                    "type" => "image",
                    "payload" => [
                        "url" => $image
                    ]
                ]
            ]
        ];
        self::curl($arr);
    }

    public static function curl($arr) {
        $json = json_encode($arr);
		$browser = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.16 (KHTML, like Gecko) \Chrome/24.0.1304.0 Safari/537.16';
		$api_url = $GLOBALS['CFG']['MESSENGER']['api_url'];
		$access_token = $GLOBALS['CFG']['MESSENGER']['access_token'];
        $ch = curl_init($api_url . $access_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, $browser);
		
        $result = curl_exec($ch);
		//$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//echo $httpcode;
		//var_dump($result);
		curl_close($ch);
		
		if($result){
			file_put_contents('last_curl_response.txt',$result);
		}else{
			file_put_contents('last_curl_response.txt',"curl_exec returned false");
		}
    }

    public static function verify($vt, $challenge) {
        if ($vt === $GLOBALS['CFG']['MESSENGER']['verify_token']) {
            echo $challenge;
            exit();
        } else {
            exit("Fel verifiering");
        }
    }

}
