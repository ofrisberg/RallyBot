<?php
require_once "../setup.php";
init('../');

session_start();
if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
	header('Location: login.php');
	exit();
}

$query = $DB->query("SELECT * FROM r18_teams WHERE t_id < 100");
if($query->num_rows == 0){
	exit("Inga lag");
}
$arr = [];
function cmp($a, $b){
	return ($a["ts"] < $b["ts"]) ? -1 : 1;
}
while($row = $query->fetch_assoc()){
	$team = new Team($row);
	
	$query2 = $DB->query("SELECT * FROM r18_messages WHERE t_id='".$team->getId()."' AND m_dir='to' AND m_text LIKE '%Lat:%' ORDER BY m_ts_insert ASC");
	if($query2->num_rows > 0){
		$subarr = [
			"nr" => $team->getId(),
			"data" => [],
		];
		$subarr["data"][] = [
			"lat" => 59.820733,
			"lng" => 17.656450,
			"ts" => $team->getTsStart2()
		];
		$subarr["data"][] = [
			"lat" => 60.000534,
			"lng" => 17.861430,
			"ts" => $team->getTsLunchIn()
		];
		$subarr["data"][] = [
			"lat" => 60.000534,
			"lng" => 17.861430,
			"ts" => $team->getTsLunchOut()
		];
		while($row2 = $query2->fetch_assoc()){
			$msg = new Message($row2);
			if(preg_match('/Lat:([0-9]+\.[0-9]+)\s/iu',$msg->getText(),$matches) && preg_match('/Lng:([0-9]+\.[0-9]+)\b/iu',$msg->getText(),$matches2)){
				$subarr["data"][] = [
					"lat" => floatval($matches[1]),
					"lng" => floatval($matches2[1]),
					"ts" => $msg->getTsInsert()
				];
			}
		}
		$subarr["data"][] = [
			"lat" => 59.820733,
			"lng" => 17.656450,
			"ts" => $team->getTsFinish()
		];
		
		usort($subarr["data"],"cmp");
		$arr[] = $subarr;
	}
}
?>

<html>
	<head>
		<style>
		html,body,div{margin:0px;padding:0px;}
		#map{
			width: 1345px;
			border: 1px solid red;
			height: 813px;
			background: url(../images/map2.png);
			background-repeat: no-repeat;
			background-size: auto;
			position:absolute;
		}
		#clock{
			position:absolute;
			font-size:50px;
			text-align:center;
			width: 1345px;
			top:20px;
		}
		#teams{
			width: 1345px;
			height: 813px;
			position:absolute;
		}
		.dot{
			height: 25px;
			width: 25px;
			background-color: black;
			color:white;
			font-size:16px;
			text-align: center;
			border-radius: 50%;
			display: inline-block;
			margin-left:-10px;
			margin-top:-10px;
			position: absolute;
		}
		</style>
	</head>
	<body>

		<div id="map"><div id="clock"></div><div id="teams"></div></div>

		<script>
			Date.prototype.hhmm = function() {
				var yyyy = this.getFullYear();
				var mm = this.getMonth() < 9 ? "0" + (this.getMonth() + 1) : (this.getMonth() + 1); // getMonth() is zero-based
				var dd  = this.getDate() < 10 ? "0" + this.getDate() : this.getDate();
				var hh = this.getHours() < 10 ? "0" + this.getHours() : this.getHours();
				var min = this.getMinutes() < 10 ? "0" + this.getMinutes() : this.getMinutes();
				var ss = this.getSeconds() < 10 ? "0" + this.getSeconds() : this.getSeconds();
				return hh+":"+min;
			};
			var app = {
				el_map: document.getElementById("map"),
				el_clock: document.getElementById("clock"),
				pixels_per_lng: 0,
				pixels_per_lat: 0,
				current_time: null,
				corners: {
					lng_left: 16.992093,
					lng_right: 18.535266,
					lat_top: 60.145448,
					lat_bottom: 59.678375,
					x_left: 0,
					x_right: 1345,
					y_top: 813,
					y_bottom: 0,
				},
				teams: <?= json_encode($arr) ?>,
				
				lng2x: function(lng){
					return (lng - this.corners.lng_left)*this.pixels_per_lng;
				},
				lat2y: function(lat){
					return (lat - this.corners.lat_bottom)*this.pixels_per_lat;
				},
				addMinutes: function(date, minutes) {
					return new Date(date.getTime() + minutes*60000);
				},
				paintLatLng: function(lat,lng){
					var x = this.lng2x(lng);
					var y = this.lat2y(lat);
					this.el_map.innerHTML += '<span class="dot" style="bottom:'+y+'px;left:'+x+'px;">14</span>';
					
				},
				setup: function(){
					this.current_time = new Date('2018-09-29 08:30:00');
					this.pixels_per_lng = this.corners.x_right/(this.corners.lng_right-this.corners.lng_left);
					this.pixels_per_lat = this.corners.y_top/(this.corners.lat_top-this.corners.lat_bottom);
				},
				init: function(){
					this.timestep();
				},
				timestep: function(){
					document.getElementById("clock").innerHTML = this.current_time.hhmm();
					this.moveTeams();
					if(this.current_time.hhmm() < '17:00'){
						this.current_time = this.addMinutes(this.current_time,1);
						setTimeout(this.timestep.bind(this), 50);
					}
				},
				moveTeams: function(){
					var html = '';
					for(var i = 0; i<this.teams.length; i++){
						var team = this.teams[i];
						html += this.moveTeam(team);
					}
					document.getElementById("teams").innerHTML = html;
				},
				moveTeam: function(team){
					var html = '';
					for(var i = 0; (i+1)<team['data'].length; i++){
						var d1 = team['data'][i];
						var d2 = team['data'][i+1];
						var t1 = new Date(d1['ts']);
						var t2 = new Date(d2['ts']);
						if(this.current_time > t1 && this.current_time < t2){
							var x1 = this.lng2x(d1['lng']);
							var y1 = this.lat2y(d1['lat']);
							
							var x2 = this.lng2x(d2['lng']);
							var y2 = this.lat2y(d2['lat']);
							
							var vx = (x2-x1)/(t2-t1);
							var vy = (y2-y1)/(t2-t1);
							
							var x = x1 + vx * (this.current_time-t1);
							var y = y1 + vy * (this.current_time-t1);
							if(x > this.corners.x_left && x < this.corners.x_right && y < this.corners.y_top && y > this.corners.y_bottom){
								html += '<span class="dot" style="bottom:'+y+'px;left:'+x+'px;">'+team['nr']+'</span>';
							}
						}
					}
					return html;
				},
			};
			app.setup();
			app.init();
			//app.paintLatLng(59.749271, 17.604341);
			
			
			
		</script>
	</body>
	
</html>