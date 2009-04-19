<?php
/*---------------------------------------------------------------
author:	alim karim
date:	April 24, 2007
file:	tng_search_submissions.php

desc:	webpage to search through different submissions
		in the db.
		
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style-new.css" rel="stylesheet" type="text/css" />
<title>View Submission Details</title>
<script src="prototype.js"></script>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_view_submission.js"> </script>
<script language="javascript">
var sub_id = -1;
// small php snippet to obtain
// submission id from the GET array
// and assign it to the local JS
// variable

<?php
	if(isset($_GET['sub_id']))
		echo "sub_id = " . $_GET['sub_id'];
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
<body onLoad="ajax_refresh_lists();ajax_populate_status_list();">
  <div id="header">
    <?php include_once('top_div.html'); ?>
  </div>
  <div id="container">
  <div id="content" class="column">
	<h1 class="pageName"> View Submission </h1>
	<p class="bodyText">
		This page displays the details of a
		particular Submission. To view the Form
		that was submitted, please click on the 
		View Form Link
	</p>
	<p class="bodyText">
		To download any of the files or layers
		that were a part of the Submission, please
		click on their respective links.
	</p>
	
	<p class="bodyText"> <b> Submission Details </b> </p>
	<br/>
	<p >
		<label style="width:100px" > <b> ID </b> </label>
		<input type="text"
				id="sub_id"
				size="35"
				disabled="1"/>
		<br/>

		<label style="width:100px" > <b> Type </b> </label>
		<input type="text"
				id="sub_type"
				size="35"
				disabled="1"/>
		<br/>

		<label style="width:100px" > <b> Title </b> </label>
		<input type="text"
				id="sub_title"
				size="35"
				disabled="1"/>
		<br/>

		<label style="width:100px" > <b> Name </b> </label>
		<input type="text"
				id="sub_name"
				size="35"/>
		<input type="button"
				class="button"
				value="Update Name"
				onClick="javascript: ajax_update_name();"/>
		<br/>

		<label style="width:100px" > <b> Status </b> </label>
		<select id="status_list"
				style="width: 300px;"
				onChange="ajax_update_status()">
				<!-- automatically populated -->
		</select>
		<br/>

		<label style="width:100px" > <b> Submitted By </b> </label>
		<input type="text"
				id="sub_user"
				size="35"
				disabled="1"/>
		<br/>

		<label style="width:100px" > <b> Assigned To </b> </label>
		<select id="asignee_list"
				style="width: 300px;"
				onChange="javascript: ajax_update_assignee()">
				<!-- automatically populated -->
		</select>
		<br/>

		<label style="width:100px" > <b> Date Submitted </b> </label>
		<input type="text"
				id="sub_date"
				size="35"
				disabled="1"/>
		<br/>
	</p>
	<hr/>
	<p class="bodyText">
		<b> Submission Form </b>
		<br/>
		<a href="#form" onClick="ajax_dislpay_form()">View Form Data </a>
	</p>
	<hr/>
	<p class="bodyText">
		<b> Submission Files </b>
		<br/>
		<ol id="sub_files" class="bodyText">
			<!-- automatically populated -->
		</ol>
		<br/>
	</p>
	<hr/>
	<p class="bodyText">
		<b> Submission Layers </b>
		<br/>
		<ol id="sub_layers" class="bodyText">
			<!-- automatically populated -->
		</ol>
		<br/>
	</p>
	<hr/>
	<p class="bodyText">
		<?php
			// if the administrator is logged
			// in, allow them access to 
			// assign_sub_perm page
			if($_SESSION['obj_login']->is_admin() == "true")
				echo "<a href='#form' onClick='change_sub_perm(sub_id)'> Change Submission Permissions </a>";
		?>
	</p>
	<hr/>
	<p class="bodyText">
		<?php
			// if the administrator is logged
			// in, allow them access to 
			// sub_notiy_list page
			if($_SESSION['obj_login']->is_admin() == "true")
				echo "<a href='#form' onClick='change_sub_notify_list(sub_id)'> Change Notification List </a>";
		?>
	</p>

	<hr/>
	<p class="bodyText">
		<b> Amendments </b>
		<br/>
		<div id="search_results">
			<br/>
			<table id="tbl_search_res"
					style="width: 90%; border-collapse: collapse;">
				<tr class="th_search">
					<th> ID </th>
					<th> Type </th>
					<th> Title </th>
					<th> Name </th>
					<th> Status </th>
					<th> Submitted By </th>
					<th> Assigned To </th>
					<th> Date Submitted </th>
				</tr>
			</table>
		</div>
		<br/>
	</p>		
  </div>
	<div id="left" class="column">
    <?php include_once('tng_links_post_login.php');?>
  </div>
  <div id="right" class="column">
    <?php include_once('links_sidebar2.html');?>
  </div>
  <div id="footer">
  	<?php include_once('links_footer.html');?></div>
  </div>
</div>
</body>
</html>
