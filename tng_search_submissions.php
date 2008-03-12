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
<title>Search Submisssions</title>
<script src="tng_ajax_utils.js"> </script>
<script src="tng_search_submissions.js"> </script>
<!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.thrColHybHdr #sidebar1, .thrColHybHdr #sidebar2 { padding-top: 30px; }
.thrColHybHdr #mainContent { zoom: 1; padding-top: 15px; }
/* the above proprietary zoom property gives IE the hasLayout it needs to avoid several bugs */
</style>
<![endif]-->
</head>
<body onLoad="ajax_populate_lists()">
	<div id="header">
	   <?php include_once('top_div.html'); ?>
	</div>
	<div id="container">
		<div id="content" class="column">
			<h1 class="pageName"> Search Submissions </h1>
				
			<form id="tng_search_submissions" 
					name="tng_search_submissions" 
					method="post" 
					enctype="multipart/form-data">
				<p class="bodyText">
					If you know the ID of the Submission you
					are searching for, please enter it in the
					Submission ID field and click the search
					button located beside it.
				</p>
				<p class="bodyText">
					To conduct a more generic search (by Submission type,
					Submission Status, Submission Asignee, etc.), please click
					on the Detailed Search button
				<br/>
				<br/>
				<label style="width: 100px; "> <b> Submission ID </b> </label>
				<input type="text" 
						id="sub_id"
						name="sub_id"
						size="35"/>
				<input type="button"
					value="Search"
					onClick="ajax_simple_search()"/>
				</p>
				<input type="button"
					id="detail_button"
					onClick="expand_collapse('detailed_search')"
					value="Detailed Search"/>
					
				<div id="detailed_search"
					style="display:none">
					<hr/>
				<br/>
				<br/>
				<dl class="bodyText">
					<dt> 
						<label style="width: 150px;"> 
							<input type="checkbox"
									id="chk_sub_name"/> 
									<b> Submission Name </b>  
						</label>
						<input type="text"
								id="sub_name"
								size="45"/>
						<br/>
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
						<br/>
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
						<br/>
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
						<br/>
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
						<br/>
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
						<br/>
						<dd>
						<label style="width: 110px;"> <i>Value</i> </label>
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
				<!-- end detailed search div -->
				<br/>
				<div id="search_results"
					style="display:none">
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
					<br/>
				</div>
				<!-- end search results div -->
			</form>
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