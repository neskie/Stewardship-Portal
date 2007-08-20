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
// include necessary classes used by this page
// OR scripts that are included BEFORE 
// start_session() is called
include_once('classes/class_login.php');
// include script to check for 
// login session variable
include_once('tng_check_session.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style.css" rel="stylesheet" type="text/css" />
<title>Manage Permissions</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_manage_permissions.js"> </script>
<!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.thrColHybHdr #sidebar1, .thrColHybHdr #sidebar2 { padding-top: 30px; }
.thrColHybHdr #mainContent { zoom: 1; padding-top: 15px; }
/* the above proprietary zoom property gives IE the hasLayout it needs to avoid several bugs */
</style>
<![endif]-->
</head>
<!-- onLoad display all users. this is done by sending
	a blank string to the ajax_search_uname function.-->
<body class="thrColHybHdr" onLoad="ajax_search_uname('');">

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
	<form id="tng_select_layers" 
		name="tng_select_layers" 
		method="post" 
		enctype="multipart/form-data">
		<h1 class="pageName"> Manage Permissions </h1>
			<p class="bodyText"> To begin, click on a user name and select
				the object that you wish to grant or revoke
				permissions to.
			</p>
			<p class="bodyText"> To search list of users by name, enter the 
				username in the field below.
			</p>
			<p class="bodyText">
			<label style="width:100px;" for="uname"> Name: </label>
			<input type="text"
					name="uname" 
					id="uname"
					size="45"
					onKeyUp="javascript: ajax_search_uname(this.value);"/> 
			</p>
			<p class="bodyText">
			<label style="width:100px;" for="user_list"> User List: </label>
			<select id="user_list" 
					name="user_list" 
					style="width: 250px;" 
					size="5" 
					onChange="javascript: ajax_populate_object();">
			<!-- this will be auto populated by javascript/ajax -->
			</select>
			</p>
		
			<p class="bodyText">
			<label style="width:100px;"> Object: </label>
			<select id="manageable_objects" name="manageable_objects"
					style="width:250px;" onChange="javascript: ajax_populate_object();">
				<option value="none"> </option>
				<option value="layer"> layers </option>
				<option value="form"> forms </option>
				<option value="group"> groups </option>
			</select>
			</p>
		
			<p class="bodyText">
				<label style="width:100px;"> Disallowed Objects: </label>
				<select id="obj_list_disallowed" name="obj_list_disallowed" style="width:250px;" size="5">
						<!-- this will be auto populated by javascript/ajax -->
				</select>
			</p>
			<p class="bodyText">
				<label style="width:100px;"> Toggle: </label> 
				<img src="images/down_arrow.gif" onClick="javascript: ajax_toggle_permission('grant')"/>
				<img src="images/up_arrow.gif" onClick="javascript: ajax_toggle_permission('revoke')"/>
			</p>
			<p class="bodyText">	
				<label style="width:100px" for="obj_list_allowed"> Allowed Objects: </label>
				<select id="obj_list_allowed" name="obj_list_allowed" style="width:250px;" size="5">
						<!-- this will be auto populated by javascript/ajax -->
				</select>
			</p>
		
			<br/>
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