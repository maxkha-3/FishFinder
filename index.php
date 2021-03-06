<?php
	error_reporting(E_ALL);	
	
	session_start();
	
	include 'func_lib.php';
	
	//When user logs in.
	if(isset($_POST['googleLogin'])) {
		//Check if Google ID is assigned
		if(isset($_POST['userID'])) {
			//Clear old session variables and create new ones
			session_unset();
			$_SESSION['user'] = $_POST['userID'];
			$_SESSION['realName'] = $_POST['userName'];
			$_SESSION['picUrl'] = $_POST['userPic'];
			$_SESSION['email'] = $_POST['userEmail'];
			//Geographical center of Sweden (map centering)
			$_SESSION['latCenter'] = 62.388;
			$_SESSION['lngCenter'] = 16.325;
			
			//If Google user signs in for the first time, then add to the database
			if(!userExistent($_POST['userID'])) {
				addNewUser($_POST['userID'], $_POST['userName'], $_POST['userEmail']);
			}
			
		}
	}
	
	//If user is already set, then go to location page without logging in
	if((isset($_SESSION['user'])) && ($_SESSION['user'] != "")) {
		header('Location: mylocations.php');
		exit();
	}
	
	if(isset($_POST['doc'])) {
		header('Location: documentation.pdf');
	}
	
	
?>
<!--Google login API-->
<script src="https://apis.google.com/js/platform.js" async defer></script>
<meta name="google-signin-client_id" content="210455189570-1o93jli9t2hv7cnm3dgqcit3tvgftvnk.apps.googleusercontent.com">

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
	
	
	<!--Login div-->
	<div class="login" align="center">
		<h3>Welcome to Fish Finder!</h3>
		<p>A web page for sharing the best fishing locations in Sweden.
		<p>We welcome all the fishing enthusiasts!
		<p>Join us for FREE now! <p> 
		
		<div class="g-signin2" data-onsuccess="onSignIn" align="center"></div>	
		
		<form id="sampleForm" name="sampleForm" method="post">
			<input name="userID" type="hidden" id="userID" value="">
			<input name="userName" type="hidden" id="userName" value="">
			<input name="userPic" type="hidden" id="userPic" value="">
			<input name="userEmail" type="hidden" id="userEmail" value="">
			<button type="submit" name="googleLogin" disabled>Continue with google</button>
			<button type="submit" name="doc">Documentation</button>
		</form>
		<a href="#" onclick="signOut();">Sign out</a>
	</div>


	<!--Google analytics-->
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

		ga('create', 'UA-86978734-1', 'auto');
		ga('send', 'pageview');
	</script>
	<!--Google login/logout-->
	<script>
		//On sign in, retrieve user information
		function onSignIn(googleUser) {
			var profile = googleUser.getBasicProfile();
			console.log('ID: ' + profile.getId());
			console.log('Name: ' + profile.getName());
			console.log('Image URL: ' + profile.getImageUrl());
			console.log('Email: ' + profile.getEmail());
			if(googleUser.isSignedIn()) {
				document.sampleForm.userID.value = profile.getId();
				document.sampleForm.userName.value = profile.getName();
				document.sampleForm.userPic.value = profile.getImageUrl();
				document.sampleForm.userEmail.value = profile.getEmail();
				document.sampleForm.googleLogin.disabled = false;
			}
		}
		//On sign out, clear the information about the user
		function signOut() {
			var auth2 = gapi.auth2.getAuthInstance();
			document.sampleForm.userID.value = "";
			document.sampleForm.userName.value = "";
			document.sampleForm.userPic.value = "";
			document.sampleForm.userEmail.value = "";
			auth2.signOut().then(function () {
				console.log('User signed out.');
			});
			document.sampleForm.googleLogin.disabled = true;
		}
	</script>

</body>
</html>
