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

///
/// main method
/// if the form_submitted post variable 
/// not is set, then
/// this is the first time this form is being
/// called i.e. it is not a post back
///
///
if(!isset($_POST['form_submitted'])){
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
	
}else{ // form_submitted has been set,
	//the page has been posted back.
	// collect and store all field values 
	// check to see if the session has 
	// expired
	if(!isset($_SESSION['obj_form'])){
		echo "session expired ";
		header("Location: tng_login.php");
	}else{ // session not expired
		$form = $_SESSION['obj_form'];
		$login = $_SESSION['obj_login'];
		$sub_id = -1;
		collect_form_data($form);
		// try to save the form
		$parent_sub_id = -1;
		// note that only an isset test 
		// is not enough. the user may type
		// something and then erase the value,
		// which would lead to a blank value
		// being submitted.
		if(isset($_POST['parent_submission']) && $_POST['parent_submission'] != "")
			$parent_sub_id = $_POST['parent_submission'];
		if(($sub_id = $form->save_form($login->uid, $parent_sub_id)) != -1 ){
			//header("Location: tng_form_saved.html");
			send_confirmation_email($sub_id, $login->email);
			echo "<META HTTP-EQUIV='Refresh' Content='0; URL=tng_form_saved.php'>";    
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
			$form->set_field_value($field_id, str_replace("'", "''", $_POST[$post_variables[$i]]));
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
function send_confirmation_email($sub_id, $email_to){
	$subject = "Submission Successful - " . $sub_id;
	$message = "You have successfully made a Submission to the TNG Portal.\n"
				. "The ID for your Submission is: " . $sub_id . ".\n"
				. "This email is for notification purposes only. " 
				. "Please do not respond to it.\n";
	mail($email_to, $subject, wordwrap($message, 70));
}
?>
