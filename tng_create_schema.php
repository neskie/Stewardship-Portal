<?php
/*---------------------------------------------------------------
author:	alim karim
date:	May 14, 2007
file:	tng_create_schema.php

desc:	webpage allowing administrator to create
		spatial schemas
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
<title>Create Schema</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_create_schema.js"> </script>
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
	<form id="tng_create_schema" 
			name="tng_create_schema" 
			method="post" 
			enctype="multipart/form-data">
		<h1 class="pageName"> Create Schema </h1>
		<ul class="bodyText">
			<li> 
				Enter a name for the schema you wish to 
				create.
			</li>
			<br/>
			<li> 
				Select the type of geometry that this schema
				will be associated with.
			</li>
			<br/>
			<li> 
				Add attributes to the schema representing the attributes
				that are expected for this schema. Note that
				each attribute should have a name and a data type.
			</li>
			<br/>
			<li> 
				Click on Create Schema to create the schema
			</li>
			
		</ul>
		<hr/>
		<br/>
		<p class="bodyText">
			<label style="width:105px"> <b> Schema Name: </b></label>
			<input type="text" id="schema_name" name="schema_name" size="45"/>
			<br/>
			<label style="width:105px"> <b> Geometry Type: </b></label>
			<select id="geom_type" class="input" style="width:250px">
				<option id="point" value="point"> Point </option>
				<option id="line" value="line"> Line </option>
				<option id="polygon" value="polygon"> Polygon </option>
			</select>
		</p>
		<p class="bodyText">
			<b> Attributes: </b>
			<br/>
			<div id="fields" >
				<!--style="border:1px solid black; width=650px;padding:5px"-->
				<hr/>
				<!-- automatically populated-->
			</div>
			<br/>
			<input type="button"
					value="Add Attribute"
					onClick="javascript: add_field()"/>
		</p>
		<br/>
		<br/>
		<input type="button"
				class="button"
				value="Create Schema"
				onClick="javascript: ajax_check_schema_name()"/>
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