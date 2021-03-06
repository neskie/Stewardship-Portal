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
include_once('tng_check_session.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style-new.css" rel="stylesheet" type="text/css" />
<title>View Available Schemas</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_view_schema.js"> </script>
<!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.thrColHybHdr #sidebar1, .thrColHybHdr #sidebar2 { padding-top: 30px; }
.thrColHybHdr #mainContent { zoom: 1; padding-top: 15px; }
/* the above proprietary zoom property gives IE the hasLayout it needs to avoid several bugs */
</style>
<![endif]-->
</head>
<!-- onLoad, send request to fetch a list of available schemas -->
<body onload="ajax_get_schemas()">
	<div id="header">
	    <?php include_once('top_div.html'); ?>
	  </div>	
	<div id="container">
	  <div id="content" class="column">
		<form id="tng_view_schema" 
			name="tng_view_schema" 
			method="post" 
			enctype="multipart/form-data">
			<h1 class="pageName"> View Available Schemas </h1>
			<p class="bodyText">
				The Stewardship Portal requires that all shapefiles 
				submitted meet specific formatting requirements, 
				known as schemas, which have already been established.  
				If you�re having trouble uploading shapefiles to the portal, 
				please view the schema requirements for the data-type 
				you�re trying to upload.
			</p>  
			<p class="bodyText">Select the schema you wish to view from the list below.  
			</p>
      	<br/>
			<p class="bodyText">
				<label style="width: 100px"> <b> Schema </b> </label>
				<select id="schema_list"
						name="schema_list"
						style="width: 250px"
						onChange="javascript: ajax_get_schema_details();">
						<!-- automatically populated -->
				</select>
				<br/>
				<br/>
				<label style="width: 100px"> <b> Geometry Type </b> </label>
				<input type="text" 
						id="geom_type"
						name="geom_type"
						size="35"
						disabled="1"/>
				<br/>
				<br/>
				<label style="width: 100px"> <b> Attributes </b> </label>
				<br/>
				<ul id="attr_list"
					name="attr_list"
					class="bodyText">
					<!-- automatically populated -->
				</ul>
			</p>
		</form>
		<br/>
		<br/>
		<p class="bodyText">
			<i>Please note: �varchar� = �text� or �string�.  
			Lengths of text fields are irrelevant to the portal�s database. 
			</i>
		</p> 
		<p class="bodyText">
			<i>Also remember that each component of the 
			shapefiles needs to be uploaded separately, 
			not in a zipfile.  You need to add the .shp, .dbf, .shx, 
			(and others) to the portal as individual files.
			</i>
		</p>
		<p class="bodyText">
			<i>If you feel additional schemas need to be made 
			or modified, please contact the portal administrator 
			to discuss the issue.
			</i>
		</p>
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