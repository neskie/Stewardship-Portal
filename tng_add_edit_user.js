
var target_url = "tng_add_edit_user_code.php";

///
/// ajax_get_users()
/// ask the back end script for 
/// all users
///
function ajax_get_users(){
	create_http_request();
	// create the parameters to be
	// sent to the php target.
	// this is a string of name=value pairs. if
	// multiple parameters are to be sent, then they
	// are separated by an ampersand.
	var post_params = "ajax_action=get_users";
	// call method to sent the request
	send_http_request(xmlHttp_handle_ulist_populate, "POST", target_url, post_params);
}

///
/// ajax_add_edit_user()
/// add a new user to db
/// or update attributes for 
/// existing user
///
function ajax_add_edit_user(){
	var uname = document.getElementById('uname').value;
	var passwd = document.getElementById('password').value;
	var fname = document.getElementById('fname').value;
	var lname = document.getElementById('lname').value;
	var email = document.getElementById('email').value;
	var active = document.getElementById('active').checked;
	var action = document.getElementById('button1').value;
		
	if(uname == ""){
		confirm('Please enter a valid user name');
		return;
	}
	
	if(passwd == ""){
		if(action == "Add User"){
			alert("Please enter a valid password for user " + uname);
			return;
		}else{
			var response = confirm("Leaving the password field blank\n"
									+ "will NOT update the password for user " + uname + "\n"
									+ "Are you sure you wish to proceed?");
			if(!response)
				return;
		}
	}
	
	if(action =="Add User" && !check_uname(uname)){
		alert("A user with user name <" 
					+ uname 
					+ "> already exists.\n"
					+ "Please choose a different user name"
				);
		return;
	}
	
	create_http_request();
	// create parameters to be sent
	// to php target based on whether
	// the user is adding a user
	// or resetting a password
	var post_params;

	if(action == "Add User" ){
		post_params = "ajax_action=add_user&ajax_uname=" + uname 
						+ "&ajax_passwd=" + passwd
						+ "&ajax_fname=" + fname
						+ "&ajax_lname=" + lname
						+ "&ajax_email=" + email
						+ "&ajax_active=" + active;
		send_http_request(xmlHttp_handle_ulist_populate, "POST", target_url, post_params);
	}else if(action == "Save"){
		var user_list = document.getElementById('user_list');
		var uid = user_list.options[user_list.selectedIndex].value;
		post_params = "ajax_action=update_user&ajax_uid=" + uid
					+ "&ajax_fname=" + fname
					+ "&ajax_lname=" + lname
					+ "&ajax_email=" + email
					+ "&ajax_active=" + active;
		// check if a new password was entered
		if(passwd != "")
			post_params += "&ajax_newpasswd=" + passwd;
			
		send_http_request(ajax_cleanup, "POST", target_url, post_params);
	}
}

///
/// ajax_update_user()
/// query for the user details so that
/// the user can change attributes
///
function ajax_update_user(){
	var user_list = document.getElementById('user_list');
	var uname = user_list.options[user_list.selectedIndex].innerHTML;
	var uid = user_list.options[user_list.selectedIndex].id;
	// cleanup fields before repopulating
	// with new data
	cleanup();
	document.getElementById('button1').value = "Save";
	create_http_request();
	post_params = "ajax_action=get_user_details"
				+ "&ajax_uid=" +  uid;
	send_http_request(handler_ajax_update_user, "POST", target_url, post_params);
}

///
/// handler_ajax_update_user()
/// populate fields from xml data.
/// schema:
///	<user>
///		<uname> alim </uname>
///		<fname> alim </fname>
///		<lname> karim </lname>
///		<email>	karima@unbc.ca </email>
/// </user>
///
function handler_ajax_update_user(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		create_xml_doc(xmlHttp.responseText);
		var uname = document.getElementById('uname');
		var fname = document.getElementById('fname');
		var lname = document.getElementById('lname');
		var email = document.getElementById('email');
		var active = document.getElementById('active');
		// note there is no check for 
		// childnodes for the uname
		// element, because each user 
		// MUST have a username and therefore 
		// a textnode must exist as the child
		// of uname.
		uname.value = xmlDoc.getElementsByTagName("uname")[0].childNodes[0].nodeValue;
		// set first name
		if(xmlDoc.getElementsByTagName("fname")[0].childNodes.length > 0)
			fname.value = xmlDoc.getElementsByTagName("fname")[0].childNodes[0].nodeValue;
		// set last name
		if(xmlDoc.getElementsByTagName("lname")[0].childNodes.length > 0)
			lname.value = xmlDoc.getElementsByTagName("lname")[0].childNodes[0].nodeValue;
		// set email
		if(xmlDoc.getElementsByTagName("email")[0].childNodes.length > 0)
			email.value = xmlDoc.getElementsByTagName("email")[0].childNodes[0].nodeValue;
		// set active checkbox. no need to check for
		// length of child nodes, because we are 
		// guaranteed to have a value for this
		// attribute
		if(xmlDoc.getElementsByTagName("active")[0].childNodes[0].nodeValue == "true")
			active.checked = true;
	}
}
///
/// xmlHttp_handle_ulist_populate()
/// this is the handler that is called when the
/// user has supplied a partial user name. it
/// recieves the xml that represents the list
/// of users
///
function xmlHttp_handle_ulist_populate(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_list_from_xml(xmlHttp.responseText, 'user_list');
		cleanup();
	}
}

///
/// populate_list_from_xml()
/// take the xml arg and populate a select
/// list with options.
/// schema:
///	<objects>
///		<object>
///			<id> 1 </id>
///			<name> abcd </name>
///		</object>
///		...
///	</objects>
///
function populate_list_from_xml(xml, list_id){
	var list = document.getElementById(list_id);
	list.options.length = 0;
	create_xml_doc(xml);
	var objects = xmlDoc.getElementsByTagName("object");
	for(var i=0; i < objects.length; i++){
		// note that objects[i].childNodes[0]
		// gives us the <id> node. the way the DOM
		// works, the <id> node then has a text node
		// child, which is why we need to go one more
		// level down before getting the node value.
		var id = objects[i].childNodes[0].childNodes[0].nodeValue;
		var value = objects[i].childNodes[1].childNodes[0].nodeValue;
		list.options[i] = new Option(value, id);
		list.options[i].id = id; // need to set the id explicitly 
	}
}


///
/// ajax_cleanup()
/// call local cleanup function
///
function ajax_cleanup(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		cleanup();
	}
}

///
/// check_uname()
/// check if a user with uname already
/// exists
///
function check_uname(uname){
	var user_list = document.getElementById('user_list');
	var n_users = user_list.options.length;
	for(var i = 0; i < n_users; i++)
		if(user_list.options[i].innerHTML == uname)
			return false;
	return true;
}

///
/// cleanup()
/// reset button and fields to original state
///
function cleanup(){
		// reset button text
		document.getElementById('button1').value = "Add User";
		// clear fields
		document.getElementById('uname').value = "";
		document.getElementById('password').value = "";
		document.getElementById('fname').value = "";
		document.getElementById('lname').value = "";
		document.getElementById('email').value = "";
		document.getElementById('active').checked = false;
}
