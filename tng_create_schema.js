var target_url = "tng_create_schema_code.php";
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
	
	var end_tag = document.createElement('br');
	//new_div.appendChild(end_tag);
	end_tag = document.createElement('br');
	par.appendChild(end_tag);
	par.appendChild(end_tag);
	
	var dt_label = document.createElement('label');
	par.appendChild(dt_label);
	dt_label.setAttribute('style', 'width:150px;');
	dt_label.style.cssText = 'width:100px;';
	dt_label.innerHTML = "Field Data Type:";
	
	var data_type = document.createElement('select');
	data_type.setAttribute('style', 'width:100px;');
	data_type.style.cssText =  'width:100px;';
	// int
	var t_int = document.createElement('option');
	t_int.setAttribute('value', 'integer');
	t_int.innerHTML = "integer";
	data_type.appendChild(t_int);
	// double
	var t_double = document.createElement('option');
	t_double.setAttribute('value', 'double precision');
	t_double.innerHTML = "double precision";
	data_type.appendChild(t_double);
	// varchar
	var t_varchar = document.createElement('option');
	t_varchar.setAttribute('value', 'varchar');
	t_varchar.innerHTML = "string";
	data_type.appendChild(t_varchar);
	
	par.appendChild(data_type);
	
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

function delete_field(field_id){
	var field_div = document.getElementById('fields');
	var delete_div = document.getElementById(field_id);
	field_div.removeChild(delete_div);
}

///
/// ajax_check_schema_name()
/// check for schema with existing
/// name. the handler for the
/// request calls the method to create
/// the schema if the name check returns
/// successfully
///
function ajax_check_schema_name(){
	var s_name = document.getElementById('schema_name').value;
	if(!check_special_chars(s_name)){
		alert("Please enter a valid name for this schema.\n"
			+ "The name cannot be blank or contain the\n"
			+ "characters [!, @, #, $, %, ^, &, *, (, ), <, >, ?, /]"
			);
		return;
	}
	
	var post_params = "ajax_action=check_schema_name&schema_name=" + s_name;
	create_http_request();
	send_http_request(handler_check_schema_name, "POST", target_url, post_params);
	//alert(params);
}

///
/// handler_check_schema_name()
/// see if the name check for the schema
/// was successful. if so, then create
/// another request to actually create
/// the schema
///
function handler_check_schema_name(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		var schema_name = document.getElementById('schema_name').value;
		if(xmlHttp.responseText == "true"){
			alert("A schema with name <" 
				+ schema_name
				+ "> already exists.\nPlease choose a different name");
		}else{
			// otherwise there is no existing schema with
			// the same name. proceed to create the schema
			var params = ajax_create_params();
			if(params != ""){
				var post_params = "ajax_action=create_schema&" + params;
				alert(post_params);
				create_http_request();
				send_http_request(handler_schema_success, "POST", target_url, post_params);
			}
		}
	}
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
	var schema_name = document.getElementById('schema_name').value;
	var geom_type = document.getElementById('geom_type').value;
	// get parent div
	var field_div = document.getElementById('fields');
	// get all child divs belonging
	// to parent div
	var child_divs = field_div.getElementsByTagName('div');
	
	params = "schema_name=" + schema_name + "&"
			+ "geom_type=" + geom_type + "&"
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
	// get two elements back, the field_name and
	// the button (delete). since the button is added
	// AFTER field_name, [0] is the index we want.
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
			alert("Attribute name <" + f_name + "> has been used more than once.\n"
				+ "Please make sure all attribute names are unique");
			return "";
		}
		
		field_names = field_names + ":" + f_name;
		
		params = params + "field_" + i + "_name=" + f_name + "&";
		params = params + "field_" + i + "_type=" + child_divs[i].getElementsByTagName('select')[0].value;
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
///
/// handler_schema_success()
/// Display message to show if schema
/// was created successfully
///
function handler_schema_success(){
	if(xmlHttp.readyState == 4 && xmlHttp.status == 200){
		alert("in success handler " + xmlHttp.responseText);
	}
}
