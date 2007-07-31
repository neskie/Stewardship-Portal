<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	tng_list_forms.php

desc:	webpage to display a list of forms that that user
		can select to fill out
---------------------------------------------------------------*/
header('Pragma: no-cache'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// includes
// include file which has all the php code associated
// with this page.
include_once('tng_list_forms_code.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style.css" rel="stylesheet" type="text/css" />
<title>Select Action</title>
<script language="javascript">

function submit_form(action_value){
	document.getElementById('form_action').value = action_value;
	document.forms[0].submit();
}

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
		    <h1 class="pageName"> Fill Form </h1>
			<form id="tng_list_forms" method="POST" action="tng_list_forms.php">
				<p class="bodyText">
					In this section, you can select a Form to be filled for
					submitting various types of data.
					</br>
					For example, to initiate a Forestry Referral, please select
					the Referral form from the list below. Upon completing the
					form, please click the Submit button.
					<br/>
					Please select the form that you would like to fill in from the list below.
				</p>
				<select id="form_id" name="form_id" style="width:250px;">
					<?php
						// fill drop down with form names
						for($i=0; $i < $form_list_size; $i++){
							printf("<option value=\"%d\"> %s </option>\n", $form_list[$i][0], $form_list[$i][1]);
						}
					?>
				</select>
				<br/>
				<br/>
				<input type="button" value="Display Form" onClick="javascript:submit_form('fill_form');"/>
				<input type="hidden" id="form_action" name="form_action"/>
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
