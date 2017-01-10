<?php
	include 'func_lib.php';
	
	session_start();
	
	/*
		Used for displaying the location image
	*/
		
	//Get the image
	$img = getImage($_SESSION['chosen_location']);

	//Display the image
	echo $img;
?>