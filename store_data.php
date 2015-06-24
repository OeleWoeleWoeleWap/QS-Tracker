<?php
	//error_reporting(-1);
	//ini_set('display_errors', 'On');

	// connect to db
	$db = new mysqli("localhost", "Adwords", "tSy92WTqsVsDLh9e", "Adwords_qs_tracker");
	if ($db->connect_errno) {
	    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}

	// get data from get params
	$client 		= utf8_decode($_GET['client']);
	$campaign 		= utf8_decode($_GET['campaign']);
	$adgroup 		= utf8_decode($_GET['adGroup']);
	$keyword 		= utf8_decode($_GET['keywd']);
	$qs 			= utf8_decode($_GET['qs']);
	$impressions 	= utf8_decode($_GET['impressions']);

	if(gettype($_GET['month']) == 'string' && gettype($_GET['year']) == 'string'){
		$month = $_GET['month'];
		$year = $_GET['year'];
		$qs = $qs * 100;
	}
	else{
		$year = date('Y');
		$month = date('n') - 1;
		if($month < 1){
			$month += 12;
			$year -= 1;
		}
	}


	// delete old value;
	$query = "DELETE FROM `Data_v2` WHERE Client = '$client' AND Campaign = '$campaign' AND Adgroup = '$adgroup' AND Keyword = '$keyword' AND Year = '$year' AND Month = '$month'";
	$db->query($query);

	// store new values;
	$query= "INSERT INTO `Data_v2`(Client,Campaign,Adgroup,Keyword,QS,Impressies,Year,Month) VALUES ('$client','$campaign','$adgroup','$keyword','$qs','$impressions','".$year."','".$month."')";
	$db->query($query);


	// get or create ID's belonging to names;
	$campaignID = getStringID($db, $campaign);
	$clientID 	= getStringID($db, $client);
	$adgroupID 	= getStringID($db, $adgroup);
	$keywordID	= getStringID($db, $keyword);

	$query = "DELETE FROM `y_DataRaw` WHERE Client = '$clientID' AND Campaign = '$campaignID' AND Adgroup = '$adgroupID' AND Keyword = '$keywordID' AND Year = '$year' AND Month = '$month'";
	$db->query($query);

	$query = "INSERT INTO `y_DataRaw`(Client,Campaign,Adgroup,Keyword,QS,Impressies,Year,Month) VALUES ('$clientID','$campaignID','$adgroupID','$keywordID','$qs','$impressions','".$year."','".$month."')";
	$db->query($query);




	function getStringID($db,$name){
		/*	Function that returns the ID belonging to $name in table $type
		 *  in db $db if it exists, otherwise creates the id and returns it
		 */

		// check if ID already exists
		$query = "SELECT `ID` FROM `strings` WHERE `Name` = '$name'";
		$result = $db->query($query);
	
		if($result->num_rows == 1){
			// return if it exists
			$row = $result->fetch_assoc();
			return $row["ID"];
		}
		else{
			// ID does not exist, create
			$query = "INSERT INTO `strings`(Name) VALUES ('$name')";
			$db->query($query) or die('Mysql error '.mysqli_error($db));

			// get and return ID;
			$query = "SELECT `ID` FROM `strings` WHERE `Name` = '$name'";
			$result = $db->query($query) or die('Mysql error: '.mysqli_error($db));
			$row = $result->fetch_assoc();
			return $row["ID"];
		}

	}


?>
