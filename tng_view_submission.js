var target_url = "tng_view_submission_code.php";

///
/// populate_list_from_xml()
/// take the xml arg and populate a select
/// list with options.
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
/// populate_list_from_xml()
/// take the xml arg and populate a select
/// list with options.
///
function populate_ordered_list_from_xml(xml, list_id, type){
	var list = document.getElementById(list_id);
	while(list.firstChild != null)
		list.removeChild(list.firstChild);
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
		var li = document.createElement("li");
		li.id = id;
		var a = document.createElement("a");
		var get_params = "type=" + type + "&id=" + id;
		a.setAttribute('href', 'tng_download_sub_file.php?' + get_params);
		a.innerHTML = value;
		li.appendChild(a);
		list.appendChild(li);
	}
}

///
/// check_special_chars()
/// check if a string contains
/// special characters
///
function check_special_chars(attribute){
	if(attribute == ""
		|| attribute.indexOf(' ') != -1
		|| attribute.indexOf('!') != -1
		|| attribute.indexOf('@') != -1
		|| attribute.indexOf('#') != -1
		|| attribute.indexOf('$') != -1
		|| attribute.indexOf('%') != -1
		|| attribute.indexOf('^') != -1
		|| attribute.indexOf('&') != -1
		|| attribute.indexOf('*') != -1
		|| attribute.indexOf('(') != -1
		|| attribute.indexOf(')') != -1
		|| attribute.indexOf('<') != -1
		|| attribute.indexOf('>') != -1
		|| attribute.indexOf('?') != -1
	)
		return false;
	else
		return true;
}

//
/// populate_children()
/// populate ammendments table
/// from xml data. 
/// schema:
///	<submissions>
///		<submission>
///			<sub_id> 122 </sub_id>
///			<sub_type> Forestry Referral </sub_type>
///			<sub_titile>  122 - Forestry Referral - John Smith </sub_title>
///			<sub_name> a0093 - Tolko - John Smith - t567 </sub_name>
///			<sub_status> New </sub_status>
///			<submitted_by> John Smith </submitted_by>
///			<assigned_to> Mary Thurow </assigned_to>
///			<sub_date> May 20, 2007 </sub_date>
///		</submission
///		...
/// </submissions>
///
function populate_children(xml, table_id){
	var table = document.getElementById(table_id);
	clear_table(table_id);
	//table.rows.length = 0;
	create_xml_doc(xml);
	var objects = xmlDoc.getElementsByTagName("submission");
	for(var i=0; i < objects.length; i++){
		var row = table.insertRow(i+1);
		// id cell
		var cell = row.insertCell(0);
		cell.setAttribute('class', 'td_search');
		var id = objects[i].childNodes[0].childNodes[0].nodeValue;
		// create a link, which when clicked 
		// goes to tng_view_submission.php
		// with a sub_id parameter
		elt = document.createElement('a');
		elt.setAttribute('href', 'tng_view_submission.php?sub_id=' + id);
		elt.innerHTML = id;
		cell.appendChild(elt);
		// remaining cells
		for(var j = 1; j < 8; j++){
			cell = row.insertCell(j);
			cell.setAttribute('class', 'td_search');
			elt_value = "";
			if(objects[i].childNodes[j].childNodes.length > 0)
				elt_value = objects[i].childNodes[j].childNodes[0].nodeValue
			elt = document.createTextNode(elt_value);
			cell.appendChild(elt);
		}		
	}
}

///
/// clear_table()
/// delete all rows except the header
/// row from the given table.
///
function clear_table(table_id){
	var table = document.getElementById(table_id);
	var n_rows = table.rows.length;
	while(table.rows.length > 1)
		table.deleteRow(table.rows.length - 1);
}

///
/// set_list_index()
/// find the option in the list that matches
/// elt_value and set the index of the list
/// to that item
///
function set_list_index(list_id, elt_value){
	var list = document.getElementById(list_id);
	for(var i = 0; i < list.options.length; i++){
		if(list.options[i].innerHTML == elt_value){
			list.selectedIndex = i;
			break;
		}
	}
}

