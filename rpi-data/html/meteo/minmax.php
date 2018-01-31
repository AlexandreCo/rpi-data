<?php

/*chart constante*/
$chart_title='Relevé Météo';
$chart_subtitle='Temperature et Pluie des dernières 24h';

/*rain*/
$rain_quantum=0.45;
$rain_color="blue";
$rain_id=6;

$temp_location = [ Terrasse, Jardin, Buanderie, Serre ];
$serre_ids = [ 4 , 15 ];
$terrasse_ids = [ 68 , 6 , 12, 13 ];
$temp_id = [ $terrasse_ids , 111, 555, $serre_ids ];
$temp_color = [ "#A9BCF5", "#00FF00", "#6C6E68", "#FFC000"];



function minmaxTemperature($info,$index,$id,$bmin)
{
	$arrlength = count($info);
	$min=300;
	$max=-300;
	$nb_id = count($id);

	for($row = 0; $row < $arrlength; $row++) {
		if($nb_id == 1) {
			// only one id
			if($info[$row]['id']==$id){
				$array_date  = explode ( "-" , $info[$row]['date'] );
				$data=$info[$row]['temp'];
				if($min>$data){
					$min=$data;			
				}

				if($max<$data){
					$max=$data;			
				}
			}
		}else{
			// multiple ids => array
			for($idx = 0; $idx < count($id); $idx++) {
				
				if($info[$row]['id']==$id[$idx]){
					$array_date  = explode ( "-" , $info[$row]['date'] );
					$data=$info[$row]['temp'];
					if($min>$data){
						$min=$data;			
					}

					if($max<$data){
						$max=$data;			
					}
				}
			}
		}

	}
	if ( $bmin == 1) {
		if($min!=300){
			echo ("\t\t{ x: Date.UTC( $array_date[0] , $array_date[1]-1 , $array_date[2] , 0 , 0 , 0 ),  y: $min },\n ") ;
		}
	}else{
		if($max!=-300){
			echo ("\t\t{ x: Date.UTC( $array_date[0] , $array_date[1]-1 , $array_date[2] , 0 , 0 , 0 ),  y: $max },\n ") ;
		}
	}
}


function displayMin($arg_date,$id,$location,$type,$nb)
{
	echo "/********* Temperature Serie $id,$location,$type *******/\n";
	echo "{\n";
	echo "\tname: \"$location\",\n";
	echo "\tcolor: '#A9BCF5',\n";
	echo "\ttype: '$type',\n";
	echo "\tdata:\n";
	echo "\t[\n";
	echo "\t// $arg_date,$id,$location,$type,$nb \n";
	for($i = 0; $i < $nb; $i++) {
		getFilenameMinMax($arg_date,$id,$i,1);
	}

	echo "\t], //fin des données $nb/$arrlength\n";
	echo "\ttooltip: { valueSuffix: '°C' }\n";
	echo "} //fin de la serie\n";
}

function displayMax($arg_date,$id,$location,$type,$nb)
{
	echo "/********* Temperature Serie $id,$location,$type *******/\n";
	echo "{\n";
	echo "\tname: \"$location\",\n";
	echo "\tcolor: '#FFC000',\n";
	echo "\ttype: '$type',\n";
	echo "\tdata:\n";
	echo "\t[\n";
	echo "\t// $arg_date,$id,$location,$type,$nb \n";
	for($i = 0; $i < $nb; $i++) {
		getFilenameMinMax($arg_date,$id,$i,0);
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

$arg_id=$_GET['id'];
if($arg_id==''){
	$arg_id=0;
}
/******************Get Data from Files ****************************/
function getFilenameMinMax($arg_date,$id,$index,$bmin)
{
	$filename=date("Y-m-d", strtotime("20$arg_date - $index day"));
	$filename .= "_data.txt";
	$txt_file    = file_get_contents("http://data.chafouinchafouine.fr/log_rtl433/$filename");
	$rows        = explode("\n", $txt_file);
	array_shift($rows);
	foreach($rows as $row => $data)
	{
	    //extract row data
	    $row_data = explode(';', $data);
	    $info[$row]['id']         = $row_data[0];
	    $info[$row]['date']       = $row_data[1];
	    $info[$row]['hour']       = $row_data[2];
	    $info[$row]['temp']       = $row_data[3];
	    $info[$row]['pression']   = $row_data[4];
	    $info[$row]['humidity']   = $row_data[5];
	    $info[$row]['temp2']      = $row_data[6];
	    $info[$row]['battery']    = $row_data[7];
	    $info[$row]['rain']       = $row_data[8];
	}
	minmaxTemperature($info,$index,$id,$bmin);
}




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
			}
		],
		tooltip: { shared: true	},

		plotOptions: {
			spline: {
				marker: { enabled: false }
			}
		},

		series: 
		[
<?php
	$nb = count($temp_id);
	//for($row = 0; $row < $nb; $row++) {
	//for($row = 0; $row < 1; $row++) {
	$row=$arg_id;
		displayMin($arg_date,$temp_id[$row],$temp_location[$row],"spline",365);
		echo ",\n";
		displayMax($arg_date,$temp_id[$row],$temp_location[$row],"spline",365);
		echo ",\n";
	//}
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

	</body>
</html>

