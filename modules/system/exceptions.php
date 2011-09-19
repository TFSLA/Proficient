<?php /* SYSTEM $Id: exceptions.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
global $AppUI;

$user_id = $_GET['user_id'];


$df = $AppUI->getPref( 'SHDATEFORMAT' );
$today = new CDate();
$date_from = new CDate();
$today = $today->format(FMT_TIMESTAMP_DATE);

$date_to = new CDate();
$date_to->addMonths(12);
$default_to = $date_to->format(FMT_TIMESTAMP_DATE);


// Registro nuevo //

if($_POST[action]=="guardar")
{
 $userid = $_GET[user_id];
 $from_date = substr($_POST[from_date],6,4)."-".substr($_POST[from_date],3,2)."-".substr($_POST[from_date],0,2);
 $to_date = substr($_POST[to_date],6,4)."-".substr($_POST[to_date],3,2)."-".substr($_POST[to_date],0,2);
 $description = $_POST[exception_name];
  
 $query = "INSERT INTO calendar_exclusions (exclusion_id, user_id, from_date, to_date, description) VALUES (NULL, '$userid', '$from_date', '$to_date', '$description')";

 $sql = mysql_query($query)or die(mysql_error()); 
 unset($_POST);
}

// Edición de registro //

if($_POST[action]=="editar")
{
 $from_date = substr($_POST[from_date],6,4)."-".substr($_POST[from_date],3,2)."-".substr($_POST[from_date],0,2);
 $to_date = substr($_POST[to_date],6,4)."-".substr($_POST[to_date],3,2)."-".substr($_POST[to_date],0,2);
 $description = $_POST[exception_name];
  
 $query = "UPDATE calendar_exclusions SET to_date= '$to_date', from_date='$from_date', description='$description' WHERE exclusion_id = '$_POST[exclusion_id]' ";

 $sql = mysql_query($query)or die(mysql_error()); 

 unset($_POST);
}

// Borra exception //

if($_POST[action]=="borrar")
{
  $query = "DELETE FROM calendar_exclusions WHERE exclusion_id = '$_POST[exclusion_id]'";

  $sql = mysql_query($query);
}

?>  
<script language="JavaScript">
    
   function delException( x, y ) {
	var form = document.delFrm;

	if (confirm( "<?php echo $AppUI->_('doDelete');?> " + y + "?" )) {
		form.exclusion_id.value = x;
		form.submit();
	}
    } 

	function editException( x ) {
	var form = document.editarFrm;
		form.exclusion_id.value = x;
		form.submit();
    } 

    
	function popCalendar( field ){
		calendarField = field;
		idate = eval( 'document.rngsFrm.' + field + '.value' );
		window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
    }

    function setCalendar( idate, fdate ) {
			fld_date = eval( 'document.rngsFrm.' + calendarField );
			fld_fdate = eval( 'document.rngsFrm.' + calendarField );
			fld_date.value = idate;
			fld_fdate.value = fdate;
	}
    

    function submitIt()
    {
     var form = document.editFrm;
	 var error = true;
     
	   if (form.exception_name.value=="")
	   {
		alert( "<?php echo $AppUI->_('error_description');?>" );
		form.exception_name.focus();
		error = false;
	   }

	   var to_date = form.to_date.value;
	   var from_date = form.from_date.value;
       
	   var date_f = from_date.split('/'); 
	   var date_t = to_date.split('/'); 

	   if((date_f[0] > 31)&&(error)){
	    alert( "<?php echo $AppUI->_('error_date from');?>" );
		form.from_date.focus();
		error = false;
	   }

	   if((date_f[1] > 12)&&(error)){
	    alert( "<?php echo $AppUI->_('error_date from');?>" );
		form.from_date.focus();
		error = false;
	   }

	   if((date_t[0] > 31)&&(error)){
	    alert( "<?php echo $AppUI->_('error_date to');?>" );
		form.to_date.focus();
		error = false;
	   }

	   if((date_t[1] > 12)&&(error)){
	    alert( "<?php echo $AppUI->_('error_date to');?>" );
		form.to_date.focus();
		error = false;
	   }

	   if((from_date > to_date)&&(error))
	   {
		alert( "<?php echo $AppUI->_('error_date');?>" );
		form.to_date.focus();
		error = false;
	   }

       if(error)
	   {
		form.submit();
	   }

    }

</script>
	
   <!-- <form name="rngsFrm" method="post">  
	    <input type="hidden" name="action" value="filter">
		<table>
		<tr>
			<td align="left" style="font-weight: bold;">
		    &nbsp;<?php echo $AppUI->_('From');?>:
			</td>
			<td nowrap="nowrap" width="120">
				<input type="hidden" name="log_from_date" value="<?php echo $date_from->format( FMT_TIMESTAMP_DATE );?>">
				<input type="hidden" name="from_date_format" value="<?php echo $df; ?>">
				<input type="text" name="from_date" value="<?php echo $date_from->format( $df );?>" class="text"  size="12" tabindex="2">
				<a href="#" onClick="popCalendar('from_date')">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>
			<td align="right" style="font-weight: bold;">
				<?php echo $AppUI->_('To');?>
			</td>
			<td nowrap="nowrap" width="120">
				<input type="hidden" name="log_to_date" value="<?php echo $date_to->format( FMT_TIMESTAMP_DATE );?>">
				<input type="hidden" name="to_date_format" value="<?php echo $df; ?>">
				<input type="text" name="to_date" value="<?php echo $date_to->format( $df );?>" class="text"  size="12" tabindex="3">
				<a href="#" onClick="popCalendar('to_date')">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>	
			<td>
			  <input type="button" value="<?=$AppUI->_("Filter")?>" onclick="submit()" class="button">
			</td>
		</tr>
		</table>
	</form> -->

	<form name="delFrm" action="" method="post">
		<input type="hidden" name="action" value="borrar" />
		<input type="hidden" name="exclusion_id" value="0" />
	</form>

	<form name="editarFrm" action="" method="post">
		<input type="hidden" name="action" value="edit" />
		<input type="hidden" name="exclusion_id" value="0" />
	</form>

	<table width="100%" border="0" cellpadding="1" cellspacing="1" class="">
	<tr class="tableHeaderGral">
		<th width="8%" nowrap>&nbsp;</th>
		<th width="45%" nowrap align="left" ><?php echo $AppUI->_( 'Name' );?></th>
		<th width="20%" nowrap align="left" ><?php echo $AppUI->_( 'From' );?></th>
		<th width="20%" nowrap align="left" ><?php echo $AppUI->_( 'To' );?></th>
		<th width="10%" nowrap>&nbsp;</th>
	</tr>
         <?

		  if (($_POST[from_date]!="")&&($_POST[to_date]!="") && $_POST[action]=="filter")
		  {
            $from_d = substr($_POST[from_date],6,4)."-".substr($_POST[from_date],3,2)."-".substr($_POST[from_date],0,2);
			$to_d = substr($_POST[to_date],6,4)."-".substr($_POST[to_date],3,2)."-".substr($_POST[to_date],0,2);
              
			$where = "and from_date >='$from_d' and to_date <='$to_d' ";
			
			
		  }
		  
		  
          $sql = "SELECT exclusion_id, DATE_FORMAT(from_date,'%d-%m-%Y' ) as from_date, DATE_FORMAT(from_date,'%Y-%m-%d' ) as df,        	   	
		          DATE_FORMAT(to_date,'%d-%m-%Y') as to_date, description 
		          FROM calendar_exclusions
		          WHERE user_id='$user_id' $where order by df desc";
        
          
		  $result = mysql_query($sql);

		  $dp = new DataPager($sql, "exc");
		  $dp->showPageLinks = true;
		  $result = $dp->getResults();
		  $rn = $dp->num_result;
		  $pager_links = $dp->RenderNav();

          foreach ($result as $vec){
		  ?>
		  <tr>
		    <td> 
		    <? if(!getDenyRead("admin")){ ?>
			  <a href="javascript:delException(<?php echo $vec["exclusion_id"];?>, '\'<?php echo $AppUI->_($vec["description"]);?>\'')" title="<?php echo $AppUI->_('delete');?>"><img src="./images/icons/trash_small.gif" alt="<?=$AppUI->_('delete')?>" border="0"></a>
			  <a href="javascript:editException(<?php echo $vec["exclusion_id"];?>)" title="<?php echo $AppUI->_( 'Edit' );?>"><img src="./images/icons/edit_small.gif" alt="<?=$AppUI->_( 'Edit' );?>" border="0" width="20" height="20"></a>
			  <? } ?>
			</td>
			<td>
			   <?=$AppUI->_($vec["description"]);?>
			</td>
			<td>
			   <?=$vec[from_date];?>
			</td>
			<td>
			   <?=$vec[to_date];?>
			</td>
		  </tr>
		  <?
		  }
               
		 ?>	
		 
	<? if(!getDenyRead("admin")){ ?>
	<form action="" method="post" name="editFrm">
	<input type="hidden" name="exclusion_id" value="<?=$_POST[exclusion_id];?>">
	<?
	  if($_POST[action]=="edit"){
	   $action = "editar";
	  }
	  else{
	   $action = "guardar";
	  }
      
      if($_POST[action]=="edit")
	  {
         //Si quiere editar, traigo los datos del registro a modificar //
		 $query = "SELECT * FROM calendar_exclusions WHERE exclusion_id = '$_POST[exclusion_id]'";
		 $sql = mysql_query($query)or die(mysql_error());

		 $row = mysql_fetch_array($sql);
		 $exclusion_id = $row[description];

		 $date_from->year = substr($row[from_date],0,4);
         $date_from->month = substr($row[from_date],5,2);
		 $date_from->day = substr($row[from_date],8,2);
    
         $date_to->year = substr($row[to_date],0,4);
         $date_to->month = substr($row[to_date],5,2);
		 $date_to->day = substr($row[to_date],8,2);
    

	  }
	  else{
		 $date_to = $date_from;
	  }

	?>
	<input type="hidden" name="action" value="<?=$action;?>">
	<tr>
		<td>&nbsp;</td>
		<td><input type="text" maxlength="255" size="40" class="text" name="exception_name" value="<?=$exclusion_id;?>" /></td>
		<td>
		        <input type="hidden" name="from_datet" value="<?php echo $date_from->format( FMT_TIMESTAMP_DATE );?>">
				<input type="hidden" name="from_date_format" value="<?php echo $df; ?>">
				<input type="text" name="from_date" value="<?php echo $date_from->format( $df );?>" class="text"  size="12" tabindex="2">
				<a href="#" onClick="popCalendar('from_date')">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
		</td>
		<td>
		        <input type="hidden" name="to_datet" value="<?php echo $date_to->format( FMT_TIMESTAMP_DATE );?>">
				<input type="hidden" name="to_date_format" value="<?php echo $df; ?>">
				<input type="text" name="to_date" value="<?php echo $date_to->format( $df );?>" class="text"  size="12" tabindex="2">
				<a href="#" onClick="popCalendar('from_date')">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
		</td>
		<td><input type="button" value="<?=$AppUI->_("save")?>" onclick="submitIt()" class="button"></td>
	</tr>	
	</form>
	<? } ?>
	</table>
	
<table border='0' width='100%' cellspacing='0' cellpadding='1'>
		<tr bgcolor="#E9E9E9">
			<td align='center'><? echo $pager_links; ?></td>
		</tr>
		<tr>
				<td height="1" colspan="5" bgcolor="#E9E9E9"></td>
		</tr>
		</table>
