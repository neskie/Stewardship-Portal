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
// include necessary classes used by this page
// OR scripts that are included BEFORE 
// start_session() is called
include_once('classes/class_login.php');
include_once('tng_check_session.php');
// only allow access if the logged in user
// is an admin user
if($_SESSION['obj_login']->is_admin() == "false")
	echo "<META HTTP-EQUIV='Refresh' Content='0; URL=tng_login_successful.php'>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style-new.css" rel="stylesheet" type="text/css" />
<title> User Management</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_add_edit_user.js"> </script>

<!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.thrColHybHdr #sidebar1, .thrColHybHdr #sidebar2 { padding-top: 30px; }
.thrColHybHdr #mainContent { zoom: 1; padding-top: 15px; }
/* the above proprietary zoom property gives IE the hasLayout it needs to avoid several bugs */
</style>
<![endif]-->
</head>
<!-- issue call to get all users when the page loads -->
<body onLoad="ajax_get_users();">
	<div id="header">
   	<?php include_once('top_div.html'); ?>
	</div>
	<div id="container">
		<div id="content" class="column">
			<form id="tng_add_edit_users" 
				name="tng_add_edit_users" 
				method="post" 
				enctype="multipart/form-data">
				<h1 class="pageName"> User Management </h1>
				<p>
				To add a user to the system, complete all the fields, 
				toggle the Active button “ON”, and click the "Add User" button. 
				</p>
		      <p>
			      No email message is automatically sent to the user’s 
			      email address, so the administrator needs to contact the user 
			      and let him/her know what password has been assigned.  
			      A user cannot change his/her password without contacting the administrator. 
		      </p> 
				<p> 
					To reset the password for an existing user, edit an 
					email address or other user information, select the 
					user from the list below and click the “Edit User” button.
					Enter the new information in the appropriate field(s), and 
					click the "Save" button to save changes.  Note that their 
					password is not displayed, and leaving that field blank 
					will not over-write the existing password, so emails etc 
					can be updated without affecting that user’s ability to log on.  
				</p>
				
		      <p>
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
					<br/>
					<label style="width:100px"> Active: </label>
					<input type="checkbox" id="active" name="active"/>
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
		<div id="left" class="column">
   		<?php include_once('tng_links_post_login.php');?>
  		</div>  
  		<div id="right" class="column">
   		<?php include_once('links_sidebar2.html');?>
  		</div>
  	</div> <!-- end container --> 
 	<div id="footer">
 		<?php include_once('links_footer.html');?></div>
 	</div>
</body>
</html>