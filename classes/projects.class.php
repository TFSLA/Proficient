<?php

class CProjects {

	var $companies = null;
	var $projects = null;
	var $tasks = null;
	var $item = array();
	var $items = array();//almacena items que seran insertados en Projectos
	
	var $_frm_name = "";
	var $_cbo_company_name = "companies";
	var $_cbo_project_name = "projects";
	var $_cbo_task_name = "task_id";
	var $_project_id_selected = "";
	
	var $_addItems_forEachcompany_inprojects = true;//agrega los items almacenados en el array Items en el array Projects por cada company
	var $_addItems_forEachproject_intasks = true;//agrega los items almacenados en el array Items en el array Tasks por cada projecto
	var $_breset_Items = true;//una vez insertados los registros se vacia
	

		   /* Función que trae las companies */

			function loadCompanies($ActiveProjects = null){
                global $AppUI;

				$allowed = array();
                  
				  // Traigo los projectos con tareas //
				  
				  $sql = "select DISTINCT task_project from tasks";
                 
				  $proj_con_tasks = db_loadColumn($sql);
                  
				

						//el SYSADMIN siempre puede ver todos los proyectos, asi que traigo todas las companies con proyectos que tengan tareas //
						if ($AppUI->user_type == 1){

		                    if (count( $proj_con_tasks)>0)
							{
							  $ActiveProjectsFilter = ($ActiveProjects == null ? "" : "AND project_active = 1");
							  
							  $strSql ="SELECT DISTINCT p.project_company as company_id,c.company_name
                                        FROM projects as p, companies as c 
                                        WHERE p.project_company=c.company_id and 
                                        project_id IN (" . implode( ',', $proj_con_tasks ) . ") 
                                        $ActiveProjectsFilter
									    ORDER BY company_name";
							  
							  $this->companies = db_loadHashList($strSql);

							 
							}
							else
							{
							  $this->companies = array();
							}
							
						}
						else
						{
                        
						// Proyectos con el rol negado //
						        if ($_GET[a]=="addeditexpense")
								{
								$on = "4";
								}
								else
								{
								$on = "3";
								}

                         
				 // Owner o admin -- sobre estas no reviso ningun permiso, puede hacer lo que quiera //


                   $strSqlP = "
					select project_id from projects where project_owner = $AppUI->user_id
					union
					select project_id from project_owners where project_owner = $AppUI->user_id
                    ";
                   
				   $owner = db_loadColumn($strSqlP);	

				    if (count($owner)=="0")
					{
					$owner[0] ="0" ;
					}
					
					// roles generales 

				    $strSqlP = "
                    select distinct task_project as project_id from task_permissions where task_user_id = $AppUI->user_id and task_access_id='3' and task_permission_on= $on";

					$user_project = db_loadColumn($strSqlP);	

					if (count($user_project)=="0")
					{
					$user_project[0] ="0" ;
					}

                    $strSqlP = "
					select distinct project_id from role_permissions where access_id='3' and item_id= $on and permission_value = '-1' and project_id NOT IN (" . implode( ',', $user_project) . ") 
					";
                    
                    $role_project_asigned = db_loadColumn($strSqlP);	
                    
					if (count($role_project_asigned)=="0")
					{
					$role_project_asigned[0] ="0" ;
					}

					$strSqlP = "
					select distinct project_id from role_permissions where access_id='3' and item_id= $on and permission_value = '0' and project_id NOT IN (" . implode( ',', $owner) . ") 
					";
                    
                    $role_project_asigned_negado = db_loadColumn($strSqlP);	

					if (count($role_project_asigned_negado)=="0")
					{
					$role_project_asigned_negado[0] ="0" ;
					}

				   // Traigo los projectos en el que es usuario . me fijo los permisos en los roles 

                   $strSqlP = "
                    select distinct task_project as project_id from task_permissions where task_user_id = $AppUI->user_id and task_access_id='3' and task_permission_on= $on and task_permission_value ='-1' 
					";

				    $user_role_asigned =  db_loadColumn($strSqlP);
                    
					if (count($user_role_asigned)=="0")
					{
					$user_role_asigned[0] ="0" ;
					}

                    
					$strSqlP = "
                    select distinct task_project as project_id from task_permissions where task_user_id = $AppUI->user_id and task_access_id='3' and task_permission_on= $on and task_permission_value ='0' 
					";

				    $user_role_asigned_negado =  db_loadColumn($strSqlP);
                    
					if (count($user_role_asigned_negado)=="0")
					{
					$user_role_asigned_negado[0] ="0" ;
					}


                    $strSqlP = "
					select project_id from project_roles where user_id = $AppUI->user_id and  user_units='100' and project_id IN (" . implode( ',', $user_role_asigned) . ")
					union
					select project_id from project_roles where user_id = $AppUI->user_id and  user_units='100' and project_id  NOT IN (" . implode( ',', $user_role_asigned) . ") and project_id IN (" . implode( ',',  $role_project_asigned) . ")   
					";


                    $user_aigned = db_loadColumn($strSqlP);	

					if (count($user_aigned)=="0")
					{
					$user_aigned[0] ="0" ;
					}
  
                    

				   // Traigo los projectos en el que es usuario . me fijo los permisos en los roles sobre project wide

				    $strSqlP = "
                    select distinct task_project as project_id from task_permissions where task_user_id = $AppUI->user_id and task_access_id='2' and task_permission_on= $on";

					$user_project_wide = db_loadColumn($strSqlP);	

					if (count($user_project_wide)=="0")
					{
					$user_project_wide[0] ="0" ;
					}

                    $strSqlP = "
					select distinct project_id from role_permissions where access_id='2' and item_id= $on and permission_value != '-1' and project_id NOT IN (" . implode( ',', $user_project_wide) . ") 
					";
                    
                    $role_project_wide = db_loadColumn($strSqlP);	

					if (count($role_project_wide)=="0")
					{
					$role_project_wide[0] ="0" ;
					}
                    

				   $strSqlP = "
                    select distinct task_project as project_id from task_permissions where task_user_id = $AppUI->user_id and task_access_id='2' and task_permission_on= $on 
					";
                    
				    $user_role_wide =  db_loadColumn($strSqlP);
                    
					if (count($user_role_wide)=="0")
					{
					$user_role_wide[0] ="0" ;
					}

                   $strSqlP = "
                    select distinct task_project as project_id from task_permissions where task_user_id = $AppUI->user_id and task_access_id='2' and task_permission_on= $on and task_permission_value ='-1' 
                    union

					select distinct project_id from role_permissions where item_id= $on and permission_value ='-1' and access_id='2' and project_id  NOT IN (" . implode( ',', $user_role_wide) . ") and project_id NOT IN (" . implode( ',', $role_project_wide) . ")
					";
                    
					$user_wide = db_loadColumn($strSqlP);	

                    if (count($user_wide)=="0")
					{
					$user_wide[0] ="0" ;
					}

                    
                   // Tareas en las que el user esta asignado //

					$strSqlP = "select task_id from user_tasks where user_id= $AppUI->user_id and task_id IN (" . implode( ',', $user_aigned  ) . ")";

                         
					$tareas_asignadas =  db_loadColumn($strSqlP);	


                    if (count($tareas_asignadas)=="0")
					{
					$tareas_asignadas[0] ="0" ;
					}      
                    
					
                    
					$strSql = "
					(select task_project as project_id from tasks 
				    where task_owner='$AppUI->user_id')

					 union

					(select task_project as project_id from tasks 
					 where task_project IN (" . implode( ',', $owner ) . "))

					 union

					(select t.task_project as project_id 
					 from tasks as t, user_tasks as u 
					 where u.task_id=t.task_id  and t.task_access='3'
					 and u.user_id='$AppUI->user_id'  and t.task_project IN  (" . implode( ',', $user_aigned  ) . "))

					 union

					(select t.task_project as project_id 
					 from tasks as t, user_tasks as u 
					 where u.task_id=t.task_id  and t.task_access='3'
					 and u.user_id='$AppUI->user_id' and t.task_id IN  (" . implode( ',', $tareas_asignadas  ) . ") and t.task_project NOT IN (" . implode( ',', $role_project_asigned_negado) . ") and t.task_project NOT IN (" . implode( ',', $user_role_asigned_negado) . "))

					 union

					(select t.task_project as project_id 
					 from tasks as t, user_tasks as u 
					 where u.task_id=t.task_id  and t.task_access='2'
					 and u.user_id='$AppUI->user_id'  and t.task_project IN  (" . implode( ',', $user_aigned  ) . "))
					
				     union
							 
					(select task_project as project_id 
					 from tasks
					 where task_access='2'
					 and task_project IN  (" . implode( ',', $user_wide  ) . ")and task_id NOT IN  (" . implode( ',', $tareas_asignadas) . "))
					";
                    
					
					$allowed =  db_loadColumn($strSql);	
						
						if (count($allowed)=="0")
						{
						$allowed[0] ="0" ;
						}

						// Companies con Projectos con tareas //
						$ActiveProjectsFilter = ($ActiveProjects == null ? "" : "AND project_active = 1");

                        $strSql ="SELECT DISTINCT p.project_company as company_id,c.company_name
                                        FROM projects as p, companies as c 
                                        WHERE p.project_company=c.company_id and 
                                        project_id IN (" . implode( ',', $allowed ) . ") 
                                        $ActiveProjectsFilter
									    ORDER BY company_name";
                        
					    $this->companies = db_loadHashList($strSql);

						}			   
			}

