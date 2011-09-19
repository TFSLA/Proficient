<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">  

	<xsl:output method="html" omit-xml-declaration="yes"/>
	<xsl:template match="/">
		<xsl:variable name="candidates" select="//candidates/candidate"/>
		<html>
			<head>
				<title>RRHH - Registros modificados del mes</title>
			</head>
			<body>
				<h2>RRHH - Registros modificados del mes</h2>
				<xsl:for-each select="$candidates">


<table style="BACKGROUND: #878676; WIDTH: 450pt;" cellSpacing="1" cellPadding="0" width="600" border="0">
  <tbody>
  <tr>
    <td  style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff; BACKGROUND: #878676" colSpan="2">
      <p><STRONG><SPAN  style="FONT-SIZE: 10pt; FONT-FAMILY: Arial">
        <xsl:value-of select="dateupdated"/> - <xsl:value-of select="lastname"/>, <xsl:value-of select="firstname"/>

	</SPAN></STRONG></p></td>
  </tr>
  <tr>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P>Telefono:</P></td>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P><xsl:value-of select="homephone"/></P></td>
  </tr>
  <tr>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P>Celular:</P></td>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P><xsl:value-of select="cellphone"/></P></td>
  </tr>
  <tr>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P>Direccion:</P></td>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P><xsl:value-of select="address"/></P></td>
  </tr>
  <tr>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P>Ciudad:</P></td>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P><xsl:value-of select="city"/></P></td>
  </tr>
  <tr>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P>Provincia:</P></td>
    <td style="BORDER-RIGHT: #ffffff; PADDING-RIGHT: 3pt; BORDER-TOP: #ffffff; PADDING-LEFT: 3pt; BACKGROUND: white; PADDING-BOTTOM: 3pt; BORDER-LEFT: #ffffff; PADDING-TOP: 3pt; BORDER-BOTTOM: #ffffff">
      <P><xsl:value-of select="state"/></P></td>
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
