<?php /* Lista las incidencias asociadas a la tarea */

global $g_status_colors, $obj;
require_once( "./modules/timexp/report_to_items.php" );
	
$xajax->printJavascript('./includes/xajax/');


$task_id = intval( dPgetParam( $_GET, "task_id", 0 ) );
$project_id = $obj->project_id;

$tab = $AppUI->state['ProjVwTab'];


// Cargo archivos de configuracion de webtracking
require_once("./modules/webtracking/config_inc.php");

if($g_status_enum_string == "")
{
	require_once("./modules/webtracking/config_defaults_inc.php");
}else{
	$status_enum_string = $g_status_enum_string;
}

// Cargo los archivos de lenguaje
if($AppUI->user_prefs['LOCALE'] == 'es'){
  require_once("./modules/webtracking/lang/strings_spanish.txt");
}else{
  require_once("./modules/webtracking/lang/strings_english.txt");	
}

// Preparo vector para asociar id de status con sus colores
$status_string = explode (",", $status_enum_string);

if($g_show_attachment_indicator == "")
{
	require_once("./modules/webtracking/config_defaults_inc.php");
	$t_show_attachments = $g_show_attachment_indicator;
}


if (count($g_status_colors) < count($status_string))
{
	$config_string = $g_status_colors;
	
	require_once("./modules/webtracking/config_defaults_inc.php");
	
	$config_default_string = $g_status_colors;
	
	$status_colors = array_merge($config_string, $config_default_string);
}


for ($i=0; $i < count($status_string); $i ++)
{
	$item = explode (":", $status_string[$i]);
	
	$status[$item[0]][desc] = $item[1];
	$status[$item[0]][color] = $status_colors[$item[1]];
}

// Cargo la severidad

if($g_severity_enum_string == "")
{
	require_once("./modules/webtracking/config_defaults_inc.php");
}

$severity_string = explode (",", $g_severity_enum_string);

for ($i=0; $i < count($severity_string); $i ++)
{
	$item = explode (":", $severity_string[$i]);
	
	$severity[$item[0]] = $item[1];
}

$sort = $_GET['sort'];
$dir = $_GET['dir'];

if ($sort == "")
{
	$sort = "last_updated";
	$dir = "DESC";
}

switch ($sort)
{
	case "priority":
		$query_ord = "order by priority $dir, last_updated DESC ";
		break;
	case "id":
		$query_ord = "order by id $dir, last_updated DESC ";
		break;
	case "category":
		$query_ord = "order by category $dir, last_updated DESC ";
		break;
	case "severity":
		$query_ord = "order by severity $dir, last_updated DESC ";
		break;
	case "status":
		$query_ord = "order by status $dir, last_updated DESC ";
		break;
	case "last_updated":
		$query_ord = "order by last_updated $dir";
		break;
	case "summary":
		$query_ord = "order by summary $dir, last_updated DESC";
		break;
	default:
		$query_ord = "order by last_updated $dir ";
}

// Hago la consulta sin paginar para saber cuantas incidencias hay en total
$query_count = "SELECT count(id) 
          FROM btpsa_bug_table 
          WHERE project_id = '".$project_id."'";
$count = db_loadColumn($query_count);
$bug_count = $count[0];


// Con el id del proyecto traigo el nivel de acceso del usuario para este proyecto
$acces_query = "SELECT access_level FROM btpsa_project_user_list_table WHERE project_id='".$project_id."' AND user_id = '".$AppUI->user_id."'";
$sql_access = db_loadColumn($acces_query);

$access_level = $sql_access[0];


// Preparo la paginacion
$page_number = intval( dPgetParam( $_GET, "page_number", 1 ) );

$r_per_page = 20;

?>

<form method="post" action="index.php?m=webtracking&amp;a=bug_actiongroup_page&o=projects&project_id=<?=$project_id?>">

<input type="hidden" name="task_id" value="<?=$task_id?>">

