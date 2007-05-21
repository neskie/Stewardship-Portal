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
<title>Add/Edit Users</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_add_edit_user.js"> </script>
<script language="javascript">
// the first time around, we should display
// all users. 
ajax_get_users();
</script>

</head>
<body>
		<div id="leftcol"> why is is that this didnt work before but it works just fine now ....</div>
		<div id="content">
			<form id="tng_add_edit_users" 
					name="tng_add_edit_users" 
					method="post" 
					enctype="multipart/form-data">
				<h2> Add/Edit Users </h2>
				<p>
					To add a user to the system, enter the desired user name
					for the new user and the password and click the 
					"Add User" button.
				</p>
				<p>
					To reset the password for an existing user, select the
					user from the list below and click the "Reset Password"
					button. Enter the new password in the password field
					and click the "Save" button to save changes.
				</p>
				
				<label style="width:100px" for="uname"> User Name: </label>
				<input type="text" id="uname" name="uname" size="45"/>
				<br/>
				<label style="width:100px" for="password"> Password: </label>
				<input type="password" id="password" name="password" size="45"/>
				<input type="button" 
						class="button" 
						id="button1" 
						value="Add User" 
						onClick="javascript: ajax_add_edit_user();"/>
				<br/>
				<br/>
				<p>	
					<label style="width:100px"> Users: </label>
					<select id="user_list" name="user_list" style="width:250px;" size="5">
							<!-- this will be auto populated by javascript/ajax -->
					</select>
					<br/>
					<label style="width:100px"></label>
					
					<input type="button" 
							class="button" 
							value="Reset Password" 
							onClick="javascript: ajax_reset_passwd(); "/>
					<br/>
					
				</p>
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
</body>
</html>