<?php

class Logic extends GPFunctions{
		
	public function __construct() {}
	
	public static function isOpen($user){
		$start = $GLOBALS['CFG']['GENERAL']['start_time'];
		$end = $GLOBALS['CFG']['GENERAL']['end_time'];
		$now = date('Y-m-d H:i:s');
		
		$debug = $GLOBALS['CFG']['GENERAL']['debugging'] == 1;
		$test_ids = [];
		$test_ids[] = "1795019383892333";//olle
		$test_ids[] = "1952156258130042";//erik
		$test_ids[] = "2221643154521269";//evelina
		if($debug && in_array($user->getId(),$test_ids)){
			return true;
		}
		
		return ($now >= $start && $now < $end);
	}
	
	public static function onNoReply($user,$message){
		if($user->setState(1)){
			return "Jag förstår inte... Ska jag kontakta rallykå? :s (ja/Nej)";
		}
		return "Något gick fel och jag kunde inte uppdatera din status :/";
	}
	
	public static function onYesNoReply($user,$message){
		if(preg_match('/^JA$/iu',$message,$matches)){
			if($user->setState(2)){
				$sl = new Slack();
				$sl->send("Någon vill prata med rallykå på Messenger!");
				return "Okej, när du är klar med rallykå så skriv 'boten anna' för att prata med mig igen :)";
			}else{
				return "Något gick fel och jag kunde inte hämta rallykå :/";
			}
		}
		if($user->setState(0)){
			return "Jag tar det som ett nej, då får du fortsätta prata med mig ^^";
		}else{
			return "Något gick fel och jag har fastnat i en loop :/";
		}
	}
		
	public static function onTalkingToRallyka($user,$message){
		if(preg_match('/^BOTEN ANNA$/iu',$message,$matches)){
			if($user->setState(0)){
				return "Nu är jag tillbaka :P /Boten Anna";
			}
			return "Något gick fel och kontakten med Boten Anna kunde inte upprättas :/";
		}
		return "";
	}
	
	/* 
	* KOPPLA LAG <token> --- kopplar messenger-användare till rallylag  
	* HJÄLPREBUS <nr> --- hämtar aktuell hjälprebus till station <nr>
	* <delad plats> --- försöker låsa upp närmaste station
	* STÅLSVAR <nr> <svar> --- sparar <svar> till fråga <nr>
	*/
	public static function initActiveRally($user,$message,$Me){
		$reply = "";
		if(preg_match('/^KOPPLA LAG ([a-z0-9]+)$/iu',$message,$matches)){
			try{
				self::connectUserToTeam($user,$matches[1]);
				$team = Team::constructById($user->getTeamId());
				$color = "blå";
				if($team->getStartPosition() % 2 == 1){
					$color = "orange";
				}
				$reply = "Anti shoulder surfing \n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n Du är ihopkopplad med ditt lag! :) \n\n (Startnr: ".$team->getStartPosition().", $color bana)";
			}catch (Exception $e) {
				$reply = 'Kunde inte ihopkoppla. Fel: '.$e->getMessage();
			}
		}else if(preg_match('/^FRÅNKOPPLA LAG$/iu',$message,$matches)){
			if($user->disconnectTeam()){
				$reply = "Ditt lag frånkopplades";
			}else{
				$reply = "Fel: Kunde inte frånkoppla lag";
			}
		}else if(false && preg_match('/^LÅS UPP ([a-z0-9]+)$/iu',$message,$matches)){
			try{
				$reply = self::unlock($user,$matches[1]);
			}catch (Exception $e) {
				$reply = 'Kunde inte låsa upp. Fel: '.$e->getMessage();
			}
		}else if(preg_match('/^HJÄLP ([0-9]+)\.([0-9])$/iu',$message,$matches)){
			try{
				$reply = self::getHelp($user, $matches[1], $matches[2]);
			}catch (Exception $e) {
				$reply = 'Kunde inte hämta hjälprebus. Fel: '.$e->getMessage();
			}
		}else if($message == 'WRONG_LOCATION'){
			$reply = "Kunde inte låsa upp. Fel: Du skickade inte din egen position.";
		}else if($Me->lat > 0 && $Me->lng > 0){
			try{
				$reply = self::unlockByCoords($user,$Me->lat,$Me->lng);
			}catch (Exception $e) {
				$reply = 'Kunde inte låsa upp. Fel: '.$e->getMessage();
			}
		}else if(preg_match('/^STÅLSVAR ([1-7]) (.*)$/iu',$message,$matches)){
			try{
				$reply = self::saveAnswer($user,$matches[1],$matches[2]);
			}catch (Exception $e) {
				$reply = 'Kunde inte spara svar. Fel: '.$e->getMessage();
			}
		}
		return $reply;
	}
	
	public static function saveAnswer($user,$question_id,$answer_text){
		if(!$user->hasTeam()){
			throw new Exception('Du har inget lag');
		}
		if(Answer::existsById($question_id,$user->getTeamId())){
			$answer = Answer::constructById($question_id,$user->getTeamId());
			if($answer->update($answer_text)){
				return "Ditt gamla stålsvar uppdaterades";
			}else{
				throw new Exception('Kunde inte uppdatera ditt gamla stålsvar');
			}
		}
		if(Answer::insert($question_id,$user->getTeamId(),$answer_text)){
			return "Ditt nya stålsvar har sparats";
		}else{
			throw new Exception('Kunde inte lägga till nytt stålsvar');
		}
	}
	
