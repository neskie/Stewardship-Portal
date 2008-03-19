// globals
var target_url = "tng_search_submissions_code.php";

function expand_collapse(id){
	if(document.getElementById(id).style.display == "none"){
		document.getElementById('detail_button').value = "Simple Search";
		document.getElementById(id).style.display = 'block';
	}else{
		document.getElementById('detail_button').value = "Detailed Search";
		document.getElementById(id).style.display = 'none';			
	}
}

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
/// clear_list()
/// clear the contents of a list
///
function clear_list(list_id){
	var list = document.getElementById(list_id);
	// clear the list
	while(list.childNodes.length != 0)
		list.removeChild(list.firstChild);
}

///
/// escape_quotes()
/// escape single quotes by replacing
/// ' with ''
///
function escape_quotes(str){
	return str.replace(/'/g, "''");
}

///
/// type_toggled()
/// called when the checkbox is 
/// checked/unchecked
///
function type_toggled(){
	if(!document.getElementById('chk_sub_type').checked)
		clear_list('spec_field_list');
}

///
/// populate_results()
/// populate results table
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
function populate_results(xml, table_id){
	// make div visible
	document.getElementById('search_results').style.display = 'block';
	var table = document.getElementById(table_id);
	clear_table('tbl_search_res');
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

//--------------------------------------------------------------------------
// ajax functions
//--------------------------------------------------------------------------

///
/// ajax_refresh_lists()
/// send call to clear all lists
/// stored in session variable
///
function ajax_refresh_lists(){
	create_http_request();
	var post_params = "ajax_action=refresh_lists";
	send_http_request(handler_populate_sub_type, "POST", target_url, post_params);
}

///
/// ajax_populate_lists()
/// being populating drop down lists
///
function ajax_populate_lists(){
	// refresh lists first
	ajax_refresh_lists();
	ajax_populate_sub_type();
	// the rest of the list populating calls
	// are made from the handlers.
}

///
/// ajax_populate_sub_type()
/// populate the list holding the submission
/// types
///
function ajax_populate_sub_type(){
	create_http_request();
	var post_params = "ajax_action=get_sub_types";
	send_http_request(handler_populate_sub_type, "POST", target_url, post_params);
}

///
/// ajax_populate_sub_status()
/// populate the list holding the submission
/// statuses
///
function ajax_populate_sub_status(){
	create_http_request();
	var post_params = "ajax_action=get_sub_statuses";
	send_http_request(handler_populate_sub_status, "POST", target_url, post_params);
}

///
/// ajax_populate_sub_asignee()
/// populate the list holding the submission
/// asignees
///
function ajax_populate_sub_asignee(){
	create_http_request();
	var post_params = "ajax_action=get_sub_asignees";
	send_http_request(handler_populate_sub_asignee, "POST", target_url, post_params);
}

///
/// ajax_populate_sub_users()
/// populate the list holding the users
/// that have made a submission
///
function ajax_populate_sub_users(){
	create_http_request();
	var post_params = "ajax_action=get_sub_users";
	send_http_request(handler_populate_sub_user, "POST", target_url, post_params);
}

///
/// ajax_populate_spec_field()
/// send request to get all searchable fields 
/// when the type of submission is selected
///
function ajax_populate_spec_field(){
	create_http_request();
	var type_list = document.getElementById('type_list');
	var form_id = type_list.options[type_list.selectedIndex].id;
	var post_params = "ajax_action=get_spec_field&form_id=" + form_id;
	send_http_request(handler_populate_spec_field, "POST", target_url, post_params);
}

///
/// ajax_simple_search()
/// conduct a simple search of submissions
/// when the ID is known.
///
function ajax_simple_search(){
	var sub_id = document.getElementById('sub_id').value;
	// make sure the ID entered is
	// a valid number
	if(!/^\d+$/.test(sub_id)){
		alert("Please enter a valid number for the Submission ID");
		return;
	}
	var where_clause = "WHERE vi_submission_search.sub_id = " + sub_id;
	create_http_request();
	var post_params = "ajax_action=perform_search&where_clause=" + where_clause; 
	send_http_request(handler_search_results, "POST", target_url, post_params);
}

///
/// ajax_detailed_search()
/// retrieve parameters for a detailed search
/// and send an ajax request for that.
function ajax_detailed_search(){
	// only search parent submissions
	var where_clause = "WHERE pid = -1 ";
	// lists
	var type_list = document.getElementById('type_list');
	var status_list = document.getElementById('status_list');
	var submitted_list = document.getElementById('user_list');
	var asignee_list = document.getElementById('asignee_list');
	var spec_field_list = document.getElementById('spec_field_list');

	// checked options
	var include_name = document.getElementById('chk_sub_name').checked;
	var include_type = document.getElementById('chk_sub_type').checked;
	var include_status = document.getElementById('chk_sub_status').checked;
	var include_user = document.getElementById('chk_sub_user').checked;
	var include_asignee = document.getElementById('chk_sub_asignee').checked;
	var include_spec_field = document.getElementById('chk_spec_field').checked;
	
	// obtain all possible values of search
	// items 
	var sub_name = escape_quotes(document.getElementById('sub_name').value);
	var type_id = type_list.options[type_list.selectedIndex].id;
	var status_id = status_list.options[status_list.selectedIndex].id;
	var uid_submitted = submitted_list.options[submitted_list.selectedIndex].id;
	var uid_asignee = asignee_list.options[asignee_list.selectedIndex].id;
	var spec_field_id = null;
	if(spec_field_list.options.length > 0)
		spec_field_id = spec_field_list.options[spec_field_list.selectedIndex].id;
		
	// check which options are included
	// and append them to the where clause
	if(include_name && sub_name != "")
		where_clause += "AND sub_name LIKE '%" + sub_name + "%' ";
	if(include_type && type_id != -1){
		where_clause += "AND sub_type_id = " + type_id + " ";
	}
	if(include_status && status_id != -1){
		where_clause += "AND status_id = " + status_id + " ";
	}
	if(include_user && uid_submitted != -1){
		where_clause += "AND uid_submitted = " + uid_submitted + " ";
	}		
	if(include_asignee && uid_asignee != -1){
		where_clause += "AND uid_assigned = " + uid_asignee + " ";
	}
	if(include_spec_field && spec_field_id != null){
		var spec_field_value = escape_quotes(document.getElementById('spec_field_value').value);
		where_clause += "AND field_id = " + spec_field_id + " "
					+ "AND field_value LIKE '%" + spec_field_value + "%' "
	}
	
	create_http_request();
	var post_params = "ajax_action=perform_search&where_clause=" + where_clause; 
	send_http_request(handler_search_results, "POST", target_url, post_params);
}
// end ajax functions

// -----------------------------------------------------------------------
// ajax handlers

///
/// handler_populate_sub_type()
/// call method to populate type list.
/// once this is done, call method to send
/// request for the submission statuses
///
function handler_populate_sub_type(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_list_from_xml(xmlHttp.responseText, 'type_list');
		ajax_populate_sub_status();
	}
}

///
/// handler_populate_sub_status()
/// call method to populate status list.
/// once this is done, call method to send
/// request for the submission asignees
///
function handler_populate_sub_status(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_list_from_xml(xmlHttp.responseText, 'status_list');
		ajax_populate_sub_asignee();
	}
}

///
/// handler_populate_sub_asignee()
/// call method to populate status list.
/// once complete, call method to send 
/// req for submission users
///
function handler_populate_sub_asignee(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_list_from_xml(xmlHttp.responseText, 'asignee_list');
		ajax_populate_sub_users();
	}
}

///
/// handler_populate_sub_user()
/// call method to populate status list.
///
function handler_populate_sub_user(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_list_from_xml(xmlHttp.responseText, 'user_list');
	}
}

///
/// handler_populate_spec_field()
/// call method to populate specific
/// field list
///
function handler_populate_spec_field(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_list_from_xml(xmlHttp.responseText, 'spec_field_list');
	}
}

///
/// handler_search_results()
/// call method to populate the results table
///
function handler_search_results(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		populate_results(xmlHttp.responseText, 'tbl_search_res');
	}
}

// end ajax handlers
// -----------------------------------------------------------------------
