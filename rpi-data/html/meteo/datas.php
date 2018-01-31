<?php

/*chart constante*/
$chart_title='Relevé Météo';
$chart_subtitle='Temperature et Pluie des dernières 24h';

/*rain*/
$rain_quantum=0.45;
$rain_color="blue";
$rain_id=13;

$humy_color="#E0FFFF";

$temp_location = [ Terrasse, Jardin, Buanderie, Serre ];
//temp id de la serre 4 et 15
//temp id de la terrasse 68, 6 12 et 13
$temp_id = [ 13, 111, 555, 15 ];
$temp_color = [ "#A9BCF5", "#00FF00", "#6C6E68", "#FFC000"];


function displayTemperature($info,$id,$location,$color,$type)
{
	echo "/********* Temperature Serie $id,$location,$color,$type *******/\n";
	echo "{\n";
	echo "\tname: \"$location\",\n";
	echo "\tcolor: '$color',\n";
	echo "\ttype: '$type',\n";
	echo "\tdata:\n";
	echo "\t[\n";
	$yesterday=$info[0]['date'];
	$arrlength = count($info);
	$nb=0;
	$last=-5;
	$filter=0;
	for($row = 0; $row < $arrlength; $row++) {
		$array_hour  = explode ( ":" , $info[$row]['hour'] );
		if($info[$row]['id']==$id){
			$array_date  = explode ( "-" , $info[$row]['date'] );
			$data=$info[$row]['temp'];
			$filter=$array_hour[1]-$last;
			if($filter<0){
				$filter = ($filter + 60);
			}
			if($filter > 4){ //le dernier echantillon date d'au moins 5 min (sinon trop de point)
				echo ("\t\t{ x: Date.UTC( $array_date[0] , $array_date[1]-1 , $array_date[2] , $array_hour[0] , $array_hour[1] , $array_hour[2] ),  y: $data },\n ") ;
				$nb++;
				$last=$array_hour[1];
			}else{
				//echo ("\t\t/*{ x: Date.UTC( $array_date[0] , $array_date[1]-1 , $array_date[2] , $array_hour[0] , $array_hour[1] , $array_hour[2] ),  y: $data },*/\n ") ;			
			}
    		}
	}
	echo "\t], //fin des données $nb/$arrlength\n";
	echo "\ttooltip: { valueSuffix: '°C' }\n";
	echo "} //fin de la serie\n";
}


function displayRain($info,$id,$location,$color,$type,$rain_quantum)
{


	echo "/********* Rain Serie $id,$location,$color,$type *******/\n";
	echo "{\n";
	echo "\tname: \"$location\",\n";
	echo "\tcolor: '$color',\n";
	echo "\ttype: '$type',\n";
	echo "\tpointWidth: 15,\n";
	echo "\tyAxis: 1,\n";
	echo "\tdata:\n";
	echo "\t[\n";
	$yesterday=$info[0]['date'];
	$current_array_date = explode ( "-" , $info[0]['date'] );
	$current_array_hour = explode ( ":" , $info[0]['hour'] );
	$rain_start=-1;
	$rainT=0;
	$current_hour=0;

	$arrlength = count($info);

	for($row = 0; $row < $arrlength-1; $row++) {
		$array_hour  = explode ( ":" , $info[$row]['hour'] );
		$array_date  = explode ( "-" , $info[$row]['date'] );

		if($info[$row]['id']==$id){
			$array_date  = explode ( "-" , $info[$row]['date'] );

			if($rain_start == -1) {
				$rainS=$rain_start=$rain=$info[$row]['rain'];
			}	
			if($current_array_hour[0] != $array_hour[0]){	
				$rainT=($rain-$rainS);
				if($rainT<0){
					$rainT+=256;
				}
				$rain_total+=$rainT;
				$rainS=$rain;
				echo ("\t\t{ x: Date.UTC( $current_array_date[0] , $current_array_date[1] -1, $current_array_date[2] , $current_array_hour[0] , 30 , 0 ), y: $rainT*$rain_quantum },\n") ;    
				$current_array_hour=$array_hour;
				$current_array_date=$array_date;
			}
			$rain=$info[$row]['rain'];
    		}
	}
	echo "\t], //fin des données\n";
	echo "\ttooltip: { valueSuffix: '°C' }\n";
	echo "} //fin de la serie\n";
}


function displayHumidity($info,$id,$location,$color,$type)
{
	echo "/********* Humidity Serie $id,$location,$color,$type *******/\n";
	echo "{\n";
	echo "\tname: \"$location\",\n";
	echo "\tcolor: '$color',\n";
	echo "\ttype: '$type',\n";
	echo "\tyAxis: 2,\n";
	echo "\tdata:\n";
	echo "\t[\n";
	$yesterday=$info[0]['date'];
	$arrlength = count($info);
	$nb=0;
	$last=-5;
	$filter=0;
	for($row = 0; $row < $arrlength; $row++) {
		$array_hour  = explode ( ":" , $info[$row]['hour'] );
		if($info[$row]['id']==$id){
			$array_date  = explode ( "-" , $info[$row]['date'] );
			$data=$info[$row]['humidity'];
			$filter=$array_hour[1]-$last;
			if($filter<0){
				$filter = ($filter + 60);
			}
			if($filter > 4){ //le dernier echantillon date d'au moins 5 min (sinon trop de point)
				echo ("\t\t{ x: Date.UTC( $array_date[0] , $array_date[1]-1 , $array_date[2] , $array_hour[0] , $array_hour[1] , $array_hour[2] ),  y: $data },\n ") ;
				$nb++;
				$last=$array_hour[1];
			}else{
				//echo ("\t\t/*{ x: Date.UTC( $array_date[0] , $array_date[1]-1 , $array_date[2] , $array_hour[0] , $array_hour[1] , $array_hour[2] ),  y: $data },*/\n ") ;			
			}
    		}
	}
	echo "\t], //fin des données $nb/$arrlength\n";
	echo "\ttooltip: { valueSuffix: '°C' }\n";
	echo "} //fin de la serie\n";
}



