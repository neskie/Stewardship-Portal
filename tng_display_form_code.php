<?php
/*---------------------------------------------------------------
author:	alim karim
date:	Feb 02, 2007
file:	tng_display_form.php

desc:	code behind form rendering page.
		it creates a form object, gets the
		fields for the form (see form class)
		and calls the generate xml method on
		the form.
		the xml is then fed through an xslt
		processor using an xslt stylesheet to
		render the html representation of the form.
---------------------------------------------------------------*/
include_once('classes/class_form.php');
include_once('classes/class_login.php');

session_start();

$xml_data = "";
$xslt_file = "tng_form_transform.xslt";
$xslt_data = "";
$generated_form_html = "";

/// the readonly post variable tells this form
/// if the html that is going to be rendered
/// should be displayed as readonly or not.
/// by default, readonly is set to false.
if(!isset($_SESSION['readonly']))
	$_SESSION['readonly'] = 'false';

if(isset($_POST['ajax_action'])){ // receiving an AJAX request
	switch($_POST['ajax_action']){
		// check for valid pid
		case "check_pid":
			$xml;
			$form = $_SESSION['obj_form'];
			$form->check_pid($_POST['pid'], $xml);
			echo $xml;
		break;
	}
}else if(!isset($_POST['form_submitted'])){
	///
	/// main method
	/// if the form_submitted post variable 
	/// not is set, then
	/// this is the first time this form is being
	/// called i.e. it is not a post back
	///
	///
	global $xml_data;
	global $xslt_file;
	global $xslt_data;
	global $generated_form_html;
	// create a new form object with the
	// form id from the SESSION variable
	if(isset($_SESSION['submission_id'])){
		$form = new Form($_SESSION['form_id'], $_SESSION['submission_id']);
		unset($_SESSION['submission_id']);
	}
	else
		$form = new Form($_SESSION['form_id']);
		
	// store the form in a session variable
	// it will be used once the page has been 
	// posted back
	$_SESSION['obj_form'] = $form;
	$xml_data = $form->generate_xml();
	$generated_form_html = "";
	// check what version of php we are running.
	// this is needed since php5 does not have the
	// xslt_create function - no sablotron.
	if(phpversion() < 5.0){
		// for debugging only - write out xml to file
		//$file = fopen("/tmp/xml.txt", "w");
		//fwrite($file, $xml_data);
		//fclose($file);
		// read xslt stylesheet into local variable
		$file = fopen($xslt_file, "r");
		$xslt_data = fread($file, filesize($xslt_file));
		fclose($file);
		// create new xlst processor
		$xslt_processor = xslt_create();
		// store array arguments to be passed
		// to the xslt processor
		// see http://ca3.php.net/manual/en/function.xslt-process.php
		// for further explanation
		$xslt_args = array('/_xml' => $xml_data, '/_xsl' => $xslt_data);
		// store parameters to be passed to
		// xslt. in this case we want to instruct
		// it to not produce the html as read only
		$xslt_params = array('readonly' => $_SESSION['readonly']);
		$generated_form_html = xslt_process($xslt_processor, 'arg:/_xml', 'arg:/_xsl', NULL, $xslt_args, $xslt_params);
	}else{
		// php5 version of code.
		// both the xml string and the 
		// xslt stylesheet are loaded
		// into DOMDocument objects
		$xml = new DOMDocument;
		$xml->loadXML($xml_data);
	
		$xsl = new DOMDocument;
		$xsl->load($xslt_file);
	
		// create xslt processor
		$proc = new XSLTProcessor;
		$proc->importStyleSheet($xsl);
		// set parameters
		$proc->setParameter('', 'readonly', $_SESSION['readonly']);
		// perform transformation and store
		// results to file. note that i could
		// not find a way to store the results
		// into a local variable. one option was 
		// to use sb_output(), but this does not
		// work in our case - the results are 
		// echoed straight out. we need to capture	
		// the result and echo it within a 	
		// particular section of the html page.
		$rand_file = "/tmp/" . uniqid(true);	
		$proc->transformToURI($xml, $rand_file);
		$file = fopen($rand_file, "r");
		$generated_form_html = fread($file, filesize($rand_file));
		fclose($file);
		unlink($rand_file);
	}
	
}else{ // form_submitted has been set,
	//the page has been posted back.
	// collect and store all field values 
	// check to see if the session has 
	// expired
	if(!isset($_SESSION['obj_form'])){
		echo "session expired ";
		echo "<META HTTP-EQUIV='Refresh' Content='0; URL=tng_login.php'>";
	}else{ // session not expired
		$form = $_SESSION['obj_form'];
		$login = $_SESSION['obj_login'];
		$sub_id = -1;
		collect_form_data($form);
		// try to save the form
		$parent_sub_id = -1;
		$failed_files = array();
		$successful_files = array();
		// note that only an isset test 
		// is not enough. the user may type
		// something and then erase the value,
		// which would lead to a blank value
		// being submitted.
		if(isset($_POST['parent_submission']) && $_POST['parent_submission'] != "")
			$parent_sub_id = $_POST['parent_submission'];
		if(($sub_id = $form->save_form($login->uid, $parent_sub_id, $failed_files, $successful_files)) != -1 ){
			//header("Location: tng_form_saved.html");
			//send_confirmation_email($sub_id, $parent_sub_id, $login, $failed_files);
			// set session variable to hold failed files. these
			// are reported on tng_form_saved.php.
			$_SESSION['failed_files'] = $failed_files;
			// set session variable to hold succesful files
			$_SESSION['successful_files'] = $successful_files;
			//echo "<META HTTP-EQUIV='Refresh' Content='0; URL=tng_form_saved.php'>";
			// set session variable
			// so that the target page is 
			// aware that the request is made from
			// a previous page and is not a 'typed in' url.
			$_SESSION['assign_sub_perm_referrer'] = "tng_display_form.php";
			echo "<META HTTP-EQUIV='Refresh' Content='0; URL=tng_assign_sub_perm.php?sub_id=" . $sub_id ."'>";        
		}
		else{
			//header("Location: tng_form_not_saved.html");
			echo "<META HTTP-EQUIV='Refresh' Content='0; URL=tng_form_not_saved.php'>";    
		}
		exit();
	}
}