<table class="width100" border="0" cellspacing="1">
    <tbody>
    
      <td class="form-title" colspan="10" nowrap="nowrap">
		<?php echo $AppUI->_( 'viewing_bugs_title' ) ?>
		<?php
		    
			if ( $bug_count > 0 ) {
				$v_start = $r_per_page * ($page_number-1) +1;
				$v_end   = $v_start + $r_per_page -1;
				
				$v_start_sql = $r_per_page * ($page_number-1);
				
			} else {
				$v_start = 0;
				$v_end   = 0;
				
				$v_start_sql = 0;
			}
			
			if($v_end > $bug_count)
			{
				$v_end = $bug_count;
			}
			
			echo "($v_start - $v_end / $bug_count)";
			
			$page_count = ceil($bug_count/$r_per_page);
			
			 
			$limit = "LIMIT $v_start_sql, $r_per_page ";
			
		    // Traigo las incidencias asociadas a esta tarea con paginacion
			$query = "SELECT id, project_id, priority, status, date_format(last_updated,'%d/%m/%Y') as date_update, date_submitted, summary, task_id,category, severity,handler_id,user_username  
			          FROM btpsa_bug_table 
			          LEFT JOIN users ON users.user_id = reporter_id 
			          WHERE project_id = '".$project_id."' $query_ord $limit";
			
			//echo "<pre>".$query."</pre>";
			$sql = db_exec($query);
			
		?>
		<?php /*  Print and Export links  */ ?>
		<span class="small">
		   [ <a href="index.php?m=webtracking&a=print_all_bug_page&project_id=<?=$project_id?>&o=projects"><?=$AppUI->_('print_all_bug_page_link')?></a> ]
		   
		   [ <a href="index.php?m=webtracking&suppressHeaders=yes&a=csv_export&includeajax=0&project_id=<?=$project_id?>&o=projects"><?=$AppUI->_('csv_export')?></a> ]
		   
		   [ <a href="index.php?m=webtracking&a=bug_report_page&project_id=<?=$project_id?>&o=projects"><?=$AppUI->_('Report Incidence')?></a> ]
		</span>
	  </td>
     
      <tr class="row-category">
         <td class="center" width="20">&nbsp;</td>
	     <td class="center" width="20">&nbsp;</td>
	     <td class="center" width="15">&nbsp;</td>
         
	     <?
	        if ($dir == "ASC"){
	        	$new_dir = "DESC";
	        	$icon_dir = "./modules/webtracking/images/up.gif";
	        }else{
	        	$new_dir = "ASC";
	        	$icon_dir = "./modules/webtracking/images/down.gif";
	        }
	        
	     ?>  
	     
		 <td class="center"  nowrap="nowrap">
		    <? if ($sort == 'priority' || $sort =="") { $dir_p = $new_dir; }else{$dir_p = $dir; }?>
		    <a href="index.php?m=projects&a=view&project_id=<?=$project_id?>&tab=<?=$tab?>&sort=priority&dir=<?=$dir_p?>" style="text-decoration: none;"><font color="#ffffff">P</font>
		    <? if ($sort == 'priority') { echo "<img src=\"$icon_dir\" width=\"10\" border=0>"; } ?>
		    </a>			
		 </td>

		 <td class="center" width="52" nowrap="nowrap">
		    <? if ($sort == 'id' || $sort =="") { $dir_id = $new_dir; }else{$dir_id = $dir; } ?>
		    <a href="index.php?m=projects&a=view&project_id=<?=$project_id?>&tab=<?=$tab?>&sort=id&dir=<?=$dir_id?>" style="text-decoration: none;"><font color="#ffffff">ID</font>
		    <? if ($sort == 'id') { echo "<img src=\"$icon_dir\" width=\"10\" border=0>"; } ?>
		    </a>			
		 </td>

		 <td class="center" >
		  #
	     </td>
	     
	     <? if ( ON == $t_show_attachments ) { ?>
	     <td class="center" >
		  <img src="./modules/webtracking/images/attachment.png" border="0">
	     </td>
	     <? } ?>

	
		 <td class="center"  nowrap="nowrap">
		    <? if ($sort == 'category' || $sort =="") { $dir_cat = $new_dir; }else{$dir_cat = $dir; } ?>
			<a href="index.php?m=projects&a=view&project_id=<?=$project_id?>&tab=<?=$tab?>&sort=category&dir=<?=$dir_cat?>" style="text-decoration: none;"><font color="#ffffff"><?=$AppUI->_('category')?></font>
			<? if ($sort == 'category') { echo "<img src=\"$icon_dir\" width=\"10\" border=0>"; } ?>
			</a>				
		 </td>

		 <td class="center"  nowrap="nowrap">
		    <? if ($sort == 'severity' || $sort =="") { $dir_s = $new_dir; }else{$dir_s = $dir; } ?>
		    <a href="index.php?m=projects&a=view&project_id=<?=$project_id?>&tab=<?=$tab?>&sort=severity&dir=<?=$dir_s?>" style="text-decoration: none;"><font color="#ffffff"><?=$AppUI->_('severity')?></font>
		    <? if ($sort == 'severity') { echo "<img src=\"$icon_dir\" width=\"10\" border=0>"; } ?>
		    </a>			
		 </td>

		 <td class="center" >
		    <? if ($sort == 'status' || $sort =="") { $dir_st = $new_dir; }else{$dir_st = $dir; } ?>
		    <a href="index.php?m=projects&a=view&project_id=<?=$project_id?>&tab=<?=$tab?>&sort=status&dir=<?=$dir_st?>" style="text-decoration: none;"><font color="#ffffff"><?=$AppUI->_('status')?></font>
		    <? if ($sort == 'status') { echo "<img src=\"$icon_dir\" width=\"10\" border=0>"; } ?>
		    </a>			
		 </td>

		 <td class="center" nowrap="nowrap">
		   <? if ($sort == 'last_updated' || $sort =="") { $dir_lu = $new_dir; }else{$dir_lu = $dir; } ?>
		   <a href="index.php?m=projects&a=view&project_id=<?=$project_id?>&tab=<?=$tab?>&sort=last_updated&dir=<?=$dir_lu?>" style="text-decoration: none;"><font color="#ffffff"><?=$AppUI->_('last_updated')?></font>
		   <? if ($sort == 'last_updated') { echo "<img src=\"$icon_dir\" width=\"10\" border=0>"; } ?>
		   </a>		
		 </td>

		 <td class="center" width="100%">
		    <? if ($sort == 'summary' || $sort =="") { $dir_su = $new_dir; }else{$dir_su = $dir; } ?>
		    <a href="index.php?m=projects&a=view&project_id=<?=$project_id?>&tab=<?=$tab?>&sort=summary&dir=<?=$dir_su?>" style="text-decoration: none;"><font color="#ffffff"><?=$AppUI->_('summary')?></font>
		    <? if ($sort == 'summary') { echo "<img src=\"$icon_dir\" width=\"10\" border=0>"; } ?>
		    </a>			
		 </td>
      </tr>
      
      <tr>
	     <td class="spacer" colspan="10">&nbsp;</td>
      </tr>
      
      
      <?
        while($row = mysql_fetch_array($sql))
        {
        	
        $id_string = str_pad($row['id'],7,"0",STR_PAD_LEFT);
        
      ?>
            <span onmouseover="tooltipLink('<pre style=&quot;margin:0px;&quot;><?=$AppUI->_('Reporter')?>: <?=$row['user_username']?></pre>', '');" onmouseout="tooltipClose();">
            
        	<tr bgcolor="<?=$status[$row['status']]['color']?>">
        	  <td>
        	  <?php if(!getDenyEdit("timexp")) { ?>
        	    <a href='javascript:report_hours("<?=$row['id']?>",6);' >
        	      <img src='./images/icons/calendar_report.png' alt='<?=$AppUI->_('Report time')?>' border=0 >
        	    </a>
        	    <?php }
        	    else echo "&nbsp;"; ?>
        	  </td>
        	  <td>
        	    <?  // Check para seleccionar multiples registros para acciones, verificar permisos de acuerdo a proyecto               
                    // Si el nivel de acceso es mayor a 25 puede editar y realizar las demas acciones
                    if ($access_level > '25' || $AppUI->user_type == '1'){
                    echo "<input name=\"bug_arr[]\" value=\"".$row['id']."\" type=\"checkbox\">";
                    }
        	    ?>
        	  </td>
        	   
        	  <td>
        	    <?  // Icono de lapiz para editar la incidencia, verificar permisos de acuerdo a proyecto  ?>
        	    
        	    <? if ($access_level > '25' || $AppUI->user_type == '1'){ ?>
        	    <a href="index.php?m=webtracking&amp;a=bug_update_page&amp;bug_id=<?=$id_string?>&project_id=<?=$project_id?>&o=projects"><img src="./modules/webtracking/images/update.gif" alt="Actualizar Incidencia" border="0"></a>	
        	    
        	    <? } ?>
        	    
        	  </td>
        	  
        	  <td align="center">
        	    <?
        	      // Prioridad
        	      $p = $row['priority'];
        	       
        	      switch ($p)
        	      {
        	      	case "10":
        	      	   $img = "<img src=\"./modules/webtracking/images/priority_none.gif\" >"; 
        	           break;
        	      	
        	      	case "20":
        	      	   $img = "<img src=\"./modules/webtracking/images/priority_low.gif\" >"; 
        	      	   break;
        	      	   
        	      	case "30":
        	      		$img = "&nbsp;";
        	      		break;
        	      		
        	      	case "40":
        	      		$img = "<img src=\"./modules/webtracking/images/priority_1.gif\" >";
        	      		break;
        	      		
        	      	case "50":
        	      		$img = "<img src=\"./modules/webtracking/images/priority_2.gif\" >";
        	      		break;
        	      		
        	      	case "60":
        	      		$img = "<img src=\"./modules/webtracking/images/priority_3.gif\" >";
        	      		break;
        	      	
        	      }
        	      
        	      echo $img;
        	    ?>
        	  </td>
        	 
        	  <td align="center">
        	     <? // Id de la inicidencia ?>
                 <a href="index.php?m=webtracking&a=bug_view_page&bug_id=<?=$id_string?>&project_id=<?=$project_id?>"><?=$id_string?></a>
        	  </td>
        	  
        	  <td align="center">
        	     <?
        	        // Cantidad de notas de la incidencia
        	        $query_cant = "SELECT count(id) FROM btpsa_bugnote_table WHERE bug_id = '".$row['id']."' ";
        	        $cant = db_loadColumn($query_cant);
        	        
        	        if($cant[0] > 0){
        	        echo "<a href=\"index.php?m=webtracking&a=bug_view_page&project_id=$project_id&bug_id=$id_string#bugnotes\">".$cant[0]."</a>";
        	        }

        	     ?>
        	  </td>
        	  
        	  <? if ( ON == $t_show_attachments ) { ?>
        	  <td align="center"> 
        	     <?
        	        // Cantidad de attachments de la incidencia
        	        $query_cant_files = "SELECT count(id) FROM btpsa_bug_file_table WHERE bug_id = '".$row['id']."' ";
        	        $cant_files = db_loadColumn($query_cant_files);
        	        
        	        if($cant_files[0]>0){
        	        	echo "<a href=\"index.php?m=webtracking&a=bug_view_page&project_id=$project_id&bug_id=$id_string#attachments\">";
						echo '<img border="0" src="./modules/webtracking/images/attachment.png"';
						echo ' title="'.$cant_files[0].' '.$AppUI->_('attachments').' "';
						echo ' />';
						echo '</a>';
        	        }else{
        	        	echo '&nbsp;';
        	        }
        	     ?>
        	  </td>
        	  <? } ?>
        	  
        	  <td nowrap>
        	     <?
        	        // Categoria
        	        
        	        echo $row['category'];
        	     
        	     ?>
        	  </td>
        	     
        	  <td align="center" nowrap>
        	     <?
        	        // Categoria
        	        
        	        echo $AppUI->_($severity[$row['severity']]);
        	     
        	     ?>
        	  </td>
        	  
        	  <td align="center" nowrap>
        	     <?
        	       // Status
        	       echo $AppUI->_($status[$row['status']][desc]);
        	       
        	       if ($row['status'] == '50' || $row['status'] == '20' )
        	       {
        	       	$query_user = "SELECT user_username FROM users WHERE user_id='".$row['handler_id']."'";
        	       	$sql_user = db_loadColumn($query_user);
        	       	echo "(".$sql_user[0].")";
        	       }
        	     ?>
        	  </td>
        	  
        	  <td align="center">
        	     <?
        	        //  Fecha de actualizacion
        	        echo $row['date_update'];
                 ?>
        	  </td>
        	  
        	  <td>
        	     <?
        	        // Descripcion
        	        echo $row['summary'];
        	     ?>
        	  </td>
        	</tr>
        	</span>
      <?
        }
      ?>
       
            <tr>
              <td align="right" colspan="12">
              
			        <table border="0">
			           <tr>
			              <? if ($access_level > '25' || $AppUI->user_type == '1'){ 
			              	
			              	$actions_vec["MOVE"] = $AppUI->_('actiongroup_menu_move');
			              	$actions_vec["ASSIGN"] = $AppUI->_('actiongroup_menu_assign');
			              	$actions_vec["CLOSE"] = $AppUI->_('actiongroup_menu_close');
			              	$actions_vec["DELETE"] = $AppUI->_('actiongroup_menu_delete');
			              	$actions_vec["RESOLVE"] = $AppUI->_('actiongroup_menu_resolve');
			              	$actions_vec["UP_PRIOR"] = $AppUI->_('actiongroup_menu_update_priority');
			              	$actions_vec["UP_STATUS"] = $AppUI->_('actiongroup_menu_update_status');
			              	$actions_vec["ASSOCIATE_TASKS"] = $AppUI->_('actiongroup_menu_associate_tasks') ;
			              	                      
			              ?>
			              <td>
				          <? echo arraySelect($actions_vec, "action", "class='small' ",'',true,true); ?>
			              </td>
			              
			              <td>
						     <input type="submit" class="button" value="<?php echo 'OK';  ?>" />
			              </td>
			              
			              <? } ?>
			              
			              <td width="2">
			              </td>
			
			              <?php /*  Page number links  */ ?>
						  <td class="right" >
							 <span class="small">
							    <?php  print_page_links( "index.php?m=projects&a=view&project_id=$project_id&tab=$tab", 1, $page_count, $page_number ) ?>
							 </span>
						  </td>
			           </tr>    
			        </table>
              </td>
            </tr>
    </tbody>
