<!--
stylesheet for generating questions and options from
an xml document. 

each question has multiple options. each option has a
radio button beside it to indicate if that option
has been selected.

in order for the radio buttons to be grouped at the
question level, the question_id needs to be passed
to the 'options' template and lower to the 
'option' template. this can be accomplished by using
the xsl:param tag. note that xsl:call-template
could be used instead of xsl:apply-templates 
but xsl:call-template DOES NOT change the current
node i.e. it does not step into the next level
in the xml tree. thus to use this, a loop
would be needed in the 'options' template and
appropriate xpath expressions would need to be
coded in the 'option' template.

some questions require that users enter in some
text to justify their answers, others require
a rank to be entered beside each option.

the elements generated need to have their
'name' attribute formatted in a certain way
in order for the aspx page to be able to parse
them through the 'request' object when the form
is submitted.

general convention for names is as follows:

(i) qnid_xx_y => for questions with only one answer
(type 3 and 4)

(ii) qnid_xx_y_txt => for questions with only one answer
and text associated with that answer
(type 5)

(iii) qnid_xx_y_opid_zz for questions with multiple answers
such as ranks for each respective opion
(type 6)

in the above strings,
xx = qnid
y = typeid
zz = opid

-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output indent="yes" method="html"/>
	<xsl:template match="/">
		<xsl:apply-templates/>
		<!-- generate page body -->
	</xsl:template> <!-- match / -->

	<!-- page template -->
	<xsl:template match="page">
		Page: 
		<xsl:value-of select="page_id"/>
		<br/> 
		<br/>
		<table border="1">
			<xsl:apply-templates select="question"/>
		</table>
	</xsl:template> 
	<!-- end page template -->

	<!-- quesition template -->
	<xsl:template match="question">
		<tr>
			<td valign="TOP">
				<xsl:value-of select="qn_number"/>. 
			</td>
			<td>
				<xsl:value-of select="qn_text"/>
				<xsl:apply-templates select="options">
					<xsl:with-param name="qn_id_param" select="qn_id"/>
					<xsl:with-param name="qn_type_id_param" select="qn_type_id"/>
				</xsl:apply-templates>
				<!-- 
				if this is a question that has 
				text associated with it, then a text area
				is provided for the answer 
				-->
				<xsl:if test="qn_type_id = 5">
					<table border="1">
						<tr>
							<td valign="TOP">
							<!--
							produce something like
							<input type="button" value="+" onclick="expand_ta('butt_25')">
							-->
								<input type="button" value="+">
									<xsl:attribute name="id">
										<xsl:value-of select='concat("butt_ex_", qn_id)'/> 
									</xsl:attribute>
									<xsl:attribute name="onClick">
										<xsl:value-of select="concat('expand_ta(','&quot;', 'qnid_txt_', qn_id, '&quot;', ')')"/> 
									</xsl:attribute>
								</input>
							</td>
							<td valign="TOP">
								<!--
								produce something like
								<input type="button" value="-" onclick="collapse_ta('butt_25')">
								-->
								<input type="button" value="-">
									<xsl:attribute name="id">
										<xsl:value-of select='concat("butt_col_", qn_id)'/> 
									</xsl:attribute>
									<xsl:attribute name="onClick">
										<xsl:value-of select="concat('collapse_ta(','&quot;', 'qnid_txt_', qn_id, '&quot;', ')')"/> 
									</xsl:attribute>
								</input>
							</td>
							<td>
								<textarea wrap="1" cols="100" rows="2">
									<xsl:attribute name="id">
										<xsl:value-of select='concat("qnid_txt_", qn_id)'/> 
									</xsl:attribute>
									<!-- 
									need 'name', otherwise the element cannot be accessed through 
									the HttpRequest collection
									-->
									<xsl:attribute name="name">
										<xsl:value-of select='concat("qnid_", qn_id, "_", qn_type_id, "_txt")'/> 
									</xsl:attribute>
									<xsl:value-of select="qn_response"/> 
								</textarea>
							</td>
						</tr>
					</table>
				</xsl:if>
			</td>
		</tr>
	</xsl:template> 
<!-- end question template -->

<!-- options template -->
<xsl:template match="options">
	<xsl:param name="qn_id_param"/>
	<xsl:param name="qn_type_id_param"/>
	<table border="0">
		<xsl:apply-templates select="option">
			<xsl:with-param name="qn_id_param" select="$qn_id_param"/>
			<xsl:with-param name="qn_type_id_param" select="$qn_type_id_param"/>
		</xsl:apply-templates>
	</table>
</xsl:template> 
<!-- end options template -->

<!-- option template -->
<xsl:template match="option">
	<xsl:param name="qn_id_param"/>
	<xsl:param name="qn_type_id_param"/>
		<tr>
			<td>
				
				<xsl:choose>
					<xsl:when test="$qn_type_id_param = 6">
						<!--
						type 6 indicates ranked questions. for this, we
						need to produce a textbox beside each option for
						the user to type a number in. note that because
						the value of the text box is equal to the text
						entered, we MUST record the option id within the
						name of the textbox. this is because the option
						id is expected by the engine (through the HTTP response)
						so that the results can be correctly linked to the question.
						-->
						<input type="text">
							<!-- produce something like name="qnid_333_6_opid_1234" -->
							<xsl:attribute name="name">
								<xsl:value-of select='concat("qnid_", $qn_id_param, "_", $qn_type_id_param, "_opid_", op_id)'/>
							</xsl:attribute>
							<!-- produce something like name="qnid_333_qnop_1234" -->
							<xsl:attribute name="id">
								<xsl:value-of select='concat("qnid_", $qn_id_param, "_opid_", op_id)'/>
							</xsl:attribute>
							<!-- set the value to whatever was entered (blank if nothing) -->
							<xsl:attribute name="value">
								<xsl:value-of select="op_response"/>
							</xsl:attribute>
							<!-- set width and max. characters -->
							<xsl:attribute name="size">
								<xsl:value-of select="1"/>
							</xsl:attribute>
							<xsl:attribute name="maxlength">
								<xsl:value-of select="1"/>
							</xsl:attribute>
						</input>
					</xsl:when>
					<xsl:otherwise>
						<!-- 
						in this case the input is a radio button.
						add all attributes related to the radio button.
						note: for radio buttons to be grouped, they
						must all have the same 'name' attribute
						-->
						<input type="radio">
							<!-- produce something like name="qnid_333" -->
							<xsl:attribute name="name">
								<xsl:value-of select='concat("qnid_", $qn_id_param, "_", $qn_type_id_param)'/> 
							</xsl:attribute>
							<!-- produce something like name="qnid_333_qnop_1234" -->
							<xsl:attribute name="id">
								<xsl:value-of select='concat("qnid_", $qn_id_param, "_opid_", op_id)'/>
							</xsl:attribute>
							<!-- value="op_id" -->
							<xsl:attribute name="value">
								<xsl:value-of select="op_id"/>
							</xsl:attribute>
							<!-- 
							add checked attribute if this option was 
							previously selected i.e. this was selected as an
							answer
							-->
							<xsl:if test="starts-with('true', selected)">
								<xsl:attribute name="checked"> 
								</xsl:attribute>
							</xsl:if> <!-- end if -->
						</input>
					</xsl:otherwise>
				</xsl:choose>
		</td>
	<td>
		<xsl:value-of select="op_label"/>
	</td>
	<td>
		<xsl:value-of select="op_txt"/>
	</td>
</tr>
</xsl:template> 
<!-- end option template -->

</xsl:stylesheet>