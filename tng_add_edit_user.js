
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
/// or save new passwd for 
/// existing user
///
function ajax_add_edit_user(){
	var uname = document.getElementById('uname').value;
	var passwd = document.getElementById('password').value;
	var action = document.getElementById('button1').value;
		
	if(uname == ""){
		alert('Please enter a valid user name');
		return;
	}
	
	if(passwd == ""){
		alert("Please enter a valid password for user " + uname);
		return;
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
		post_params = "ajax_action=add_user&ajax_uname=" + uname + "&ajax_passwd=" + passwd;
		send_http_request(xmlHttp_handle_ulist_populate, "POST", target_url, post_params);
	}else if(action == "Save"){
		var user_list = document.getElementById('user_list');
		var uid = user_list.options[user_list.selectedIndex].value;
		post_params = "ajax_action=reset_password&ajax_uid=" + uid + "&ajax_newpasswd=" + passwd;
		send_http_request(ajax_cleanup, "POST", target_url, post_params);
	}
}

///
/// ajax_reset_passwd()
/// reset password for the selected
/// user in the list. this does not
/// call the back end script, rather
/// it just moves the name of the user
/// into the uname field and changes
/// the text on the button beside
/// the password field.
///
function ajax_reset_passwd(){
	var user_list = document.getElementById('user_list');
	var uname = user_list.options[user_list.selectedIndex].innerHTML;
	document.getElementById('uname').value = uname;
	document.getElementById('button1').value = "Save";
}


///
/// xmlHttp_handle_uname_search()
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
}