///
/// change_sub_perm()
/// navigate to the page which allows the
/// administrator to change the permissions
/// of the current submission.
///
function change_sub_perm(sub_id){
	// issue ajax request to set the session
	// variable which is checked on the
	// assign_sub_perm page. once the
	// request completes, redirect to the
	// assign_sub_perm page		
	new Ajax.Request(target_url, 
					{
  					method:'post',
  					contentType: "application/x-www-form-urlencoded",
  					parameters: {ajax_action: 'set_assign_perm_session'},
  					requestHeaders: {Accept: 'text/html'}, 
					onSuccess: function(transport){
    								window.location = "tng_assign_sub_perm.php?sub_id=" + sub_id;
 								},
  					onFailure: function (transport) { alert ("an error occurred."); }			
					});
}

///
/// ajax_refresh_lists()
/// send call to clear all lists
/// stored in session variable
///
function ajax_refresh_lists(){
	create_http_request();
	var post_params = "ajax_action=refresh_lists";
	send_http_request(null, "POST", target_url, post_params);
}

///
/// ajax_populate_assignee_list()
/// make a request for a list of asignees
///
function ajax_populate_assignee_list(){
	create_http_request();
	var post_params = "ajax_action=get_sub_asignees";
	send_http_request(handler_populate_assignee_list, "POST", target_url, post_params);
}

///
/// ajax_populate_status_list()
/// make a request for a list of statuses
///
function ajax_populate_status_list(){
	create_http_request();
	var post_params = "ajax_action=get_sub_statuses";
	send_http_request(handler_populate_status_list, "POST", target_url, post_params);
}

///
/// ajax_get_sub_details()
/// request for the details of the submission
///
function ajax_get_sub_details(){
	create_http_request();
	var post_params = "ajax_action=get_sub_details&sub_id=" + sub_id;
	send_http_request(handler_get_sub_details, "POST", target_url, post_params);
}

///
/// ajax_get_sub_files()
/// request for the files belonging
/// to the submission
///
function ajax_get_sub_files(){
	create_http_request();
	var post_params = "ajax_action=get_sub_files&sub_id=" + sub_id;
	send_http_request(handler_get_sub_files, "POST", target_url, post_params);
}

///
/// ajax_get_sub_layers()
/// request for the layers belonging
/// to the submission
///
function ajax_get_sub_layers(){
	create_http_request();
	var post_params = "ajax_action=get_sub_layers&sub_id=" + sub_id;
	send_http_request(handler_get_sub_layers, "POST", target_url, post_params);
}

///
/// ajax_get_sub_children()
/// get the child submissions of this
/// submission.
function ajax_get_sub_children(){
	create_http_request();
	var post_params = "ajax_action=get_sub_children&sub_id=" + sub_id;
	send_http_request(handler_get_sub_children, "POST", target_url, post_params);
}

///
/// ajax_dislpay_form()
/// send request to display the 
/// form that was filled when the submission
/// was made. 
///
function ajax_dislpay_form(){
	create_http_request();
	var post_params = "ajax_action=display_form&sub_id=" + sub_id;
	send_http_request(handler_ajax_dislpay_form, "POST", target_url, post_params);
}

///
/// ajax_update_name() 
/// update the name assigned to a submission
///
function ajax_update_name(){
	var new_name = document.getElementById('sub_name').value;
	if(!check_special_chars(new_name)){
		alert('The name cannot contain special characters');
	}
	if(new_name != ""){
		create_http_request();
		var post_params = "ajax_action=update_sub_name&sub_id=" + sub_id + "&sub_name=" + new_name;
		send_http_request(null, "POST", target_url, post_params);
	}
}

///
/// ajax_update_status()
/// update the status of a submission
///
function ajax_update_status(){
	var status_list = document.getElementById('status_list');
	var status_id = status_list.options[status_list.selectedIndex].id;
	create_http_request();
	var post_params = "ajax_action=update_sub_status&sub_id=" + sub_id + "&status_id=" + status_id;
	send_http_request(null, "POST", target_url, post_params);
}

///
/// ajax_updated_assignee()
/// update the asignee
///
function ajax_update_assignee(){
	var asignee_list = document.getElementById('asignee_list');
	var uid = asignee_list.options[asignee_list.selectedIndex].id;
	create_http_request();
	var post_params = "ajax_action=update_sub_asignee&sub_id=" + sub_id + "&uid_assigned=" + uid;
	send_http_request(null, "POST", target_url, post_params);
}