</table>

</form>
<br>

<? // Codigo de colores
   echo '<table class="width100" cellspacing="1" border=0>';
	   echo '<tr>';
		
		$enum_count = count( $status );
		$width = (integer) (100 / $enum_count);
		
		if ($enum_count > 0)
		{
			foreach ($status as $id_status=>$values)
			{
				if ($values['desc'] == "updated"){
					$string = $AppUI->_('updated_bug');
				}else{
					$string = $AppUI->_($values['desc']);
				}
				
				echo "<td class=\"small-caption\" width=\"$width%\" bgcolor=\"".$values['color']."\">".$string."</td>";
			}
		}
		
	   echo '</tr>';
   echo '</table>';
?>

<?
// Funciones

    # print a HTML page link
	function print_page_link( $p_page_url, $p_text="", $p_page_no=0, $p_page_cur=0 ) {
		if (is_blank( $p_text )) {
			$p_text = $p_page_no;
		}

		if ( ( 0 < $p_page_no ) && ( $p_page_no != $p_page_cur ) ) {
			print " <a href=\"$p_page_url&page_number=$p_page_no\">$p_text</a> ";
		} else {
			print " $p_text ";
		}
	}
	
	# --------------------
	# print a list of page number links (eg [1 2 3])
	function print_page_links( $p_page, $p_start, $p_end, $p_current ) {
		global $AppUI;
		
		$t_items = array();
		$t_link = "";

		# Check if we have more than one page,
		#  otherwise return without doing anything.

		if ( $p_end - $p_start < 1 ) {
			return;
		}

		# Get localized strings 
		$t_first = $AppUI->_( 'first' );
		$t_last  = $AppUI->_( 'last' );
		$t_prev  = $AppUI->_( 'prev' );
		$t_next  = $AppUI->_( 'next' );

		$t_page_links = 10;

    print( "[ " );

		# First and previous links
		print_page_link( $p_page, $t_first, 1, $p_current );
		print_page_link( $p_page, $t_prev, $p_current - 1, $p_current );
		
		# Page numbers ...

		$t_first_page = max( $p_start, $p_current - $t_page_links/2 );
		$t_first_page = min( $t_first_page, $p_end - $t_page_links );
		$t_first_page = max( $t_first_page, $p_start );

		if ( $t_first_page > 1 )
			print( " ... " );

		$t_last_page = $t_first_page + $t_page_links;
		$t_last_page = min( $t_last_page, $p_end );

		for ( $i = $t_first_page ; $i <= $t_last_page ; $i++ ) {
			if ( $i == $p_current ) {
				array_push( $t_items, $i );
			} else {
				array_push( $t_items, "<a href=\"$p_page&page_number=$i\">$i</a>" );
			}
		}
		echo implode( '&nbsp;', $t_items );

		if ( $t_last_page < $p_end )
			print( " ... " );

		# Next and Last links
		if ( $p_current < $p_end )
			print_page_link( $p_page, $t_next, $p_current + 1, $p_current );
		else
			print_page_link( $p_page, $t_next );
		print_page_link( $p_page, $t_last, $p_end, $p_current );

    print( " ]" );
	}
    
	function is_blank( $p_var ) {
		if ( strlen( trim( $p_var ) ) == 0 ) {
			return true;
		}
		return false;
	}
?>
     

