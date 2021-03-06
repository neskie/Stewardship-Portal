<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	tng_submit_form.php

desc:	webpage to submit a dynamically generated form
---------------------------------------------------------------*/
session_start();
include('classes/class_form.php');
include('classes/class_login.php');
// global variables on this page
$login;
$form;
///
/// main method
/// only proceed if login object
/// is valid
///
if(!isset($_SESSION['obj_login']) || !isset($_SESSION['obj_form'])){
	echo "login session variable / form object not set";
	header("Location: tng_login.php");
}else{
	global $login;
	global $form;
	// retrieve login and form objects
	$login = $_SESSION['obj_login'];
	$form = $_SESSION['obj_form'];
	// call function to fill array with
	// names and ids of forms in the db
	fetch_form_list($login->uid);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
</head>
<body>
	<form id="tng_display_form" method="post" action="tng_submit_form.php">
	<?php
		// print out the html generated by the stylesheet
		// from the xml
		echo $generated_form_html;
	?>
	<input type="submit" value="Submit" />
	</form>
</body>
</html>
