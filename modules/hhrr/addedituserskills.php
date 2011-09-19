<?
global $AppUI, $hhrr_portal;

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$post_id = $id;
if($_GET["a"] == "addeditrole"){
	$id = -1*$id;
}
$tab = $_GET['tab'];
$a = $_GET["a"];

$canEditHHRR = !getDenyEdit("hhrr") || $id == $AppUI->user_id;
                         
$timeunits= array("1" =>  $AppUI->_("Months"), "12"=>$AppUI->_("Years"));

// si el usuario logueado no puede leer hhrr y no es él mismo
if(($_GET["a"]!="addeditrole") && !$hhrr_portal){
	if ((!$canEditHHRR || (!validar_permisos_hhrr($id,'matrix',-1)))){
	 $AppUI->redirect( "m=public&a=access_denied" );
	}
}

$categories = db_loadHashList(" SELECT skillcategories.id, skillcategories.name
						  FROM skillcategories
						  ORDER BY skillcategories.name
						");

$categ = array();

foreach($categories as $key => $val)
{
 $mysql_cant = mysql_query("SELECT count(idskillcategory)
							FROM skills
							WHERE idskillcategory='$key' ");
 $total = mysql_result($mysql_cant,0);

 $mysql_cant = mysql_query("SELECT count(idskillcategory)
							FROM skills
							WHERE idskillcategory='$key' ");

 $total = mysql_result($mysql_cant,0);

 $mysql_ids = "SELECT id FROM skills WHERE idskillcategory='$key' ";

 $list = db_loadColumn($mysql_ids);

 $mysql_par = mysql_query("SELECT count(id) FROM hhrrskills WHERE user_id = '$id'  and idskill IN (" . implode( ',', $list ) . ")  ");
 
 $parcial = mysql_result($mysql_par,0);

 $categ[$key] = $val." - ".$parcial."/".$total;

}

$categories = $categ;

if(count($categories)==0){
	die( $AppUI->_("msgNoCategories"));
}

if(!$id){
	$AppUI->redirect("m=hhrr&a=addedit&tab=0");
}

if($_GET['a']=='addeditrole'){
	$jobObj = new CJobs();
	if (!$jobObj->load(-1*$id)){
		$AppUI->setMsg( 'Job' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	}
}else{
	$userObj = new CUser();
	if (!$userObj->load($id, false)){
		$AppUI->setMsg( 'User' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	}
}

$cat_id=$_POST['cat_id'];

if ($categories[$cat_id]){
	$cat_id =$cat_id;
}else{
	$pk_categories = array_keys($categories);
	$cat_id = $pk_categories[0];
}

$cat_links=array();
foreach ($categories as $c_id => $c_name){
	if ($c_id == $cat_id){
		$cat_links[] = "<b>$c_name</b>";
	}else{
		$cat_links[] = "<a href='$PHP_SELF?m=hhrr&a=addedituserskills&id=$id&cat_id=$c_id'>$c_name</a>";
	}
}

$skills = CHhrr::getUserSkills($id, $cat_id);
$id_Skills=array();
for($i=0; $i< count($skills); $i++){
	$id_Skills[] = "'".$skills[$i]["idskill"]."'";
}
?>
<script type="text/javascript" language="javascript">
<!--
function popCalendar( field ){
    calendarField = field;
    idate = eval( 'document.skillsFrm.' + field + '.value' );

    window.open( './index.php?a=calendar&m=public&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *    @param string Input date in the format YYYYMMDD
 *    @param string Formatted date
 */
function setCalendar( idate, fdate ) {
    fld_date = eval( 'document.skillsFrm.' + calendarField );
    fld_fdate = eval( 'document.skillsFrm.' + calendarField + '_txt');
    fld_date.value = idate;
    fld_fdate.value = fdate;
}

function submitIt(){
    var form = document.skillsFrm;
    var rta = true;
    var today = new Date();
    
    var skill_ids = new Array(<?=implode($id_Skills, ", ")?>);
    
    for(var i=0; i < skill_ids.length; i++){
    	
    	campo = eval("document.skillsFrm.value" + skill_ids[i]);
    	
    	if (campo.value != "1")
    	{	
    		fecha = eval("document.skillsFrm.lastuse" + skill_ids[i] + "_txt");
    		
    		var vec_fecha = fecha.value.split("/");
            var lastuse_txt = new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);
            
            if (lastuse_txt > today){
		    	alert1("<?php echo $AppUI->_('DateErrorMayor');?>");
		    	rta = false;
		    }
    
    	}
    	
    }
    
    if (rta){
	form.submit();
	}
       

}

function checkDates()
{

    var skill_ids = new Array(<?=implode($id_Skills, ", ")?>);
    for(var i=0; i < skill_ids.length; i++){
        day = eval("document.skillsFrm.day" + skill_ids[i]);
        month =  eval("document.skillsFrm.month" + skill_ids[i]);
        year = eval("document.skillsFrm.year" + skill_ids[i]);

        if (day.value + month.value + year.value !=""){
            if(!    (parseInt(day.value) == 0 &&
                    parseInt(month.value) ==0 &&
                    parseInt(year.value) == 0)){
                if (isNaN(parseInt(day.value))
                            || isNaN(parseInt(month.value))
                            || isNaN(parseInt(year.value))) {
                    alert1("<?php echo $AppUI->_('msgBadDate');?>");
                    day.focus();
                    return false;
                } else if (parseInt(day.value) < 1 || parseInt(day.value) > 31) {
                    // There appears to be a bug with this part of the Birthday Validation
                    // Providing the single digit months (i.e. 1-9) in the MM format (01-09)
                    // causes the validation function to fail. Can someone please fix and
                    // remove this comment.  TIA (JRP 30 Aug 2002).
                    alert1("<?php echo $AppUI->_('msgInvalidDay').' '.$AppUI->_('msgBadDate');?>");
                    day.focus();
                    return false;
                } else if (parseInt(month.value) < 1 || parseInt(month.value) > 12) {
                    alert1("<?php echo $AppUI->_('msgInvalidMonth').' '.$AppUI->_('msgBadDate');?>");
                    month.focus();
                    return false;
                } else if(parseInt(year.value) < <?=date("Y") - 100?> || parseInt(year.value) > <?=date("Y")?>) {
                    alert1("<?php echo $AppUI->_('msgInvalidYear').' '.$AppUI->_('msgBadDate');?>");
                    year.focus();
                    return false;
                }
            }
        }
    }
    return true;
}
-->

</script>
<table cellspacing="0" cellpadding="0" border="0" width="100%" class="tableForm_bg">
<tr bgcolor="">
<form method="POST" action="" name="frmSelCat">
<input type="hidden" name="cat_id" value="<?=$c_id;?>" />
	<th colspan="5" valign="top"><?
	echo $AppUI->_("Categories").":&nbsp;";
	echo arraySelect($categories,"cat_id",'size="1" class="text" onchange="javascript: document.frmSelCat.submit();"', $cat_id, true,true,"350px" );
	?>
	</th>
</form>
</tr>
<form name="skillsFrm" action="" method="POST">
<input type="hidden" name="user_id" value="<?=$post_id;?>" />
<input type="hidden" name="dosql" value="do_userskills_aed" />
<input type="hidden" name="cat_id" value="<?=$cat_id;?>" />
<tr class="tableHeaderGral">
	<th><?=$AppUI->_("Skill")?></th>
	<?php if($a != "addeditrole"){ ?>
		<th><?=$AppUI->_("Autoevaluated Value")?></th>
		<?php if(!$hhrr_portal){ ?>
			<th><?=$AppUI->_("Perceived Value")?></th>
		<?php } ?>
		<? /*	<th> $AppUI->_("Experience") </th> */ ?>
	<?php }else{ ?>
		<th align="left"><?=$AppUI->_("Job Required Value")?></th>
	<?php } ?>
	<th><?=$AppUI->_("Last Use")?></th>
	<th><?=$AppUI->_("Accum.Experience")?></th>
	<th><?=$AppUI->_("Comments")?></th>
</tr>
<?
if (count($skills)==0){
	echo $AppUI->_("msgNoSkills");
} else {
	// format dates
	$df = $AppUI->getPref('SHDATEFORMAT');

	foreach ($skills as $s_values){
		foreach ($s_values as $varname => $varvalue){
			$varname = "sk_".$varname;
			$$varname = $varvalue;
		}
		//extract ($s_values, EXTR_PREFIX_ALL, "sk_");
		$sk_exp_options = split(","," ,".$sk_valueoptions);
		unset($sk_exp_options[0]);
		$sk_exp_options_perceived = array_merge(array("0"=>"N/E"),$sk_exp_options);
		$last_use = intval( $sk_lastuse ) ? new CDate( $sk_lastuse ) : "";
		$day=intval(substr($sk_lastuse,8,2));
		$month=intval(substr($sk_lastuse,5,2));
		$year=intval(substr($sk_lastuse,0,4));
		$sk_timeunit = "1";
		if ($sk_monthsofexp!="NULL"){
			if (($sk_monthsofexp % 12)==0 && $sk_monthsofexp > 0){
				$sk_timeunit =  "12" ;
				$sk_monthsofexp = $sk_monthsofexp / 12;
			}
		}
		$sk_monthsofexp=$sk_monthsofexp==0?"":$sk_monthsofexp;
?>
<tr>
<input type="hidden" name="idskill[]" value="<?=$sk_idskill?>">
	<td><?=$sk_description?></td>
	
	<td><?= $AppUI->_($sk_valuedesc)." ".arraySelect($sk_exp_options,"value$sk_idskill",'size="1" class="text"', $sk_value, true,false );?></td>
	<?php if(!$hhrr_portal && $a != "addeditrole"){ ?>
		<td><?= $AppUI->_($sk_valuedesc)." ".arraySelect($sk_exp_options_perceived,"perceived_value$sk_idskill",'size="1" class="text"', $sk_perceived_value, true,false );?></td>
	<?php } ?>
	
	<td><?php
		if ($sk_hidelastuse=="N"){	?>
		<? /*
		  <input class="text" type="text" size="2" name="day<?=$sk_idskill?>" value="<?=$day?$day:""?>">
		  <input class="text" type="text" size="2" name="month<?=$sk_idskill?>" value="<?=$month?$month:""?>">
		  <input class="text" type="text" size="4" name="year<?=$sk_idskill?>" value="<?=$year?$year:""?>">
		*/
		?>
			<input type="text" name="lastuse<?=$sk_idskill?>_txt" value="<?php echo $last_use ? $last_use->format( $df ) : "" ;?>" class="text" disabled="disabled" size="12" />
			<a href="javascript: //" onClick="popCalendar('lastuse<?=$sk_idskill?>')">
				<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
			</a>
			<input type="hidden" name="lastuse<?=$sk_idskill?>" value="<?php echo $last_use ? $last_use->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />
		<?php
		}else{	?>
		  <input type="hidden" name="lastuse<?=$sk_idskill?>_txt" value="">
		  <input type="hidden" name="lastuse<?=$sk_idskill?>" value="">
		<? } ?>
		</td>
	<td>
	<?php 
if ($sk_hidemonthsofexp=="N"){	?>
<input type="text" name="monthsofexp<?=$sk_idskill?>" value="<?=$sk_monthsofexp?>" class="text" size="3" /><?=arraySelect($timeunits,"timeunit$sk_idskill",'size="1" class="text" style="width:80px"', $sk_timeunit,false );?>
<?php 
}else{	?>
 <input type="hidden" name="monthsofexp<?=$sk_idskill?>" value="">
<? }?>

</td>

	<td><input class="text" type="text" size="20" name="comment<?=$sk_idskill?>" value="<?=$sk_comment?>">
</td>
</tr>
<tr class="tableRowLineCell"><td colspan="5"></td></tr>
<?
	}
}
	?>
<tr>
	<td colspan="6" align="right">
		<table border="0" cellpadding="5" cellspacing="0">
		<tr>
    		<td>
			<?             
            
            if($_GET[a]=="personalinfo")
			{
				$back = "index.php?a=personalinfo&tab=0&id=".$id;
			}else{
				$back = "index.php?m=hhrr&a=addedit&tab=0&id=".$id;
			}

            ?>
			<!-- <input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:window.location='<?=$back;?>';" />  -->
			</td>
    		<td colspan="3" align="center">
			
			<td align="right"><input type="button" value="<?php echo $AppUI->_( 'save' );?>" class="button" onClick="submitIt()" />
			</td>
            
		    <td align="right">
			<?
			if($_GET[a]!="personalinfo")
			{
			if($_GET['a']=="addeditrole"){
				$salir = "index.php?m=hhrr&a=viewrole&tab=$tab&id=".(-1*$id);
			}else{
				$salir = "index.php?m=hhrr&a=viewhhrr&tab=$tab&id=".$id;
			}
			?>
			<input type="button" value="<?php echo $AppUI->_( 'exit' );?>" class="button" onClick="javascript:window.location='<?=$salir;?>';" />
			<? } ?>
			</td>
		</tr>
		</table>
	</td>
</tr>
</form>
</table>