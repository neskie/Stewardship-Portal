var target_url = "tng_create_form_code.php";
var field_count = 0;

function add_field(){	 
	var field_div = document.getElementById('fields');
	
	var new_div = document.createElement('div');
	var par = document.createElement('p');
	par.setAttribute('class', 'bodyText');
	par.className = 'bodyText';
	new_div.appendChild(par);
	// again, to keep IE happy, append the
	// new div in the DOM before accessing
	// any of its properties.
	field_div.appendChild(new_div);
	
	new_div.setAttribute('id', 'f_' + field_count);
	new_div.style.position =  "relative";
	new_div.style.width =  "600px";
	new_div.style.padding =  "5px";
	//new_div.style.border = "1px dashed blue";
	new_div.style.marginLeft = "20px";
	new_div.style.marginBottom = "10px";
		
	var field_label = document.createElement('label');
	//field_label.setAttribute('style', 'margin-left:10px;position:relative;float:none;width:150px;');
	field_label.setAttribute('style', 'width:100px;');
	field_label.style.cssText = 'width:100px;';
	field_label.innerHTML = "Field Name:";
	par.appendChild(field_label);
		
	var field_name = document.createElement('input');
	par.appendChild(field_name);
	field_name.setAttribute('type', 'text');
	field_name.setAttribute('size', '45');
	
	par.appendChild(document.createElement('br'));
	par.appendChild(document.createElement('br'));
	
	var rank_label = document.createElement('label');
	//field_label.setAttribute('style', 'margin-left:10px;position:relative;float:none;width:150px;');
	rank_label.setAttribute('style', 'width:100px;');
	rank_label.style.cssText = 'width:100px;';
	rank_label.innerHTML = "Rank From Top:";
	par.appendChild(rank_label);
		
	var field_rank = document.createElement('input');
	field_rank.setAttribute('type', 'text');
	field_rank.setAttribute('size', '5');
	field_rank.value = field_count + 1;
	par.appendChild(field_rank);
	
	par.appendChild(document.createElement('br'));
	par.appendChild(document.createElement('br'));
	
	var dt_label = document.createElement('label');
	dt_label.setAttribute('style', 'width:150px;');
	dt_label.style.cssText = 'width:100px;';
	dt_label.innerHTML = "Field Data Type:";
	par.appendChild(dt_label);
	
	var data_type = document.createElement('select');
	data_type.setAttribute('style', 'width:100px;');
	data_type.style.cssText =  'width:100px;';
	// t_text
	var t_text = document.createElement('option');
	t_text.setAttribute('value', 'text');
	t_text.innerHTML = "text";
	data_type.appendChild(t_text);
	// t_label
	var t_label = document.createElement('option');
	t_label.setAttribute('value', 'label');
	t_label.innerHTML = "label";
	data_type.appendChild(t_label);
	// t_checkbox
	var t_checkbox = document.createElement('option');
	t_checkbox.setAttribute('value', 'checkbox');
	t_checkbox.innerHTML = "checkbox";
	data_type.appendChild(t_checkbox);
	// t_other
	var t_other = document.createElement('option');
	t_other.setAttribute('value', 'other');
	t_other.innerHTML = "other";
	data_type.appendChild(t_other);
	
	par.appendChild(data_type);
	par.appendChild(document.createElement('br'));
	par.appendChild(document.createElement('br'));
	
	var search_label = document.createElement('label');
	search_label.setAttribute('style', 'width:150px;');
	search_label.style.cssText = 'width:100px;';
	search_label.innerHTML = "Field Searchable:";
	par.appendChild(search_label);
	
	var chk_search = document.createElement('input');
	chk_search.setAttribute('type', 'checkbox');
	chk_search.setAttribute('style', 'margin-left:10px;');
	par.appendChild(chk_search);
	
	var delete_button = document.createElement('input');
	delete_button.setAttribute('type', 'button');
	delete_button.setAttribute('class', 'button');
	delete_button.setAttribute('style', 'margin-left:10px;');
	delete_button.setAttribute('value', 'Del');
	// for firefox
	delete_button.setAttribute('onClick', "javascript: delete_field('f_" + field_count + "')");
	// ok .. good old IE is being
	// a major pain in the butt when it
	// comes to setting the onClick 
	// attribute. apparently the only way
	// to do it is to add an inline function that
	// calls the function we wish to call.
	// pathetic, but oh well.
	delete_button.onclick = function(){delete_field(new_div.id);};
	par.appendChild(delete_button);
	
	field_count++;
}