            /* Mete los proyectos en un array */
			function Companies(){
				return $this->companies;
			}
            
			/* Genera el html con el select de proyectos */
			function generateHTMLcboCompanies($selected="", $class=""){
				return arraySelect($this->Companies(), "idcompany", "class=\"$class\" style=\"width:220 px;\" tabindex=\"8\" onchange=\"javascript:changeProject();\" ", $selected, true, false);
				
			}

			function generateHTMLcboCompanies_tabla($selected="", $class="",$onchange){
				return arraySelectJs($this->Companies(), "idcompany[]", "class=\"$class\" ".$onchange, $selected, true);
			}

            
			/* Agrega un item a un array */
			function addItem($value, $text, $bstore=false){
				if($bstore){
					$this->items[] = array($value => $text);
				}else{
					$this->item = array($value => $text);
				}

				return $this->item;
			}

            /* Agrega un item al principio de un array */
            function addItemAtBeginOf(&$target, $item){
				if($target != "" && (is_array($item) || $item !== "")){
					$target = arrayMerge($item, $target);
				}
			}

            /* Agrega un item al principio del array companies */
			function addItemAtBeginOfCompanies($item){
				$this->addItemAtBeginOf($this->companies, $item);
			}


            /* Funcion que trae los proyectos de acuerdo a la company seleccionada */

