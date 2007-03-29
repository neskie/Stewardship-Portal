<!--
author:			alim karim

date:			march 23, 2007

description:	stylesheet to be applied to xml
				data representing submissions.
				each submission can have zero or
				more files and/or layers.
				our goal is to group the submissions,
				files and layers and into collapsed 
				divs, which the user can then click on 
				and expand.
				
				the names of the files and the names of
				the layers should appear as links which
				the user can click to download the 
				appropriate file/layer. this is done by
				adding a javascript function which interfaces
				between the link that is clicked and the
				form submission.
				
				the current schema of the xml representing the
				submissions is:
				
				<submissions>
					<submission>
						<id> 123 				</id>
						<user> default			</user>
						<date> jan 12th 2007	</date>
						<file>
							<fid>	45					</fid>
							<fname> abcd.pdf			</fname>
							<path> /home/abcd/abcd.pdf	</path>
						</file>
						<layer>
							<lid>	861 			<lid>
							<lname>	latlong.shp		</lname>
							<view>	vi_forest_poly	<view>
						</layer>
					</submission>
					<submission>
						...
					</submission>
				</submissions>
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output indent="yes" method="html"/>
	<xsl:template match="/">
		<ul>
			<xsl:apply-templates/> 
		</ul>
	</xsl:template> <!-- match / -->

	<!-- submission template -->
	<xsl:template match="submission">
		<li>
			<img src="images/right_arrow.gif" width="9" height="9" border="0">
				<xsl:attribute name="onClick">
					<xsl:value-of select='concat("expand_collapse(", "&apos;", id, "&apos;", ");")'/>
				</xsl:attribute>
					
				<b>
						Submission ID: 
						<xsl:value-of select="id"/> 
						, User: 
						<xsl:value-of select="user"/>
						, Date:
						<xsl:value-of select="date"/>
				  
				</b>
			</img>
			<div style="display:none;"> <!-- start main div -->
				<xsl:attribute name="id">
					<xsl:value-of select='id'/>
				</xsl:attribute>
		
				<br/>
				<!-- create click-able link for fields section -->
				<!-- produce something like: -->
				<!-- <img onClick="submit_form('fields', 12)"> files </img> -->
				<img src="images/right_arrow.gif" width="9" height="9" border="0">	
					<xsl:attribute name="onClick">
						<xsl:value-of select='concat("javascript:submit_form(", "&apos;" , "form", "&apos;", ",", "&apos;" , id, "&apos;", ");")'/>
					</xsl:attribute>
					Form Data
				</img>
				<br/>
				<br/>
				<!-- create click-able link for file section -->
				<!-- produce something like: -->
				<!-- <img onClick="expand_collapse('45_files')"> files </a> -->
				<img src="images/right_arrow.gif" width="9" height="9" border="0">	
					<xsl:attribute name="onClick">
						<xsl:value-of select='concat("expand_collapse(", "&apos;" , id, "_files", "&apos;", ");")'/>
					</xsl:attribute>
					Files:
				</img>
				<br/>
				<div style="display:none;"> <!-- start files div -->
					<xsl:attribute name="id">
						<xsl:value-of select='concat(id, "_files")'/>
					</xsl:attribute>
					<ul>
						<xsl:apply-templates select="file"/>
					</ul>
				</div> <!-- end files div -->
				<br/>
				<!-- create click-able link for layer section -->
				<!-- produce something like: -->
				<!-- <img onClick="expand_collapse('45_layers')"> files </a> -->
				<img src="images/right_arrow.gif" width="9" height="9" border="0">
					<xsl:attribute name="onClick">
						<xsl:value-of select='concat("expand_collapse(", "&apos;" , id, "_layers", "&apos;", ");")'/>
					</xsl:attribute>
					Layers:
				</img>
				<br/>
				<div style="display:none;"> <!-- start layers div -->
					<xsl:attribute name="id">
						<xsl:value-of select='concat(id, "_layers")'/>
					</xsl:attribute>
					<ul>
						<xsl:apply-templates select="layer"/>
					</ul>
				</div> <!-- end layers div -->
			</div> <!-- end main div -->
		</li>
		<hr/>
	</xsl:template> 
	<!-- end submission template -->

	<!-- file template -->
	<xsl:template match="file">
		<li>
			<a>
				<!-- produce something like href="javascript:submit_form('file','68') -->
				<xsl:attribute name="href">
					<xsl:value-of select='concat("javascript:submit_form(", "&apos;" , "file", "&apos;", ",", "&apos;" , fid, "&apos;", ");")'/>
				</xsl:attribute>
				<xsl:value-of select="fname"/>
			</a>
		</li>
	</xsl:template> 
	<!-- end file template -->
	
	<!-- layer template -->
	<xsl:template match="layer">
		<li>
			<a>
				<!-- produce something like href="submit_form('layer','68') -->
				<xsl:attribute name="href">
					<xsl:value-of select='concat("javascript:submit_form(", "&apos;" , "layer", "&apos;", ",", "&apos;" , lid, "&apos;", ");")'/>
				</xsl:attribute>
				<xsl:value-of select="lname"/>
			</a>
		</li>
	</xsl:template> 
	<!-- end layer template -->
</xsl:stylesheet>