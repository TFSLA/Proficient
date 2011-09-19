<?php

class CTodos {

	var $companies = null;
	var $projects = null;
	var $todos = null;
	var $item = array();
	var $items = array();//almacena items que seran insertados en Projectos
	
	var $_frm_name = "";
	var $_cbo_company_name = "companies";
	var $_cbo_project_name = "projects";
	var $_cbo_todo_name = "task_id";
	var $_project_id_selected = "";
	
	var $_addItems_forEachcompany_inprojects = true;//agrega los items almacenados en el array Items en el array Projects por cada company
	var $_addItems_forEachproject_intodos = true;//agrega los items almacenados en el array Items en el array Tasks por cada projecto
	var $_breset_Items = true;//una vez insertados los registros se vacia
	

		   /* Función que trae las companies */

			function loadCompanies($ActiveProjects=null){
                global $AppUI;

				$allowed = array();
                  
				  // Traigo los projectos con todos //
				   
				  $sql = "SELECT DISTINCT project_id FROM project_todo WHERE user_assigned = $AppUI->user_id ";
                 
				  $proj_con_todos = db_loadColumn($sql);
                  
				

						//el SYSADMIN siempre puede ver todos los proyectos, asi que traigo todas las companies con proyectos que tengan todos //
						if ($AppUI->user_type == 1){

		                    if (count( $proj_con_todos)>0)
							{
							  $InactiveProjectsFilter = $ActiveProjects = null ? "" : "AND project_active = 1";
							  
							  $strSql ="SELECT DISTINCT p.project_company as company_id,c.company_name
                                        FROM projects as p, companies as c 
                                        WHERE p.project_company=c.company_id and 
                                        project_id IN (" . implode( ',', $proj_con_todos ) . ") 
                                        $InactiveProjectsFilter
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
                            $strSql = "SELECT DISTINCT project_id 
							           FROM project_todo 
									   WHERE user_assigned= $AppUI->user_id
									   ";
					
							$allowed =  db_loadColumn($strSql);	
							
							if (count($allowed)=="0")
							{
							$allowed[0] ="0" ;
							}

							// Companies con Projectos con tareas //
							$InactiveProjectsFilter = ($ActiveProjects == null ? "" : "AND project_active = 1");

							$strSql ="SELECT DISTINCT p.project_company as company_id,c.company_name
											FROM projects as p, companies as c 
											WHERE p.project_company=c.company_id and 
											project_id IN (" . implode( ',', $allowed ) . ") 
											$InactiveProjectsFilter
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
				return arraySelect($this->Companies(), "idcompany_todo", "class=\"$class\" tabindex=\"8\" onchange=\"javascript:changeProject_todo();\" ", $selected, true, false);
				
			}

			function generateHTMLcboCompanies_tabla($selected="", $class="",$onchange){
				return arraySelectJs($this->Companies(), "idcompany_todo[]", "class=\"$class\" ".$onchange, $selected, true);
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
                  
				  $sql = "SELECT DISTINCT project_id FROM project_todo WHERE user_assigned = $AppUI->user_id";
				 

				  $proj_con_todos = db_loadColumn($sql);	


						//el SYSADMIN siempre puede ver todos los proyectos
					if ($AppUI->user_type == 1){
                         
						 if (count( $proj_con_todos)>0)
						 {
						 	$InactiveProjectsFilter = ($ActiveProjects == null ? "" : "AND project_active = 1");
						 	
							 $strSql = "SELECT project_id,project_name,project_company as company_id, -1 permission_value"
							 . "\nFROM projects  where project_id IN (" . implode( ',', $proj_con_todos ) . ")"
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
                           $strSql = "SELECT DISTINCT project_id 
							           FROM project_todo 
									   WHERE user_assigned= $AppUI->user_id 
									   ";

                           $allowed =  db_loadColumn($strSql);
                             

                           if (count( $allowed)>0)
						    {
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
            

			function generateHTMLcboProjects($selected="", $class="", $isEmpty=true){
				$arProjectsTmp = array();
				return arraySelect($arProjectsTmp, $this->getCboProjects(), "class=\"$class\" tabindex=\"9\" onchange=\"javascript:changeTodo();\" ", $selected, true);
			}

			/* Genera el html con el select de proyectos para la tabla */
			function generateHTMLcboProjects_tabla($selected="", $class="",$onchange){
				return arraySelectJs($this->Projects(), "project_id_todo[]", "class=\"$class\" ". $onchange, $selected, true);
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
			function loadTodos($intProject = null){
				global $AppUI;

				$arTmp = array();

				// Traigo los todos que tiene permitidas //

				    if ($AppUI->user_type == 1){
					  $strSql = "SELECT id_todo,description, project_id"
								. "\nFROM project_todo "
								."\n WHERE user_assigned = $AppUI->user_id"
								. "\n order by description";	
							
						}
						else
						{ 
						 $strSql = "SELECT DISTINCT project_id 
							        FROM project_todo 
									WHERE user_assigned= $AppUI->user_id 
									";
                         
                         $allowed =  db_loadColumn($strSql);

						 if (count($allowed)=="0")
							{
							$allowed[0] ="-1" ;
							}

						 $strSql = "SELECT id_todo,description, project_id
									FROM project_todo
									WHERE 
									project_id IN (" . implode( ',', $allowed ) . ")
								    and user_assigned= $AppUI->user_id 
									order by description asc";
                     
				        }
				
				$arTodos = db_loadList($strSql);
				
				$i = 0;
				foreach ($arTodos as $ToDo)
				{
					$arTodos[$i]["description"] = ereg_replace("&aacute;","á",$arTodos[$i]["description"]);
					$arTodos[$i]["description"] = ereg_replace("&eacute;","é",$arTodos[$i]["description"]);
					$arTodos[$i]["description"] = ereg_replace("&iacute;","í",$arTodos[$i]["description"]);
					$arTodos[$i]["description"] = ereg_replace("&oacute;","ó",$arTodos[$i]["description"]);
					$arTodos[$i]["description"] = ereg_replace("&uacute;","ú",$arTodos[$i]["description"]);
					$arTodos[$i]["description"] = ereg_replace("&apos;","'",$arTodos[$i]["description"]);
					$arTodos[$i]["description"] = ereg_replace("&#039;","'",$arTodos[$i]["description"]);
					$arTodos[$i]["description"] = ereg_replace("&quot;","'",$arTodos[$i]["description"]);
					$arTodos[$i]["description"] = ereg_replace("&apos;","'",$arTodos[$i]["description"]);
									
					$i++;
				}
				$this->todos = $arTodos;

				if($this->_addItems_forEachProject_inTodos && count($this->items) > 0){
					$intProjectId = "";
					foreach($arTodos as $rRow){
						if($rRow["project_id"] != $intProjectId){
							$intProjectId = $rRow["project_id"];
							foreach($this->items as $kItem => $rItem){
								$this->addItemAtBeginOfTodos($this->addItemTodo($intProjectId, key($rItem), $rItem[key($rItem)]));
							}
						}
					}
					if($this->_breset_Items) $this->items = array();
				}
			}


			/* Array de tareas */
			function Todos(){
				return $this->todos;
			}


			/* Agrega item al principio de Tareas */
			function addItemAtBeginOfTodos($item){
				$arTmp = array();

				//corro los indices en uno asi puedo insertar el item
				if (count($this->Todos())>0)
				{
					foreach($this->Todos() as $k => $r){
						$k++;
						$arTmp[$k] = $r;
					}
				}
                
				$this->todos = $arTmp;
				$arTmp = null;
				$this->addItemAtBeginOf($this->todos, $item);
			}
 

            /* Agrega los items en el array tasks */
			function addItemTodo($project, $todo, $todoname){

				$arTmp = array("project_id" => $project,
								"id_todo" => $todo,
								"description" => $todoname	
								);
				return $this->addItem(0, $arTmp);
			}
            

			function generateHTMLcboTodos($selected="", $class="", $isEmpty=true){
				$arTodosTmp = array();
				return arraySelect($arTodosTmp, $this->getCboTodos(), "class=\"$class\" tabindex=\"10\"", $selected, true);
			}

			function generateHTMLcboTodos_tabla($selected="", $class="", $isEmpty=true){
				$arTodosTmp = array();
				return arraySelectJs($arTodosTmp, "id_todo[]", "class=\"$class\"", $selected, true);
			}

			function setCboTodos($strName){
				$this->_cbo_todo_name = $strName;
			}
			function getCboTodos(){
				return $this->_cbo_todo_name;
			}
			
			function setJSSelectedTodo($value){
				$this->_id_todo_selected = $value;
			}

			function getJSSelectedTodo(){
				return $this->_id_todo_selected;
			}


            /*genera el array con las tasks */
			function _JSgenerateArrayTodos(){
				global $AppUI;
				$strJS = "var arTodos = new Array();\n";
				
				if($this->Todos()){ 
					foreach($this->Todos() as $rTodo){
						$desc =  str_replace("\""," ",$rTodo["description"]);
						$descp = substr($desc,0,25);

						$strJS .= "arTodos[arTodos.length] = new Array({$rTodo["project_id"]}, {$rTodo["id_todo"]}, \"".$descp."\");\n";
					}
				}
				return $strJS;
			}
		    
		   /*genera el codigo JS que va en la pagina*/
			function generateJSTodo(){
				
				$strJS = "";
				
				$strJS .= "var intIdTodo = ";
				
				if($this->getJSSelectedTodo() != 0){
					$strJS .= $this->getJSSelectedTodo()."\n";
				}else{
					$strJS .= "'';\n";
				}
				
				$strJS .= $this->_JSgenerateFunctionsTodo();
				$strJS .= $this->_JSgenerateArrayTodos();
				
				return $strJS;
			}

			/*genera las funciones que realizan la actualizacion de los cbos*/
			function _JSgenerateFunctionsTodo(){
				$strJS = "";
				$strJS .= "	function selectTodo(){
								var f = document.".$this->getFrmName().";
								f.".$this->getCboTodos().".options[0].selected = true;
							}\n
							";

				$strJS .= "	function changeTodo() {\n

				                var f = document.".$this->getFrmName().";
								var sel_todo = document.". $this->getFrmName().".".$this->getCboTodos().";
								
								// Remove options
								while ( sel_todo.length != 0 ) {
									sel_todo[0] = null;
								}
								var index = f.project_id_todo.selectedIndex;
								var jur = f.project_id_todo[index].value;
						
								for( i = 0 ; i < arTodos.length ; i++) {
										if ( arTodos[i][0] == jur ) {
										//  matches
										var opt = new Option(arTodos[i][2], arTodos[i][1], false, false);
										sel_todo.options[sel_todo.options.length] = opt;
									}
								}
								selectTodo();
							}			
						";
				
				$strJS .= "function findTodo(obj){
							var f = document.".$this->getFrmName().";
							if(obj != \"\"){
								for(x=0; x < f.".$this->getCboTodos().".options.length; x++){
									if(f.".$this->getCboTodos().".options[x].value == obj){
										f.".$this->getCboTodos().".options[x].selected = true;
										break;
									}
								}
								f.".$this->getCboTodos().".selectedValue;
							}
						}
						";
				
				return $strJS;
			}

			
           /*genera el array con los projectos */
			function _JSgenerateArrayProjects(){
				global $AppUI;
				$strJS = "var arProjects_todo = new Array();\n";
				
				if($this->Projects()){
					foreach($this->Projects() as $rProject){
						$descp =  str_replace("\""," ",$rProject["project_name"]);
						$strJS .= "arProjects_todo[arProjects_todo.length] = new Array({$rProject["company_id"]}, {$rProject["project_id"]}, \"".$descp."\");\n";
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
				$strJS .= "	function selectProject_todo(){
								var f = document.".$this->getFrmName().";
								f.".$this->getCboProjects().".options[0].selected = true;
							}\n
							";

				$strJS .= "	function changeProject_todo() {\n
								var sel = document.". $this->getFrmName().".".$this->getCboProjects().";
								var f = document.".$this->getFrmName().";
								// Remove options
								while ( sel.length != 0 ) {
									sel[0] = null;
								}
								var index = f.idcompany_todo.selectedIndex;
								var jur = f.idcompany_todo[index].value;
						        

								for( i = 0 ; i < arProjects_todo.length ; i++) {
										if ( arProjects_todo[i][0] == jur ) {
										//  matches
										var opt = new Option(arProjects_todo[i][2], arProjects_todo[i][1], false, false);
										sel.options[sel.options.length] = opt;
									}
								}

								var sel_todo = document.". $this->getFrmName().".".$this->getCboTodos().";

		                        
								if (typeof sel_todo != 'undefined')
								{
									changeTodo();
							        findTodo();
								}

								selectProject_todo();
							}			
						";
				
				$strJS .= "function findProject_todo(obj){
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


			function generateJScallFunctions($project_sel=null,$withTag=true){
				$strJS = "	changeProject_todo();
							findProject_todo($project_sel);
						";
				if($withTag) $strJS = "<script language=\"javascript\">$strJS</script>";
				
				return $strJS;
			}

			function generateJScallFunctions_todo($todo_sel=null,$withTag=true){
				$strJS = "changeTodo();
							findTodo($todo_sel);
						";
				if($withTag) $strJS = "<script language=\"javascript\">$strJS</script>";
				
				return $strJS;
			}

} 

?>
