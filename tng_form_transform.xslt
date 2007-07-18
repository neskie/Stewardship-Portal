<!--

-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output indent="yes" method="html"/>
	<xsl:param name="readonly"/> <!-- parameter passed from php -->
	<xsl:template match="/">
		<xsl:apply-templates/> 
	</xsl:template> <!-- match / -->

	<!-- form template -->
	<xsl:template match="form">
		<h2>
			<xsl:value-of select="form_name"/> 
		</h2>
		<br/>
		<xsl:apply-templates select="field"/>
	</xsl:template> 
	<!-- end form template -->

	<!-- field template -->
	<xsl:template match="field">
		<label>
			<xsl:attribute name="class">
				<xsl:value-of select="field_label_css_class"/>
			</xsl:attribute>
			<xsl:value-of select="field_label"/>
		</label>
		<xsl:choose>
			<xsl:when test="field_type = 'text'">
				<textarea>
					<xsl:attribute name="name">
						<xsl:value-of select='concat("fieldid_", field_id)'/>
					</xsl:attribute>
					<xsl:attribute name="class">
						<xsl:value-of select="field_css_class"/>
					</xsl:attribute>
					<!-- check if the readonly parameter is set -->
					<xsl:if test="$readonly = 'true'">
						<xsl:attribute name="readonly">
							<xsl:value-of select="yes"/>
						</xsl:attribute>
					</xsl:if>
					<!-- print out the value of the field -->
					<xsl:value-of select="field_value"/>
				</textarea>
				<br/>
			</xsl:when>
			<xsl:when test="field_type = 'checkbox'">
				<input type="checkbox">
					<xsl:attribute name="name">
						<xsl:value-of select='concat("fieldid_", field_id)'/>
					</xsl:attribute>
					<xsl:attribute name="class">
						<xsl:value-of select="field_css_class"/>
					</xsl:attribute>
					<!-- check if the readonly parameter is set -->
					<xsl:if test="$readonly = 'true'">
						<xsl:attribute name="disabled">
							<xsl:value-of select="true"/>
						</xsl:attribute>
					</xsl:if>
					<!-- print out the value of the field -->
					<xsl:if test="field_value = 'on'">
						<xsl:attribute name="checked">
							<xsl:value-of select="1"/>
						</xsl:attribute>
					</xsl:if>
				</input>
				<br/>
			</xsl:when>
			<xsl:otherwise>
				<br/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template> 
	<!-- end field template -->
</xsl:stylesheet>