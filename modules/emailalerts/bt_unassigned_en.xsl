<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	<xsl:template match="/">
		<xsl:variable name="bugs" select="//bugs/bug"/>
		<html>
			<head>
				<title>XSLT Testing</title>
			</head>
			<body>
				<h2>Incidencias sin asignar</h2>
				<xsl:for-each select="$bugs">


<table style="BACKGROUND: #878676; WIDTH: 450pt;" cellSpacing="1" cellPadding="0" width="600" border="0">
  <tbody>
  <tr>
    <td  style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff; BACKGROUND: #878676" colSpan="2">
      <p><STRONG><SPAN  style="FONT-SIZE: 10pt; FONT-FAMILY: Arial">
        <xsl:value-of select="project"/>
	</SPAN></STRONG></p></td>
  </tr>
  <tr>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P>Fecha:</P></td>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P><xsl:value-of select="date"/></P></td>
  </tr>
  <tr>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P>Resumen:</P></td>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P><xsl:value-of select="summary"/></P></td>
  </tr>
</tbody></table><br/>
				</xsl:for-each>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
