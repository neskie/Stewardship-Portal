<?php
/*---------------------------------------------------------------
author:	alim karim
date:	May 14, 2007
file:	tng_add_edit_user.php

desc:	webpage to add users or reset passwords for
		existing users
---------------------------------------------------------------*/
header('Pragma: no-cache'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style.css" rel="stylesheet" type="text/css" />
<title> User Management</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_add_edit_user.js"> </script>
<script language="javascript">
// the first time around, we should display
// all users. 
ajax_get_users();
</script>
<!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.thrColHybHdr #sidebar1, .thrColHybHdr #sidebar2 { padding-top: 30px; }
.thrColHybHdr #mainContent { zoom: 1; padding-top: 15px; }
/* the above proprietary zoom property gives IE the hasLayout it needs to avoid several bugs */
</style>
<![endif]-->
</head>
<body class="thrColHybHdr">

<div id="container">
  <div id="header">
    <?php include_once('top_div.html'); ?>
  </div>
  <!-- end #header -->
  
  <div id="sidebar1">
    <?php include_once('tng_links_post_login.php');?>
  </div>  
  <!-- end #sidebar1 -->
  
  <div id="sidebar2">
    <p><span class="subHeader">Portal Access</span><br /></p>
    <p class="smallText">
		A user account is needed to log into the Stewardship Portal. 
        To acquire a username and password, please send an email 
        to:<a href="mailto:tsdgis@tsilqotin.ca">Portal Administrator</a></p>
			
    <p><span class="subHeader">TITLE HERE</span><br />
		Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam. </p>

	<p><span class="subHeader">TITLE HERE</span><br />
		Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam. </p>
        
  </div>
  <!-- end #sidebar2 -->

  <div id="mainContent">
	<form id="tng_add_edit_users" 
			name="tng_add_edit_users" 
			method="post" 
			enctype="multipart/form-data">
		<h1 class="pageName"> User Management </h1>
		<p class="bodyText">
			To add a user to the system, enter the desired user name
			for the new user and the password and click the 
			"Add User" button.
		</p>
		<p class="bodyText"> 
			To reset the password for an existing user, select the
			user from the list below and click the "Reset Password"
			button. Enter the new password in the password field
			and click the "Save" button to save changes.
		</p>
		<p class="bodyText">
			<label style="width:100px" for="uname"> User Name: </label>
			<input type="text" id="uname" name="uname" size="45"/>
			<br/>
			<label style="width:100px" for="password"> Password: </label>
			<input type="password" id="password" name="password" size="45"/>
			<br/>
			<br/>
			<label style="width:100px"> First Name: </label>
			<input type="text" id="fname" name="fname" size="45"/>
			<br/>
			<label style="width:100px"> Last Name: </label>
			<input type="text" id="lname" name="lname" size="45"/>
			<br/>
			<label style="width:100px"> Email Address: </label>
			<input type="text" id="email" name="email" size="45"/>
		</p>
		<input type="button" 
				class="button" 
				id="button1" 
				value="Add User" 
				onClick="javascript: ajax_add_edit_user();"/>
		<br/>
		<br/>
		<p class="bodyText">	
			<label style="width:100px"> Users: </label>
			<select id="user_list" name="user_list" style="width:250px;" size="5">
					<!-- this will be auto populated by javascript/ajax -->
			</select>
			<br/>
			<label style="width:100px"></label>
			
			<input type="button" 
					class="button" 
					value="Edit User" 
					onClick="javascript: ajax_update_user(); "/>
			<br/>
			
		</p>
	</form>
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