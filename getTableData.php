<?php
	//error_reporting(-1);
	//ini_set('display_errors', 'On');

	$data = array();

	// connect to db
	$db = new mysqli("localhost", "Adwords", "tSy92WTqsVsDLh9e", "Adwords_qs_tracker");
	if ($db->connect_errno) {
	    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}

	// get parameters from POST
	$client 	= $_POST['client'];
	$campaign 	= $_POST['campaign'];
	$adgroup	= $_POST['adgroup'];
	$keyword 	= $_POST['keyword'];

	// determine which type of data to retrieve;
	$type = "Client";
	if($client != '%'){ 	$type = "Campaign";}
	if($campaign != '%'){ 	$type = "Adgroup";}
	if($adgroup != '%'){ 	$type = "Keyword";}

	$data = getRows($db, $type, $client, $campaign, $adgroup, $keyword);

	echo json_encode($data);



	function getRows($db, $type, $client, $campaign, $adgroup, $keyword){
		// initiate empty data array;
		$data = array();

		// get all unique rows
		$query = "SELECT DISTINCT `$type` FROM `y_DataRaw` WHERE `Client` ".
			"LIKE '$client' AND `Campaign` LIKE '$campaign' AND `Adgroup`  ".
			"LIKE '$adgroup' AND `Keyword` LIKE '$keyword'";
		$rows = $db->query($query);
		// loop through all rows
		while($row = $rows->fetch_assoc()){
			// initiate empty row data array
			$rowdata = array();

			// get row name
			$query = "SELECT `Name` FROM `strings` WHERE `ID` = '".$row[$type]."'";
			$name = $db->query($query);
			$name = $name->fetch_assoc();

			$clientSel 		= $client;
			$campaignSel 	= $campaign;
			$adgroupSel 	= $adgroup;
			$keywdSel 		= $keyword;

			if($type == 'Client') $clientSel 		= $row['Client'];
			if($type == 'Campaign') $campaignSel	= $row['Campaign'];
			if($type == 'Adgroup') $adgroupSel 		= $row['Adgroup'];
			if($type == 'Keyword') $keywdSel 		= $row['Keyword'];

			// store data
			$rowdata['name'] 	= $name['Name'];
			$rowdata['clientID'] = $clientSel;
			$rowdata['campaignID'] = $campaignSel;
			$rowdata['keywordID'] = $keywdSel;
			$rowdata['adgroupID'] = $adgroupSel;
			
			$rowdata['data'] = getData($db, $type, $clientSel,$campaignSel,$adgroupSel,$keywdSel);
			$data[] = $rowdata;
		}

		return $data;
	}

	function getData($db, $type, $clientID, $campaignID, $adgroupID, $keywordID){
		$data = array();
		$numMonths = $_POST['numMonths'];

		$period = strtotime("today");

		for ($i=0; $i < $numMonths; $i++) { 
			// go back one month
			$period = strtotime("-1 month",$period);

			$year 	= (int) date('Y',$period);
			$month 	= (int) date('m',$period);


			$query = "SELECT * FROM `z_DataCalculated` WHERE `Client` = '$clientID' AND ".
				"`Campaign` = '$campaignID' AND `Adgroup` = '$adgroupID' AND ".
				"`Keyword` = '$keywordID' AND `Month` = '$month' AND `Year` = ".
				"'$year'";	
			$result = $db->query($query);
			if($result->num_rows == 1){
				$qs = $result->fetch_assoc();
				$data[$month."/".$year] = round($qs['QS'] / 100,1);
			}
			else{
				$query = "SELECT * FROM `y_DataRaw` WHERE `Client` LIKE '$clientID' AND ".
					"`Campaign` LIKE '$campaignID' AND `Adgroup` LIKE '$adgroupID' AND ".
					"`Keyword` LIKE '$keywordID' AND `Month` = '$month' AND `Year` = ".
					"'$year'";	
				$keywords = $db->query($query);

				if($keywords->num_rows != 0){
					$totalimp 	= 0;
					$totalqs 	= 0;
					while($keyword = $keywords->fetch_assoc()){
						$totalimp 	+= (int) $keyword['Impressies'];
						$totalqs	+= (int) $keyword['Impressies'] * (int) $keyword['QS'];
					}
					$qs =  $totalqs / $totalimp;
					$data[$month."/".$year] = round($qs/100,1);

					$query = "INSERT INTO `z_DataCalculated`(Client, Campaign, Adgroup, Keyword,".
						" QS, Year, Month) VALUES ('$clientID', '$campaignID', '$adgroupID', '$keywordID', ".
						" $qs, $year, $month)";
					$db->query($query);
				}
			}
		}
		return $data;

	}


?>