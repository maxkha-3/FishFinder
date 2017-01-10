<?php
	//API for Fish Finder
	
	//See create_dbstructure.php for MySQL database details

	
	//Set up the connection with the server
	function setUpConnection() {
		$db_host = 'localhost';
		$db_user = 'afo1009';
		$db_password = 'ttju34lX';
		$db_database = 'afo1009_projekt';
		$link = mysqli_connect($db_host, $db_user, $db_password, $db_database);
		
		if (!$link) {
			echo "Error: Unable to connect to MySQL." . PHP_EOL;
			echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
			echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
			exit;
		}
		
		return $link;
	}
	
	//Checks is the user exists in the database
	function userExistent($googleID) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		
		$sql = "SELECT * FROM user WHERE user_id='$googleID'";
		$result = mysqli_query($link, $sql);
	
		$nr = mysqli_affected_rows($link);
		
		
		if ($nr == 0) {
			return false;
		}
		else {
			return true;
		}
	}
	
	//Adds a new user to the database
	function addNewUser($id, $userName, $userEmail) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$stmt = $link->prepare("INSERT INTO user (user_id, name, email) VALUES (?, ?, ?)");
		$stmt->bind_param("sss", $id, $userName, $userEmail);
		$stmt->execute();
		$stmt->close();
		/*$sql = "INSERT INTO user (user_id, name, email) VALUES ('$googleID', '$userName', '$userEmail')";
		$result = mysqli_query($link, $sql);*/
	}
	
	//Gets the information about user
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
		/*$result = mysqli_query($link, $sql);
	
		$nr = mysqli_affected_rows($link);
		
		$db['result'] = $result;
		$db['link'] = $link;
		
		return $db;*/
	}
	
	//Gets user name from the ID
	function getUserName($id) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$stmt = $link->prepare("SELECT name FROM user WHERE user_id=?");
		$stmt->bind_param("s", $id);
		$stmt->execute();
		$stmt->bind_result($name);
		$stmt->fetch();
		return $name;
		/*$sql = "SELECT * FROM user WHERE user_id='$googleID'";
		$result = mysqli_query($link, $sql);
	
		$nr = mysqli_affected_rows($link);
		
		$db['result'] = $result;
		$db['link'] = $link;
		
		$arr = mysqli_fetch_array($db['result']);
		$userName = $arr['name'];
		
		return $userName;*/
	}
	
	//Updates user information
	function setUserInfo($user_id, $address, $city, $bio, $county_id) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$stmt = $link->prepare("UPDATE user SET address=?, city=?, biography=?, county_id=? WHERE user_id=?");
		$stmt->bind_param("sssss", $address, $city, $bio, $county_id, $user_id);
		$stmt->execute();
		/*$sql = "UPDATE user SET address='$address', city='$city', biography='$bio', county_id='$county_id' WHERE user_id='$googleID'";
		$result = mysqli_query($link, $sql);*/
	}
	
	//Gets the information about the residence of the user (county)
	function getCounty($googleID) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$sql = "SELECT county_id FROM user WHERE user_id = '$google_id'";
		$result = mysqli_query($link, $sql);
		
		$db['result'] = $result;
		$db['link'] = $link;
		
		return $db['result'];
	}
	
	//Gets the average grade of the location
	function getLocationRating($county_id, $rating) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$rows = array();
		//$sql = "SELECT * FROM location WHERE county_id='$county_id' AND rating>='$rating'";
		if ($county_id == 0) {
			$stmt = $link->prepare("SELECT name, lat, lng, rating, description FROM location WHERE rating>=?");
			$stmt->bind_param("i", $rating);
			$stmt->execute();
			$stmt->bind_result($name, $lat, $lng, $rating, $description);
			while($stmt->fetch()) {
				$row = array("name"=>$name, "lat"=>$lat, "lng"=>$lng, "rating"=>$rating, "description"=>$description);
				$rows[] = $row;
			}
			//$sql = "SELECT * FROM location WHERE rating>='$rating'";
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
		/*$result = mysqli_query($link, $sql);
		$nr = mysqli_affected_rows($link);
		
		$db['result'] = $result;
		$db['link'] = $link;
		
		return $db;*/
	}
	
	//Gets all the counties
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
	
	//Gets the center of the map. Used when changing the county.
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
		/*$sql = "SELECT county_id, lat, lng FROM counties WHERE county_id = '$id'";
		$result = mysqli_query($link, $sql);
		
		$nr = mysqli_affected_rows($link);
		
		$db['result'] = $result;
		$db['link'] = $link;
		
		return $db;*/
	}
	
	//Adds a new location to the database
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
	
	
	//Adds a new fishing trip to a location
	function addFishingTrip($user_id, $loc_id, $comment, $catch, $weather, $rating) {
		$link = setUpConnection();
		mysqli_query($link, "SET NAMES 'utf8'");
		$stmt = $link->prepare("INSERT INTO fishing_trip (user_id, loc_id, comment, catch, weather, rating) VALUES (?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("sisisi", $user_id, $loc_id, $comment, $catch, $weather, $rating);
		$stmt->execute();
		$stmt->close();
		
		/*$sql = "INSERT INTO fishing_trip (user_id, loc_id, comment, catch, weather, rating) VALUES ('$googleID', '$loc_id', '$comment', '$catch', '$weather', '$rating')";
		$result = mysqli_query($link, $sql);*/
	}
	
	//Gets the comments/reviews for a specified location
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
	
	//Gets the BLOB image for a location
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
	
	//Gets Location ID
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
	
	//Gets the position for a location
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
	
	//Recalculates the average grade for a location and updates it
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