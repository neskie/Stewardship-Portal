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
		    <h1 class="pageName"> Available Layers </h1>
			<form id="tng_select_layers" 
					name="tng_select_layers" 
					method="post" 
					enctype="multipart/form-data" 
					action="tng_select_layers_code.php">
				<p class="bodyText"> The list below shows the layers that you have permission to view. Please 
					select the layers that you wish to appear in the mapping agent.
				</p>
				<p class="bodyText"> To search the available layers by name, enter the 
					name of the layer in the field below.
				</p>
				<p class="bodyText"> Name: 
				<input type="text"
						name="layer_name" 
						id="layer_name"
						size="50"
						onKeyUp="javascript: ajax_post_search(this.value);"/> 
				</p>
				<!-- <div id="layer_list"> -->
					<!-- this will be auto populated by javascript/ajax -->
				<!-- </div> -->
				<dl id="layer_list" name="layer_list" class="bodyText">
					<!-- this will be auto populated by javascript/ajax -->
				</dl>
				<br/>
				<input type="button"  
						value="Refresh List"
						onClick="javascript: ajax_refresh_layers()"/>
				<br/>
				<br/>
				<input type="button"  
						value="Launch Map Viewer" 
						onClick="javascript: ajax_submit_form()"/>
				<br/>
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