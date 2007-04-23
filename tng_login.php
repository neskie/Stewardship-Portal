<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	tng_login.php

desc:	web page for logging in into the tng portal
---------------------------------------------------------------*/

// includes
// include file which has the php
// code associated with this page
include('tng_login_code.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link href="style.css" rel="stylesheet" type="text/css" />
	<title>TNG Portal Login</title>
</head>
<body>
	<!--<div id="container"> -->
		<div id="leftcol">
				Much effort has been made to ensure that 
			the layouts in the BlueRobot Layout Reservoir appear 
			as intended in CSS2 compliant browsers. The content 
			should be viewable, though unstyled, in other web browsers. 
			If you encounter a problem that is not listed as a known 
			issue, I am most likely not aware of it. Your help will 
			benefit the other five or six people who visit this site. 
		</div>
		<div id="content">
			<form id="tng_login_form" name="tng_login_form" method="post" action="tng_login.php">
				<h2> Login </h2>
				<p> To proceed further, please provide your username 
					and password.
				</p>	
			
				<label style="width:100px;"> User Name </label>
				<input type="text" name="uname" size="50"/>
				<br/>
				<label style="width:100px;"> Password </label>
				<input type="password" name="passwd" size="50" align="right"/>
				<br/>
			
				<input type="submit" value="Login" class="button"/><br/><br/>
			</form>
		</div>
		<div id="rightcol"> 
			Much effort has been made to ensure that 
		the layouts in the BlueRobot Layout Reservoir appear 
		as intended in CSS2 compliant browsers. The content 
		should be viewable, though unstyled, in other web browsers. 
		If you encounter a problem that is not listed as a known 
		issue, I am most likely not aware of it. Your help will 
		benefit the other five or six people who visit this site.  
		</div> 
	<!-- </div> -->
</body>
</html>
