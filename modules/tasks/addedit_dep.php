<?php /* TASKS $Id: addedit_dep.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */
/**
* Tasks :: Add/Edit - Tab Dependencias 
*/

?>

 <script language = "javascript" >
    
	function addTaskDependency() {
                  
		var form = document.editFrm;
		var at = form.all_tasks.length -1;
		var td = form.task_dependencies.length -1;
		var tasks = "x";
        
		
			//build array of task dependencies
			for (td; td > -1; td--) {
				tasks = tasks + "," + form.task_dependencies.options[td].value + ","
			}
			

			//Pull selected resources and add them to list
			for (at; at > -1; at--) {
				if (form.all_tasks.options[at].selected 
					&& tasks.indexOf( "," + form.all_tasks.options[at].value + "," ) == -1
					&& at > 0) {
						
					t = form.task_dependencies.length
					opt = new Option( form.all_tasks.options[at].text, form.all_tasks.options[at].value );
					form.task_dependencies.options[t] = opt
					
					form.all_tasks.options[at] = null;
					
				}
			}
			
			var tempC = new Array();
		    var cd = form.task_dependencies.length -1;
            var z = 0;
		    
            for(cd; cd > -1; cd--)
            {
            	tempC[z] = form.task_dependencies.options[cd].text;
            	z = z + 1;
            }
            
            tempC.sort().reverse();
            
            var at = form.task_dependencies.length -1;
            
			for (at; at > -1; at--) {
				  form.task_dependencies.options[at] = null;
			}
            
            var ct = tempC.length -1;
            
            <? echo $strJs_ao; ?>
            
            t3 = 0;
            
            for (ct; ct > -1; ct--)
            {
            	
            	var tl = tasksP.length -1;
            	
            	for ( tl; tl > -1; tl --)
		        {
		        	if(tasksP[tl][2] == tempC[ct] )
		        	{
				        opt3 = new Option( tasksP[tl][2] , tasksP[tl][0]  );
				
				        form.task_dependencies.options[t3] = opt3;
				        t3 = t3 + 1;
				        
				        tl = -1;
				        
		        	}
		        }
            	
            }
			
			
			execute_remote_script("add_del_dependencies");
		
	}

    function removeTaskDependency() {

		var form = document.editFrm;
		var td = form.task_dependencies.length -1;
		var at = form.all_tasks.length -1;

		for (td; td > -1; td--) {
			if (form.task_dependencies.options[td].selected) {

				t = form.all_tasks.length
				opt = new Option( form.task_dependencies.options[td].text, form.task_dependencies.options[td].value );
				form.all_tasks.options[t] = opt

				form.task_dependencies.options[td] = null;

			}
		 }
        
		 <? echo $strJs_ao; ?>

		  var tempC = new Array();
		  var tempD = new Array();
          var z = 0;
          
		  for ( tl=0; tl <= tasksP.length -1; tl ++)
		  {
			var sta = 0;
            var ts = form.all_tasks.length -1;   
            
			for (ts; ts > -1; ts--) {

				if(tasksP[tl][0] == form.all_tasks[ts].value )
				{
				 if(tasksP[tl][0] >0){
                 tempC[z] = tasksP[tl][0];
				 tempD[z] = tasksP[tl][2];
                 z = z + 1;
                 }
				}
			}

		  }
          
         var at = form.all_tasks.length -1;
       
		 for (at; at > -1; at--) {
			  form.all_tasks.options[at] = null;
		  }
        

		t = 0;
		opt = new Option( "<?php echo $AppUI->_('None');?>" , form.task_parent.value );
		form.all_tasks.options[t] = opt;

		for (r=0; r<= tempC.length -1; r++)
		{   
			if (form.task_id.value != tempC[r])
			{
				t = t + 1;
				opt = new Option( tempD[r] , tempC[r]  );
				
				form.all_tasks.options[t] = opt;
			}
		}

		execute_remote_script("add_del_dependencies");

    }
    

	function parent_change(obj){
        
		var form = document.editFrm;
		var tp = form.task_parent.length -1;
        var ntaB = '0';
		var ntaC = '0';
		
		update_afterof(obj.value);
		 // Recorro el vector de tareas padres, para saber cual falta en las posibles dependencias y en las dependencias
    
		for (tp; tp > -1; tp--) 
		{ 
          var at = form.all_tasks.length -1;
          var parent = form.task_parent[tp].value;
          var td = form.task_dependencies.length -1;        
		  var staB = '0';
		  var staC = '0';

		  // Primero me fijo si esta como padre
		  for (at; at > -1; at-- )
		  {  
		      if(parent == form.all_tasks[at].value && parent > 0)
			  {
			   staB = parent;
			   //alert(parent +' - Esta en todas las tareas');
			  }
		  }
          
		  if(staB == 0 && parent > 0)
		  {
		   ntaB = parent;
		   var indB = tp;
		  }
          
		  // Me fijo si esta como dependencia 
		  for (td; td > -1; td-- )
		  {  
		      if(parent == form.task_dependencies[td].value && parent > 0)
			  {
			   staC = parent;
			  }
		  }
          
		  if(staC == 0 && parent > 0)
		  {
		   ntaC = parent;
		   var indC = tp;
		  }

            
		}
		  
          if(ntaC !='0' && ntaB !="0")
		  { 
			t = form.all_tasks.length;
			 
			opt = new Option( form.task_parent[indB].text, form.task_parent.options[indB].value );
			form.all_tasks.options[t] = opt;
			
		  }
         
		 var at = form.all_tasks.length -1;
       
		 for (at; at > -1; at--) {
             
			 if( obj.value == form.all_tasks[at].value && obj.value > 0)
			 {
			  form.all_tasks.options[at] = null;
			 }
		  }
         
		 var td = form.task_dependencies.length -1;   
		 var act = false;
		 
		 for (td; td > -1; td--) {
			 if( obj.value == form.task_dependencies[td].value && obj.value > 0)
			 {
			  form.task_dependencies.options[td] = null;
              act = true;
			 }
		  }
		  
		 var tk = form.task_dependencies.length -1;
		 var tl = form.all_tasks.length -1;    
		 
		 for (tk; tk > -1; tk--) {
			
			for (tl; tl >-1; tl--){
				if(form.all_tasks[tl].value == form.task_dependencies[tk].value)
				{ 
					//alert('saco: '+form.all_tasks[tl].value);
					form.all_tasks.options[tl] = null;
					tl = -1;
				}
			}
		  }
          
		  <? echo $strJs_ao; ?>

		  var tempC = new Array();
		  var tempD = new Array();
          var z = 0;
          
		  for ( tl=0; tl <= tasksP.length -1; tl ++)
		  {
			var sta = 0;
            var ts = form.all_tasks.length -1;   
            
			for (ts; ts > -1; ts--) {

				if(tasksP[tl][0] == form.all_tasks[ts].value && tasksP[tl][0] != '<?echo $obj->task_id; ?>')
				{
				 if(tasksP[tl][0] >0){
                 tempC[z] = tasksP[tl][0];
				 tempD[z] = tasksP[tl][2];
                 z = z + 1;
                 }
				}
			}

		  }
          
         var at = form.all_tasks.length -1;
       
		 for (at; at > -1; at--) {
             
			 if( obj.value > 0)
			 {
			  form.all_tasks.options[at] = null;
			 }
		  }
        

		t = 0;
		opt = new Option( "<?php echo $AppUI->_('None');?>" , form.task_parent.value );
		form.all_tasks.options[t] = opt;

		for (r=0; r<= tempC.length -1; r++)
		{   
			t = t + 1;
			opt = new Option( tempD[r] , tempC[r]  );
			form.all_tasks.options[t] = opt;
		}
         

		  if(act){
          execute_remote_script("add_del_dependencies");
		  }else{
		  execute_remote_script("add_del_parent");
		  }

	}