			function loadProjects($intCompany = null, $ActiveProjects = null){
				 global $AppUI;

				 $allowed = array();


				// Traigo solo los projectos con tareas //
                  
				  $sql = "select DISTINCT task_project from tasks";
				 

				  $proj_con_tareas = db_loadColumn($sql);	


						//el SYSADMIN siempre puede ver todos los proyectos
					if ($AppUI->user_type == 1){
                         
						 if (count( $proj_con_tareas)>0)
						 {
						 	$InactiveProjectsFilter = ($ActiveProjects == null ? "" : "AND project_active = 1");
							 $strSql = "SELECT project_id,project_name,project_company as company_id, -1 permission_value"
							 . "\nFROM projects  where project_id IN (" . implode( ',', $proj_con_tareas ) . ")"
							 . $InactiveProjectsFilter
							 . "\nORDER BY project_name";	
						     
							 $arProjects = db_loadList($strSql);
				
				             $this->projects = $arProjects;
							 
								
						  }
						  else
						  {
							$this->proyects = array();
						  }
								
						}
						else
						{   
                            if (count( $proj_con_tareas)>0)
						    {
								// Proyectos con el rol negado //
						        if ($_GET[a]=="addeditexpense")
								{
								$on = "4";
								}
								else
								{
								$on = "3";
								}

                         
						 // Owner o admin -- sobre estas no reviso ningun permiso, puede hacer lo que quiera //


						   $strSqlP = "
							select project_id from projects where project_owner = $AppUI->user_id
							union
							select project_id from project_owners where project_owner = $AppUI->user_id
							";
						   
						   $owner = db_loadColumn($strSqlP);	

							if (count($owner)=="0")
							{
							$owner[0] ="0" ;
							}
							
							// roles generales 

							$strSqlP = "
							select distinct task_project as project_id from task_permissions where task_user_id = $AppUI->user_id and task_access_id='3' and task_permission_on= $on";

							$user_project = db_loadColumn($strSqlP);	

							if (count($user_project)=="0")
							{
							$user_project[0] ="0" ;
							}

							$strSqlP = "
							select distinct project_id from role_permissions where access_id='3' and item_id= $on and permission_value = '-1' and project_id NOT IN (" . implode( ',', $user_project) . ") 
							";
							
							$role_project_asigned = db_loadColumn($strSqlP);	

							if (count($role_project_asigned)=="0")
							{
							$role_project_asigned[0] ="0" ;
							}

							$strSqlP = "
							select distinct project_id from role_permissions where access_id='3' and item_id= $on and permission_value = '0' and project_id NOT IN (" . implode( ',', $owner) . ") 
							";
							
							$role_project_asigned_negado = db_loadColumn($strSqlP);	

							if (count($role_project_asigned_negado)=="0")
							{
							$role_project_asigned_negado[0] ="0" ;
							}

						   // Traigo los projectos en el que es usuario . me fijo los permisos en los roles 

						   $strSqlP = "
							select distinct task_project as project_id from task_permissions where task_user_id = $AppUI->user_id and task_access_id='3' and task_permission_on= $on and task_permission_value ='-1' 
							";

							$user_role_asigned =  db_loadColumn($strSqlP);
							
							if (count($user_role_asigned)=="0")
							{
							$user_role_asigned[0] ="0" ;
							}
                            

							$strSqlP = "
							select distinct task_project as project_id from task_permissions where task_user_id = $AppUI->user_id and task_access_id='3' and task_permission_on= $on and task_permission_value ='0' 
							";

							$user_role_asigned_negado =  db_loadColumn($strSqlP);
							
							if (count($user_role_asigned_negado)=="0")
							{
							$user_role_asigned_negado[0] ="0" ;
							}


							$strSqlP = "
							select project_id from project_roles where user_id = $AppUI->user_id and  user_units='100' and project_id IN (" . implode( ',', $user_role_asigned) . ")
							union
							select project_id from project_roles where user_id = $AppUI->user_id and  user_units='100' and project_id  NOT IN (" . implode( ',', $user_role_asigned) . ") and project_id IN (" . implode( ',',  $role_project_asigned) . ")   
							";
							
							
							$user_aigned = db_loadColumn($strSqlP);	

							if (count($user_aigned)=="0")
							{
							$user_aigned[0] ="0" ;
							}
		  
							

						  // Traigo los projectos en el que es usuario . me fijo los permisos en los roles sobre project wide

							$strSqlP = "
							select distinct task_project as project_id from task_permissions where task_user_id = $AppUI->user_id and task_access_id='2' and task_permission_on= $on";

							$user_project_wide = db_loadColumn($strSqlP);	

							if (count($user_project_wide)=="0")
							{
							$user_project_wide[0] ="0" ;
							}

							$strSqlP = "
							select distinct project_id from role_permissions where access_id='2' and item_id= $on and permission_value != '-1' and project_id NOT IN (" . implode( ',', $user_project_wide) . ") 
							";
							
							$role_project_wide = db_loadColumn($strSqlP);	

							if (count($role_project_wide)=="0")
							{
							$role_project_wide[0] ="0" ;
							}


						   $strSqlP = "
							select distinct task_project as project_id from task_permissions where task_user_id = $AppUI->user_id and task_access_id='2' and task_permission_on= $on 
							";

							$user_role_wide =  db_loadColumn($strSqlP);
							
							if (count($user_role_wide)=="0")
							{
							$user_role_wide[0] ="0" ;
							}


						   $strSqlP = "
							select distinct task_project as project_id from task_permissions where task_user_id = $AppUI->user_id and task_access_id='2' and task_permission_on= $on and task_permission_value ='-1' 
							union

							select distinct project_id from role_permissions where item_id= $on and permission_value ='-1' and access_id='2' and project_id  NOT IN (" . implode( ',', $user_role_wide) . ") and project_id NOT IN (" . implode( ',', $role_project_wide) . ")
							";
							

							$user_wide = db_loadColumn($strSqlP);	

							if (count($user_wide)=="0")
							{
							$user_wide[0] ="0" ;
							}





						   // Tareas en las que el user esta asignado //

							$strSqlP = "select task_id from user_tasks where user_id= $AppUI->user_id and task_id IN (" . implode( ',', $user_aigned  ) . ")";
								 
							$tareas_asignadas =  db_loadColumn($strSqlP);	


							if (count($tareas_asignadas)=="0")
							{
							$tareas_asignadas[0] ="0" ;
							}      

							
							$strSql = "
							(select task_project as project_id from tasks 
							 where task_owner='$AppUI->user_id')

							 union

							(select task_project as project_id from tasks 
							 where task_project IN (" . implode( ',', $owner ) . "))

							 union

							(select t.task_project as project_id 
							 from tasks as t, user_tasks as u 
							 where u.task_id=t.task_id  and t.task_access='3'
							 and u.user_id='$AppUI->user_id' and t.task_project IN  (" . implode( ',', $user_aigned  ) . "))

							 union

							(select t.task_project as project_id 
							 from tasks as t, user_tasks as u 
							 where u.task_id=t.task_id  and t.task_access='3'
							 and u.user_id='$AppUI->user_id' and t.task_id IN  (" . implode( ',', $tareas_asignadas  ) . ") and t.task_project NOT IN (" . implode( ',', $role_project_asigned_negado) . ") and t.task_project NOT IN (" . implode( ',', $user_role_asigned_negado) . "))
							
							 union

							(select t.task_project as project_id 
							 from tasks as t, user_tasks as u 
							 where u.task_id=t.task_id  and t.task_access='2'
							 and u.user_id='$AppUI->user_id' and t.task_project IN  (" . implode( ',', $user_aigned  ) . ") )
							
							 union
									 
							(select distinct task_project as project_id 
							 from tasks
							 where task_access='2'
							 and task_project IN  (" . implode( ',', $user_wide  ) . ")and task_id NOT IN  (" . implode( ',', $tareas_asignadas) . "))
							";
						    
							//echo $strSql;

							$allowed =  db_loadColumn($strSql);
                             //print_r($allowed);
							
							// Projectos con tareas asignadas //

							/*$strSql = "(SELECT DISTINCT p.project_id,p.project_name,p.project_company as company_id
									FROM task_permissions AS t,projects AS p
									WHERE t.task_user_id = '$AppUI->user_id'
									AND t.task_permission_value <> 0 
									AND p.project_id IN (" . implode( ',', $allowed ) . "))
									
									union

									(SELECT DISTINCT p.project_id,p.project_name,p.project_company as company_id
									FROM tasks as t,projects AS p
									WHERE t.task_access = 2
									AND t.task_project=p.project_id
									AND t.task_project IN (" . implode( ',', $allowed ) . "))
									order by project_name asc
									";*/
							  
							    if (count($allowed)=="0")
								{
								$allowed[0] ="0" ;
								}
								
								$InactiveProjectsFilter = ($ActiveProjects == null ? "" : "AND project_active = 1");

							  $strSql = "SELECT DISTINCT p.project_id,p.project_name,p.project_company as company_id
									FROM projects AS p
									WHERE 
									project_id IN (" . implode( ',', $allowed ) . ")
									$InactiveProjectsFilter
									order by project_name asc";
							 
							 $arProjects = db_loadList($strSql);
				             
				             $this->projects = $arProjects;
								
						  }
						  else
						  {
							$this->proyects = array();
						  }
					     
						}
                            
		
				if($this->_addItems_forEachCompany_inProjects && count($this->items) > 0){
					$intCompanyId = "";
					foreach($arProjects as $rRow){
						if($rRow["company_id"] != $intCompanyId){
							$intCompanyId = $rRow["company_id"];
							foreach($this->items as $kItem => $rItem){
								$this->addItemAtBeginOfProjects($this->addItemProject($intCompanyId, key($rItem), $rItem[key($rItem)]));
							}
						}
					}
					if($this->_breset_Items) $$this->items = array();
				}

			}
            

