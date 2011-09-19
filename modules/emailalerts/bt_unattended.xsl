<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	<xsl:template match="/">
		<xsl:variable name="bugs" select="//bugs/bug"/>
		<html>
			<head>
				<title>Incidencias asignadas por mas de 1 mes</title>
			</head>
			<body>
				<h2>Incidencias asignadas por mas de 1 mes</h2>
				<xsl:for-each select="$bugs">


<table style="BACKGROUND: #878676; WIDTH: 450pt;" cellSpacing="1" cellPadding="0" width="600" border="0">
  <tbody>
  <tr>
    <td  style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff; BACKGROUND: #878676" colSpan="2">
      <p><STRONG><SPAN  style="FONT-SIZE: 10pt; FONT-FAMILY: Arial">
        Incidencia <xsl:value-of select="bugid"/>
	</SPAN></STRONG></p></td>
  </tr>
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
  <tr>
    <td colspan="2" style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <a>
        <xsl:attribute name="href"><xsl:value-of select="link/url" /></xsl:attribute>
        <xsl:value-of select="link/label" />
      </a>
    </td>
  </tr>
</tbody></table><br/>
				</xsl:for-each>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
