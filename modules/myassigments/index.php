<? /* My Assigment $Id: index.php,v 1.3 2009-06-26 17:43:24 pkerestezachi Exp $ */

global $debuguser, $xajax;

$AppUI->savePlace();
$canRead = !getDenyRead( $m  );
$canEdit = !getDenyEdit( $m  );

if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// Preparo la fecha
$df = "%d/%m/%Y";

$filter_from_date = $_POST['filter_from_date'];

if ($filter_from_date !=""){
$from_date = substr($_POST['filter_from_date'],6,2)."/".substr($_POST['filter_from_date'],4,2)."/".substr($_POST['filter_from_date'],0,4);
}

$filter_to_date = $_POST['filter_to_date'];

if($filter_to_date !="")
{
$to_date = substr($_POST['filter_to_date'],6,2)."/".substr($_POST['filter_to_date'],4,2)."/".substr($_POST['filter_to_date'],0,4);
}

$company_id = isset($_POST["company_id"]) ? $_POST["company_id"] : 0;
$canal_id = isset($_POST["canal_id"]) ? $_POST["canal_id"] : 0;
$project_id = isset($_POST["project_id"]) ? $_POST["project_id"] : 0;
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : 0;


// setup the title block
$titleBlock = new CTitleBlock( 'My Assigments', 'tasks.gif', $m, "$m.index" );

$titleBlock->show();

$obj = new CCompany();
$companies = $obj->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$companies = arrayMerge( array( '0'=>$AppUI->_('All') ), $companies );

?>
<script type="text/javascript"> 


     function popCalendar( field ){
			calendarField = field;
			idate = eval( 'document.filter_fm.filter_' + field + '.value' );
			window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
	 }

     function setCalendar( idate, fdate ) {
			
             if (calendarField == "from_date" )
             {
             	anio = idate.substring(0,4);
             	mes = idate.substring(4,6);
             	dia = idate.substring(6,8);
             	
             	sdate = new Date(anio,mes,dia); 
                edate = new Date(sdate.getFullYear(),(sdate.getMonth()+1),sdate.getDate()); 
               
                if(edate.getMonth()==0)
                {
                	mes_p = 12; 
                	year_p = edate.getFullYear()-1;
                }else{
                    mes_p = edate.getMonth();
                	year_p = edate.getFullYear();
                }
                
                if(mes_p < 10)
                {
                	mes = 0+''+edate.getMonth();
                }else{
                	mes = mes_p;
                }
                
                if(edate.getDate()<10)
                {
                	day = 0+''+edate.getDate();
                }else{
                    day = edate.getDate();
                }
                
             	document.filter_fm.filter_to_date.value = year_p +''+ mes +''+ day;
             	document.filter_fm.to_date.value = day +'/'+ mes +'/'+ year_p;
             
             }
             
			 fld_date = eval( 'document.filter_fm.filter_' + calendarField );
			 fld_fdate = eval( 'document.filter_fm.' + calendarField );
			 fld_date.value = idate;
			 fld_fdate.value = fdate;
	  }
      
	  function show_hide_projects (project)
	  {
	  	var imgExpand = new Image;
		var imgCollapse = new Image;
		imgExpand.src = './images/icons/expand.gif';
		imgExpand.alt = '<?=$AppUI->_('Show')?>';
		imgCollapse.src = './images/icons/collapse.gif'; 
		imgCollapse.alt = '<?=$AppUI->_('Hide')?>';
		hide = false;
		
		for(i=0;i<document.frmMyAssigment.elements.length;i++)
		{
			var trprojectname = 'tr_project_' + project + "_" + document.frmMyAssigment.elements[i].name.substring(10);
			
			if(document.getElementById(trprojectname + '_0'))
			{
				if( document.getElementById(trprojectname + '_0').style.display == 'none' )
				{
					document.getElementById(trprojectname).style.display = '';
					document.getElementById(trprojectname + '_0').style.display = '';
				}
				else
				{
					hide = true;
					document.getElementById(trprojectname).style.display = 'none';
					document.getElementById(trprojectname + '_0').style.display = 'none';
				}
			}
 			}
 		
 		if(hide == true)
 		{
   			    document.getElementById('imgprj_' + project).src = imgExpand.src;
			    document.getElementById('imgprj_' + project).alt = imgExpand.alt;	
 			}
 		else
 		{
			document.getElementById('imgprj_' + project).src = imgCollapse.src;
			document.getElementById('imgprj_' + project).alt = imgCollapse.alt;
 		}
	  }
	  
	  function reset_filter()
	  {
	  	 var fm = document.filter_fm;
	  	 
	  	 fm.company_id.value = 0;
	  	 fm.canal_id.value = 0;
	  	 fm.project_id.value = 0;
	  	 fm.user_id.value = 0;
	  	 fm.filter_from_date.value = "";
	  	 fm.from_date.value = "";
	  	 fm.filter_to_date.value = "";
	  	 fm.to_date.value = "";
	  	 fm.only_old.checked = false;
	  	 
	  	 fm.submit();
	  }
	  
	  function submItF(user)
	  {
	  	var fm = document.todo_filter;
	  	fm.FtAss.value = user;
	  	 
	  	fm.submit();
	  }
	  
      function filterCombos(company, canal, project, user)
      {
      	xajax_addCanal('canal_id', company, canal, 'TRUE' , '', '' );
      	xajax_addProjects(company, canal, project, 'TRUE', '', '', 'project_id' );
      	xajax_addUsersMyOwnerProjects('user_id', project, company, canal, user, 'TRUE');
      }
      
