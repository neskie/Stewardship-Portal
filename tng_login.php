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
	<div id="container">
		<div id="leftcol">wefwef </div>
		<div id="content">
			<form id="tng_login_form" name="tng_login_form" method="post" action="tng_login.php">
				<br/>
				<p class="lbl_wide"> Please enter your username and password below </p>
				<br/>
				<br/>
				<label class="lbl_general"> User Name </label>
				<input type="text" name="uname" class="input_regular"/><br/>
			
				<label class="lbl_general"> Password </label>
				<input type="password" name="passwd"class="input_regular"/> <br><br>
			
				<input type="submit" value="Login" class="button"/><br/><br/>
			</form>
		</div>
		<div id="rightcol"> wefwef  </div> 
	</div>
</body>
</html>
