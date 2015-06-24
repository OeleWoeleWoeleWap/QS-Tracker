<?php
function fillClient(){
	$numcols = $_GET['numcols'];

	$con = mysql_connect("localhost","Adwords","tSy92WTqsVsDLh9e") or die('MySQL error: '.mysql_error());
	$db = mysql_select_db("Adwords_qs_tracker") or die("MySQL error: ".mysql_error());

	$query = "SELECT DISTINCT Client FROM `Data`";
	$clients = mysql_query($query);

	$dataString= "";
	while($client = mysql_fetch_array($clients)){
		$dataString .= "['<div class=\'client\' client=\'".utf8_encode($client['Client'])."\'>".utf8_encode($client['Client'])." </div><div class=\'adData\'>+</div>',";
		$qsArray = array();

		$query = "SELECT DISTINCT Year, Month FROM `Data` WHERE Client='".$client['Client']."' ORDER BY Year, Month ASC";
		$periods = mysql_query($query) or die(mysql_error());

		while($period = mysql_fetch_array($periods)){
			$qs = 0;
			$imp = 0;

			$query = "SELECT * FROM `Data` WHERE Client='".$client['Client']."' AND Year='".$period['Year']."' AND Month='".$period['Month']."'";
			$keywds = mysql_query($query);

			if(mysql_num_rows($keywds)==0){$qualityScore = 'no data';}
			else{
				while($keywd = mysql_fetch_array($keywds)){
					$qs += intval($keywd['QS']) * intval($keywd['Impressies']);
					$imp += intval($keywd['Impressies']);
				}
				if($imp==0){$qualityScore = 0;}
				else{$qualityScore = round($qs/$imp,2);}
			}

			if(gettype($qsArray[$period['Year']]) == 'NULL'){
				$qsArray[$period['Year']] = array();
			}

			$qsArray[$period['Year']][$period['Month']] = round($qualityScore / 100,2);
		}


		$currentMonth = date('n');
		$currentyear = date('Y');

		for($i=$numcols-1;$i>-1;$i--){
			$thisMonth = $currentMonth - $i -1;
			$thisyear = $currentyear;
			if($thisMonth < 1){
				$thisMonth += 12;
				$thisyear -= 1;
			}
			$lastMonth = $currentMonth - $i - 2;
			$lastyear = $thisyear;
			if($lastMonth < 1){
				$lastMonth += 12;
				$lastyear -= 1;
			}
			
			if(gettype($qsArray[$thisyear][$thisMonth]) == 'NULL'){
				$dataString .= "'<center><gray>-</gray></center>',";
			}elseif(gettype($qsArray[$lastyear][$lastMonth]) == 'NULL'){
				$dataString .= "'<center>".$qsArray[$thisyear][$thisMonth]."</center>',";
			}else{
				$qsImpr = round(($qsArray[$thisyear][$thisMonth]/$qsArray[$lastyear][$lastMonth] - 1) * 100,1);
				if($qsImpr < 0){$dataString .= "'<center>".$qsArray[$thisyear][$thisMonth]." (<red>".$qsImpr."%</red>)</center>',";}
				elseif($qsImpr > 0){$dataString .= "'<center>".$qsArray[$thisyear][$thisMonth]." (<green>+".$qsImpr."%</green>)</center>',";}
				elseif($qsImpr == 0){$dataString .= "'<center>".$qsArray[$thisyear][$thisMonth]." (+0%)</center>',";}
			}

		}
		$dataString .= "],";
	}
	return '['.trim($dataString,',').']';

	
}
echo fillClient();
?>