			/* Array de Projectos */
			function Projects(){
				return $this->projects;
			}


			/* Agrega item al principio de Projectos */
			function addItemAtBeginOfProjects($item){
				$arTmp = array();

				//corro los indices en uno asi puedo insertar el item
				if (count($this->Projects())>0)
				{
					foreach($this->Projects() as $k => $r){
						$k++;
						$arTmp[$k] = $r;
					}
				}

				$this->projects = $arTmp;
				$arTmp = null;
				$this->addItemAtBeginOf($this->projects, $item);
			}
 

            /* Agrega los items en el array projects */
			function addItemProject($company, $project, $projectname){
				$arTmp = array("company_id" => $company,
								"project_id" => $project,
								"project_name" => $projectname	
								);
				return $this->addItem(0, $arTmp);
			}
            

			function generateHTMLcboProjects($selected="", $class="", $onchange, $isEmpty=true){
				$arProjectsTmp = array();
				return arraySelect($arProjectsTmp, $this->getCboProjects(), "class=\"$class\" style=\"width:220px;\" tabindex=\"9\" ".$onchange, $selected, true);
			}

			/* Genera el html con el select de proyectos para la tabla */
			function generateHTMLcboProjects_tabla($selected="", $class="",$onchange){
				return arraySelectJs($this->Projects(), "project_id_task[]", "class=\"$class\" ". $onchange, $selected, true);
			}

