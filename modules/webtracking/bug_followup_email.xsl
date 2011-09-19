<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" omit-xml-declaration="yes"/>
	<xsl:template match="/">
		<xsl:variable name="publicaciones" select="//publicaciones/publicacion"/>
		<html>
			
			<body>
				
				<xsl:for-each select="$publicaciones">


<table style="WIDTH: 450pt;" cellSpacing="1" cellPadding="0" width="600" border="0">
  <tbody>
  <tr>
    <td ><img> 
                        <xsl:attribute name="src"><xsl:value-of select="logo" /></xsl:attribute>
             </img> 
    </td>
  </tr>
  <tr>
      <td>
       <table  align="center" width="100%" border="0" cellpadding="0" cellspacing="0">
	  <tr>
	        <td valign="top" style="padding:0.3cm 0cm 0cm 0cm;height:35.5pt">
	         <p style='margin-left:35.4pt;font-size:9.0pt;
    font-family:"Verdana","sans-serif"'>Estimado/a <xsl:value-of select="namereceptor"/>,
	             </p>
	        </td>
	   </tr>
	   <tr>
	        <td>
	             <p  style='margin-left:35.4pt'><i><span style='font-size:
    9.0pt;font-family:"Verdana","sans-serif";color:black'><xsl:value-of select="resolutiontext"/></span></i></p>
	        </td>
	  </tr>
	  <tr>
	        <td>
	            <p style='margin-left:0cm'><span style='font-size: 9.0pt;font-family:"Verdana","sans-serif"'><br/></span></p>
                  
	            <p style='margin-left:35.4pt'><span style='font-size:9.0pt; font-family:"Verdana","sans-serif";color:black'>Recuerda que estamos para servirte,</span></p>
	        </td>
	  </tr> 
	  <tr style='height:3.25pt'>
                        <td valign="top" style="padding:1cm 0cm 0cm 0cm;height:3.25pt">
                           <p style='margin-left:0cm'><span style='font-size: 9.0pt;font-family:"Verdana","sans-serif"'><br/></span></p>
                           <p style='margin-left:35.4pt'><span style='font-size:10.0pt;font-family:"Verdana","sans-serif";color:#8C4DB5'><xsl:value-of select="handler"/></span><br/>
                           <i><span style='font-size:10.0pt;font-family:"Verdana","sans-serif";color:#8C4DB5'><xsl:value-of select="cargousuario"/></span></i><br/>
                           <span style='font-size:10.0pt; font-family:"Verdana","sans-serif";color:#8C4DB5'>CALISTA CENTER</span><br/>
                           <span style='font-size:10.0pt; font-family:"Verdana","sans-serif";color:#8C4DB5'>Centro de Estética Integral</span>
                           <br/>
                           <span style='font-size:10.0pt;font-family:"Verdana","sans-serif";color:#8C4DB5'>Amenabar 630 Buenos Aires Argentina.</span>
                           </p>
   
		    <p style='margin-left:35.4pt'><span style='font-size:8.5pt; font-family:"Arial","sans-serif";color:#8C4DB5'>España: 900-093370<br/>
		    Colombia: 01800-700-2170<br/>
		    Chile: (2) 897-7095<br/>
		    Peru: (1) 708-5373<br/>
		    Otros paises:+54-11-5256-0404</span><span style='font-size:12.0pt'></span>
                            </p>
                     </td>
             </tr>
       </table></td>     
  </tr>
</tbody></table><br/>
				</xsl:for-each>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
