<body onunload="refrescar_padre()">

<?php

if ( isset($_GET['user_id']) )
{
	$user_id = $_GET['user_id'];
	
	$sql= "SELECT user_first_name, user_last_name FROM users where user_id = $user_id;";
	$rc=mysql_query($sql);
	$vec=mysql_fetch_array($rc);
	$user_first_name = $vec['user_first_name'];
	$user_last_name = $vec['user_last_name'];
}
else
{
	$user_id = $AppUI->user_id;
	$user_first_name = $AppUI->user_first_name;
	$user_last_name = $AppUI->user_last_name;
}


switch($_POST[action]) 
{   
	case "new":
	{
    $insert = "INSERT INTO hhrr_permissions (id_user, company, department,personal, matrix, work_experience, education, performance_management, compensations, development)
    															VALUES ($user_id,".$_POST['company'].", ".$_POST['department'].", ".$_POST['personal'].", ".$_POST['matrix'].", ".$_POST['work_experience'].", ".$_POST['education'].", ".$_POST['performance_management'].", ".$_POST['compensations'].", ".$_POST['development'].")";
    //echo "$insert";
    $sql = mysql_query($insert);
    
		break;
	}

	case "update":
	{
		foreach( $_POST['permUpdate'] as $perm )
		{	
			 $update = "UPDATE hhrr_permissions SET
			 personal = ".$perm['personal'].",
			 matrix = ".$perm['matrix'].",
			 work_experience = ".$perm['work_experience'].",
			 education = ".$perm['education'].",
			 performance_management = ".$perm['performance_management'].",
			 compensations = ".$perm['compensations'].",
			 development = ".$perm['development']."
			 WHERE id = ".$perm['id'];
			 
			 //echo "<br>".$update;
			 $sql = mysql_query($update);
		}
		  
		break;
	}

	case "del":
	{
		$del_query = "DELETE FROM hhrr_permissions WHERE id = ".$_POST['id'];
	 	//echo "$del_query";
	 	$sql_del = mysql_query($del_query);
	 
		break;
	}	
}


$sql = "SELECT company_id, company_name FROM companies order by company_name;";
$companys = arrayMerge (array ('-1'=>$AppUI->_('All')), db_loadHashList ($sql));

$tiposPermiso = array( "-1"=>"Read Write", "0"=>"Denied", "1"=>"Read Only" );
?>
<script language="javascript">
function confirma(obj)
{  
   var f = document.delFrm;
   f.id.value = obj;

  if ( confirm1("<?=$AppUI->_("delete_reg")?>") )
	{
    f.submit();
	}
}

function submitIt()
{
	var form = document.editFrm;
	form.action.value = "update";

	form.submit();
}

function submitItNew()
{
	var form = document.editFrm;
	
	form.action.value = "new";
	form.submit();
}

function popDept() {
    var f = document.editFrm;
    if (f.company.selectedIndex == 0) {
        alert1("<?=$AppUI->_('Please select a company first!')?>");
    } else {
        window.open('./index.php?m=public&a=selector&dialog=1&suppressLogo=1&callback=setDept&table=departments&company_id='
            + f.company.options[f.company.selectedIndex].value
            + '&dept_id='+f.department.value,'dept','left=50,top=50,height=250,width=400,resizable')
    }
}
// Callback function for the generic selector
function setDept( key, val ) {
    var f = document.editFrm;
    if (val != '') {
        f.department.value = key;
        f.dept_name.value = val;
    } else {
        f.department.value = '0';
        f.dept_name.value = '';
    }
}
function refrescar_padre( ) 
{
	window.opener.location.reload();
}
</script>

<form name="delFrm" action="" method="POST">
	<input type="hidden" name="id" value="" />
	<input type="hidden" name="action" value="del" />
</form>

<form name="editFrm" method="post">
	<input type="hidden" name="user_id" value=<? echo $user_id; ?> />
	<input type="hidden" name="action" value=""/>
<table width="100%" border="0" cellpadding="1" cellspacing="1" class="std">
<tr height="20">
	<th colspan="2"><?php echo $AppUI->_('User Permissions Module HHRR');?>: <?php echo "$user_first_name $user_last_name";?></th>
