<?php

class Logic extends GPFunctions{
		
	public function __construct() {}
	
	
	public static function onNoReply($user,$message){
		if($user->setState(1)){
			return "Jag förstår inte... Ska jag kontakta rallykå? :s (ja/Nej)";
		}
		return "Något gick fel och jag kunde inte uppdatera din status :/";
	}
	
	public static function onYesNoReply($user,$message){
		if(preg_match('/^JA$/iu',$message,$matches)){
			if($user->setState(2)){
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
			return "Något gick fel och kontakten med Boten Anna kunde upprättas :/";
		}
		return "";
	}
	
	public static function initActiveRally($user,$message,$Me){
		$reply = "";
		if(preg_match('/^KOPPLA LAG ([a-z0-9]+)$/iu',$message,$matches)){
			try{
				self::connectUserToTeam($user,$matches[1]);
				$reply = "Du är ihopkopplad med ett lag ($user->t_id)";
			}catch (Exception $e) {
				$reply = 'Kunde inte ihopkoppla. Fel: '.$e->getMessage();
			}
		}else if(preg_match('/^FRÅNKOPPLA LAG$/iu',$message,$matches)){
			if($user->disconnectTeam()){
				$reply = "Ditt lag frånkopplades";
			}else{
				$reply = "Fel: Kunde inte frånkoppla lag";
			}
		}else if(preg_match('/^LÅS UPP ([a-z0-9]+)$/iu',$message,$matches)){
			try{
				$reply = self::unlock($user,$matches[1]);
			}catch (Exception $e) {
				$reply = 'Kunde inte låsa upp. Fel: '.$e->getMessage();
			}
		}else if(preg_match('/^HJÄLPREBUS ([0-9]+)$/iu',$message,$matches)){
			try{
				$reply = self::getHelp($user, $matches[1]);
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
		}
		return $reply;
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
		if($progress->getStationId() == $max_station_id){
			return "Grattis, ert lag har gått i mål!";
		}else if($progress->getStationId() == 0){
			return "Rebusrallyt har börjat. Lycka till och kör försiktigt!";
		}else{
			return "Lyckad upplåsning";
		}
		
	}
	
	public static function unlockByCoords($user,$lat,$lng){
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
			throw new Exception("$distance > ".$distance_limit." SID: ".$station->getId());
		}else{
			return "Lyckad upplåsning. $distance <= ".$distance_limit. " SID: ".$station->getId();
		}
		
		if(Progress::exists($team,$station)){
			throw new Exception('Redan låst upp stationen');
		}
		$progress = Progress::constructByTeamAndStation($team,$station);
		
		return "Latitud: $lat Longitud: $lng";
	}
	
	public static function getHelp($user,$s_id){
		if(!$user->hasTeam()){
			throw new Exception('Du har inget lag');
		}
		$team = Team::constructById($user->getTeamId());
		$station = Station::constructById($s_id);
		if(!Progress::exists($team,$station)){
			throw new Exception('Lås upp stationen först');
		}
		$progress = Progress::constructByTeamAndStation($team,$station);
		
		if($progress->getNrHelps() <= 3){
			if(!$progress->increaseHelp()){
				throw new Exception('Kunde inte uppdatera antal');
			}
		}
		
		$replytext = "";
		if($progress->getNrHelps() <= 0){$replytext = $station->getHelp1();
		}else if($progress->getNrHelps() == 1){$replytext = $station->getHelp2();
		}else if($progress->getNrHelps() == 2){$replytext = $station->getHelp3();
		}else if($progress->getNrHelps() >= 3){$replytext = $station->getFacit();}
		
		if($progress->getNrHelps() < 3 && strpos($replytext,".jpg") !== false){
			$base_url = $GLOBALS['CFG']['GENERAL']['base_url'];
			$replytext = $base_url."/images/help/hr".$station->getId()."/".$replytext;
		}
		return $replytext;
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