</script>

<form name='todo_filter' action='index.php?m=todo' method='POST'>
    	  
  <input type='hidden' name='FtAss' value=''> 
  <input type='hidden' name='tid' value=''> 
  <input type='hidden' name='pid' value=''> 
    	  
</form>


<table border='0' VALIGN=top  width='100%' cellpadding="0" cellspacing="0" >
  
  <form name="filter_fm" id="filter_fm" method="POST"> 
  
  <tr class="tableForm_bg">
    <td>
		<table border='0' cellpadding="5" cellspacing="0" width="10%">
		   <tr>
			<td width='1' align='right' nowrap>
			  <?php echo $AppUI->_('Company'); ?>
			</td>
			<td align='left' width='1' nowrap>
			  <?                                                                               
                echo arraySelect( $companies, "company_id", "style=\"font-size:10px\" onchange=\"filterCombos(document.filter_fm.company_id.value, '', '')\"", $company_id, TRUE , FALSE );
              ?>	
			</td>

			<td width='1' align='right' nowrap>
			  &nbsp;&nbsp;&nbsp;&nbsp;<?php echo $AppUI->_('Canal'); ?>
			</td>
			<td align='left' width='1' nowrap>
			   <select name="canal_id" id="canal_id" style="font-size:10px; width: 160px" onchange="filterCombos(document.filter_fm.company_id.value, document.filter_fm.canal_id.value, '', '');"></select>
			</td>            
            			
			<td align='right' nowrap>
			  <?php echo $AppUI->_('Project'); ?>
			</td>
			<td align='left' nowrap>
			   <select name="project_id" id="project_id" style="font-size:10px; width: 160px" onchange="filterCombos(document.filter_fm.company_id.value, document.filter_fm.canal_id.value, document.filter_fm.project_id.value, '');"></select>
			</td>
			  
			<?			
				$users_owners = CProject::getUsersMyOwnerProjects('', $project_id, $company_id, $canal_id, '', false);
			?>
			  
			<td align='left' id='td_user_id' colspan="2" style="display:'<?=(count($users_owners) > 0 ? '' : 'none')?>';" nowrap>
				&nbsp;&nbsp;<?php echo $AppUI->_('User'); ?>&nbsp;
			    <select name="user_id" id="user_id" style="font-size:10px; width: 160px;"></select>
			</td>
			
		   </tr>
		   
			<script type="text/javascript">
              filterCombos('<?=$company_id?>', '<?=$canal_id?>', '<?=$project_id?>', '<?=$user_id?>');
            </script>		   
		   
		   <tr>
		   
		    <td align='right' width='1' nowrap>
			  <?php echo $AppUI->_('From'); ?>
			</td>
			<td align='left' nowrap>
			  <input type="hidden" name="filter_from_date" value="<?php echo $filter_from_date;?>">
              <input type="hidden" name="filter_from_date_format" value="<?php echo $df; ?>">
              <input type="text" name="from_date" value="<?php echo $from_date;?>" class="text"  size="12" tabindex="2" disabled >
		      <a href="#" onClick="popCalendar('from_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
			</td>
			
			<td align='right' nowrap>
			  <?php echo $AppUI->_('To'); ?>
			</td>
			<td align='left' nowrap>
			  <input type="hidden" name="filter_to_date" value="<?php echo $filter_to_date;?>">
              <input type="hidden" name="filter_to_date_format" value="<?php echo $df; ?>">
              <input type="text" name="to_date" value="<?php echo $to_date;?>" class="text"  size="12" tabindex="2" disabled >
		      <a href="#" onClick="popCalendar('to_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
			</td>
			
			<td align='left' colspan="2" nowrap>
				<input type="checkbox" name="only_old" value="true" <? if ($_POST['only_old']) echo "checked"; ?> >
				<?php echo $AppUI->_('Hide overdue'); ?>&nbsp;&nbsp;
			</td>
			<td align='right' colspan="2" nowrap>
				<input type="submit" value="&nbsp;<?=$AppUI->_("filter")?>&nbsp;" class="button">&nbsp;
				<input type="button" value="<?=$AppUI->_("reset")?>" class="button" onclick="reset_filter()">
			</td>
           </tr>
           
           </form>
		   
	   </table>
	   
	 </td>			
	 
  </tr>
  
 
</table>
  

  
<!-- Tabla con las asignaciones por proyecto -->
<? include ('vw_assigments.php'); ?>
	