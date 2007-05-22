<?php
/*---------------------------------------------------------------
author:	alim karim
date:	April 18, 2007
file:	tng_select_layers.php

desc:	webpage to give the user a list of available
		layers that can be viewed in the mapviewer
---------------------------------------------------------------*/
header('Pragma: no-cache'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

//include_once('tng_select_layers_code.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style.css" rel="stylesheet" type="text/css" />
<title>Available Layers</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_select_layers.js"> </script>

<script language="javascript">
// the first time around, we should display
// all available layers. any previous lists
// held in a session variable should be cleared.
ajax_refresh_layers();

</script>

</head>
<body>
	<!-- <div id="container"> -->
		<div id="content">
			<form id="tng_select_layers" 
					name="tng_select_layers" 
					method="post" 
					enctype="multipart/form-data" 
					action="tng_select_layers_code.php">
				<h2> Available Layers </h2>
				<p> The list below shows the layers that you have permission to view. Please 
					select the layers that you wish to appear in the mapping agent.
				</p>
				<p> To search the available layers by name, enter the 
					name of the layer in the field below.
				</p>
				<p> Name: 
				<input type="text"
						name="layer_name" 
						id="layer_name"
						size="50"
						onKeyUp="javascript: ajax_post_search(this.value);"/> 
				</p>
				<!-- <div id="layer_list"> -->
					<!-- this will be auto populated by javascript/ajax -->
				<!-- </div> -->
				<dl id="layer_list" name="layer_list">
					<!-- this will be auto populated by javascript/ajax -->
				</dl>
				<br/>
				<input type="button" 
						class="button" 
						value="Refresh List"
						onClick="javascript: ajax_refresh_layers()"/>
				<br/>
				<input type="button" 
						class="button" 
						value="Launch Map Viewer" 
						onClick="javascript: ajax_submit_form()"/>
				<br/>
				<br/>
			</form>
		</div>
		<div id="leftcol"> whr is is that this didnt work before but it works just fine now ....</div>
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