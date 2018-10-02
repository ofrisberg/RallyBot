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
	el_map: null,
	el_clock: null,
	pixels_per_lng: 0,
	pixels_per_lat: 0,
	current_time: null,
	current_r_step: 0,
	tot_r_steps: 100,
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
	current_stats: {
		nr_start : 0,
		nr_start2 : 0,
		nr_messages : 0,
		nr_unlocks : 0,
		nr_lunchin : 0,
		nr_lunchout : 0,
		nr_lunchnetto : 0,
		nr_answers : 0,
		nr_finish : 0,
		nr_confused_bot : 0,
		nr_cold_statements : 0,
		nr_patric_hates_life : 0,
		nr_olle_replies_winwin : 0,
	},
	teams: null,
	resultlist: null,
	stats: null,
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
	setup: function(teams,resultlist,stats){
		this.teams = teams;
		this.resultlist = resultlist;
		this.stats = stats;
		this.stats = stats;
		this.el_map = document.getElementById("map");
		this.el_clock = document.getElementById("clock");
		this.current_time = new Date('2018-09-29 06:00:00'); //16:30
		this.pixels_per_lng = this.corners.x_right/(this.corners.lng_right-this.corners.lng_left);
		this.pixels_per_lat = this.corners.y_top/(this.corners.lat_top-this.corners.lat_bottom);
	},
	init: function(){
		this.timestep();
	},
	timestep: function(){
		document.getElementById("clock").innerHTML = "Klockan "+this.current_time.hhmm();
		this.moveTeams();
		this.updateStats();
		
		
		if(this.current_time.hhmm() < '17:00'){
			this.current_time = this.addMinutes(this.current_time,1);
			setTimeout(this.timestep.bind(this), 20);
		}else{
			this.initResult();
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
				
				var color = 'black';
				if('fail' in d2){color = 'red';} // && ((i+2) >= team['data'].length || (this.pixelDistance(d2,team['data'][i+2]) > 700 || 'fail' in team['data'][i+2]))
				
				if(x > this.corners.x_left && x < this.corners.x_right && y < this.corners.y_top && y > this.corners.y_bottom){
					html += '<span class="dot" style="bottom:'+y+'px;left:'+x+'px;background:'+color+'">'+team['nr']+'</span>';
				}
			}
		}
		return html;
	},
	pixelDistance: function(d1,d2){
		var x1 = this.lng2x(d1['lng']);
		var y1 = this.lat2y(d1['lat']);
		var x2 = this.lng2x(d2['lng']);
		var y2 = this.lat2y(d2['lat']);
		return Math.sqrt(x1*x2+y1*y2);
	},
	updateStats: function(){
		var arr = this.stats[this.current_time.hhmm()];
		
		this.current_stats.nr_start += arr['nr_start'];
		this.current_stats.nr_start2 += arr['nr_start2'];
		this.current_stats.nr_messages += arr['nr_messages'];
		this.current_stats.nr_unlocks += arr['nr_unlocks'];
		this.current_stats.nr_lunchin += arr['nr_lunchin'];
		this.current_stats.nr_lunchout += arr['nr_lunchout'];
		this.current_stats.nr_lunchnetto += arr['nr_lunchin'];
		this.current_stats.nr_lunchnetto -= arr['nr_lunchout'];
		this.current_stats.nr_answers += arr['nr_answers'];
		this.current_stats.nr_finish += arr['nr_finish'];
		this.current_stats.nr_confused_bot += arr['nr_confused_bot'];
		this.current_stats.nr_cold_statements += 5;
		this.current_stats.nr_patric_hates_life += 1;
		this.current_stats.nr_olle_replies_winwin += 1;
		
		var html = '';
		html += "<tr><td><b>Meddelanden</b></td><td>"+this.current_stats.nr_messages+"</td></tr>";
		html += "<tr><td><b>Incheckade</b></td><td>"+this.current_stats.nr_start+"</td></tr>";
		html += "<tr><td><b>Startade</b></td><td>"+this.current_stats.nr_start2+"</td></tr>";
		html += "<tr><td><b>Upplåsningar</b></td><td>"+this.current_stats.nr_unlocks+"</td></tr>";
		html += "<tr><td><b>Lunch in</b></td><td>"+this.current_stats.nr_lunchin+"</td></tr>";
		html += "<tr><td><b>Lunch ut</b></td><td>"+this.current_stats.nr_lunchout+"</td></tr>";
		html += "<tr><td><b>Lunch netto</b></td><td>"+this.current_stats.nr_lunchnetto+"</td></tr>";
		html += "<tr><td><b>StåL-svar</b></td><td>"+this.current_stats.nr_answers+"</td></tr>";
		html += "<tr><td><b>Målgångar</b></td><td>"+this.current_stats.nr_finish+"</td></tr>";
		html += "<tr><td><b>Boten förstår ej</b></td><td>"+this.current_stats.nr_confused_bot+"</td></tr>";
		
		/*html += "<tr><td>-</td><td></td></tr>";
		html += "<tr><td><b>Rallykåit: Det är kallt</b></td><td>"+this.current_stats.nr_cold_statements+"</td></tr>";
		html += "<tr><td><b>Patric: Livet suger</b></td><td>"+this.current_stats.nr_patric_hates_life+"</td></tr>";
		html += "<tr><td><b>Olle: Win win</b></td><td>"+this.current_stats.nr_olle_replies_winwin+"</td></tr>";*/
		
		
		
		document.getElementById("stats_table").innerHTML = html;
	},
	initResult: function(){
		document.getElementById("clock").innerHTML = 'Rebusrallyt 2018';//
		document.getElementById("teams").innerHTML = '';
		document.getElementById("stats").style.display = 'none';
		this.timestepResult();
	},
	timestepResult: function(){
		this.moveResults();
		this.current_r_step++;
		if(this.current_r_step < this.tot_r_steps){
			setTimeout(this.timestepResult.bind(this), 10);
		}else{
			setTimeout(function(){
				var elements = document.getElementsByClassName("dot");
				for(var i = 0; i<elements.length; i++){
					elements[i].style.width = 250;
					elements[i].style.textAlign = 'left';
					elements[i].style.borderRadius = '0%';
				}
				var elements2 = document.getElementsByClassName("hidden_name");
				for(var i2 = 0; i2<elements2.length; i2++){
					elements2[i2].style.display = 'inline';
				}
			},30);
		}
	},
	moveResults: function(){
		var html = '';
		for(var i = 0; i<this.resultlist.length; i++){
			html += this.moveResult(this.resultlist[i]);
		}
		document.getElementById("teams").innerHTML = html;
	},
	moveResult: function(res){
		var dt = 1;
		var x1 = this.lng2x(17.656450);
		var y1 = this.lat2y(59.820733);
		
		var x2;
		var y2;
		
		var y_base = 700;
		var x_base = 20;
		var x_times = 270;
		if(res['placement'] < 21){
			x2 = x_base + x_times*0;
			y2 = y_base - 30 * (res['placement']-0);
		}else if(res['placement'] < 41){
			x2 = x_base + x_times*1;
			y2 = y_base - 30 * (res['placement']-20);
		}else if(res['placement'] < 61){
			x2 = x_base + x_times*2;
			y2 = y_base - 30 * (res['placement']-40);
		}else if(res['placement'] < 81){
			x2 = x_base + x_times*3;
			y2 = y_base - 30 * (res['placement']-60);
		}else{
			x2 = x_base + x_times*4;
			y2 = y_base - 30 * (res['placement']-80);
		}
							
		var vx = (x2-x1)/(this.tot_r_steps);
		var vy = (y2-y1)/(this.tot_r_steps);
		
		var x = x1 + vx * (this.current_r_step);
		var y = y1 + vy * (this.current_r_step);
		return '<span class="dot" style="bottom:'+y+'px;left:'+x+'px;">'+res['placement']+' <span class="hidden_name">'+res['name']+'</span></span>';
	},
};