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
<script src="tng_create_schema.js"> </script>
</head>
<body onload="add_field();">
		<div id="leftcol"> why is is that this didnt work before but it works just fine now ....</div>
		<div id="content">
			<form id="tng_create_schema" 
					name="tng_create_schema" 
					method="post" 
					enctype="multipart/form-data">
				<h2> Create Schema </h2>
				<ul>
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
				<p>
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
				<p>
					<b> Attributes: </b>
					<br/>
					<div id="fields" style="border:1px solid black; width=650px;padding:5px">
						<!-- automatically populated-->
					</div>
					<br/>
					<input type="button"
							value="Add Attribute"
							onClick="javascript: add_field()"/>
				</p>
				<br/>
				<br/>
				<br/>
				<br/>
				<input type="button"
						class="button"
						value="Create Schema"
						onClick="javascript: ajax_check_schema_name()"/>
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