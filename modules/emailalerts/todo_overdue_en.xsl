<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	<xsl:template match="/">
		<xsl:variable name="projects" select="//projects/project"/>
		<html>
			<head>
				<title>XSLT Testing</title>
			</head>
			<body>
				<h2>To Dos - Vencidos</h2>


<xsl:for-each select="$projects">
<table style="BACKGROUND: #878676; WIDTH: 450pt;" cellSpacing="1" cellPadding="0" width="600" border="0">
  <tbody>

  <tr>
    <td colspan="11" style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff; BACKGROUND: #878676" colSpan="2">
      <p><STRONG><SPAN  style="FONT-SIZE: 10pt; FONT-FAMILY: Arial">
        Proyecto: <xsl:value-of select="name"/>
	</SPAN></STRONG></p></td>
  </tr>
        <tr>
          <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff; BACKGROUND: #979686">
            <p><STRONG><SPAN style="FONT-SIZE: 10pt; FONT-FAMILY: Arial">Prioridad</SPAN></STRONG></p>
          </td>
          <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff; BACKGROUND: #979686">
            <p><STRONG><SPAN style="FONT-SIZE: 10pt; FONT-FAMILY: Arial">Fecha</SPAN></STRONG></p>
          </td>
          <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff; BACKGROUND: #979686">
            <p><STRONG><SPAN style="FONT-SIZE: 10pt; FONT-FAMILY: Arial">Descripcion</SPAN></STRONG></p>
          </td>
          <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff; BACKGROUND: #979686">
            <p><STRONG><SPAN style="FONT-SIZE: 10pt; FONT-FAMILY: Arial">Responsable</SPAN></STRONG></p>
          </td>
          <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff; BACKGROUND: #979686">
            <p><STRONG><SPAN style="FONT-SIZE: 10pt; FONT-FAMILY: Arial">Asignado</SPAN></STRONG></p>
          </td>
          <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff; BACKGROUND: #979686">
            <p><STRONG><SPAN style="FONT-SIZE: 10pt; FONT-FAMILY: Arial">Vencimiento</SPAN></STRONG></p>
          </td>
        </tr>
<xsl:variable name="todos" select="todo"/>
<xsl:for-each select="$todos">
  <tr>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P><xsl:value-of select="priority"/></P></td>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P><xsl:value-of select="date"/></P></td>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P><xsl:value-of select="description"/></P></td>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P><xsl:value-of select="ownerlname"/>, <xsl:value-of select="ownerfname"/></P></td>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P><xsl:value-of select="assignedlname"/>, <xsl:value-of select="assignedfname"/></P></td>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P><xsl:value-of select="duedate"/></P></td>
  </tr>

</xsl:for-each>
</tbody></table><br/>
</xsl:for-each>

			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
