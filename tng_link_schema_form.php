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
// only allow access if the logged in user
// is an admin user
if($_SESSION['obj_login']->is_admin() == "false")
	echo "<META HTTP-EQUIV='Refresh' Content='0; URL=tng_login_successful.php'>";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style.css" rel="stylesheet" type="text/css" />
<title>Associate Schema With Form</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_link_schema_form.js"> </script>

<!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.thrColHybHdr #sidebar1, .thrColHybHdr #sidebar2 { padding-top: 30px; }
.thrColHybHdr #mainContent { zoom: 1; padding-top: 15px; }
/* the above proprietary zoom property gives IE the hasLayout it needs to avoid several bugs */
</style>
<![endif]-->
</head>
<!-- onLoad, call method to populate form list -->
<body class="thrColHybHdr" onLoad="ajax_populate_forms();">

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
	<form id="tng_link_schema" 
		name="tng_link_schema" 
		method="post" 
		enctype="multipart/form-data">
		<h1 class="pageName"> Associate Schema With Form </h1>
			<p class="bodyText">This page allows the portal administrator to associate one or more schemas with a form.</p>
                  <p class="bodyText">Once a form is selected in the drop-down “Form:” list below, you will be able to see the list of linked and unlinked schemas with respect to that selected form.</p> 
                  <p class="bodyText">To associate a schema with the selected form, select the schema from the <b>Unlinked Schemas</b> box and click the DOWN ARROW toggle button to move it to the <b>Linked Schemas</b> box.</p> 
                  <p class="bodyText">To remove an associated schema from the selected form, select the schema from the <b>Linked Schemas</b> box and click the UP ARROW toggle button to move it to the <b>Unlinked Schemas</b> box.</p>
                  <p class="bodyText"><i>One form can have multiple schemas associated with it, and one schema can be reused and be associated with multiple forms, if appropriate.</i></p>
			<br/>
			<p class="bodyText">
			<label style="width:100px;" for="user_list"> Form: </label>
			<select id="form_list" 
					name="form_list" 
					style="width: 250px;" 
					onChange="javascript: ajax_populate_schemas();">
			<!-- this will be auto populated by javascript/ajax -->
			</select>
			</p>
			
			<p class="bodyText">
				<label style="width:100px;"> Unlinked Schemas: </label>
				<select id="unlinked_schema_list" 
						name="unlinked_schema_list" 
						style="width:250px;" size="5">
						<!-- this will be auto populated by javascript/ajax -->
				</select>
			</p>
			<p class="bodyText">
				<label style="width:100px;"> Toggle: </label> 
				<img src="images/down_arrow.gif" onClick="javascript: ajax_toggle_linkage('link')"/>
				<img src="images/up_arrow.gif" onClick="javascript: ajax_toggle_linkage('unlink')"/>
			</p>
			<p class="bodyText">	
				<label style="width:100px" for="obj_list_allowed"> Linked Schemas: </label>
				<select id="linked_schema_list" 
						name="linked_schema_list" 
						style="width:250px;" 
						size="5">
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
	    <?php include_once('links_footer.html');?></div>
	   </div>
	  <!-- end #footer -->
	  </div>
	<!-- end #container -->
</body>
</html>
