<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="style.css" type="text/css" />
<title>Stewardship Portal</title>

<!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.thrColHybHdr #sidebar1, .thrColHybHdr #sidebar2 { padding-top: 30px; }
.thrColHybHdr #mainContent { zoom: 1; padding-top: 15px; }
/* the above proprietary zoom property gives IE the hasLayout it needs to avoid several bugs */
</style>
<![endif]--></head>

<body class="thrColHybHdr">

<div id="container">
  <div id="header">
    <?php include_once('top_div.html'); ?>
  </div>
  <!-- end #header -->
  
  <div id="sidebar1">
    <?php include_once('links_no_login.html');?>
  </div>  
  <!-- end #sidebar1 -->
  
  <div id="sidebar2">
    <p><span class="subHeader">Portal Access</span><br /></p>
    <p class="smallText">
		A user account is needed to log into the Stewardship Portal. 
        To acquire a username and password, please send an email 
        to:<a href="mailto:tsdgis@tsilqotin.ca">Portal Administrator</a></p>
			
    <p><span class="subHeader">TITLE HERE</span><br />
		Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam. </p>

	<p><span class="subHeader">TITLE HERE</span><br />
		Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam. </p>
        
  </div>
  <!-- end #sidebar2 -->
  <div id="mainContent">
    <h1 class="pageName"> Stewardship Portal </h1>
    <p class="bodyText">
    The Stewardship Department of Tsilhqot'in National Government 
    is developing and interactive land and resource &quot;Stewardship Portal.&quot; 
    The Stewardship Portal is a web-based land-use information management and 
    planning support system.
    </p>

	<p class="bodyText">
    A primary function of the Stewardship Portal is to improve the efficiency 
    and quality of the referral process. Many of the tedius steps of the 
    referral tracking/filing and land-use planning (e.g. mapping protected areas) 
    are simplified through the automated functions of our spatial database. 
    The database stores any type of land and resource infromation from reports 
    to photos in relation to the area of land for which they are relevant.
    </p>
	
     <p class="bodyText">
     By making the power of Geographic Information Systems (GIS) 
     accessible (cost-effective and user-friendly) for remote communities 
     and minimizing paper handling time. The Portal is a significant step toward 
     empowing First Nations communiies to be directly involved in the land and 
     resource stewardship.
     </p>
          
   </div>
	<!-- end #mainContent -->
	<!-- This clearing element should immediately follow 
    the #mainContent div in order to force the 
    #container div to contain all child floats -->
   <br class="clearfloat" />
   <div id="footer">
    <p>Footer</p>
   </div>
  <!-- end #footer -->
  </div>
<!-- end #container -->
</body>
</html>