			/*metodos y propiedades para el form generado*/
			function setFrmName($strName){
			$this->_frm_name = $strName;
			}
			function getFrmName(){
				return $this->_frm_name;
			}
			
			function setCboCompanies($strName){
				$this->_cbo_company_name = $strName;
			}
			function getCboCompanies(){
				return $this->_cbo_company_name;
			}
			
			function setCboProjects($strName){
				$this->_cbo_project_name = $strName;
			}
			function getCboProjects(){
				return $this->_cbo_project_name;
			}
			
			function setJSSelectedProject($value){
				$this->_project_id_selected = $value;
			}
			function getJSSelectedProject(){
				return $this->_project_id_selected;
			}
            			

			/* Funcion que trae las tareas de acuerdo al proyecto seleccionado */
			function loadTasks($intProject = null, $user_id = null, $wbs = null, $timexpPermissionFilter = null){
				global $AppUI;
				$userId = $user_id ? $user_id : $AppUI->user_id;
				require_once( $AppUI->getModuleClass( 'admin' ) );
				
				$Cuser = new CUser();
				$Cuser->load($userId);
				
				$arTmp = array();

				// Traigo las tareas que tiene permitidas //

			    if ($Cuser->user_type == 1){ // Traigo los projectos en los que el user es owner o admin sobre estos no necesito  revisar los permisos //
				  $strSql = "SELECT task_id, task_name, task_project as project_id, task_wbs_level, task_wbs_number, task_parent"
						. "\nFROM tasks "
						. "order by task_name";	
						
				}
				else
				{
			        if ($_GET[a]=="addeditexpense" || $timexpPermissionFilter != null) $on = "4";
					else $on = "3";

                   $strSqlP = "
					select project_id from projects where project_owner = $Cuser->user_id
					union
					select project_id from project_owners where project_owner = $Cuser->user_id
                    ";
                   
				   $owner = db_loadColumn($strSqlP);	

				    if (count($owner)=="0")
					{
					$owner[0] ="0" ;
					}
					
					// roles generales : TOCAR ACÁ EN CASO DE NECESITAR SUBIR/BAJAR LAS RESTRICCIONES PARA LISTADO DE TAREAS
					
				    $strSqlP = "
                    select distinct task_project as project_id from task_permissions where task_user_id = $Cuser->user_id and task_access_id='3' and task_permission_on= $on";

					$user_project = db_loadColumn($strSqlP);	

					if (count($user_project)=="0")
					{
					$user_project[0] ="0" ;
					}
					
                    $strSqlP = "
					select distinct project_id from role_permissions where access_id='3' and item_id= $on and permission_value = '-1' and project_id NOT IN (" . implode( ',', $user_project) . ") 
					";
                    
                    $role_project_asigned = db_loadColumn($strSqlP);	

					if (count($role_project_asigned)=="0")
						$role_project_asigned[0] ="0" ;

					$strSqlP = "
						select distinct project_id from role_permissions where access_id='3' and item_id= $on and permission_value = '0' and project_id NOT IN (" . implode( ',', $owner) . ") 
					";
                    
                    $role_project_asigned_negado = db_loadColumn($strSqlP);	

					if (count($role_project_asigned_negado)=="0")
						$role_project_asigned_negado[0] ="0" ;

				   // Traigo los projectos en el que es usuario . me fijo los permisos en los roles 
                   $strSqlP = "
                    	select distinct task_project as project_id from task_permissions where task_user_id = $Cuser->user_id and task_access_id='3' and task_permission_on= $on and task_permission_value ='-1' 
					";
					
				    $user_role_asigned =  db_loadColumn($strSqlP);
                    
					if (count($user_role_asigned)=="0")
						$user_role_asigned[0] ="0" ;
                    
					$strSqlP = "
                    	select distinct task_project as project_id from task_permissions where task_user_id = $Cuser->user_id and task_access_id='3' and task_permission_on= $on and task_permission_value ='0' 
					";

				    $user_role_asigned_negado =  db_loadColumn($strSqlP);
                    
					if (count($user_role_asigned_negado)=="0")
						$user_role_asigned_negado[0] ="0" ;

                    $strSqlP = "
					select project_id from project_roles where user_id = $Cuser->user_id and  user_units='100' and project_id IN (" . implode( ',', $user_role_asigned) . ")
					union
					select project_id from project_roles where user_id = $Cuser->user_id and  user_units='100' and project_id  NOT IN (" . implode( ',', $user_role_asigned) . ") and project_id IN (" . implode( ',',  $role_project_asigned) . ")   
					";					
                    
                    $user_aigned = db_loadColumn($strSqlP);	

					if (count($user_aigned)=="0")
					$user_aigned[0] ="0" ;
                    
				  // Traigo los projectos en el que es usuario . me fijo los permisos en los roles sobre project wide
				    $strSqlP = "
                    select distinct task_project as project_id from task_permissions where task_user_id = $Cuser->user_id and task_access_id='2' and task_permission_on= $on";

					$user_project_wide = db_loadColumn($strSqlP);	

					if (count($user_project_wide)=="0")
						$user_project_wide[0] ="0" ;

                    $strSqlP = "
						select distinct project_id from role_permissions where access_id='2' and item_id= $on and permission_value != '-1' and project_id NOT IN (" . implode( ',', $user_project_wide) . ") 
					";
                    
                    $role_project_wide = db_loadColumn($strSqlP);	

					if (count($role_project_wide)=="0")
						$role_project_wide[0] ="0" ;

				   $strSqlP = "
                    	select distinct task_project as project_id from task_permissions where task_user_id = $Cuser->user_id and task_access_id='2' and task_permission_on= $on 
					";

				    $user_role_wide =  db_loadColumn($strSqlP);
                    
					if (count($user_role_wide)=="0")
						$user_role_wide[0] ="0" ;

                   $strSqlP = "
                    	select distinct task_project as project_id from task_permissions where task_user_id = $Cuser->user_id and task_access_id='2' and task_permission_on= $on and task_permission_value ='-1' 
                    union
						select distinct project_id from role_permissions where item_id= $on and permission_value ='-1' and access_id='2' and project_id  NOT IN (" . implode( ',', $user_role_wide) . ") and project_id NOT IN (" . implode( ',', $role_project_wide) . ")
					";
                    
					$user_wide = db_loadColumn($strSqlP);	

                    if (count($user_wide)=="0")
						$user_wide[0] ="0" ;

                   // Tareas en las que el user esta asignado //

					$strSqlP = "select task_id from user_tasks where user_id= $Cuser->user_id and task_id IN (" . implode( ',', $user_aigned  ) . ")";
                         
					$tareas_asignadas =  db_loadColumn($strSqlP);	


                    if (count($tareas_asignadas)=="0")
						$tareas_asignadas[0] ="0" ;

					$strSql = "
					(select task_name, task_id, task_project as project_id, task_wbs_level, task_wbs_number, task_parent from tasks 
                     where task_owner='$Cuser->user_id')

					 union

					(select task_name, task_id, task_project as project_id, task_wbs_level, task_wbs_number, task_parent  from tasks 
                     where task_project IN (" . implode( ',', $owner ) . "))

					 union

					(select t.task_name, t.task_id, t.task_project as project_id, t.task_wbs_level, t.task_wbs_number, t.task_parent  
					 from tasks as t, user_tasks as u 
					 where u.task_id=t.task_id  and t.task_access='3'
					 and u.user_id='$Cuser->user_id' and t.task_project IN  (" . implode( ',', $user_aigned  ) . "))
					
					 union

					(select t.task_name,t.task_id,t.task_project as project_id, t.task_wbs_level, t.task_wbs_number, t.task_parent 
					 from tasks as t, user_tasks as u 
					 where u.task_id=t.task_id  and t.task_access='3'
					 and u.user_id='$Cuser->user_id' and t.task_id IN  (" . implode( ',', $tareas_asignadas  ) . ") and t.task_project NOT IN (" . implode( ',', $role_project_asigned_negado) . ") and t.task_project NOT IN (" . implode( ',', $user_role_asigned_negado) . "))
					 
					 union

					(select t.task_name,t.task_id,t.task_project as project_id, t.task_wbs_level, t.task_wbs_number, t.task_parent  
					 from tasks as t, user_tasks as u 
					 where u.task_id=t.task_id  and t.task_access='2'
					 and u.user_id='$Cuser->user_id' and t.task_project IN  (" . implode( ',', $user_aigned  ) . "))
					 
					 union
					 
					(select t.task_name,t.task_id,t.task_project as project_id, t.task_wbs_level, t.task_wbs_number, t.task_parent 
					 from tasks as t, user_tasks as u 
					 where u.task_id=t.task_id and u.user_id='$Cuser->user_id')
					 
					 union
                     
					(select task_name,task_id,task_project as project_id, task_wbs_level, task_wbs_number, task_parent
					 from tasks
					 where task_access='2'
					 and task_project IN  (" . implode( ',', $user_wide  ) . ") and task_id NOT IN  (" . implode( ',', $tareas_asignadas) . "))
					";
                     
				        }
				$arTasks = db_loadList($strSql);
				
				if($wbs != null)
				{
					function ordenar($a,$b) //Callback para uasort
					{
						  return strcmp($a["task_name"], $b["task_name"]);
					}
					 
					$i = 0;
					
					foreach($arTasks as $rRow)
					{
					  $vec_task = array();
				      $vec_task['task_id'] = $rRow["task_id"];
				      $vec_task['task_wbs_level'] = $rRow['task_wbs_level'];
				      $vec_task['task_wbs_number'] = $rRow['task_wbs_number'];
				      $vec_task['task_parent'] = $rRow['task_parent'];
				      
				      $Wbs = wbs($vec_task);
				      $TaskName = $Wbs."-".$arTasks[$i]["task_name"];
			       	  
				      $TaskName = ereg_replace("&aacute;","á",$TaskName);
					  $TaskName = ereg_replace("&eacute;","é",$TaskName);
					  $TaskName = ereg_replace("&iacute;","í",$TaskName);
					  $TaskName = ereg_replace("&oacute;","ó",$TaskName);
					  $TaskName = ereg_replace("&uacute;","ú",$TaskName);
					  $TaskName = ereg_replace("&apos;","'",$TaskName);
					  $TaskName = ereg_replace("&acute;","´",$TaskName);
					  $TaskName = ereg_replace("acute;","´",$TaskName);
					  $TaskName = ereg_replace('"',"'",$TaskName);
					  
				      $arTasks[$i]["task_name"] = $TaskName;
				      $i++;
					}
					
					uasort($arTasks,'ordenar');
				}
				
				$this->tasks = $arTasks;
				
				if($this->_addItems_forEachProject_inTasks && count($this->items) > 0){
					$intProjectId = "";
					foreach($arTasks as $rRow){
						if($rRow["project_id"] != $intProjectId){
							$intProjectId = $rRow["project_id"];
							foreach($this->items as $kItem => $rItem){
								$this->addItemAtBeginOfTasks($this->addItemTask($intProjectId, key($rItem), $rItem[key($rItem)]));
							}
						}
					}
					if($this->_breset_Items) $this->items = array();
				}
			}


