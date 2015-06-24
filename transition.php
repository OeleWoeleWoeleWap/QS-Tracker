<?php

	/*	Function that allows transistion from a string based database table
	 *  to an ID based db table, with strings belonging to the ids in other
	 *  tables.
	 */

	// connect
	$db = new mysqli("localhost", "Adwords", "tSy92WTqsVsDLh9e", "Adwords_qs_tracker");
	if ($db->connect_errno) {
	    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}

	// get old data
	$query = "SELECT * FROM `Data`";
	$data_old = $db->query($query) or die("Getting data fialed");

	// loop through old data
	while($row = $data_old->fetch_assoc()){
		$campaign = $row['Campaign'];
		$client = $row['Client'];
		$adgroup = $row['Adgroup'];
		$keyword = $row['Keyword'];

		$qs 	= $row["QS"];
		$imp 	= $row["Impressies"];
		$year 	= $row["Year"];
		$month 	= $row["Month"];

		
		// get or create ID's belonging to names;
		$campaignID = getStringID($db, $campaign);
		$clientID 	= getStringID($db, $client);
		$adgroupID 	= getStringID($db, $adgroup);
		$keywordID	= getStringID($db, $keyword);
		
		// store
		$query = "INSERT INTO `y_DataRaw`(Client, Campaign, Adgroup, Keyword, 
			QS, Impressies, Year, Month) VALUES ($clientID, $campaignID, 
			$adgroupID, $keywordID, $qs, $imp, $year, $month)";
		$db->query($query);
	}

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