</script>

<table cellspacing="2" cellpadding="0" border="0" width="98%" class="tableForm_bg">
  <col width="100"><col width="85%">
	  <tr>
		<td align="right" nowrap="nowrap" style="font-weight: bold;">
		  <?php echo $AppUI->_( 'Task Parent' );?>:</td>
		<td >
			<?php echo arraySelect($possible_parents, 'task_parent', 'class="text" onChange="parent_change(this);"',$task_parent,false,false); ?> 
		</td>		
	  </tr>
	  <tr>
		 <td colspan="4">
			<hr noshade="noshade" size="1">
		 </td>
	  </tr> 
  <tr>
	<td colspan="4">
		<table cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td><?php echo $AppUI->_( 'All Tasks' );?></td>
				<td><?php echo $AppUI->_( 'Task Dependencies' );?></td>
			</tr>
			<tr>
				<td>
					<?php echo arraySelect( $possible_dependences, 'all_tasks', 'style="width:250px" size="10" class="text" multiple="multiple"', null, false, false );?>
				</td>
				<td>
					<?php echo arraySelect( $taskDep, 'task_dependencies', 'style="width:250px" size="10" class="text" multiple="multiple"', null, false, false ); ?>
				</td>
			</tr>
			<tr>
				<td align="right"><input type="button" class="button"  value="&gt;" onClick="addTaskDependency()" /></td>
				<td align="left"><input type="button" class="button"  value="&lt;" onClick="removeTaskDependency()" /></td>
			</tr>
		</table>	
	</td>
  </tr>
</table>		