///
/// collect_form_data
/// parse out fields from the $_POST array
/// and call form method to set the values
/// of the respective field objects as they
/// appear in $_POST
///
function collect_form_data(&$form){
	$field_prefix = "fieldid";
	$separator = "_";
	$post_variables = array();
	$post_variables = array_keys($_POST);
	$length = count($post_variables);
	for($i = 0; $i < $length; $i++){
		// verify that this indeed is a post
		// variable begining with "fieldid_"
		if(substr_count($post_variables[$i], $field_prefix . $separator) == 1){
			// strstr returns the remainder of the string 
			// after the first occurrence of $separator
			// i.e. if we pass fieldid_13 with $separator = "_"
			// then we should get _13 as the result.
			// applying the substring function as
			// substring("_13", 1) will return 13, which
			// is what we are after.
			$field_id  = substr(strstr($post_variables[$i], $separator), 1);
			// to set the value of the field object, we need
			// to use $post_variables[$i] to index the $_POST
			// array to get the value entered by the user. this
			// is then passed to the set_field_value function
			// along with the field_id.
			// surround single quotes with '' so that they are
			// escaped properly.
			// 2008.08.21 - ak
			// note that a backslash is added before a
			// single quote as well. we need to get rid 
			// of this.
			// see http://trac.geoborealis.ca/ticket/33
			// for details
			$form->set_field_value($field_id, 
									str_replace(Array("'", "\\"), 
												Array("''", ""), 
												$_POST[$post_variables[$i]]));
		}
	}
	// now that all the post variables are dealt
	// with, deal with the files that were attached
	// to the form.
	// note that the checks for spatial files is done
	// on the client side using javascript before the form
	// is submitted.
	$file_keys = array();
	$file_keys = array_keys($_FILES);
	$length = count($file_keys);
	// note that the $_FILES cannot be indexed using
	// an integer, so we use the array_keys function
	// to obtain the names of the keys used in the file
	// array.
	for($i = 0; $i < $length; $i++){
		$form->add_file($_FILES[$file_keys[$i]]['tmp_name'], $_FILES[$file_keys[$i]]['name']);
	}
}

///
/// send_confirmation_email()
/// send mail to user confirming that
/// the submission was successful
///
function send_confirmation_email($sub_id, $pid, $login, $failed_files){
	$subject = "Submission Successful - " . $sub_id;
	if ($pid != -1)  
		$subject .= " - amendment to: " . $pid;
		
	$message = " Thank you, " . $login-> fname . " " . $login->lname . ", " 
		. "for your Submission to the TNG Portal.\n\n"
		. "The ID for this Submission is:" ;
	if ($pid == -1) 
		$message .= $sub_id; 
	else
		$message .= $pid; 
	
	$message .= ".\n\n"
		. "To identify who has been assigned to your file, " 
		. "enter ";
	if ($pid == -1)
		$message .= $sub_id;
	else 
		$message .= $pid ;
	
	$message .= " on the Find Submissions page at " 
				. "www.tngportal.ca.  Then contact that person directly at 250-392-3918.\n\n";
				
	if(count($failed_files) > 0){
		$message .= "The following files could not be loaded into the Portal.\n"
				. "For any failed shapefiles, please ensure that they match a valid schema.\n"
				."--------------------------------------------------------\n";
				foreach ($failed_files as $file)
					$message .=  "- " . $file . "\n";
		$message .="--------------------------------------------------------\n\n";
	}
		
	$message .= "This email is for notification purposes only, please do not respond to it.\n\n"
		. "Thank you,\n\n"
		. "The Tsilhqotin Stewardship Department.\n";
			
	$headers = 'From: portaladmin@tsilqhotin.ca' . "\r\n"
   		  . 'Cc: ' . 'portaladmin@tsilhqotin.ca' . "\r\n";
    			
	mail($login->email, $subject, wordwrap($message, 70), $headers);
}
?>
