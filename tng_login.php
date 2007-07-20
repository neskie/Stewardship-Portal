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
<body class="thrColHybHdr">

<div id="container">
  <div id="header">
    <?php include_once('top_div.html'); ?>
  </div>
  <!-- end #header -->
  
  <div id="sidebar1">
    <?php include_once('links_no_login.html');?>
  </div>  
  <!-- end #sidebar1 -->
  
  <div id="sidebar2">
    <p><span class="subHeader">Portal Access</span><br /></p>
    <p class="smallText">
		A user account is needed to log into the Stewardship Portal. 
        To acquire a username and password, please send an email 
        to:<a href="mailto:tsdgis@tsilqotin.ca">Portal Administrator</a></p>        
  </div>
  <!-- end #sidebar2 -->

  <div id="mainContent">
    <h1 class="pageName"> Stewardship Portal Login </h1>
		<form id="tng_login_form" name="tng_login_form" method="post" action="tng_login.php">
				<p class="bodyText"> To proceed further, please provide your username 
					and password.
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
	</p>
   
  </div>
	<!-- end #mainContent -->
	
<!-- This clearing element should immediately follow 
   the #mainContent div in order to force the 
   #container div to contain all child floats -->
  <br class="clearfloat" />
   
	<div id="footer">
    	<p>Footer</p>
    </div>
  	<!-- end #footer -->
  
	</div>
<!-- end #container -->
</body>
	
</html>
