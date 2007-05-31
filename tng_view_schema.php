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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style.css" rel="stylesheet" type="text/css" />
<title>Create Schema</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_view_schema.js"> </script>
</head>
<body onload="ajax_get_schemas()">
		<div id="leftcol"> why is is that this didnt work before but it works just fine now ....</div>
		<div id="content">
			<form id="tng_view_schema" 
					name="tng_view_schema" 
					method="post" 
					enctype="multipart/form-data">
				<h2> View Schema </h2>
				<p>
					Select the schema you wish to view from the 
					list below.
				</p>
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
					name="attr_list">
					<!-- automatically populated -->
				</ul>
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