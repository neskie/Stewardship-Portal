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
<title>Search Submisssions</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_search_submissions.js"> </script>
<script language="javascript">

</script>

</head>
<body onLoad="ajax_populate_lists()">
		<div id="leftcol"> why is is that 
						this didnt work before but 
						it works just fine now ....
		</div>
		<div id="content">
			<form id="tng_search_submissions" 
					name="tng_search_submissions" 
					method="post" 
					enctype="multipart/form-data">
				<h2> Search Submissions </h2>
				<p>
					If you know the ID of the Submission you
					are searching for, please enter it in the
					Submission ID field and click the search
					button located beside it.
				</p>
				<p>
					To conduct a more generic search (by Submission type,
					Submission Status, Submission Asignee, etc.), please click
					on the Detailed Search button
				</p>
				<label style="width: 100px; "> <b> Submission ID </b> </label>
				<input type="text" 
						id="sub_id"
						name="sub_id"
						size="35"/>
				<input type="button"
					value="Search"
					onClick="ajax_simple_search()"/>
				<br/>
				<br/>
				<input type="button"
					id="detail_button"
					onClick="expand_collapse('detailed_search')"
					value="Detailed Search"/>
					
				<div id="detailed_search"
					style="display:none">
					<hr/>
				<br/>
				<br/>
				<dl>
					<dt> 
						<label style="width: 150px;"> 
							<input type="checkbox"
									id="chk_sub_name"/> 
									<b> Submission Name </b>  
						</label>
						<input type="text"
								id="sub_name"
								size="45"/>
					</dt>
					<br/>
					<dt> 
						<label style="width: 150px;"> 
							<input type="checkbox"
									id="chk_sub_type"
									onClick="type_toggled()"/> 
									<b> Submission Type </b>  
						</label>
						<select id="type_list"
							name="type_list"
							style="width: 250px"
							onChange="javascript: ajax_populate_spec_field();">
							<!-- automatically populated by ajax-->
						</select>
					</dt>
					<br/>
					<dt>
						<label style="width: 150px;"> 
							<input type="checkbox"
									id="chk_sub_status"/> 
							<b>Submission Status</b> 
						</label>
						<select id="status_list"
								name="status_list"
								style="width: 250px">
								<!-- automatically populated by ajax -->
						</select>
					</dt>
					<br/>
					<dt>
						<label style="width: 150px;">
							<input type="checkbox"
									id="chk_sub_user"/> 
							<b>Submitted By</b> 
						</label>
						<select id="user_list"
								name="user_list"
								style="width: 250px">
								<!-- automatically populated by ajax -->
						</select>
					</dt>
					<br/>
					<dt>
						<label style="width: 150px;">
							<input type="checkbox"
									id="chk_sub_asignee"/> 
							<b>Asignee</b> 
						</label>
						<select id="asignee_list"
								name="asignee_list"
								style="width: 250px">
								<!-- automatically populated by ajax -->
						</select>
					</dt>
					<br/>
					<dt>
						<label style="width: 150px;">
								<input type="checkbox"
										id="chk_spec_field"/> 
							<b>Specific Field</b> 
						</label>
						<select id="spec_field_list"
								name="spec_field_list"
								style="width: 250px">
								<!-- automatically populated by ajax -->
						</select>
						<br/>
						<dd>
						<label style="width: 110px"> <i>Value</i> </label>
						<input type="text" 
								id="spec_field_value" 
								name="spec_field_value"
								size="45"/>
						</dd>
					</dt>
				</dl>
				<br/>
				<input type="button"
					class="button"
					value="Search Submissions"
					onClick="ajax_detailed_search()"/>
				<hr/>
				</div>
				<br/>
				<div id="search_results"
					style="display:none">
					<br/>
					<table id="tbl_search_res"
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