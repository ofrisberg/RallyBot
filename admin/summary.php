<?php
require_once "../setup.php";
init('../');

session_start();
if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
	header('Location: login.php');
	exit();
}

$summ = new Summary();

?>

<html><head><script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script><meta name="viewport" content="width=device-width, initial-scale=1"></head><body>
<h1>Sammanfattning</h1>

<h2>Allmänt</h2>
<table>
	<tr>
		<td><b>Ihopkopplade</b></td>
		<td><?= $summ->nrCoupled() ?>/100</td>
	</tr>
	<tr>
		<td><b>Startade</b></td>
		<td><?= $summ->nrStarted() ?>/100</td>
	</tr>
	<tr>
		<td><b>Upplåsningar (digitala)</b></td>
		<td><?= $summ->nrUnlocks() ?>/1000</td>
	</tr>
	<tr>
		<td><b>Hjälprebusar (digitala)</b></td>
		<td><?= $summ->nrHelps() ?>/4000</td>
	</tr>
	<tr>
		<td><b>Lunch in</b></td>
		<td><?= $summ->nrLunchIn() ?>/100</td>
	</tr>
	<tr>
		<td><b>Lunch ut</b></td>
		<td><?= $summ->nrLunchOut() ?>/100</td>
	</tr>
	<tr>
		<td><b>Stålsvar (digitala)</b></td>
		<td><?= $summ->nrAnswers() ?>/700</td>
	</tr>
	<tr>
		<td><b>Avslutade</b></td>
		<td><?= $summ->nrFinished() ?>/100</td>
	</tr>
</table>

<h2>Dragna hjälprebusar</h2>
<p>Efter stationsnummer. Fysiska och digitala för båda banorna.</p>
<table>
<?php
$arr = $summ->getHelpStatsByStation();
foreach($arr as $k => $nr){
	?>
	<tr>
		<td><b><?= $k ?></b></td>
		<td><?= $nr ?></td>
	</tr>
	<?php
}
?>
</table>

<h2>Översiktsgraf</h2>
<div style="width:1500px;max-width:100%;"><canvas id="chart1"></canvas></div>
<?php
$t_start = "2018-09-29 07:30:00";
$t_end = "2018-09-29 17:30:00";
$t_min_delta = 30;
$t_last = $t_start;

$x_values = [];
$y_values = [];
while($t_last < $t_end){
	$t_current = new DateTime($t_last);
	$t_current->add(new DateInterval('PT' . $t_min_delta . 'M'));
	$t_current = $t_current->format('Y-m-d H:i:s');
	$y_values_help[] = $DB->query("SELECT * FROM r18_messages WHERE m_ts_insert>='$t_last' AND m_ts_insert<'$t_current' AND m_text LIKE 'hjälp %' AND m_text LIKE '%.%' AND t_id<100")->num_rows;
	$y_values_lunch[] = $DB->query("SELECT * FROM r18_teams WHERE t_ts_lunch_in>='$t_last' AND t_ts_lunch_in<'$t_current' AND t_id<100")->num_rows;
	$y_values_unlock[] = $DB->query("SELECT * FROM r18_progress WHERE r_ts_unlock>='$t_last' AND r_ts_unlock<'$t_current' AND t_id<100")->num_rows;
	$y_values_stalsvar[] = $DB->query("SELECT * FROM r18_answers WHERE a_ts_insert>='$t_last' AND a_ts_insert<'$t_current' AND t_id<100")->num_rows;
	$y_values_finish[] = $DB->query("SELECT * FROM r18_teams WHERE t_ts_finish>='$t_last' AND t_ts_finish<'$t_current' AND t_id<100")->num_rows;
	$tmp = (new DateTime($t_last))->format('H:i');
	$x_values[] = '"'.$tmp.'"';
	$t_last = $t_current;
}

?>
<script>
var ctx1 = document.getElementById('chart1').getContext('2d');
var chart1 = new Chart(ctx1, {
    // The type of chart we want to create
    type: 'line',

    // The data for our dataset
    data: {
        labels: [<?= implode(',',$x_values) ?>],
        datasets: [{
            label: "Hjälprebusar",
            borderColor: 'rgb(255, 0, 0)',
            data: [<?= implode(',',$y_values_help) ?>],
        },
		{
            label: "Lunch",
            borderColor: 'rgb(0, 255, 0)',
            data: [<?= implode(',',$y_values_lunch) ?>],
        },
		{
            label: "Upplåsningar",
            borderColor: 'rgb(0, 0, 255)',
            data: [<?= implode(',',$y_values_unlock) ?>],
        },
		{
            label: "Stålsvar",
            borderColor: 'rgb(0, 122, 122)',
            data: [<?= implode(',',$y_values_stalsvar) ?>],
        },
		{
            label: "Målgångar",
            borderColor: 'rgb(122, 122, 0)',
            data: [<?= implode(',',$y_values_finish) ?>],
        }]
    },

    // Configuration options go here
    options: {}
});
</script>
























