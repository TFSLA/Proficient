<?php
include_once("../../../includes/config.php");

$upload_dir = $dPconfig['upload_imag'];
$accion = $_POST[accion];
$base_dir = $dPconfig['base_url'];

if($accion !=""){

if (($HTTP_POST_FILES[archivo][error]!="0")&&($HTTP_POST_FILES[archivo][size]!="0")){
   $msg = "Error al subir el archivo";
  }
  else{
	if($accion=="subir"){
        
		$nombre_arch = $HTTP_POST_FILES['archivo']['name'];
		$var = explode(".","$nombre_arch");
		$extension = $var[1];
		$tamanio_arch = $HTTP_POST_FILES['archivo']['size'];
		$imgsize=getimagesize($_FILES['archivo']['tmp_name']);
		$fheight = $imgsize[1];
		$fwidth = $imgsize[0];
		
		$num = count($extensiones);
		$valor = $num-1;
	

		 if (is_uploaded_file($HTTP_POST_FILES['archivo']['tmp_name']))
		 {   
			 $ts = time();
			 $temp = date("YmdHis",$ts);
			 $new_name = $temp.".".$extension;
             
			 $dir = $dPconfig['root_dir'].$dPconfig['upload_imag']."/".$new_name;

			 if (!copy($HTTP_POST_FILES['archivo']['tmp_name'], $dir)){
				 print("failed to copy $file...<br>\n");

			 }
	     }
        
         $f_horiz = $fwidth;
		 $f_vert = $fheight;
         

		 if($_POST[f_border]==""){
		  $border = "0";
		 }
		 else{
          $border = $_POST[f_border];
		 }
		 $f_border = $border;
         

		 if($_POST[alt]==""){
		  $alt = "";
		 }
		 else{
          $alt = $_POST[alt];
		 }
		 $f_alt = $alt;
         
		 $view = $dPconfig['upload_imag']."/$new_name";

	 }else{

		 if($_POST[horiz]==""){
		  $horizontal = $fwidth;
		 }
		 else{
          $horizontal = $_POST[horiz];
		 }
         $f_horiz = $horizontal;


		 if($_POST[vert]==""){
		  $vertical = $fheight;
		 }
		 else{
          $vertical =$_POST[vert];
		 }
		 $f_vert = $vertical;
         

		 if($_POST[f_border]==""){
		  $border = "0";
		 }
		 else{
          $border =$_POST[f_border];
		 }
		  $f_border = $border;

		 if($_POST[alt]==""){
		  $alt = "";
		 }
		 else{
          $alt = $_POST[alt];
		 }
		 $f_alt = $alt;

		 if($_POST[align]==""){
		  $align = "";
		 }
		 else{
          $align = $_POST[align];
		 }
		 $f_align = $align;
         

		 if($_POST[f_url]!=""){
		 $view = $_POST[f_url];
		 }
         else{
		 $view = $dPconfig['upload_imag']."/$new_name";
		 }
               
	 }
  }
}

?>
<html>

<head>
  <title>Insert Image</title>

<script type="text/javascript" src="popup.js"></script>

<script type="text/javascript">

window.resizeTo(500, 100);

function Init() {
  __dlg_init();
  var param = window.dialogArguments;
  var img_src;
  
  if (param) {
      document.getElementById("f_url").value = param["f_url"];
      document.getElementById("f_alt").value = param["f_alt"];
      document.getElementById("f_border").value = param["f_border"];
      document.getElementById("f_align").value = param["f_align"];
      document.getElementById("f_vert").value = param["f_vert"];
      document.getElementById("f_horiz").value = param["f_horiz"];
      window.ipreview.location.replace('<?=$base_dir;?>/'+param.f_url);
  }
};

function Init_off() {
  __dlg_init();
  var param = window.dialogArguments;
  var img_src;
};

function onCancel() {   
   __dlg_close(null);
  return false;
};

function onClose(){
   // pass data back to the calling window
     if(document.editFrm.f_url.value!=""){
	  var fields = ["f_url", "f_alt", "f_align", "f_border",
					"f_horiz", "f_vert"];
	  var param = new Object();
	  
		for (var i in fields) {
		var id = fields[i];
		var el = document.getElementById(id);
		param[id] = el.value;
		 }

	 }
  
  
  __dlg_close(param);
  return false;
};

function EditIt(){
  
   var form = document.editFrm;

   form.accion.value = "edit";
   form.submit();

};


</script>

<style type="text/css">
html, body {
  background: ButtonFace;
  color: ButtonText;
  font: 11px Tahoma,Verdana,sans-serif;
  margin: 0px;
  padding: 0px;
}
body { padding: 5px; }
table {
  font: 11px Tahoma,Verdana,sans-serif;
}
form p {
  margin-top: 5px;
  margin-bottom: 5px;
}
.fl { width: 9em; float: left; padding: 2px 5px; text-align: right; }
.fr { width: 6em; float: left; padding: 2px 5px; text-align: right; }
fieldset { padding: 0px 10px 5px 5px; }
select, input, button { font: 11px Tahoma,Verdana,sans-serif; }
button { width: 70px; }
.space { padding: 2px; }

