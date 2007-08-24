<?php
/*---------------------------------------------------------------
author:	alim karim
date:	July 12, 2007
file:	tng_login_successful.php

desc:	webpage that will be displayed upon
		successful login
---------------------------------------------------------------*/
header('Pragma: no-cache'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
// include necessary classes used by this page
// OR scripts that are included BEFORE 
// start_session() is called
include_once('classes/class_login.php');
include_once('tng_check_session.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="style.css" type="text/css" />
<title>Stewardship Portal Main Page</title>

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
     <?php include_once('links_sidebar2.html');?>
  </div>
  <!-- end #sidebar2 -->
  <div id="mainContent">
    <h1 class="pageName"> Welcome to The Stewardship Portal </h1>
    <p class="bodyText">
    	You have successfully logged in to The Stewardship Portal. The list
		below outlines the functions that are accessible from the menu on the left.
    </p>

	<p class="bodyText">
		<ul>
			<li class="subHeader"> Forms </li>
				<p class="bodyText">
					Select a Form to be filled for
					submitting various types of data.
					<br/>			
					For example, to initiate a Forestry Referral, click on
					"View Forms" and select the Referral Form from the list.
				</p>
			<li class="subHeader"> Search Submissions </li>
				<p class="bodyText">
					This function allows you 
					to search through the Submissions in the Portal.
					<br/>
					Note that you will not see submissions from other
					users unless you the appropriate have permissions.
				</p>
			<li class="subHeader"> Map Layers </li>
				<p class="bodyText">
					To view the spatial layers that you are permitted
					to see, click on the "Map Layers" link. 
					<br/>
					You will be
					able to search through the list of layers by name
					and select which layers you would like to see
					in the Map Viewer.
				</p>
			<li class="subHeader"> Manage Permissions </li>
				<p class="bodyText">
					This is an administrative function that
						enables you to 
						manage permissions on various objects such 
						as layers, forms, etc. that a user has
						access to.
				</p>
			<li class="subHeader"> User Management </li>
				<p class="bodyText">
					Use this function to add new users to the 
					Portal or to change
					passwords, email addresses, etc. for existing users.
				</p>
			<li class="subHeader"> Create Spatial Schema </li>
				<p class="bodyText">
					An administrative function allowing creation of
					Spatial Schemas that can be linked to various forms.
				</p>
			<li class="subHeader"> View Available Schemas </li>
				<p class="bodyText">
					Allows you to see what schemas are available
					and which forms are associated with which schema(s).
				</p>
		</ul>
     </p>
          
   </div>
	<!-- end #mainContent -->
	<!-- This clearing element should immediately follow 
    the #mainContent div in order to force the 
    #container div to contain all child floats -->
   <br class="clearfloat" />
   <div id="footer">
    <?php include_once('links_footer.html');?></div>
   </div>
  <!-- end #footer -->
  </div>
<!-- end #container -->
</body>
</html>
