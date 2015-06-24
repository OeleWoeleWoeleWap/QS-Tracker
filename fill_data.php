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

	$data = getRows($db, $type, $client, $campaign, $adgroup, $keyword)


	echo json_encode($data);




	function getRows($db, $type, $client, $campaign, $adgroup, $keyword){
		// initiate empty data array;
		$data = array();

		// get all unique rows
		$query = "SELECT DISTINCT `$type` FROM `y_DataRaw` WHERE `Client` ".
			"LIKE '$clientID' AND `Campaign` LIKE '$campaignID' AND `Adgroup`  ".
			"LIKE '$adgroupID' AND `Keyword` LIKE '$keywordID'";
		$rows = $db->query($query);

		// loop through all rows
		
		while($row = $rows->fetch_assoc()){
			// initiate empty row data array
			$rowdata = array();

			// get row name
			$query = "SELECT `Name` FROM `a_Clients` WHERE `ID` = '".$row['Client']."'";
			$name = $db->query($query);
			$name = $name->fetch_assoc();

			// store data
			$rowdata['name'] 	= $name['Name'];
			$rowdata['clientID'] = $row['Client'];
			$rowdata['campaignID'] = "%";
			$rowdata['keywordID'] = "%";
			$rowdata['adgroupID'] = "%";
			$rowdata['childdatagotten'] = false;

			$rowdata['data'] = getData($db, "Client", $client['Client'],'%','%','%');
			$data[] = $rowdata;
		}
		return $data;
	}

	function getData($db, $type, $clientID, $campaignID, $adgroupID, $keywordID){
		// initiate data variable
		$data = array();

		// get periods
		$query = "SELECT DISTINCT `Year`,`Month` FROM `y_DataRaw` WHERE `Client` ".
			"LIKE '$clientID' AND `Campaign` LIKE '$campaignID' AND `Adgroup`  ".
			"LIKE '$adgroupID' AND `Keyword` LIKE '$keywordID'";
		$periods = $db->query($query);

		while($period = $periods->fetch_assoc()){
			$year = $period['Year'];
			$month = $period['Month'];
			
			$query = "SELECT * FROM `y_DataRaw` WHERE `Client` LIKE '$clientID' AND ".
				"`Campaign` LIKE '$campaignID' AND `Adgroup` LIKE '$adgroupID' AND ".
				"`Keyword` LIKE '$keywordID' AND `Month` = '$month' AND `Year` = ".
				"'$year'";	
			$keywords = $db->query($query);

			$totalimp 	= 0;
			$totalqs 	= 0;
			while($keyword = $keywords->fetch_assoc()){
				$totalimp 	+= (int) $keyword['Impressies'];
				$totalqs	+= (int) $keyword['Impressies'] * (int) $keyword['QS'];
			}

			$data[$month."/".$year] = round($totalqs / $totalimp / 10,2);
		}

		return $data;

	}

?>