			/* Array de tareas */
			function Tasks(){
				return $this->tasks;
			}


			/* Agrega item al principio de Tareas */
			function addItemAtBeginOfTasks($item){
				$arTmp = array();

				//corro los indices en uno asi puedo insertar el item
				if (count($this->Tasks())>0)
				{
					foreach($this->Tasks() as $k => $r){
						$k++;
						$arTmp[$k] = $r;
					}
				}

				$this->tasks = $arTmp;
				$arTmp = null;
				$this->addItemAtBeginOf($this->tasks, $item);
			}
 

            /* Agrega los items en el array tasks */
			function addItemTask($project, $task, $taskname){
				$arTmp = array("project_id" => $project,
								"task_id" => $task,
								"task_name" => $taskname	
								);
				return $this->addItem(0, $arTmp);
			}
            

			function generateHTMLcboTasks($selected="", $class="", $isEmpty=true){
				$arTasksTmp = array();
				return arraySelect($arTasksTmp, $this->getCboTasks(), "class=\"$class\" style=\"width: 220px\" tabindex=\"10\"", $selected, true);
			}

			function generateHTMLcboTasks_tabla($selected="", $class="", $isEmpty=true){
				$arTasksTmp = array();
				return arraySelectJs($arTasksTmp, "task_id[]", "class=\"$class\"", $selected, true);
			}

