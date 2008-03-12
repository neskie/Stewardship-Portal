<?php
/*---------------------------------------------------------------
author:	alim karim
date:	July 26, 2007
file:	tng_view_form_details.php

desc:	webpage allowing administrator to view
		the fields associated with a form and to 
		 change whether the field is searchable or not
---------------------------------------------------------------*/
header('Pragma: no-cache'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
// note that the class MUST be included before
// session_start() is called, otherwise the
// processor will complain about an 
// incomplete class definition.
include_once('classes/class_login.php');
// include script to check for 
// login session variable
include_once('tng_check_session.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style-new.css" rel="stylesheet" type="text/css" />
<title>View Form Fields</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_view_form_details.js"> </script>
<script language="javascript">
	<?php
		// echo true or false to the
		// JS user_is_admin variable
		echo "user_is_admin = " . $_SESSION['obj_login']->is_admin() . ";";
		//echo "alert(" . $_SESSION['obj_login']->is_admin() . ");";
	?>
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
<!-- onLoad, send request to get the list of forms -->
<body onLoad="ajax_get_forms()">
	<div id="header">
		<?php include_once('top_div.html'); ?>
	 </div>

	<div id="container">
	  <div id="content" class="column">
		<form id="tng_view_form" 
			name="tng_view_form" 
			method="post" 
			enctype="multipart/form-data">
			<h1 class="pageName"> View Form Fields </h1>
			<p class="bodyText">
				Please note that the changes made here are instant. 
				Select the form you wish to view from the 
				list below.
			</p>
			<p class="bodyText">
				<label style="width: 100px"> <b> Form </b> </label>
				<select id="form_list"
						name="form_list"
						style="width: 250px"
						onChange="javascript: ajax_get_form_details();">
						<!-- automatically populated -->
				</select>
				<br/>
				<br/>
				<label style="width: 100px"> <b> Fields </b> </label>
				<br/>
				<table id="field_table"
					style="width: 50%; border-collapse: collapse;">
					<tr class="th_search">
						<th> Name </th>
						<th> Type </th>
						<th> Searchable </th>
					</tr>
					<tbody class="td_search" id='detail_body'>
					<!-- automatically populated -->
					</tbody>
				</table>
				<br/>
			</p>
		</form>
		</div>
		<div id="left" class="column">
	    <?php include_once('tng_links_post_login.php');?>
	  </div>
	  <div id="right" class="column">
	    <?php include_once('links_sidebar2.html');?>
	  </div> 
	 </div>
	 <div id="footer">
	    <?php include_once('links_footer.html');?>
	 </div>  
</body>
</html>