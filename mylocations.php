<?php
	error_reporting(E_ALL);	
	
	session_start();
	
	include 'func_lib.php';
	
	//Check if there is a user, who is logged in.
	if((!isset($_SESSION['user'])) || ($_SESSION['user'] == "")) {
		session_unset();
		header('Location: index.php');
		exit();
	}
	else {
		//Retrieve user information
		$userID = $_SESSION['user'];
		$userPic = $_SESSION['picUrl'];
		$arr = getUserInfo($userID);
		$userEmail = $arr['email'];
		$userName = $arr['name'];
		$address = $arr['address'];
		$city = $arr['city'];
		$bio = $arr['biography'];
		$cnt = $arr['county_id'];
		$_SESSION['sql'] = "SELECT * FROM location";
		$_SESSION['region'] = 0;
		$_SESSION['fGrade'] = 1;
		$_SESSION['latCenter'] = 62.388;
		$_SESSION['lngCenter'] = 16.325;
	}
	
	//If user decides to log out
	if(isset($_POST['logout'])) {
		session_unset();
		header('Location: index.php');
		exit();
	}
	
	//If user updates the profile
	if(isset($_POST['updateProfile'])) {
		$county = $_POST['change_county'];
		$array = getCenterMap($county);
		setUserInfo($_SESSION['user'], $_POST['change_address'], $_POST['change_city'], $_POST['change_bio'], $county);
		header('Location: mylocations.php');
	}
	
	//Filters the locations
	if(isset($_POST['filterLocation'])) {
		$selected = $_POST['county'];
		$rating = $_POST['fGrade'];
		$_SESSION['region'] = $selected;
		$_SESSION['fGrade'] = $rating;
		//Changes map centering if user changes the county
		if ($selected == 0) {
			$_SESSION['latCenter'] = 62.388;
			$_SESSION['lngCenter'] = 16.325;
		}
		else {
			$array = getCenterMap($selected);
			$_SESSION['latCenter'] = $array['lat'];
			$_SESSION['lngCenter'] = $array['lng'];
		}
	}
	//Adds new location
	if(isset($_POST['addLocation'])) {
		$county = $_POST['new_county'];
		$array = getCenterMap($county);
		$county_id = $array['county_id'];
		
		//Check image file format
		$allowed =  array('gif','png' ,'jpg', 'jpeg');
		//Convert image
		$image = addslashes(file_get_contents($_FILES['loc_image']['tmp_name'])); //SQL Injection defence!
		$image_name = addslashes($_FILES['loc_image']['name']);
		$ext = pathinfo($image_name, PATHINFO_EXTENSION);
		
		//Exit if file format is incorrect
		if(!in_array($ext, $allowed) ) {
			echo "<script type='text/javascript'>alert('Format error! Allowed formats are: .gif, .png, .jpg and .jpeg');</script>";
		}
		else {
			//Add to DB via the API
			addLocation(floatval($_POST['lat']), floatval($_POST['lng']), $_POST['trip_grade'], $_POST['new_county'], $_POST['name'], $_POST['new_desc'], $image, $image_name);
			
			//Add first review
			$location_id = getLocationID(getLocationLatLng(floatval($_POST['lat']), floatval($_POST['lng'])));
			addFishingTrip($userID, $location_id, $_POST['trip_comment'], $_POST['trip_weight'], $_POST['trip_weather'], $_POST['trip_grade']);
			updateGrade($location_id);
		}
	}
	
	//Go to specified location
	if (isset($_POST['choose'])) {
		$lat = floatval($_POST['locLat']);
		$lng = floatval($_POST['locLng']);
		$_SESSION['chosen_location'] = getLocationLatLng($lat, $lng);
		header('Location: location.php');
		exit();
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
<body>
	<!--Logo-->
	<div class="logo" align="center">
	<img src="logo.png" class="log_img" alt="Logo">
	</div>
	<!--User menu-->
	<nav class = "menu_user">
		<div class="profilePic">
			<img src=<?=$userPic?> height="30" width="30">
		</div>
		<div class="profileInfo">
			<?=$userName ?> 
			<br>(<?=$userEmail?>)
			
		</div>
		<div class="profileBut">
			<img align="right" src="triangle.png" height="12" width="25">
		</div>
		<ul>
			<button type="submit" onclick="document.getElementById('id01').style.display='block'" style="width:100px" name="profile">Profile</button>
			<form method = post>
				<button type="submit" style="width:100px" name="logout">Sign out</button>
			</form>
		</ul>
	</nav>

	
	<!--Filtering options-->
	<div class="selection_options">
		<form id="countyForm" name="countyForm" method=post>
		<select name="county">
			<option value="" disabled>Select your region</option>
			<option value=0>Whole Sweden</option>
		<?php
			//Get all counties
			$db = getCounties();
			while ($array = mysqli_fetch_array($db['result'])) {
				$id = $array['county_id'];
				$name = $array['name'];
				if ($id == $_SESSION['region']) {
					echo "<option selected value='$id' id='county$id'>$name</option>";
				}
				else {
					echo "<option value='$id' id='county$id'>$name</option>";
				}
				
			}
		?>
		</select>
		<!--Grades-->
		<select id="filterGrade" name="fGrade">
			<option value="" disabled>Filter the grades</option>
			<?php

				$text = array('All', 'All', 'Bad or higher (2+)', 'Good or higher (3+)', 'Very Good or higher (4+)', 'Excellent (5)');
				$grade = $_SESSION['fGrade'];
				for ($i = 5; $i > 0; $i--) {
					if ($grade == $i) {
						echo "<option selected='selected' value=$i>$text[$i]</option>";
					} else {
						echo "<option value=$i>$text[$i]</option>";
					}
				}
			?>

		</select>
		<input type="submit" name="filterLocation" value="Filter"></input>
		</form>
	</div>
	
	<!--Map-->
    <div align="center" id="map"></div>
	
	<div class="help_click">
		<h4 align="center">Map tips</h4>
		<label><b>To add a new location, right click on a place on the map!</b></label><p>
		<label><b>Hover over a marker to see the detailed information about the fishing location.</b></label><p>
		<label><b>Click on a marker to visit the fishing location.</b></label>
	</div>
	
	<!--User info pop-up-->
	<div id="id01" class="modal">
	
	  <form method=post class="modal-content animate">
		<div class="imgcontainer">
		  <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
		  <img src="user_icon.png" style="width:100px; height:100px;" alt="Avatar" class="avatar">
		</div>

		<div class="container">
			<label><b>Name</b></label>
			<input type="text" placeholder="Enter Username" name="change_uname" disabled value=<?="'$userName'"?>>

			<label><b>E-mail</b></label>
			<input type="text" placeholder="Enter E-mail" name="change_email" disabled value=<?="'$userEmail'"?>>
			
			<label><b>Address</b></label>
			<input type="text" placeholder="Enter Address" name="change_address" value=<?="'$address'"?>>
			
			<label><b>City</b></label>
			<input type="text" placeholder="Enter City" name="change_city" value=<?="'$city'"?>>
			
			<label><b>County</b></label><p>
			<select name="change_county">
			<?php
				$db = getCounties();
				while ($array = mysqli_fetch_array($db['result'])) {
					$id = $array['county_id'];
					$name = $array['name'];
					if ($id == $cnt) {
						echo "<option selected value='$id' id='county$id'>$name</option>";
					}
					else {
						echo "<option value='$id' id='county$id'>$name</option>";
					}
					
				}
			?>
			</select><p>
			
			<label><b>Biography</b></label><p>
			<textarea placeholder="Enter your Biography" name="change_bio" rows="4" cols="100"><?=$bio?></textarea>
			
			<button type="submit" name="updateProfile">Save</button>
		</div>
		
		<div class="container" style="background-color:#f1f1f1">
		  <button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
		</div>
	  </form>
	</div>
	
	<!--Add location pop-up-->
	<div id="id02" class="modal">
		<form method=post class="modal-content animate" enctype="multipart/form-data">
		<div class="container">
			<h3 align="center">Add new location</h3>
			<label><b>Latitude</b></label>
			<input type="text" required placeholder="Rightclick on the point on the map" id="new_lat" name="lat">
			<label><b>Longtitide</b></label>
			<input type="text" required placeholder="Rightclick on the point on the map" id="new_long" name="lng">
			<label><b>Name</b></label>
			<input type="text" required placeholder="Enter name of the fishing spot" id="new_name" name="name">
			<label><b>County</b></label>
			<select name="new_county">
			<option value="" disabled selected>Select region</option>
			<?php
				$db = getCounties();
				while ($array = mysqli_fetch_array($db['result'])) {
					$id = $array['county_id'];
					$name = $array['name'];
					echo "<option value='$id' id='county$id'>$name</option>";	
				}
			?>
			</select>
			<label><b>Description</b></label><p>
			<textarea placeholder="Fishing Location Description" required name="new_desc" rows="4" cols="40"></textarea>
			<label><b>Image (Allowed formats are: .gif, .png, .jpg and .jpeg)</label><p>
			<input type="file" name="loc_image" id="loc_image">
			<hr>
			<h3 align="center">Review</h3>
			<label><b>Weather</b></label><br>
			<select name="trip_weather">
				<option value="" disabled selected>Weather</option>
				<option value=Sunny>Sunny</option>
				<option value=Cloudy>Cloudy</option>
				<option value=Foggy>Foggy</option>
				<option value=Rainy>Rainy</option>
			</select>
			<p><label><b>Total fish weight</b></label>
			<input type="text" placeholder="Add total wight of the fish" required name="trip_weight">
			<label><b>Comment</b></label>
			<textarea placeholder="Add your comment" name="trip_comment" required rows="4" cols="40"></textarea>
			<label><b>Overall grade</b></label>
			<select name="trip_grade">
				<option value="" disabled selected>Choose grade</option>
				<option value=5>5 (Excellent)</option>
				<option value=4>4 (Very Good)</option>
				<option value=3>3 (Good)</option>
				<option value=2>2 (Bad)</option>
				<option value=1>1 (Very Bad)</option>
			</select>
			<button type="button" onclick="document.getElementById('id02').style.display='none'" class="cancelbtn">Cancel</button>
			<button type="submit" name="addLocation">Add location to the map</button>
		</div>
		</form>
	</div>
	
	<!--Form for location selection-->
	<form id="locForm" name="locForm" method="post">
		<input name="locLat" type=hidden id="locLat" value="">
		<input name="locLng" type=hidden id="locLng" value="">
		<button type="submit" hidden="hidden" id="choose" name="choose">GO</button>
	</form>
	
	<script>
	// Get the user detail popup
	var userinfowindow = document.getElementById('id01');
	
	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
		if (event.target == userinfowindow) {
			userinfowindow.style.display = "none";
		}
	}
	
	// Get the new location popup
	var newlocationinfo = document.getElementById('id02');
	
	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
		if (event.target == newlocationinfo) {
			newlocationinfo.style.display = "none";
		}
	}
	</script>
	
	<script>
		//Click variable declaration
		var click_lat = 0;
		var click_lng = 0;
		var click_mark = null;
		
		//Initialize the map
        function initMap() {
		
		var lat = <?php echo json_encode($_SESSION['latCenter'], JSON_HEX_TAG); ?>;
		var lng = <?php echo json_encode($_SESSION['lngCenter'], JSON_HEX_TAG); ?>;
		var zm = 8;
		if ((lng == 16.325) && (lat == 62.388)) {
			zm = 6;
		}
		
		
		
        var map = new google.maps.Map(document.getElementById('map'), {
          center: new google.maps.LatLng(lat, lng),
		  zoom: zm
          
        });
		google.maps.event.addListener(map, "rightclick", function(event) {
			document.getElementById('id02').style.display='block';
			if (click_mark != null) {
				click_mark.setMap(null);
			}
			click_lat = event.latLng.lat();
			click_lng = event.latLng.lng();
			document.getElementById('new_lat').value = click_lat.toFixed(4);
			document.getElementById('new_long').value = click_lng.toFixed(4);
			var myLatLng = {lat: click_lat, lng: click_lng};
			// populate yor box/field with lat, lng
			click_mark = new google.maps.Marker({
				position: myLatLng,
				map: map,
				title: 'Hello World!'
			});
		});
        var infoWindow = new google.maps.InfoWindow;
			//Get marker data from the XML file
			downloadUrl('locationxml.php', function(data) {
            var xml = data.responseXML;
            var markers = xml.documentElement.getElementsByTagName('marker');
			//Parse the XML file and construct the markers (fill them with database information)
            Array.prototype.forEach.call(markers, function(markerElem) {
              var name = markerElem.getAttribute('name');
			  var rating = markerElem.getAttribute('rating');
			  var desc = markerElem.getAttribute('desc');
              var point = new google.maps.LatLng(
                  parseFloat(markerElem.getAttribute('lat')),
                  parseFloat(markerElem.getAttribute('lng')));
				
			//Fill infowindow content
              var infowincontent = document.createElement('div');
              var boldTitle = document.createElement('strong');
              boldTitle.textContent = name;
              infowincontent.appendChild(boldTitle);
              infowincontent.appendChild(document.createElement('br'));

              var ratingLabel = document.createElement('text');
              ratingLabel.textContent = "Avg. rating: " + rating
              infowincontent.appendChild(ratingLabel);
			  infowincontent.appendChild(document.createElement('p'));
			  
			  var describtionText = document.createElement('text');
			  describtionText.textContent = desc
              infowincontent.appendChild(describtionText);
			  infowincontent.appendChild(document.createElement('br'));
			  

              var marker = new google.maps.Marker({
                map: map,
                position: point,
              });
			  //Onclick retrieve marker position
			  marker.addListener('click', function() {
				document.locForm.locLat.value = marker.getPosition().lat();
				document.locForm.locLng.value = marker.getPosition().lng();
				document.locForm.choose.click();
              });
			  //Onhover display information
              marker.addListener('mouseover', function() {
                infoWindow.setContent(infowincontent);
                infoWindow.open(map, marker);
              });
			  //Onhoverout stop displaying information
			  marker.addListener('mouseout', function() {
					infoWindow.close();
			  });
            });
          });
        }

		
		//XML request
      function downloadUrl(url, callback) {
        var request = window.ActiveXObject ?
            new ActiveXObject('Microsoft.XMLHTTP') :
            new XMLHttpRequest;

        request.onreadystatechange = function() {
          if (request.readyState == 4) {
            request.onreadystatechange = doNothing;
            callback(request, request.status);
          }
        };

        request.open('GET', url, true);
        request.send(null);
      }

      function doNothing() {}
    </script>
	<!--Maps API key-->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAquCRzK53TvC2xsUdMM41A12eWiLfLS4k&callback=initMap">
	</script>
	
	
	
</body>
</html>