			function setCboTasks($strName){
				$this->_cbo_task_name = $strName;
			}
			function getCboTasks(){
				return $this->_cbo_task_name;
			}
			
			function setJSSelectedTask($value){
				$this->_task_id_selected = $value;
			}

			function getJSSelectedTask(){
				return $this->_task_id_selected;
			}


            /*genera el array con las tasks */
			function _JSgenerateArrayTasks(){
				global $AppUI;
				$strJS = "var arTasks = new Array();\n";
				
				if($this->Tasks()){

					foreach($this->Tasks() as $rTask){
						$desc =  str_replace("\""," ",$rTask["task_name"]);
                        
						$strJS .= "arTasks[arTasks.length] = new Array({$rTask["project_id"]}, {$rTask["task_id"]}, \"".$desc."\");\n";
					}
				}
				return $strJS;
			}
		    
		   /*genera el codigo JS que va en la pagina*/
			function generateJSTask(){
				
				$strJS = "";
				
				$strJS .= "var intIdTask = ";
				
				if($this->getJSSelectedTask() != 0){
					$strJS .= $this->getJSSelectedTask()."\n";
				}else{
					$strJS .= "'';\n";
				}
				
				$strJS .= $this->_JSgenerateFunctionsTask();
				$strJS .= $this->_JSgenerateArrayTasks();
				
				return $strJS;
			}