	public static function connectUserToTeam($user,$token){
		if($user->hasTeam()){
			throw new Exception('Du har redan ett lag');
		}
		if(!Team::existsByToken($token)){
			throw new Exception('Laget hittades inte');
		}
		$team = Team::constructByToken($token);
		if(!$user->setTeamId($team->id)){
			throw new Exception('Databas-fel');
		}
		return;
	}
	
	public static function unlock($user,$token){
		if(!self::isOpen($user)){throw new Exception('Rallyt är stängt');}
		if(!$user->hasTeam()){
			throw new Exception('Du har inget lag');
		}
		$team = Team::constructById($user->getTeamId());
		$station = Station::constructByToken($token);

		if(Progress::exists($team,$station)){
			throw new Exception('Redan låst upp stationen');
		}
		$progress = Progress::constructByTeamAndStation($team,$station);
		
		$max_station_id = Station::getMaxId();
		if(false && $progress->getStationId() == $max_station_id){
			return "Grattis, ert lag har gått i mål!";
		}else if(false && $progress->getStationId() == 0){
			return "Rebusrallyt har börjat. Lycka till och kör försiktigt!";
		}else{
			return "Lyckad upplåsning";
		}
		
	}
	
	public static function unlockByCoords($user,$lat,$lng){
		if(!self::isOpen($user)){throw new Exception('Rallyt är stängt');}
		if(!$user->hasTeam()){
			throw new Exception('Du har inget lag');
		}
		$team = Team::constructById($user->getTeamId());
		$lat = floatval($lat);
		$lng = floatval($lng);
		$station = Station::constructByCoords($lat,$lng);
		
		$distance = $station->getDistance($lat,$lng);
		$distance_limit = $GLOBALS['CFG']['GENERAL']['distance_limit'];
		if($distance > $distance_limit){
			throw new Exception("Ni är för långt ifrån stationen \n\n $distance m, s_id:".$station->getId()."");
		}else{
			return "Lyckad upplåsning \n\n $distance m, s_id:".$station->getId()."";
		}
		
		if(Progress::exists($team,$station)){
			throw new Exception('Redan låst upp stationen');
		}
		$progress = Progress::constructByTeamAndStation($team,$station);
		
		return "Latitud: $lat Longitud: $lng";
	}
	
	public static function getHelp($user,$s_id,$nr_help){
		if(!self::isOpen($user)){throw new Exception('Rallyt är stängt');}
		if(!$user->hasTeam()){
			throw new Exception('Du har inget lag');
		}
		$team = Team::constructById($user->getTeamId());
		$s_id_converted = self::convertStationId($team,$s_id);
		$station = Station::constructById($s_id_converted);
		if(!Progress::exists($team,$station)){
			throw new Exception('Lås upp stationen först');
		}
		$progress = Progress::constructByTeamAndStation($team,$station);
		
		$nr = intval($nr_help);
		if($nr < 1 || $nr > 4){
			throw new Exception('Hjälprebus är 1-3 och facit 4. Inga andra siffror är giltiga efter punkten');
		}
		
		if($progress->getNrHelps() < $nr){
			if(!$progress->setHelp($nr)){
				throw new Exception('Kunde inte uppdatera antal');
			}
		}

		$replytext = "";
		if($nr == 1){$replytext = $station->getHelp1();
		}else if($nr == 2){$replytext = $station->getHelp2();
		}else if($nr == 3){$replytext = $station->getHelp3();
		}else if($nr == 4){$replytext = $station->getFacit();}
		
		/*if($progress->getNrHelps() < 3 && strpos($replytext,".jpg") !== false){
			$base_url = $GLOBALS['CFG']['GENERAL']['base_url'];
			$replytext = $base_url."/images/help/hr".$station->getId()."/".$replytext;
		}*/
		return $replytext;
	}
	
	public static function convertStationId($team, $s_id){
		$i = intval($s_id);
		$sp = $team->getStartPosition();
		if($sp % 2 == 1){return (11-$i);}
		return $i;
	}
	
	
	public static function getBestTimeSQL(){
		$sql = [];
		$ban_help = $GLOBALS['CFG']['GENERAL']['ban_help_constant'];
		$sql['help_add'] = $ban_help."*(SELECT SUM(r_help) FROM r18_progress WHERE r18_progress.t_id = r18_teams.t_id)";
		$sql['score'] = "(TIMESTAMPDIFF(minute, t_ts_start, t_ts_finish) + ($sql[help_add]))";
		$sql['exclude'] = "t_ts_start IS NOT NULL AND t_ts_finish IS NOT NULL";
		$sql['tot'] = "SELECT *,$sql[score] AS nr_minutes FROM r18_teams WHERE $sql[exclude] ORDER BY $sql[score] ASC";
		return $sql['tot'];
	}
	
	public static function getIOReply($input,$ios){
		foreach ($ios as $io) {
            if (in_array($input, explode("|",$io["i"]))) {
                return $io["o"];
            }
        }
        return '';
	}
}



?>