</tr>
<tr>
	<td>
		<table width="100%" border="0" cellpadding="1" cellspacing="1" class="std">			
			<tr>
				<th width='16'>&nbsp;</th>
				<th>
					<?=$AppUI->_("Company")?>
				</th>
				<th>
					<?=$AppUI->_('Department')?>
				</th>
				<th>
					<?=$AppUI->_('Personal data')?>
				</th>				
				<th>
					<?=$AppUI->_('Matrix')?>
				</th>
				<th>
					<?=$AppUI->_('Work Experience')?>
				</th>
				<th>
					<?=$AppUI->_('Education')?>
				</th>				
				<th>
					<?=$AppUI->_('Performance Management')?>
				</th>
				<th>
					<?=$AppUI->_('compensations')?>
				</th>
				<th>
					<?=$AppUI->_('Development')?>
				</th>												
			</tr>
			<?
				$query="SELECT id, company, company_name, department, dept_name, personal, matrix, work_experience, education, performance_management, compensations, development FROM hhrr_permissions
								LEFT JOIN companies ON hhrr_permissions.company=companies.company_id
								LEFT JOIN departments ON hhrr_permissions.department=departments.dept_id
								WHERE id_user = $user_id;";
	  		$sql = mysql_query($query);
	
	 			while ($vec = mysql_fetch_array($sql)){
			?>			
			<tr>
				<input type="hidden" name="<?="permUpdate[".$vec['id']."][id]"?>" value="<?= $vec['id']; ?>" />
				<td>
				  <a href="JavaScript:confirma(<?=$vec[id]?>)"><img src='./images/icons/trash_small.gif' alt='Delete' border='0'></a>
				</td>
				<td>
					<?=($vec['company_name'] != NULL) ? $vec['company_name'] : $AppUI->_('All');?>
				</td>
				<td>
					<?=($vec['dept_name'] != NULL) ? $vec['dept_name'] : $AppUI->_('All');?>
				</td>
				<td>
					<? echo arraySelect( $tiposPermiso, "permUpdate[".$vec['id']."][personal]", 'class="text"', $vec['personal'], true,'','125px'); ?>
				</td>				
				<td>
					<? echo arraySelect( $tiposPermiso, "permUpdate[".$vec['id']."][matrix]", 'class="text"', $vec['matrix'], true,'','125px'); ?>
				</td>
				<td>
					<? echo arraySelect( $tiposPermiso, "permUpdate[".$vec['id']."][work_experience]", 'class="text"', $vec['work_experience'], true,'','125px'); ?>
				</td>
				<td>
					<? echo arraySelect( $tiposPermiso, "permUpdate[".$vec['id']."][education]", 'class="text"', $vec['education'], true,'','125px'); ?>
				</td>
				<td>
					<? echo arraySelect( $tiposPermiso, "permUpdate[".$vec['id']."][performance_management]", 'class="text"', $vec['performance_management'], true,'','125px'); ?>
				</td>
				<td>
					<? echo arraySelect( $tiposPermiso, "permUpdate[".$vec['id']."][compensations]", 'class="text"', $vec['compensations'], true,'','125px'); ?>
				</td>			
				<td>
					<? echo arraySelect( $tiposPermiso, "permUpdate[".$vec['id']."][development]", 'class="text"', $vec['development'], true,'','125px'); ?>
				</td>		

			</tr>
			<?
			}
			if ( mysql_num_rows($sql) )
			{
			?>
			<tr>
				<td colspan="10" align="right">
					<input type="button" value="<?=$AppUI->_("save permissions")?>" class="button" onClick="submitIt()" />
				</td>
			</tr>
			<?
			}			
			?>
			<tr>
				<th colspan="10" align="center">
					<?=$AppUI->_("New Permission")?>
				</th>
			</tr>
			<tr>
				<td colspan='2'>
					<?=arraySelect( $companys, "company", 'class="text"', "0",'','','205px' ) ?>
				</td>
				<td nowrap >
	        <input type="hidden" name="department" value="-1" />
	        <input type="text" class="text" name="dept_name" value="<?=$AppUI->_('All');?>" size="15" disabled />
	        <input type="button" class="button" value="..." onclick="popDept()" />
				</td>
					<td>
					<?					
						echo arraySelect( $tiposPermiso, "personal", 'class="text"', -1, true,'','125px');
					?>
					</td>
					<td>
					<?					
						echo arraySelect( $tiposPermiso, "matrix", 'class="text"', -1, true,'','125px');
					?>
					</td>
					<td>
					<?					
						echo arraySelect( $tiposPermiso, "work_experience", 'class="text"', -1, true,'','125px');
					?>
					</td>
					<td>
					<?					
						echo arraySelect( $tiposPermiso, "education", 'class="text"', -1, true,'','125px');
					?>
					</td>
					<td>
					<?					
						echo arraySelect( $tiposPermiso, "performance_management", 'class="text"', -1, true,'','125px');
					?>
					</td>
					<td>
					<?					
						echo arraySelect( $tiposPermiso, "compensations", 'class="text"', -1, true,'','125px');
					?>
					</td>
					<td>
					<?					
						echo arraySelect( $tiposPermiso, "development", 'class="text"', -1, true,'','125px');
					?>
					</td>
				</tr>
				<tr>
					<td align="right" colspan='10'>
						<input type="button" value="<?=$AppUI->_("add permission")?>" class="button" onClick="submitItNew()" />
					</td>
				</tr>
		</table>
	</td>
</tr>
<tr>
	<td align="right">
		<br>
		<input class="button"  type="button" value="<?php echo $AppUI->_('cancel');?>" onClick="javascript: refrescar_padre();window.close();" />
		&nbsp;&nbsp;&nbsp;
		<input class="button"  type="button" value="<?php echo $AppUI->_('submit');?>" onClick="javascript: submitIt();refrescar_padre();window.close();" />
	</td>
</tr>
</table>
</form>
</body>