/******************Get Args **************************************/
$arg_date=$_GET['date'];
if($arg_date==''){
	$arg_date=date("ymd");
}


/******************Get Data from Files ****************************/
/*get yesteday raw file*/
$filename=date("Y-m-d", strtotime("20$arg_date - 1 day"));
//$filename=date("Y-m-d",strtotime("-1 days"));
$filename .= "_data.txt";
$txt_file    = file_get_contents("http://data.chafouinchafouine.fr/log_rtl433/$filename");
$rows        = explode("\n", $txt_file);
array_shift($rows);
foreach($rows as $row => $data)
{
    //extract row data
    $row_data = explode(';', $data);
    $yesterday[$row]['id']         = $row_data[0];
    $yesterday[$row]['date']       = $row_data[1];
    $yesterday[$row]['hour']       = $row_data[2];
    $yesterday[$row]['temp']       = $row_data[3];
    $yesterday[$row]['pression']   = $row_data[4];
    $yesterday[$row]['humidity']   = $row_data[5];
    $yesterday[$row]['temp2']      = $row_data[6];
    $yesterday[$row]['battery']    = $row_data[7];
    $yesterday[$row]['rain']       = $row_data[8];
}
/*get today raw file*/
$filename=date("Y-m-d", strtotime("20$arg_date"));
//$filename=date("Y-m-d");
$filename .= "_data.txt";
$txt_file    = file_get_contents("http://data.chafouinchafouine.fr/log_rtl433/$filename");
$rows        = explode("\n", $txt_file);
array_shift($rows);
foreach($rows as $row => $data)
{
    //extract row data
    $row_data = explode(';', $data);
    $today[$row]['id']         = $row_data[0];
    $today[$row]['date']       = $row_data[1];
    $today[$row]['hour']       = $row_data[2];
    $today[$row]['temp']       = $row_data[3];
    $today[$row]['pression']   = $row_data[4];
    $today[$row]['humidity']   = $row_data[5];
    $today[$row]['temp2']      = $row_data[6];
    $today[$row]['battery']    = $row_data[7];
    $today[$row]['rain']       = $row_data[8];
}

/*merge yesterday and today*/
$info = array_merge( $yesterday , $today);

/******************HTML ******************************************/
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Metéo</title>

		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<style type="text/css">
${demo.css}
		</style>
		<script type="text/javascript">
$(function () {
	$('#container').highcharts({
		chart: { zoomType: 'x' },
		title:    {<?php echo "text: '$chart_title'"; ?>},
		subtitle: {<?php echo "text: '$chart_subtitle'"; ?>},
		xAxis: {
			startOnTick: true,
			type: 'datetime',
			title: { text: 'Heure (UTC)' },
			crosshair: true
		},

		yAxis: 
		[
			{ // Primary yAxis
			    labels: {
				format: '{value}°C',
				style: {
				    color: Highcharts.getOptions().colors[1]
				}
			    },
			    title: {
				text: 'Temperature',
				style: {
				    color: Highcharts.getOptions().colors[1]
				}
			    },
			}, 
			{ // Secondary yAxis
				min: 0,
				max: 10,
				opposite: true,
				labels: {
					format: '{value}mm',
					style: { <?php echo "color: '$rain_color'"; ?> }
				},
				title: {
					text: 'Pluie',
					style: { <?php echo "color: '$rain_color'"; ?> }
				},
			}, 
			{ // thierd yAxis
				min: 0,
				max: 100,
				opposite: true,
				labels: {
					format: '{value}%',
					style: { <?php echo "color: 'black'"; ?> }
				},
				title: {
					text: 'Humidité',
					style: { <?php echo "color: 'black'"; ?> }
				},
			}
		],
		tooltip: { shared: true	},

		plotOptions: {
			spline: {
				marker: { enabled: false }
			},
			areaspline: {
				marker: { enabled: false }
			}
		},
		series: 
		[
<?php
	$nb = count($temp_id);

	displayHumidity($info, $temp_id[2], $temp_location[2], $humy_color, "areaspline");
	echo ",\n";
	
	for($row = 0; $row < $nb; $row++) {
		displayTemperature($info, $temp_id[$row], $temp_location[$row], $temp_color[$row], "spline");
		echo ",\n";
	}

	displayRain($info,$rain_id,"Pluie",$rain_color,"column",$rain_quantum);
	
?>

		] //fin des series
	});//fin du contenaire highcharts
});
		</script>
	</head>
	<body >
<script src="/highcharts/js/highcharts.js"></script>
<script src="/highcharts/js/modules/exporting.js"></script>

<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
<?php 
	echo "<center>\n";
	$yesterdayA=date('ymd', strtotime("20$arg_date - 1 day"));
	$yesterday=date('Y-m-d', strtotime("20$arg_date - 1 day"));
	echo "<a href='datas.php?date=$yesterdayA'>$yesterday</a>\n";
	$tomorrowA=date('ymd', strtotime("20$arg_date + 1 day"));
	$tomorrow=date('Y-m-d', strtotime("20$arg_date + 1 day"));
	echo "<a href='datas.php?date=$tomorrowA'>$tomorrow</a>\n";
	echo "</center>\n";

?>
	</body>
</html>
