<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output indent="yes" method="html"/>
	<xsl:template match="/">
		<xsl:apply-templates/> 
	</xsl:template> <!-- match / -->

	<!-- layers template -->
	<xsl:template match="layers">
		<dl>
			<xsl:apply-templates select="layer"/>
		</dl>
	</xsl:template> 
	<!-- end layers template -->

	<!-- layer template -->
	<xsl:template match="layer">
		<dd>	
			<input type="checkbox" class="input_checkbox" onChange='javascript: ajax_post_selected(this.id)'>
				<xsl:attribute name="name">
					<xsl:value-of select='concat("layerid_", layer_id)'/>
				</xsl:attribute>
				<xsl:attribute name="id">
					<xsl:value-of select='concat("layerid_", layer_id)'/>
				</xsl:attribute>
				<xsl:if test="display = 'true'">
					<xsl:attribute name="checked"/>
				</xsl:if>
				<xsl:value-of select="layer_name"/>
			</input>
		</dd>
	</xsl:template> 
	<!-- end layer template -->
</xsl:stylesheet>