///
/// delete_field()
/// delete field from list of fields
/// by removing the specific div
///
function delete_field(field_id){
	var field_div = document.getElementById('fields');
	var delete_div = document.getElementById(field_id);
	field_div.removeChild(delete_div);
}

///
/// ajax_check_form_name()
/// check for form with existing
/// name. the handler for the
/// request calls the method to create
/// the form if the name check returns
/// successfully
///
function ajax_check_form_name(){
	var f_name = document.getElementById('form_name').value;
	if(f_name == "" || !check_special_chars(f_name)){
		alert("Please enter a valid name for this form.\n"
			+ "The name cannot be blank or contain the\n"
			+ "characters [!, @, #, $, %, ^, &, *, (, ), <, >, ?, /]"
			);
		return;
	}
	
	var post_params = "ajax_action=check_form_name&form_name=" + f_name;
	create_http_request();
	send_http_request(handler_check_form_name, "POST", target_url, post_params);
}


///
/// ajax_create_params()
/// collect the field names and
/// data types. parse these into
/// a string that can be sent as a post
/// variable.
///
function ajax_create_params(){
	var params = "";
	var form_name = document.getElementById('form_name').value;
	// get parent div
	var field_div = document.getElementById('fields');
	// get all child divs belonging
	// to parent div
	var child_divs = field_div.getElementsByTagName('div');
	
	params = "form_name=" + form_name + "&"
			+ "n_fields=" + child_divs.length + "&";
	// loop through child divs and
	// obtain values for the
	// field names and the field types.
	// this is done by using the
	// getElementsByTagName function which returns
	// an array of child elements that match the tag
	// name provided to the function whose parent
	// is the calling object.
	// note that in the case of 'input', we will
	// get three elements back, the field_name,
	// the searchable checkbox and
	// the button (delete). since the button is added
	// AFTER field_name:
	// [0] -> field name
	// [1] -> rank
	// [2] -> searchable flag.
	// see add_field function for details.	
	var field_names = "";	
	for(var i = 0; i < child_divs.length; i++){
		var f_name = child_divs[i].getElementsByTagName('input')[0].value;
		// check special chars
		if(!check_special_chars(f_name)){
			alert("Attribute " + (i + 1) + " cannot contain spaces\n" 
				+ "or characters [!, @, #, $, %, ^, &, *, (, ), <, >, ?, /].");
			return "";
		}
		// check for duplicate field names
		if(field_names.indexOf(f_name) != -1){
			alert("Field name <" + f_name + "> has been used more than once.\n"
				+ "Please make sure all Field names are unique");
			return "";
		}
		// check if the number entered for
		// the rank is a valid integer/double prec.
		if(!/^\d+[\.\d]*$/.test(child_divs[i].getElementsByTagName('input')[1].value)){
			alert("Please enter a valid rank for Field <" + f_name + ">");
			return "";
		}
		// append to list of existing
		// names. this is then used to check
		// if a name has been used more than once
		field_names = field_names + ":" + f_name;
		
		params = params + "field_" + i + "_name=" + f_name + "&";
		params = params + "field_" + i + "_type=" + child_divs[i].getElementsByTagName('select')[0].value + "&";
		params = params + "field_" + i + "_rank=" + child_divs[i].getElementsByTagName('input')[1].value + "&";
		params = params + "field_" + i + "_searchable=" + child_divs[i].getElementsByTagName('input')[2].checked;
		if(i < child_divs.length - 1)
			params += "&";
	}
	return params;
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
		|| attribute.indexOf('/') != -1
	)
		return false;
	else
		return true;
}

//-------------------------------------------------------------------------
// ajax handlers
//-------------------------------------------------------------------------

///
/// handler_check_form_name()
/// see if the name check for the schema
/// was successful. if so, then create
/// another request to actually create
/// the schema
///
function handler_check_form_name(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		var form_name = document.getElementById('form_name').value;
		if(xmlHttp.responseText == "true"){
			alert("A Form with name <" 
				+ form_name
				+ "> already exists.\nPlease choose a different name");
		}else{
			// otherwise there is no existing form with
			// the same name. proceed to create the form
			var params = ajax_create_params();
			if(params != ""){
				var post_params = "ajax_action=create_form&" + params;
				create_http_request();
				send_http_request(handler_form_success, "POST", target_url, post_params);
			}
		}
	}
}

///
/// handler_form_success()
/// Display message to show if form
/// was created successfully
///
function handler_form_success(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		if(xmlHttp.responseText == "true")
			alert("The Form was created successfully.");
		else{
			alert("An error occurred while creating the form\n"
				+ xmlHttp.responseText);
		}
	}
}
//-------------------------------------------------------------------------