//-----------------------------------------------------------------
// ajax handlers
///
/// handler_populate_assignee_list()
/// retrieve the xml sent back and call method to
/// populate the asignee list from it.
///
function handler_populate_assignee_list(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_list_from_xml(xmlHttp.responseText, 'asignee_list');
		ajax_get_sub_details();
	}
	
}

///
/// handler_populate_status_list()
/// populate list of available statuses
///
function handler_populate_status_list(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_list_from_xml(xmlHttp.responseText, 'status_list');
		ajax_populate_assignee_list();
	}
}

///
/// handler_get_sub_details()
/// populate the details of this
/// submission from the xml. once complete,
/// call method to send request for the 
/// files associated with this submission.
/// schema:
///	<submissions>
///		<submission>
///			<sub_id> 122 </sub_id>
///			<sub_type> Forestry Referral </sub_type>
///			<sub_titile>  122 - Forestry Referral - John Smith </sub_title>
///			<sub_name> a0093 - Tolko - John Smith - t567 </sub_name>
///			<sub_status> New </sub_status>
///			<submitted_by> John Smith </submitted_by>
///			<assigned_to> Mary Thurow </assigned_to>
///			<sub_date> May 20, 2007 </sub_date>
///		</submission
///		...
/// </submissions>
///
function handler_get_sub_details(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		create_xml_doc(xmlHttp.responseText);
		// we should only have one
		// <submission> element.
		var submission = xmlDoc.getElementsByTagName("submission")[0];
		// set the ID
		document.getElementById('sub_id').value = 
								submission.childNodes[0].childNodes[0].nodeValue;
		// check if we have a type
		if(submission.childNodes[1].childNodes.length > 0)
			document.getElementById('sub_type').value = 
								submission.childNodes[1].childNodes[0].nodeValue;
		// check if we have a title
		if(submission.childNodes[2].childNodes.length > 0)
			document.getElementById('sub_title').value = 
								submission.childNodes[2].childNodes[0].nodeValue;
		// check if we have a name
		if(submission.childNodes[3].childNodes.length > 0)
			document.getElementById('sub_name').value = 
								submission.childNodes[3].childNodes[0].nodeValue;
		// submitted by:
		if(submission.childNodes[5].childNodes.length > 0)
			document.getElementById('sub_user').value = 
								submission.childNodes[5].childNodes[0].nodeValue;
		// status:								
		set_list_index('status_list', submission.childNodes[4].childNodes[0].nodeValue);
		// assigned to:
		if(submission.childNodes[6].childNodes.length > 0)
			set_list_index('asignee_list', submission.childNodes[6].childNodes[0].nodeValue);
		else
			set_list_index('asignee_list', 'unassigned');
		// date submitted:
		if(submission.childNodes[7].childNodes.length > 0)
			document.getElementById('sub_date').value = 
								submission.childNodes[7].childNodes[0].nodeValue;
		
		ajax_get_sub_files();
	}
}

///
/// handler_get_sub_files()
/// call method to populate an ordered
/// list from the xml that is returned.
/// once complete, call method to send 
/// request for layers.
///
function handler_get_sub_files(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_ordered_list_from_xml(xmlHttp.responseText, 'sub_files', 'file');
		ajax_get_sub_layers();
	}
}

///
/// handler_get_sub_layers()
/// call method to populate an ordered
/// list from the xml that is returned.
/// once complete, call method to send 
/// request for children.
///
function handler_get_sub_layers(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_ordered_list_from_xml(xmlHttp.responseText, 'sub_layers', 'layer');
		ajax_get_sub_children();
	}
}

///
/// handler_ajax_dislpay_form()
/// redirect to tng_display_form.php. the session
/// variables needed by that page should be
/// set up by the backend script.
///
function handler_ajax_dislpay_form(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		window.location = "tng_display_form.php";
	}
}

///
/// handler_get_sub_children()
/// populate table with children
///
function handler_get_sub_children(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_children(xmlHttp.responseText, 'tbl_search_res');
	}
}

//-----------------------------------------------------------------
