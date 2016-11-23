<?php
	error_reporting(E_ALL);	
	
	session_start();
	
	include 'func_lib.php';
	
	if((!isset($_SESSION['user'])) || ($_SESSION['user'] == "")) {
		header('Location: index.php');
		exit();
	}
	
	if(isset($_POST['logout'])) {
		unset($_SESSION['user']);
		unset($_SESSION['realName']);
		unset($_SESSION['picUrl']);
		unset($_SESSION['email']);
		header('Location: index.php');
		exit();
	}
	
	$userID = $_SESSION['user'];
	$userPic = $_SESSION['picUrl'];
	$userEmail = $_SESSION['email'];
	$userName = $_SESSION['realName'];
?>


<!DOCTYPE html>
<html>
<head>
	<title>Fish Finder</title>
	<link rel=stylesheet href=style.css> 
	<link rel="icon" type="image/png" href="fish_icon.ico?v=2">
</head>
<body>
	<div class="infoContainer">
		<div class="profilePic">
			<img src=<?=$userPic?> height="30" width="30">
		</div>
		<div class="profileInfo">
			<?=$userName ?> <br>(<?=$userEmail?>)
		</div>
		<div class="profileBut">
		<form method = post>
			<button type="submit" style="width:100px" name="logout">Sign out</button>
		</form>
		</div>
		<div class="profileBut">
			<button type="submit" onclick="document.getElementById('id01').style.display='block'" style="width:100px" name="profile">Profile</button>
		</div>
	</div>
	
	<h3>Fish Map</h3>
	
	<div>
		<form id="countyForm" name="countyForm" method=post>
		<select onchange="initMap()" id="county">
			<option selected="selected" value="southern_norrland">Södra Norrland</option>
			<option value="northern_norrland">Norra Norrland</option>
			<option value="svealand">Svealand</option>
			<option value="gotaland">Götaland</option>
		</select>
		<select onchange="initMap()" id="filterLoc">
			<option selected="selected" value="allLoc">All locations</option>
			<option value="myLoc">My locations</option>
		</select>
		<select onchange="initMap()" id="filterGrade">
			<option selected="selected" value="excellent">Excellent (5)</option>
			<option value="veryGood">Very Good or higher (4+)</option>
			<option value="good">Good or higher (3+)</option>
			<option value="bad">Bad or higher (2+)</option>
			<option value="veryBad">Very Bad or higher (1+)</option>
		</select>
		</form>
	</div>
	
	
    <div id="map"></div>
	
	<button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Login</button>
	<div id="id01" class="modal">
	
	  <form class="modal-content animate">
		<div class="imgcontainer">
		  <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
		  <img src="user_icon.png" style="width:100px; height:100px;" alt="Avatar" class="avatar">
		</div>

		<div class="container">
			<label><b>Name</b></label>
			<input type="text" placeholder="Enter Username" name="change_uname" disabled value=<?=$userName ?>>

			<label><b>E-mail</b></label>
			<input type="text" placeholder="Enter E-mail" name="change_email" disabled value=<?=$userEmail ?>>
			
			<label><b>Address</b></label>
			<input type="text" placeholder="Enter Address" name="change_address">
			
			<label><b>City</b></label>
			<input type="text" placeholder="Enter City" name="change_city">
			
			<label><b>Biography</b></label><p>
			<textarea placeholder="Enter your Biography" name="change_bio" rows="4" cols="100"></textarea>
			
			<button type="submit">Save</button>
		</div>

		<div class="container" style="background-color:#f1f1f1">
		  <button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
		</div>
	  </form>
	</div>

	<script>
	// Get the modal
	var modal = document.getElementById('id01');

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
		if (event.target == modal) {
			modal.style.display = "none";
		}
	}
	</script>
	
	
    <script>
      function initMap() {
		var uluru = {lat: 59.334591, lng: 18.063240};
		var e = document.countyForm.county;
		var strUser = e.options[e.selectedIndex].value;
		if(strUser=="northern_norrland") {
			uluru = {lat: 66.587135, lng: 19.790184};
		}
		if(strUser=="southern_norrland") {
			uluru = {lat: 64.386692, lng: 17.206839};
		}
		if(strUser=="svealand") {
			uluru = {lat: 60.099691, lng: 15.033009};
		}
		if(strUser=="gotaland") {
			uluru = {lat: 58.285508, lng: 14.121053};
		}
			
        
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 6,
          center: uluru
        });
        var marker = new google.maps.Marker({
          position: uluru,
          map: map
        });
      }
    </script>
	
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAquCRzK53TvC2xsUdMM41A12eWiLfLS4k&callback=initMap">
	</script>
	
	
	
</body>
</html>