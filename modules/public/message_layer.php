<?php 
/*
echo "<pre>";
var_dump($_POST);
echo "</pre>";
*/
?>
<style>
html,body {
	margin-top: 0px;
	margin-left: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	padding:0px;
	background-color: #eeeeee;
}
</style>
<script language="JavaScript">
   function hide_message(){
   		window.parent.hide_message();

   }
</script>
      <table width="390" height="145" border="0" cellpadding="0" cellspacing="0" class="std">
      <col width="6px"><col ><col width="6px"><col width="6px">
      <tr height="19" style="border: 1px solid; background-image: URL(images/common/back_degrade.gif);">
        <td width="6"><img src="images/common/ladoizq.gif" width="6" height="19" /></td>
        <td><img src="images/icons/log-<?php echo $_POST["type"]; ?>.gif" alt="<?php echo ($_POST["type"]); ?>" />&nbsp;<span class="boldtext"><?php 
        echo htmlentities($_POST["title"]);
        ?>
          <div id="msgWindowTitle"></div>
        </span></td>
        <td align="right" nowrap="nowrap"><a href="javascript: window.parent.hide_message();" >X</a></td>
        <td align="right" nowrap="nowrap"><img src="images/common/ladoder.gif" width="6" height="19"/></td>
      </tr>
      <tr height="126">
        <td colspan="4" height="126" style="border: 1px solid;">
        	<div style="width: 390px; height: 126px; overflow: auto; padding:0px; margin: 0px;">
          	<table border="0" cellpadding="4" cellspacing="0" width="95%" height="98%" class="std">
            <tr>
	              <td><?php echo nl2br($_POST["message"])?>
	              <div id="msgWindowMessage"></div></td>
            </tr>
        	</table>
        	</div>
        </td>
      </tr>
    </table>