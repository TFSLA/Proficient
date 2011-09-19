<?php

class CProjects {

	var $companies = null;
	var $projects = null;
	var $item = array();
	var $items = array();//almacena items que seran insertados en Tasks
	
	var $_frm_name = "";
	var $_cbo_company_name = "companies";
	var $_cbo_project_name = "projects";
	var $_project_id_selected = "";
	
	var $_addItems_forEachcompany_inprojects = true;//agrega los items almacenados en el array Items en el array Tasks por cada projecto
	var $_breset_Items = true;//una vez insertados los registros se vacia
		   

		   /* Función que trae las companias */

			function loadCompanies(){

				global $AppUI;
                  
				   $strSql = "SELECT company_id,company_name"
								. "\nFROM companies "
								. "\nORDER BY company_name";	

				   $this->companies = db_loadHashList($strSql);

			}



            /* Mete las companies en un array */
			function Companies(){
				return $this->companies;
			}
            
			/* Genera el html con el select de companies */
			function generateHTMLcboCompanies($selected="", $class=""){
				return arraySelect($this->Companies(), "company_id", "class=\"$class\" ", $selected, true);
			}

			/* Genera el html con el select de proyectos para la tabla */
			function generateHTMLcboCompanies_tabla($selected="", $class="",$onchange){
				return arraySelectJs($this->Companies(), "company_id[]", "class=\"$class\" ". $onchange, $selected, true);
			}

            
			/* Agrega un item a un array */
			function addItem($value, $text, $bstore=false){
				if($bstore){
					$this->items[] = array($value => $text);
				}else{
					$this->$item = array($value => $text);
				}

				return $this->$item;
			}

            /* Agrega un item al principio de un array */
            function addItemAtBeginOf(&$target, $item){
				
				if($target != "" && (is_array($item) || $item !== "")){
					$target = arrayMerge($item, $target);
				}
			}

            /* Agrega un item al principio del array projectos */
			function addItemAtBeginOfCompanies($item){
				$this->addItemAtBeginOf($this->companies, $item);
			}


            /* Funcion que trae las tareas de acuerdo al proyecto seleccionado */
			function loadProjects($intCompany = null){
				global $AppUI;

				$arTmp = array();

				$allowed = array();
                

				// Traigo solo los projectos con tareas //
                  
				  $sql = "select DISTINCT task_project from tasks";

				  $proj_con_tareas = db_loadColumn($sql);	

						//el SYSADMIN siempre puede ver todos los proyectos
						if ($AppUI->user_type == 1){
							$strSql = "SELECT project_id,project_name,project_company as company_id, -1 permission_value"
								. "\nFROM projects  where project_id IN (" . implode( ',', $proj_con_tareas ) . ")"
								. ($orderby ? "\nORDER BY $orderby" : '');	
							
						}
						else
						{

						//obtengo los proyectos en donde el usuario es responsable, administrador o usuario del proyecto
						$strSql = "
						select project_id from projects where project_owner = $AppUI->user_id
						union
						select project_id from project_owners where project_owner = $AppUI->user_id
						union
						select project_id from project_roles where  role_id = 2 and user_id = $AppUI->user_id
						";
					   //echo "<pre>$sql</pre>";		
						$allowed =  db_loadColumn($strSql);		
						
						$strSql = "select project_company from projects  where project_id IN (" . implode( ',', $allowed ) . ")";
						
						$companies = (count($allowed) > 0 ? db_loadColumn($sql) : array("-1"));	


						
						// Projectos con tareas asignadas //
						$strSql = "SELECT DISTINCT p.project_id,p.project_name,project_company as company_id
								FROM task_permissions 
								INNER JOIN projects AS p
								WHERE task_user_id = '$AppUI->user_id'
								AND task_permission_value <> 0 
								AND project_id IN (" . implode( ',', $allowed ) . ")
								
								union

								SELECT DISTINCT p.project_id,p.project_name,project_company as company_id
								FROM tasks
								INNER JOIN projects AS p
								WHERE task_access = 2
								AND task_project=p.project_id
								AND task_project NOT IN (" . implode( ',', $allowed ) . ")";
					     
						}
				
				$arProjects = db_loadList($strSql);
				
				$this->projects = $arProjects;
				
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
					if($this->_breset_Items) $this->items = array();
				}
				
			}
            

			/* Array de projectos */
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
 

            /* Agrega los items en el array tasks */
			function addItemProject($company, $project, $projectname){
				$arTmp = array("company_id" => $company,
								"project_id" => $project,
								"project_name" => $projectname	
								);
				return $this->addItem(0, $arTmp);
			}
            

			function generateHTMLcboProjects($selected="", $class="", $isEmpty=true){
				$arProjectsTmp = array();
				return arraySelect($arProjectsTmp, $this->getCboProjects(), "class=\"$class\"", $selected, true);
			}

			function generateHTMLcboProjects_tabla($selected="", $class="", $isEmpty=true){
				$arProjectsTmp = array();
				return arraySelectJs($arProjectsTmp, $this->getCboProjects(), "class=\"$class\"", $selected, true);
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
            
			/*genera el array con los projectos */
			function _JSgenerateArrayProjects(){
				global $AppUI;
				$strJS = "var arProjects = new Array();\n";
				
				if($this->Projects()){
					foreach($this->Projects() as $rProject){
						$strJS .= "arProjects[arProjects.length] = new Array({$rProject["company_id"]}, {$rProject["project_id"]}, \"".$AppUI->_($rProject["project_name"])."\");\n";
					}
				}
				
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
				$strJS = "";
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
								var index = f.company_id.selectedIndex;
								var jur = f.company_id[index].value;
			                 			
								for( i = 0 ; i < arProjects.length ; i++) {
										if ( arProjects[i][0] == jur ) {
										//  matches
										var opt = new Option(arProjects[i][2], arProjects[i][1], false, false);
										sel.options[sel.options.length] = opt;
									}
								}
								selectProject();
							}			
						";
				
				$strJS .= "function findProject(){
							var f = document.".$this->getFrmName().";
							if(intIdProject != \"\"){
								for(x=0; x < f.".$this->getCboProjects().".options.length; x++){
									if(f.".$this->getCboProjects().".options[x].value == intIdProject){
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


			function generateJScallFunctions($withTag=true){
				$strJS = "	changeProject();
							findProject();
						";
				if($withTag) $strJS = "<script language=\"javascript\">$strJS</script>";
				
				return $strJS;
			}

			function generateJScallFunctions_tabla($withTag=false){
				$strJS = "	changeProject();
							findProject();
						";
				if($withTag) $strJS = "<script language=\"javascript\">$strJS</script>";
				
				return $strJS;
			}

		    
			
} 

				        
?>