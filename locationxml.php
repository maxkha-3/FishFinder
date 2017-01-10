<?php
	error_reporting(E_ALL);	
	session_start();
	/*
		Creates an XML file, that acts as an input to the Google Maps
	*/
	include 'func_lib.php';
	function parseToXML($htmlStr)
	{
		$xmlStr=str_replace('<','&lt;',$htmlStr);
		$xmlStr=str_replace('>','&gt;',$xmlStr);
		$xmlStr=str_replace('"','&quot;',$xmlStr);
		$xmlStr=str_replace("'",'&#39;',$xmlStr);
		$xmlStr=str_replace("&",'&amp;',$xmlStr);
		return $xmlStr;
	}

	$rows = getLocationRating($_SESSION['region'], $_SESSION['fGrade']);

	header("Content-type: text/xml");

	// Start XML file, echo parent node = mysqli_fetch_array($db['result'])
	echo '<markers>';

	// Iterate through the rows, printing XML nodes for each
	foreach ($rows as $row){
		// Add to XML document node
		$name = parseToXML($row['name']);
		$lat = $row['lat'];
		$lng = $row['lng'];
		$rating = $row['rating'];
		$desc = $row['description'];
		echo '<marker ';
		echo 'name="' . $name . '" ';
		echo 'lat="' . $lat . '" ';
		echo 'lng="' . $lng . '" ';
		echo 'rating="' . $rating . '" ';
		echo 'desc="' . $desc . '" ';
		echo '/>';
	}

	// End XML file
	echo '</markers>';

?>