			/*genera las funciones que realizan la actualizacion de los cbos*/
			function _JSgenerateFunctionsTask(){
				$strJS = "";
				$strJS .= "	function selectTask(){
								var f = document.".$this->getFrmName().";
								f.".$this->getCboTasks().".options[0].selected = true;
							}\n
							";

				$strJS .= "	function changeTask() {\n
								var sel = document.". $this->getFrmName().".".$this->getCboTasks().";
								var f = document.".$this->getFrmName().";
								// Remove options
								while ( sel.length != 0 ) {
									sel[0] = null;
								}
								var index = f.project_id_task.selectedIndex;
								var jur = f.project_id_task[index].value;
						
								for( i = 0 ; i < arTasks.length ; i++) {
										if ( arTasks[i][0] == jur ) {
										//  matches
										var opt = new Option(arTasks[i][2], arTasks[i][1], false, false);
										sel.options[sel.options.length] = opt;
									}
								}
								selectTask();
							}			
						";
				
				$strJS .= "function findTask(obj){
							var f = document.".$this->getFrmName().";
							if(obj != \"\"){
								for(x=0; x < f.".$this->getCboTasks().".options.length; x++){
									if(f.".$this->getCboTasks().".options[x].value == obj){
										f.".$this->getCboTasks().".options[x].selected = true;
										break;
									}
								}
								f.".$this->getCboTasks().".selectedValue;
							}
						}
						";
				
				return $strJS;
			}

			
           /*genera el array con los projectos */
			function _JSgenerateArrayProjects(){
				global $AppUI;
				$strJS = "var arProjects = new Array();\n";
				
				if($this->Projects()){
					foreach($this->Projects() as $rProject){
						$descp =  str_replace("\""," ",$rProject["project_name"]);
						$strJS .= "arProjects[arProjects.length] = new Array({$rProject["company_id"]}, {$rProject["project_id"]}, \"".$descp."\");\n";
					}
				}
				
                
				$strJS .= "\n\n";
				
				return $strJS;
			}

			/*genera el codigo JS que va en la pagina*/
			function generateJS(){
				
				$strJS = "";
				
				$strJS .= "var intIdProject = ";
				
				if($this->getJSSelectedProject() != 0){
					$strJS .= $this->getJSSelectedProject()."\n";
				}else{
					$strJS .= "'';\n";
				}
				
				$strJS .= $this->_JSgenerateArrayProjects();
				$strJS .= $this->_JSgenerateFunctions();
				
				return $strJS;
			}

			/*genera las funciones que realizan la actualizacion de los cbos*/
			function _JSgenerateFunctions(){

				$strJS .= "";
				$strJS .= "	function selectProject(){
								var f = document.".$this->getFrmName().";
								f.".$this->getCboProjects().".options[0].selected = true;

								
							}\n
							";

				$strJS .= "	function changeProject() {\n
								var sel = document.". $this->getFrmName().".".$this->getCboProjects().";
								var f = document.".$this->getFrmName().";
								// Remove options
								while ( sel.length != 0 ) {
									sel[0] = null;
								}
								var index = f.idcompany.selectedIndex;
								var jur = f.idcompany[index].value;
						        

								for( i = 0 ; i < arProjects.length ; i++) {
										if ( arProjects[i][0] == jur ) {
										//  matches
										var opt = new Option(arProjects[i][2], arProjects[i][1], false, false);
										sel.options[sel.options.length] = opt;
									}
								}

								var sel_task = document.". $this->getFrmName().".".$this->getCboTasks().";

		                        
								if (typeof sel_task != 'undefined')
								{
                                changeTask();
							    findTask();
								}

								selectProject();
                                  
							}			
						";
				
				$strJS .= "function findProject(obj){
							var f = document.".$this->getFrmName().";
							
							if(obj != \"\"){
								for(x=0; x < f.".$this->getCboProjects().".options.length; x++){
									if(f.".$this->getCboProjects().".options[x].value == obj){
										f.".$this->getCboProjects().".options[x].selected = true;
										break;
									}
								}
								f.".$this->getCboProjects().".selectedValue;
							}
						}
						";

				
				return $strJS;
			}


			function generateJScallFunctions($project_id=null,$withTag=true){
				$strJS = "	changeProject();
							findProject($project_id);
						";
				if($withTag) $strJS = "<script language=\"javascript\">$strJS</script>";
				
				return $strJS;
			}

			function generateJScallFunctions_task($task_sel=null,$withTag=true){
				$strJS = "changeTask();
							findTask($task_sel);
						";
				if($withTag) $strJS = "<script language=\"javascript\">$strJS</script>";
				
				return $strJS;
			}

}

?>