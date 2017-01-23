<?php
	//API for Fish Finder
	
	//See create_dbstructure.php for MySQL database details

	//Set up the connection with the server
	
	include 'db_info.php';
	
	function setUpConnection() {
		//Fetch db info from the separate php file
		$db_host = get_db_host();
		$db_user = get_db_user();
		$db_password = get_db_password();
		$db_database = get_db_database();
		
		//Establish connection
		$link = mysqli_connect($db_host, $db_user, $db_password, $db_database);
		
		if (!$link) {
			echo "Error: Unable to connect to MySQL." . PHP_EOL;
			echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
			echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
			exit;
		}
		
		return $link;
	}
	
	/*
	Checks if the user exists in the database. 
	Is used to determine if a new user should be created or not.
	
	parameters: 
	$id The id of the user.
	
	return:
	true if user exists, otherwise false.
	*/
	function userExistent($id) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		
		$sql = "SELECT * FROM user WHERE user_id='$id'";
		$result = mysqli_query($link, $sql);
	
		$nr = mysqli_affected_rows($link);
		
		
		if ($nr == 0) {
			return false;
		}
		else {
			return true;
		}
	}
	
	/*
	Adds a new user to the database
	Sets the user_id, name and email columns in the database.
	This is the information from the google profile.
	
	parameters:
	$id The id of the user.
	$userName The (google) user name.
	$userEmail The email of the user.
	*/
	function addNewUser($id, $userName, $userEmail) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$stmt = $link->prepare("INSERT INTO user (user_id, name, email) VALUES (?, ?, ?)");
		$stmt->bind_param("sss", $id, $userName, $userEmail);
		$stmt->execute();
		$stmt->close();
	}
	
	/*
	Gets the information about user stored in the database.
	
	parameters:
	$id The id of the user.
	
	return:
	An associative array from which different types of information can be extracted. 
	['email', 'address', 'city', 'biography', 'county_id'].
	*/
	function getUserInfo($id) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$stmt = $link->prepare("SELECT email, name, address, city, biography, county_id  FROM user WHERE user_id=?");
		$stmt->bind_param("s", $id);
		$stmt->execute();
		$stmt->bind_result($email, $name, $address, $city, $biography, $county_id);
		$stmt->fetch();
		$row = array("email"=>$email, "name"=>$name, "address"=>$address, "city"=>$city, "biography"=>$biography, "county_id"=>$county_id);
		return $row;
	}
	
	/*
	Gets user name from the ID
	
	parameters:
	$id The ID of the user.
	
	return:
	The user name.
	*/
	function getUserName($id) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$stmt = $link->prepare("SELECT name FROM user WHERE user_id=?");
		$stmt->bind_param("s", $id);
		$stmt->execute();
		$stmt->bind_result($name);
		$stmt->fetch();
		return $name;
	}
	
	/*
	Updates user information with address, city, biography and county.
	
	parameters:
	$user_id The ID of the user that is to be updated.
	$address The address of the user.
	$city The city where the user lives.
	$bio The biography typed in by the user.
	$county_id The id of the county where the user lives.
	*/
	function setUserInfo($user_id, $address, $city, $bio, $county_id) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$stmt = $link->prepare("UPDATE user SET address=?, city=?, biography=?, county_id=? WHERE user_id=?");
		$stmt->bind_param("sssss", $address, $city, $bio, $county_id, $user_id);
		$stmt->execute();
	}
	
	/*Gets the information about the residence of the user (county)
	
	parameters:
	$id The ID of the user.
	
	return:
	The ID of the county where the user lives.
	*/
	function getCounty($id) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$sql = "SELECT county_id FROM user WHERE user_id = '$id'";
		$result = mysqli_query($link, $sql);
		
		$db['result'] = $result;
		$db['link'] = $link;
		
		return $db['result'];
	}
	
	/*
	Gets locations based on rating and county. Used to filter the locations showed on the map.
	
	parameters:
	$county_id Only locations in the county with this ID will be returned. A county ID of 0 will return locations from all counties.
	$rating The minimum average rating allowed on the returned locations.
	
	return:
	An array containing associative arrays for each location. 
	Each associative array contains the name, latitude, longitude, rating and description of the location.
	['name', 'lat', 'lng', 'rating', 'description'].
	*/
	function getLocationRating($county_id, $rating) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$rows = array();
		if ($county_id == 0) {
			$stmt = $link->prepare("SELECT name, lat, lng, rating, description FROM location WHERE rating>=?");
			$stmt->bind_param("i", $rating);
			$stmt->execute();
			$stmt->bind_result($name, $lat, $lng, $rating, $description);
			while($stmt->fetch()) {
				$row = array("name"=>$name, "lat"=>$lat, "lng"=>$lng, "rating"=>$rating, "description"=>$description);
				$rows[] = $row;
			}
		} else {
			$stmt = $link->prepare("SELECT name, lat, lng, rating, description FROM location WHERE county_id=? AND rating>=?");
			$stmt->bind_param("ii", $county_id, $rating);
			$stmt->execute();
			$stmt->bind_result($name, $lat, $lng, $rating, $description);
			while($stmt->fetch()) {
				$row = array("name"=>$name, "lat"=>$lat, "lng"=>$lng, "rating"=>$rating, "description"=>$description);
				$rows[] = $row;
			}
		}
		return $rows;
	}
	
	/*
	Gets all the counties.
	
	return:
	All counties in the database.
	*/
	function getCounties() {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$sql = "SELECT * FROM counties";
		$result = mysqli_query($link, $sql);
		
		$nr = mysqli_affected_rows($link);
		
		$db['result'] = $result;
		$db['link'] = $link;
		
		return $db;
	}
	
	/*
	Gets the center of the map. Used when changing the county.
	
	parameters: 
	$id The ID of the county.
	
	return
	An array containing the latitude and longitude of the county. 
	['lat', 'lng'].
	*/
	function getCenterMap($id) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$stmt = $link->prepare("SELECT county_id, lat, lng FROM counties WHERE county_id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt->bind_result($county_id, $lat, $lng);
		$stmt->fetch();
		$row = array("county_id"=>$county_id, "lat"=>$lat, "lng"=>$lng);
		return $row;
	}
	
	/*
	Adds a new location to the database
	
	parameters:
	$lat The latitude of the location.
	$lng The longitude of the location.
	$county_id The id of the county where the location is.
	$name Name of the location.
	$desc The description the user typed in when creating the location.
	$image Image uploaded by the user creating the location.
	$image_name Name of the image.
	*/
	function addLocation($lat, $lng, $rating, $county_id, $name, $desc, $image, $image_name) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		/*$stmt = $link->prepare("INSERT INTO location (lat, lng, rating, county_id, name, description, image, image_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
		$null = NULL;
		$stmt->bind_param("dddissbs", $lat, $lng, $rating, $county_id, $name, $desc, $null, $image_name);
		$stmt->send_long_data(6, $image);
		$stmt->execute();
		$stmt->close();*/
		$sql = "INSERT INTO location (lat, lng, rating, county_id, name, description, image, image_name) VALUES ('$lat', '$lng', '$rating', '$county_id', '$name', '$desc', '$image', '$image_name')";
		$result = mysqli_query($link, $sql);
	}
	
	
	/*
	Adds a new fishing trip to a location
	
	parameters:
	$user_id The ID of the user adding the fishing trip.
	$loc_id The ID of the location where the fishing trip is added.
	$comment Comment written about the fishing trip.
	$catch Weight of the catch.
	$weather The weather during the fishing trip.
	$rating Rating given to the location.
	*/
	
	function addFishingTrip($user_id, $loc_id, $comment, $catch, $weather, $rating) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$stmt = $link->prepare("INSERT INTO fishing_trip (user_id, loc_id, comment, catch, weather, rating) VALUES (?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("sisisi", $user_id, $loc_id, $comment, $catch, $weather, $rating);
		$stmt->execute();
		$stmt->close();
	}
	
	/*
	Gets the comments/reviews for a specified location
	
	parameters:
	$location The ID of the location.
	
	return:
	An array containing associative arrays for all reviews made about the location. 
	Each associative array contains the user ID, weather, catch, rating and comment for that fishing trip.
	['user\_id', 'weather', 'catch', 'rating', 'comment'].
	*/
	function getComments($location) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$stmt = $link->prepare("SELECT user_id, weather, catch, rating, comment FROM fishing_trip WHERE loc_id = ?");
		$stmt->bind_param("i", $location);
		$stmt->execute();
		$stmt->bind_result($user_id, $weather, $catch, $rating, $comment);
		$rows = array();
		while($stmt->fetch()) {
			$row = array("user_id"=>$user_id, "weather"=>$weather, "catch"=>$catch, "rating"=>$rating, "comment"=>$comment);
			$rows[] = $row;
		}
		return $rows;
	}
	
	/*
	Gets the BLOB image for a location.
	
	parameter:
	$loc Name of the location.
	
	return:
	The image stored at this location.
	*/
	function getImage($loc) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");

		$stmt = $link->prepare("SELECT image FROM location WHERE name = ?");
		$stmt->bind_param("s", $loc);
		$stmt->execute();
		$stmt->bind_result($image);
		$stmt->fetch();
		return $image;
	}
	
	/*
	Gets Location ID
	
	parameter:
	$loc Name of the location
	
	return:
	The location ID corresponding to the location with name $loc.
	*/
	function getLocationID($loc) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$stmt = $link->prepare("SELECT id FROM location WHERE name = ?");
		$stmt->bind_param("s", $loc);
		$stmt->execute();
		$stmt->bind_result($id);
		$stmt->fetch();
		return $id;
	}
	
	/*
	Gets the position for a location
	
	parameter:
	$lat The location's latitude.
	$lng The location's longitude.
	
	return:
	The name of the location.
	*/
	function getLocationLatLng($lat, $lng) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$stmt = $link->prepare("SELECT name FROM location WHERE lat=? AND lng=?");
		$stmt->bind_param("ss", $lat, $lng);
		$stmt->execute();
		$stmt->bind_result($name);
		$stmt->fetch();
		return $name;
		/*$sql = "SELECT name FROM location WHERE lat='$lat' AND lng='$lng'";
		$result = mysqli_query($link, $sql);
		$db['result'] = $result;
		$db['link'] = $link;
		$array = mysqli_fetch_array($db['result']);
		$name = $array['name'];*/

	}
	
	/*
	Recalculates the average grade for a location and updates it.
	
	parameter:
	$loc_id The ID of the location where the rating should be updated.
	*/
	function updateGrade($loc_id) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		
		$sql = "SELECT rating FROM fishing_trip WHERE loc_id = '$loc_id'";
		
		$result = mysqli_query($link, $sql);
		
		$nr = mysqli_affected_rows($link);
		$db['result'] = $result;
		$db['link'] = $link;
		
		$sum = 0;
		
		while ($array = mysqli_fetch_array($db['result'])) {
			$this_grade = $array['rating'];
			$sum = $sum + $this_grade;
		}
		
		$sum = $sum / $nr;
		
		$sql = "UPDATE location SET rating='$sum' WHERE id='$loc_id'";
		$result = mysqli_query($link, $sql);
	}
?>