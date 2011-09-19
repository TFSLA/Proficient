<?php /* ADMIN  $Id: vw_usr.php,v 1.2 2009-07-27 15:53:19 nnimis Exp $ */
global $let, $utype, $orderby;

$link = "index.php?m=admin&tab=0&utype=$utype&stub=";

if( $AppUI->getState( 'Revert' ) == "1" ) $revert = "0";
else $revert = "1";

$downImage = "<img src='./images/arrow-down.gif' border='0' alt='".$AppUI->_("Ascending")."'>";
$upImage = "<img src='./images/arrow-up.gif' border='0' alt='".$AppUI->_("Descending")."'>";
$orderImage = (($revert == "0" || empty($revert)) ? $upImage : $downImage);
?>
<table border="0" cellpadding="0" cellspacing="0" background="images/common/back_botones-01.gif">
  <tr>
    <td><table border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
          <td><img src="images/common/cuadradito_naranja.gif" width="9" height="9">
          <a href="<?php echo $link."0";?>"><?php echo $AppUI->_('All'); ?></a>
            <?php
                for ($c=65; $c < 91; $c++) {
                    $cu = chr( $c );
                    //$cell = strpos($let, "$cu") > 0 ?
                    if(strpos($let, "$cu") > 0){
            ?>
            <a href="<?php echo $link.$cu ?>"><?php echo $cu;?></a>&nbsp;
            <?php     }else{ ?>
            <font color="#999999"><?php echo $cu; ?></font>
            <?php     }
                }
            ?>
            </td>
          <td width="6"> <div align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></div></td>
        </tr>
      </table></td>
  </tr>
</table>
<table cellpadding="2" cellspacing="0" border="0" width="100%">
<tr class="tableHeaderGral">
	<td width="80" align="right" class="tableHeaderText" nowrap="nowrap">
		&nbsp; <?php echo $AppUI->_('sort by');?>:&nbsp;
	</td>
	<td width="150" class="tableHeaderText">
		<?php if($orderby == "user_username") echo $orderImage; ?>
		<a href="javascript:ordenar('user_username','<?=$revert?>');" class=""><?=$AppUI->_('Login Name');?></a>
	</td>
	<td class="tableHeaderText">
		<?php if($orderby == "user_last_name") echo $orderImage; ?>
		<a href="javascript:ordenar('user_last_name','<?=$revert?>');" class=""><?php echo $AppUI->_('Real Name');?></a>
	</td>
	<td class="tableHeaderText">
		<?php if($orderby == "company_name") echo $orderImage; ?>
		<a href="javascript:ordenar('company_name','<?=$revert?>');" class=""><?php echo $AppUI->_('Company');?></a>
	</td>
	<td class="tableHeaderText">
		<?php if($orderby == "user_status") echo $orderImage; ?>
		<a href="javascript:ordenar('user_status','<?=$revert?>');" class=""><?php echo $AppUI->_('Status');?></a>
	</td>
</tr>
<?php
if (count($users)){
	foreach ($users as $row) {
		if ($row['user_status']){
			$b[0]="<font color='red'>";
			$status=$AppUI->_('In-Active');
			$b[1]="</font>";
		}
		else {
			$b[0]="<font color='blue'><b>";
			$status=$AppUI->_('Active');
			$b[1]="</b></font>";
		}
		?>
		<tr>
			<td align="right" nowrap="nowrap" width=70 >
		<?php 
		$canEdit = (!es_admin($row["user_id"]) OR ( es_admin($row["user_id"]) AND $AppUI->user_type == 1));
		
			if ($canEdit) { 
				?>
					<table align=center width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td>
							<a href="./index.php?m=admin&a=addedituser&user_id=<?php echo $row["user_id"];?>" title="<?php echo $AppUI->_('edit');?>">
								<?php echo dPshowImage( './images/icons/edit_small.gif', 20, 20, $AppUI->_('edit') ); ?>
							</a> 
						</td>
						<td style='font-size: xx-small; text-align: left; vertical-align: top;'>
							<a style='vertical-align: middle' href="?m=admin&a=viewuser&user_id=<?php echo $row["user_id"];?>&tab=1" title="<?php echo strtolower($AppUI->_('Add or Edit Permissions'));?>">
								<img src="images/obj/edit_permissions_small.gif" width="20" height="20" border="0" alt="<?php echo strtolower($AppUI->_('Add or Edit Permissions'));?>">
							</a>
							(<?php if ($row['perm_count']) echo "<b>".$row['perm_count']."</b>"; else echo $row['perm_count'];?>) 
						</td>
						<td>
							<a href="javascript:delMe(<?php echo $row["user_id"];?>, '<?php echo $row["user_first_name"] . " " . $row["user_last_name"];?>')" title="<?php echo $AppUI->_('delete');?>">
								<?php echo dPshowImage( './images/icons/trash_small.gif',16, 16, $AppUI->_('delete') ); ?>
							</a>
						</td>
					</tr>
					</table>
				<?php 
			}
			?>
			</td>
			<td>
				<a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $row["user_id"];?>">
					<?php echo $b[0].$row["user_username"].$b[1];?>
				</a>
			</td>
			<td>
				<a href="mailto:<?php echo $row["user_email"];?>" title="email"><img src="images/obj/email.gif" width="16" height="16" border="0" alt="email"></a>
				<?php echo $b[0].$row["user_last_name"].', '.$row["user_first_name"].$b[1];?>
			</td>
			<td>
				<a href="./index.php?m=companies&a=view&company_id=<?php echo $row['user_company'];?>"><?php echo $b[0].$row["company_name"].$b[1];?></a>
			</td>
			<td>
				<?php echo $b[0].$status.$b[1];?>
			</td>
		</tr>
		<tr>
				<td colspan="20" bgcolor="#E9E9E9"></td>
		</tr>
		<?php
		 
	}
}
else{
	?>
	<tr>
			<td colspan="20" bgcolor="#E9E9E9"><?php echo $AppUI->_("No data available");?></td>
	</tr>
	<?php
}
?>

</table>