.title { background: #ddf; color: #000; font-weight: bold; font-size: 120%; padding: 3px 10px; margin-bottom: 10px;
border-bottom: 1px solid black; letter-spacing: 2px;
}
form { padding: 0px; margin: 0px; }
</style>

</head>


<?if(count($_POST)=="0"){?>
<body onload="Init()">
<?} else{?>
<body onload="Init_off()">
<?}?>


<div class="title">Insert Image</div>
<!--- new stuff --->
<form  enctype='multipart/form-data' action="" method="post" name="editFrm">
<input type="hidden" name="accion"  id="accion" value="subir">
<input type="hidden" name="f_url" id="f_url" value="<?=$view;?>">
<input type="hidden" name="view" id="view" value="<?=$f_url;?>">

<table border="0" width="100%" style="padding: 0px; margin: 0px">
  <tbody>

  <tr>
    <td style="width: 7em; text-align: right">Image :</td>
    <td>
	  <input type="file" name="archivo" VALUE="<?=$archivo; ?>"  size="30" >
    </td>
  </tr>

  <tr>
    <td colspan="2" align="center">
	   <br>
	    <button type="button" name="add" onclick="submit();">Upload</button> 
	   <br>&nbsp;
	</td>
  </tr>
  <tr>
    <td style="width: 7em; text-align: right">Alternate text:</td>
    <td><input type="text" name="alt" id="f_alt" value="<?=$f_alt;?>" style="width:100%"
      title="For browsers that don't support images" /></td>
  </tr>

  </tbody>
</table>

<p />

<fieldset style="float: left; margin-left: 5px;">
<legend>Layout</legend>

<div class="space"></div>

<div class="fl">Alignment:</div>
<select size="1" name="align" id="f_align"
  title="Positioning of this image">
  <option value=""  <?if($f_align=="")echo "selected"; ?>             >Not set</option>
  <option value="left" <?if($f_align=="left")echo "selected"; ?>      >Left</option>
  <option value="right"  <?if($f_align=="right")echo "selected"; ?>    >Right</option>
  <option value="texttop" <?if($f_align=="texttop")echo "selected"; ?>   >Texttop</option>
  <option value="absmiddle" <?if($f_align=="absmiddle")echo "selected"; ?> >Absmiddle</option>
  <option value="baseline" <?if($f_align=="baseline")echo "selected"; ?>  >Baseline</option>
  <option value="absbottom" <?if($f_align=="absbottom")echo "selected"; ?> >Absbottom</option>
  <option value="bottom"  <?if($f_align=="bottom")echo "selected"; ?>   >Bottom</option>
  <option value="middle"  <?if($f_align=="middle")echo "selected"; ?>   >Middle</option>
  <option value="top" <?if($f_align=="top")echo "selected"; ?>       >Top</option>
</select>

<p />

<div class="fl">Border thickness:</div>
<input type="text" name="f_border" id="f_border" value="<?=$f_border;?>"  size="5"
title="Leave empty for no border" />

<div class="space"></div>

</fieldset>

<fieldset style="float:right; margin-right: 5px;">
<legend>Spacing</legend>

<div class="space"></div>

<div class="fr">Horizontal:</div>
<input type="text" name="horiz" id="f_horiz" size="5" value="<?=$f_horiz;?>"
title="Horizontal padding" />

<p />

<div class="fr">Vertical:</div>
<input type="text" name="vert" id="f_vert" size="5" value="<?=$f_vert;?>"
title="Vertical padding" />

<div class="space"></div>

</fieldset>
<br clear="all" />
<br>
<div align="center"><button type="button" name="edit" onClick="EditIt()" >Edit Attributes</button></div>

<table width="100%" height="300" style="margin-bottom: 0.2em" border="0">
 <tr>
   <td valign="bottom">
    Image Preview:<br />
    <?
	  if($view!=""){
	  echo "<IMG SRC=\"$base_dir/$view\" WIDTH=\"".$f_horiz."\" HEIGHT=\"".$f_vert."\" BORDER=\"".$f_border."\" ALT=\"".$f_alt."\">";
      }else{
	?>
    <iframe name="ipreview" id="ipreview" frameborder="0" style="border : 1px solid gray;" height="200" width="300" src=""></iframe>
	<?
	  }
	?>

  </td> 
  <td valign="bottom" style="text-align: right">
    
    <button type="button" name="cancel" onclick="return onClose();">Insert</button> 
	<button type="button" name="cancel" onclick="return onCancel();">Cancel</button> 
  </td>
 </tr>
</table>
</form>
</body>
</html>
