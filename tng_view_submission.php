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

// include script to check for 
// login session variable
include_once('tng_check_session.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style.css" rel="stylesheet" type="text/css" />
<title>View Submission Details</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_view_submission.js"> </script>
<script language="javascript">
var target_url = "tng_view_submission_code.php";
var sub_id = -1;
// small php snippet to obtain
// submission id from the GET array
// and assign it to the local JS
// variable

<?php
	if(isset($_GET['sub_id']))
		echo "sub_id = " . $_GET['sub_id'];
?>


//ajax_refresh_lists();
ajax_populate_status_list();

</script>

</head>
<body >
		<div id="leftcol"> why is is that 
						this didnt work before but 
						it works just fine now ....
		</div>
		<div id="content">
			<form id="tng_view_submissions" 
					name="tng_view_submissions" 
					method="post" 
					enctype="multipart/form-data">
			<h2> View Submission </h2>
			<p>
				This page displays the details of a
				particular Submission. To view the Form
				that was submitted, please click on the 
				View Form Link
			</p>
			<p>
				To download any of the files or layers
				that were a part of the Submission, please
				click on their respective links.
			</p>
			
			<h3> Submission Details </h3>
			<br/>
			<p>
				<label style="width:100px" > <b> ID </b> </label>
				<input type="text"
						id="sub_id"
						size="55"
						disabled="1"/>
				<br/>
	
				<label style="width:100px" > <b> Type </b> </label>
				<input type="text"
						id="sub_type"
						size="55"
						disabled="1"/>
				<br/>
	
				<label style="width:100px" > <b> Title </b> </label>
				<input type="text"
						id="sub_title"
						size="55"
						disabled="1"/>
				<br/>
	
				<label style="width:100px" > <b> Name </b> </label>
				<input type="text"
						id="sub_name"
						size="55"/>
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
						size="55"
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
						size="55"
						disabled="1"/>
				<br/>
			</p>
			<hr/>
			<p>
				<h3> Submission Form </h3>
				<br/>
					<a href="#form" onClick="ajax_dislpay_form()">View Form Data </a>
				<br/>
				<br/>
			</p>
			<hr/>
			<p>
				<h3> Submission Files </h3>
				<ol id="sub_files">
					<!-- automatically populated -->
				</ol>
				<br/>
			</p>
			<hr/>
			<p>
				<h3> Submission Layers </h3>
				<ol id="sub_layers">
					<!-- automatically populated -->
				</ol>
				<br/>
			</p>
			<hr/>
			<p>
				<h3> Ammendments </h3>
				<div id="search_results">
					<br/>
					<table id="tbl_sub_children"
							style="width: 90%;">
						<tr>
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
		<div id="rightcol"> 
			Much effort has been made to ensure that 
			the layouts in the BlueRobot Layout Reservoir appear 
			as intended in CSS2 compliant browsers. The content 
			should be viewable, though unstyled, in other web browsers. 
			If you encounter a problem that is not listed as a known 
			issue, I am most likely not aware of it. Your help will 
			benefit the other five or six people who visit this site. 
		</div> 
	</body>
</body>
</html>