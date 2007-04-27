<?php
/*---------------------------------------------------------------
author:	alim karim
date:	April 24, 2007
file:	tng_manage_permissions.php

desc:	webpage to give the user an interface to 
 		manage permissions for various objects such
		as layers, forms, etc.
		
---------------------------------------------------------------*/
header('Pragma: no-cache'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

//include_once('tng_manage_permissions_code.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style.css" rel="stylesheet" type="text/css" />
<title>Manage Permissions</title>
<script src="tng_manage_permissions.js"> </script>
<script language="javascript">

// the first time around, we should display
// all users. this is done by sending
// a blank string to the ajax_search_uname function.
ajax_search_uname("");

</script>

</head>
<body>
		<div id="leftcol"> why is is that this didnt work before but it works just fine now ....</div>
		<div id="content">
			<form id="tng_select_layers" 
					name="tng_select_layers" 
					method="post" 
					enctype="multipart/form-data">
				<h2> Manage Permissions </h2>
				<p> To begin, click on a user name and select
					the object that you wish to grant or revoke
					permissions to.
				</p>
				<p> To search list of users by name, enter the 
					username in the field below.
				</p>
				<p>
				<label style="width:100px;" for="uname"> Name: </label>
				<input type="text"
						name="uname" 
						id="uname"
						size="45"
						onKeyUp="javascript: ajax_search_uname(this.value);"/> 
				</p>
				<p>
				<label style="width:100px;" for="user_list"> User List: </label>
				<select id="user_list" 
						name="user_list" 
						style="width: 250px;" 
						size="5" 
						onChange="javascript: ajax_populate_object();">
				<!-- this will be auto populated by javascript/ajax -->
				</select>
				</p>
				
				<p>
				<label style="width:100px;"> Object: </label>
				<select id="manageable_objects" name="manageable_objects"
						style="width:250px;" onChange="javascript: ajax_populate_object();">
					<option value="none"> </option>
					<option value="layer"> layers </option>
					<option value="form"> forms </option>
					<option value="group"> groups </option>
				</select>
				</p>
				
				<p>
					<label style="width:100px;"> Disallowed Objects: </label>
					<select id="obj_list_disallowed" name="obj_list_disallowed" style="width:250px;" size="5">
							<!-- this will be auto populated by javascript/ajax -->
					</select>
				</p>
				<p>
					<label style="width:100px;"> Toggle: </label> 
					<img src="images/down_arrow.gif" onClick="javascript: ajax_toggle_permission('grant')"/>
					<img src="images/up_arrow.gif" onClick="javascript: ajax_toggle_permission('revoke')"/>
				</p>
				<p>	
					<label style="width:100px" for="obj_list_allowed"> Allowed Objects: </label>
					<select id="obj_list_allowed" name="obj_list_allowed" style="width:250px;" size="5">
							<!-- this will be auto populated by javascript/ajax -->
					</select>
				</p>
				
				<br/>
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