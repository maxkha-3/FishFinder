<?php
	include 'func_lib.php';
	
	function create_user_table() {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$sql = "CREATE TABLE user (user_id int, name varchar(100), address varchar(100), biography text, 
			email varchar(100), city varchar(100), PRIMARY KEY (user_id)";
		$result = mysqli_query($link, $sql);
	}
	
	function create_location_table() {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$sql = "CREATE TABLE location (lat int, lng int, id int, rating int, PRIMARY KEY (id)";
		$result = mysqli_query($link, $sql);
	}
	
	function create_fishingtrip_table() {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$sql = "CREATE TABLE fishing_trip (user_id int, loc_id int, date date, catch int, weather varchar(100), 
			time int, trip_id int, rating int, PRIMARY KEY (id)";
		$result = mysqli_query($link, $sql);
	}
	
	function create_county_table() {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$sql = "CREATE TABLE counties (county_id int, name int, lat int, lng int, PRIMARY KEY (county_id)";
		$result = mysqli_query($link, $sql);
	}

	
?>
