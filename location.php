<?php
	error_reporting(E_ALL);	
	
	session_start();
	
	include 'func_lib.php';
	
	//Check if the crucial session variables are set. If not, go back to the home page.
	if((!isset($_SESSION['user'])) || ($_SESSION['user'] == "") || (!isset($_SESSION['chosen_location']))) {
		session_unset();
		header('Location: index.php');
		exit();
	}
	else {
		$user = $_SESSION['user'];
		$this_location = $_SESSION['chosen_location'];
	}
	
	//If user cancels the fishing trip
	if(isset($_POST['cancelTrip']) || isset($_POST['goBack'])) {
		unset($_SESSION['chosen_location']);
		header('Location: mylocations.php');
		exit();
	}
	
	//Adds the fishing trip to the database if user presses the button
	if(isset($_POST['addTrip'])) {
		$location_id = getLocationID($_SESSION['chosen_location']);
		addFishingTrip($user, $location_id, $_POST['trip_comment'], $_POST['trip_weight'], $_POST['trip_weather'], $_POST['trip_grade']);
		updateGrade($location_id);
	}
	
	
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset=utf-8>
	<title>Fish Finder</title>
	<link rel=stylesheet href=style.css>
	<link rel="icon" type="image/png" href="fish_icon.ico?v=2">
</head>
<body class="home">
	
	<!--Logo-->
	<div class="logo" align="center">
	<img src="logo.png" class="log_img" alt="Logo">
	</div>
	
	<!--New fishing trip div-->
	<div class=container style="width: 80%; margin-left: auto; margin-right: auto; border: 2px solid darkgreen; background-color: rgba(255,255,255,0.6); margin-top: 20px;">
		<h3>Add a fishing trip at <?echo $this_location;?></h3>
			<div align=center>
				<img src="getImage.php" width="480" height="270" />
			</div>
			<form method=post>
			<label><b>Weather</b></label><br>
			<select name="trip_weather">
				<option value="" disabled selected>Weather</option>
				<option value=Sunny>Sunny</option>
				<option value=Cloudy>Cloudy</option>
				<option value=Foggy>Foggy</option>
				<option value=Rainy>Rainy</option>
			</select>
			<p><label><b>Total fish weight</b></label>
			<input type="text" placeholder="Add total wight of the fish" name="trip_weight">
			<label><b>Comment</b></label>
			<textarea placeholder="Add your comment" name="trip_comment" rows="4" cols="40"></textarea>
			<label><b>Overall grade</b></label>
			<select name="trip_grade">
				<option value="" disabled selected>Choose grade</option>
				<option value=5>5 (Excellent)</option>
				<option value=4>4 (Very Good)</option>
				<option value=3>3 (Good)</option>
				<option value=2>2 (Bad)</option>
				<option value=1>1 (Very Bad)</option>
			</select>
			<button type="submit" name="addTrip">Submit the trip</button>
			<button type="submit" name="cancelTrip">Cancel</button>
			</form>
	</div>
	
	<!--User reviews/comment box div-->
	<div class=container style="width: 80%; margin-left: auto; margin-right: auto; border: 2px solid darkgreen; background-color: rgba(255,255,255,0.7); margin-top: 20px;">
		<h3>User reviews</h3>
		<?php
			//Retrieve comments and reviews from database
			$rows = getComments(getLocationID($this_location));
			foreach ($rows as $array) {
				echo "<div class='comment'>";
				$com_user_id = $array['user_id'];
				$com_user = getUserName($com_user_id);
				$com_weather = $array['weather'];
				$com_catch = $array['catch'];
				$comment = $array['comment'];
				$grade = $array['rating'];
				echo "<p><font color='darkgreen'>User: $com_user | Weather: $com_weather | Catch amount: $com_catch kg | Grade: $grade</font>";
				echo "<p>$comment";
				echo "</div>";
			}
			
		?>
		<!--Button for going back to the map-->
		<form  align=center method=post>
			<button type="submit" name="goBack">Go back</button>
		</form>
	</div>
	
</body>