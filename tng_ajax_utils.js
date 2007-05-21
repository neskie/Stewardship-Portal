// global variables to this script block
var xmlHttp;
var xmlDoc;

///
/// create_http_request()
/// initialize the global xmlHttp variable
/// to the correct http request object based on
/// the browser type
///
function create_http_request(){
	try{
		// Firefox, Opera 8.0+, Safari
		xmlHttp=new XMLHttpRequest();
	}catch (e){
    	// Internet Explorer
    	try{
      		xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    	}catch (e){
      		try{
        		xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
      		}catch (e){
        		alert("Your browser does not support AJAX!");
        		return false;
       		}
     	}
   	}
}

///
/// create_xml_doc()
/// create xml document from
/// xml string
///
function create_xml_doc(xml_txt){
	if (window.ActiveXObject){
		xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
		xmlDoc.async = false;
		xmlDoc.loadXML(xml_txt)
	}
	else{
		try{
			var parser = new DOMParser();
			xmlDoc = parser.parseFromString(xml_txt, "text/xml");
		}catch (e){
			alert('Your browser can\'t handle this script');
			return;
		}
	}
}
///
/// send_http_request()
/// send a request to the target with
/// the given parameters. note that params
/// should be url encoded i.e. multiple params
/// should be separated by &.
/// handler is the name of the function to be executed
/// when the readystate of the xml http
/// request changes state.
/// method can be GET or POST.
///
function send_http_request(handler, method, target, params){
	// set the content type 
	// for POST to work as a method
	// when opening the xmlHttp request
	// to the server
	var content_type = "application/x-www-form-urlencoded; charset=UTF-8";
	xmlHttp.onreadystatechange = handler;
	xmlHttp.open(method, target,true);
	xmlHttp.setRequestHeader("Content-Type", content_type);
	xmlHttp.send(params);
}
