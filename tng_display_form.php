<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	tng_display_form.php

desc:	webpage to render a form that the user can fill out
---------------------------------------------------------------*/
header('Pragma: no-cache'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

include_once('tng_display_form_code.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="style-new.css" rel="stylesheet" type="text/css" />
<link href="print.css" rel="stylesheet" type="text/css" media="print"/>
   
<title>Fill Form</title>
<script language="javascript" src="tng_ajax_utils.js"> </script>
<script language="javascript">
var file_count = 0;
var file_prefix = 'file_';

///
/// add_file_input
/// create a set of elements on the form that allow
/// a file to be uploaded with the submission of 
/// a form.
///
function add_file_input(){	 
	var file_div = document.getElementById('form_files');
	file_count++;
	var file_desc_label = document.createElement('label');
	file_desc_label.setAttribute('for', 'txt_area_file_description');
	file_desc_label.setAttribute('class', 'lbl_regular');
	file_desc_label.innerHTML = "File Description";
	file_div.appendChild(file_desc_label);
	
	var end_tag = document.createElement('br');
	file_div.appendChild(end_tag);
	
	var file_desc = document.createElement('textarea');
	file_desc.setAttribute('class', 'txt_area_file_description');
	file_div.appendChild(file_desc);
	
	var end_tag = document.createElement('br');
	file_div.appendChild(end_tag);
	
	var file_input = document.createElement('input');
	file_input.setAttribute('type', 'file');
	file_input.setAttribute('id', file_prefix + file_count);
	file_input.setAttribute('name', file_prefix + file_count);
	file_input.setAttribute('class', 'input_file'); 
	file_input.setAttribute('size', '65'); 
	file_div.appendChild(file_input);
	
	var end_tag = document.createElement('br');
	file_div.appendChild(end_tag);
	var end_tag = document.createElement('br');
	file_div.appendChild(end_tag);
	
	var end_tag = document.createElement('hr');
	file_div.appendChild(end_tag);
	
	var end_tag = document.createElement('br');
	file_div.appendChild(end_tag);	
}

///
/// ajax_validate_parent()
/// send an ajax request to check
/// if the parent ID entered is valid
/// i.e. the ID exists in the DB and that it
/// does not have a parent of its own
///
function ajax_validate_parent(){
	// first check to see if this is a 
	// valid integer
	var pid = document.getElementById('parent_submission').value;
	// blank pid means this submission does not
	// have a parent. proceed to submitting the form.
	if(pid == ""){
		submit_form();
	}else{
		if(!/^\d+$/.test(pid)){
			alert("The Parent Submission ID must be an integer.\n"
				+ "Please enter a valid Parent Submission ID");
			return;
		}else{ 
			// valid int, send request
			// to see if the pid is valid
			// and does not have a parent
			var post_params = "ajax_action=check_pid&pid=" + pid;
			var target = 'tng_display_form_code.php';
			create_http_request();
			send_http_request(handler_validate_parent, "POST", target, post_params);
		}
	}
	
}
///
/// submit_form()
/// submit the form if file validation
/// succeeds
///
function submit_form(){
	if(validate_files() == false)
		alert('You are missing a .shp, .dbf or a .shx file');
	else{ // check passed, submit the form
		elt = document.getElementById('form_submitted');
		elt.value = "submitted";
		document.forms[0].submit();
	}
}

///
/// validate_files()
/// check to see if every shapefile
/// has a corresponding dbf and shx files.
/// note that the user can get away with providing
/// unrelated shape, dbf and index files - names are
/// not checked. for instance, the user could 
/// upload abc.shp, abc.dbf and xyz.shx.
///
function validate_files(){
	var shp_count = 0;
	var dbf_count = 0;
	var shx_count = 0;
	
	for(var i = 0; i < file_count; i++){
		var file_num = i + 1;
		var elt_id = file_prefix + file_num;
		var file_input = document.getElementById(elt_id);
		// this is needed in case the user clicks 'Add file'
		// and then leaves the contents of the file input
		// blank
		if(file_input.value.length > 0){
			var file_name = file_input.value.substring(file_input.value.lastIndexOf("/") + 1, file_input.value.length);
			var file_ext = file_name.substring(file_name.lastIndexOf(".") + 1, file_name.length);
			if(file_ext == "shp")
				shp_count++;
			else if(file_ext == "dbf")
				dbf_count++;
			else if(file_ext == "shx")
				shx_count++;
			else{
				// non-spatial file, do not increment counters
			}
		}
	}
	
	if((shp_count == dbf_count) && (shp_count == shx_count))
		return true;
	else
		return false;
}

/// ----------------------------------------------------------------------
/// ajax handlers
/// ----------------------------------------------------------------------

///
/// handler_validate_parent()
/// process the response from the request
/// sent to validate the PID.
/// schema of the XML result:
/// <result> invalid_pid </result>
///
function handler_validate_parent(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		create_xml_doc(xmlHttp.responseText);
		// no text node below the <result>
		// node means that all checks were passed.
		if(xmlDoc.getElementsByTagName("result")[0].childNodes.length == 0)
			submit_form();
		else{
			var response = xmlDoc.getElementsByTagName("result")[0].childNodes[0].nodeValue;
			if(response == "invalid_pid")
				alert("The Parent ID you entered is not valid.");
			else if(response == "is_child")
				alert("The Parent ID you entered is a Submission Ammendment\n"
					+ "and cannot be used as a Parent. Please enter a valid\n"
					+ "Parent ID");
		}
	}
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
<body>
<div id="header"><?php include_once('top_div.html'); ?></div>
<div id="container">	
		<!-- <div id="wrapper"> -->
			<div id="content" class="column">
				<form id="tng_display_form" 
					name="tng_display_form" 
					method="post" 
					enctype="multipart/form-data" 
					action="tng_display_form.php">
				<br/>
				<br/>
					<p class="bodyText">
						<label style="width:120px"><b> Parent Submission ID: </b> </label>
							<input type="text" 
									id="parent_submission" 
									name="parent_submission" 
									size="10"
									<?php if($_SESSION['readonly'] == 'true') echo "disabled"; ?>
							/>
						<br/>
						Please leave this field blank if you do not
						wish to link this submission with a previously
						made submission.
					</p>
					<p class="bodyText">
			      		<?php
						// print out the html generated by the stylesheet		
						// from the xml		
						echo $generated_form_html;
						?>
					</p>
					<hr />
					<input type="hidden" id="form_submitted" name="form_submitted" value=""/>
					<!-- 
						the buttons below are disabled 
						if the form is being displayed in
						read only mode.
					-->
					<input type="button" 
							onclick="add_file_input()" 
							value="Add File" 
							class="button" 
							<?php if($_SESSION['readonly'] == 'true') echo "disabled"; ?>
							/>
					<input type="button" 
							onclick="javascript: ajax_validate_parent()" 
							value="Submit" 
							class="button" 
							<?php if($_SESSION['readonly'] == 'true') echo "disabled"; ?> 
							/>
					<hr />
					<div id="form_files">
						<!-- this is where file input elements will be added -->
					</div>
				</form>
			</div>
		<!-- /div>			 -->
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
