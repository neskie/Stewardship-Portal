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
include_once('tng_login_code.php');?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link href="style-new.css" rel="stylesheet" type="text/css" />
	<title>TNG Portal Login</title>
</head>
<body>
	<div id="header"><?php include_once('top_div.html'); ?></div>
	<div id="container">	
		<div id="content" class="column">
			<h1 class="pageName"> Stewardship Portal Login </h1>
			<form id="tng_login_form" name="tng_login_form" method="post" action="tng_login.php">
				<p> To proceed further, please provide your username and password.
				<br/>
				<br/>
				<label style="width:100px;"> User Name </label>
				<input type="text" name="uname" size="50"/>
				<br/>
				<label style="width:100px;"> Password </label>
				<input type="password" name="passwd" size="50" align="right"/>
				<br/>
				<br/>
				<input type="submit" value="Login" class="button"/>
				</p>
			</form>
 			<br/>
 		</div>
		<div id="left" class="column">
			<?php include_once('links_no_login.html');?>
		</div>
		<div id="right" class="column">
			 <?php include_once('links_sidebar_alternate.html');?>
		</div>
	</div> <!-- end container -->
	<div id="footer">
			<?php include_once('links_footer.html');?>
	</div>
</body>
</html>
