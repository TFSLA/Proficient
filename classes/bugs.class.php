<?php

class CBugs {

	var $companies_bug = null;
	var $projects_bug = null;
	var $bugs = null;
	var $item = array();
	var $items = array();//almacena items que seran insertados en Projects
	
	var $_frm_name = "";
	var $_cbo_company_name = "companies_bug";
	var $_cbo_project_name = "projects_bug";
	var $_cbo_bug_name = "bug_id";
	var $_project_id_selected = "";
	
	var $_addItems_forEachcompany_inprojects_bug = true;//agrega los items almacenados en el array Items en el array Projects por cada company
	var $_addItems_forEachproject_inbugs = true;//agrega los items almacenados en el array Items en el array Bugs por cada projecto
	var $_breset_Items = true;//una vez insertados los registros se vacia
	       

		   /* Función que trae las companies de acuerdo a los permisos */

			function loadCompanies($ActiveProjects = null){

				global $AppUI;

				$allowed = array();
                

				// Traigo solo los projectos con bugs //
                  
				  $sql = "select DISTINCT project_id from btpsa_bug_table";

				  $proj_con_bugs = db_loadColumn($sql);	

                   
						if ($AppUI->user_type == 1){

		                    if (count( $proj_con_bugs)>0)
							{
							$InactiveProjectsFilter = ($ActiveProjects == null ? "" : "AND project_active = 1");
							
							$strSql = " SELECT DISTINCT p.project_company as company_id,c.company_name
                                        FROM projects as p, companies as c 
                                        WHERE p.project_company=c.company_id and 
                                        project_id IN (" . implode( ',', $proj_con_bugs ) . ") 
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

						if (count( $proj_con_bugs)>0)
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
					

                    $strSqlP = "
					select distinct project_id from project_roles where user_id='".$AppUI->user_id."' and role_id = '2' ";
                    
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

                    $strSqlP = "
					select distinct project_id from role_permissions where access_id='2' and item_id= $on and permission_value != '-1' ";
                    
                    $role_project_wide = db_loadColumn($strSqlP);	

					if (count($role_project_wide)=="0")
					{
					$role_project_wide[0] ="0" ;
					}
                    
					// Projectos con bugs //
					
					$InactiveProjectsFilter = ($ActiveProjects == null ? "" : "AND project_active = 1");

				   $strSql = "SELECT DISTINCT p.project_company as company_id,c.company_name
                                        FROM projects as p, companies as c 
                                        WHERE p.project_company=c.company_id 
                                        AND 
                                        ( project_id IN (" . implode( ',', $owner ) . ")
                                        OR
                                        project_id IN (" . implode( ',', $role_project_asigned ) . ") 
                                        )
                                        AND 
                                        project_id IN (" . implode( ',', $proj_con_bugs ) . ") 
                                        $InactiveProjectsFilter
									    ORDER BY company_name";

					
					$this->companies = db_loadHashList($strSql);

				}
				else
				{
				$this->companies = array();
				}
			}
				     
	}

            /* Mete las companies en un array */
			function Companies(){
				return $this->companies;
			}
            
			/* Genera el html con el select de companies */
			function generateHTMLcboCompanies($selected="", $class=""){
				return arraySelect($this->Companies(), "id_company_bug", "class=\"$class\" tabindex=\"8\" onchange=\"javascript:changeProject_bug();\" ", $selected, true, false);
			}

			function generateHTMLcboCompanies_tabla($selected="", $class="",$onchange){
				return arraySelectJs($this->Companies(), "id_company_bug[]", "class=\"$class\" ".$onchange, $selected, true);
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
			function loadProjects($intProject = null, $ActiveProjects = null){
				global $AppUI;

				$arTmp = array();

				   // Traigo solo los projectos con bugs //
                  
				   $sql = "select DISTINCT project_id from btpsa_bug_table";

				   $proj_con_bugs = db_loadColumn($sql);	
                
			      
				    if ($AppUI->user_type == 1){

							if (count( $proj_con_bugs)>0)
							{
							$InactiveProjectsFilter = ($ActiveProjects == null ? "" : "AND project_active = 1");
							
							$strSql = "SELECT project_id,project_name,project_company as company_id, -1 permission_value"
							 . "\nFROM projects  where project_id IN (" . implode( ',', $proj_con_bugs ) . ")"
							 . $InactiveProjectsFilter
							 . "\nORDER BY project_name";

							$arProjects_bug = db_loadList($strSql);
				
				            $this->projects = $arProjects_bug;
								
							}
							else
							{
							$this->projects = array();
							}
						}
						else
						{
							
						  if (count( $proj_con_bugs)>0)
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
							

							$strSqlP = "
							select distinct project_id from project_roles where user_id='".$AppUI->user_id."' and role_id = '2' ";
							
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

						

							$strSqlP = "
							select distinct project_id from role_permissions where access_id='2' and item_id= $on and permission_value != '-1' ";
							
							$role_project_wide = db_loadColumn($strSqlP);	

							if (count($role_project_wide)=="0")
							{
							$role_project_wide[0] ="0" ;
							}

							// Projectos con tareas asignadas //
							$InactiveProjectsFilter = ($ActiveProjects == null ? "" : "AND project_active = 1");
							
							$strSql = "SELECT DISTINCT p.project_id,p.project_name,p.project_company as company_id
							FROM btpsa_bug_table as b, projects as p
							WHERE p.project_id = b.project_id 
							AND 
                            ( p.project_id IN (" . implode( ',', $owner ) . ")
                              OR
                              p.project_id IN (" . implode( ',', $role_project_asigned ) . ") 
                            )
                            AND 
							p.project_id IN (" . implode( ',', $proj_con_bugs ) . ") 
							$InactiveProjectsFilter
							";

							 $arProjects_bug = db_loadList($strSql);
				
				             $this->projects = $arProjects_bug;
								
						  }
						  else
						  {
						   $this->projects = array();
						  }
					      
				        }
				
				
				if($this->_addItems_forEachCompany_inProjects_bug && count($this->items) > 0){
					$intCompanyId_bug = "";
					foreach($arProjects_bug as $rRow){
						if($rRow["company_id"] != $intCompanyId_bug){
							$intCompanyId_bug = $rRow["company_id"];
							foreach($this->items as $kItem => $rItem){
								$this->addItemAtBeginOfProjects($this->addItemProject($intCompanyId_bug, key($rItem), $rItem[key($rItem)]));
							}
						}
					}
					if($this->_breset_Items) $$this->items = array();
				}
			}
            

			/* Array de Projectoss */
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

				return arraySelect($arProjectsTmp, $this->getCboProjects(),"class=\"$class\" tabindex=\"9\" onchange=\"javascript:changeBug();\" ", $selected, true);
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
				$this->_project_id_bug_selected = $value;
			}
			function getJSSelectedProject(){
				return $this->_project_id_bug_selected;
			}
            
			/*genera el array con los projects*/
			function _JSgenerateArrayProjects(){
				global $AppUI;
				$strJS = "var arProjects_bug = new Array();\n";
				
				if($this->Projects()){
					foreach($this->Projects() as $rProject_bug){
                        $descb =  str_replace("\""," ",$rProject_bug["project_name"]);

						$strJS .= "arProjects_bug[arProjects_bug.length] = new Array({$rProject_bug["company_id"]}, {$rProject_bug["project_id"]}, \"".$descb."\");\n";
					}
				}
				
				return $strJS;
			}

			/*genera el codigo JS que va en la pagina*/
			function generateJS(){
				
				$strJS = "";
				
				$strJS .= "var intIdProject_bug = ";
				
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
				$strJS .= "	function selectProject_bug(){
								var f = document.".$this->getFrmName().";
								f.".$this->getCboProjects().".options[0].selected = true;
							}\n
							";

				$strJS .= "	function changeProject_bug() {\n
								var sel = document.". $this->getFrmName().".".$this->getCboProjects().";
								var f = document.".$this->getFrmName().";

								while ( sel.length != 0 ) {
									sel[0] = null;
								}
								var index = f.id_company_bug.selectedIndex;
								var jur = f.id_company_bug[index].value;
						
								for( i = 0 ; i < arProjects_bug.length ; i++) {
										if (arProjects_bug[i][0] == jur ) {
										//  matches
										var opt = new Option(arProjects_bug[i][2], arProjects_bug[i][1], false, false);
										sel.options[sel.options.length] = opt;
									}
								}
								
								var sel_bug = document.". $this->getFrmName().".".$this->getCboBugs().";

		                        
								if (typeof sel_bug != 'undefined')
								{
								changeBug();
							    findBug();
								}

								selectProject_bug();
							}			
						";
				
				$strJS .= "function findProject_bug(obj){
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
				$strJS = "	changeProject_bug();
							findProject_bug($project_id);
						";
				if($withTag) $strJS = "<script language=\"javascript\">$strJS</script>";
				
				return $strJS;
			}


	///////////////////////////////////////////
    /*    Funciones para traer los bugs     */
    //////////////////////////////////////////


	function generateJScallFunctions_bug($bug_sel=null,$withTag=true){
				$strJS = "changeBug();
							findBug($bug_sel);
						";
				if($withTag) $strJS = "<script language=\"javascript\">$strJS</script>";
				
				return $strJS;
			}



			/* Funcion que trae las tareas de acuerdo al proyecto seleccionado */
			function loadBugs($intProject = null){
				global $AppUI;

				$arTmp = array();


				// Traigo solo los projectos con bugs //
                  
				   $sql = "select DISTINCT project_id from btpsa_bug_table";

				   $proj_con_bugs = db_loadColumn($sql);	
                

				// Traigo los bugs //

				    if ($AppUI->user_type == 1){

					  if (count( $proj_con_bugs)>0)
					  {
					  $strSql = "SELECT id,summary,project_id"
								. "\nFROM btpsa_bug_table "
								. "\nORDER BY summary";	

					 $arBugs = db_loadList($strSql);
				
				     $this->bugs = $arBugs;
					  }
							else
							{
				
				            $this->bugs = array();
							}
							
						}
						else
						{  

							if (count( $proj_con_bugs)>0)
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
					

                    $strSqlP = "
					select distinct project_id from project_roles where user_id='".$AppUI->user_id."' and role_id = '2' ";
                    
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

                    $strSqlP = "
					select distinct project_id from role_permissions where access_id='2' and item_id= $on and permission_value != '-1' ";
                    
                    $role_project_wide = db_loadColumn($strSqlP);	

					if (count($role_project_wide)=="0")
					{
					$role_project_wide[0] ="0" ;
					}
					
                    

					$strSql = "select id,summary,project_id from btpsa_bug_table 
                               where 1=1
                               AND 
                               ( project_id IN (" . implode( ',', $owner ) . ")
                                 OR
                                 project_id IN (" . implode( ',', $role_project_asigned ) . ") 
                               )
                               AND 
                               project_id IN (" . implode( ',', $proj_con_bugs ) . ")  
                               order by summary";

					$arBugs = db_loadList($strSql);
					
					$i = 0;
					foreach ($arBugs as $Bug)
					{
						$arBugs[$i]["description"] = ereg_replace("&aacute;","á",$arBugs[$i]["description"]);
						$arBugs[$i]["description"] = ereg_replace("&eacute;","é",$arBugs[$i]["description"]);
						$arBugs[$i]["description"] = ereg_replace("&iacute;","í",$arBugs[$i]["description"]);
						$arBugs[$i]["description"] = ereg_replace("&oacute;","ó",$arBugs[$i]["description"]);
						$arBugs[$i]["description"] = ereg_replace("&uacute;","ú",$arBugs[$i]["description"]);
						$arBugs[$i]["description"] = ereg_replace("&apos;","'",$arBugs[$i]["description"]);
						$arBugs[$i]["description"] = ereg_replace("&#039;","'",$arBugs[$i]["description"]);
						$arBugs[$i]["description"] = ereg_replace("&quot;","\"",$arBugs[$i]["description"]);
						
						$i++;
					}
					
				    $this->bugs = $arBugs;
					}
					else
					{
						$this->bugs = array();
					}
							
				}
				
				
				if($this->_addItems_forEachProject_inBugs && count($this->items) > 0){
					$intProjectId = "";
					foreach($arBugs as $rRow){
						if($rRow["project_id"] != $intProjectId){
							$intProjectId = $rRow["project_id"];
							foreach($this->items as $kItem => $rItem){
								$this->addItemAtBeginOfBugs($this->addItemBug($intProjectId, key($rItem), $rItem[key($rItem)]));
							}
						}
					}
					if($this->_breset_Items) $this->items = array();
				}
			}


			/* Array de bugs */
			function Bugs(){
				return $this->bugs;
			}


			/* Agrega item al principio de Bugs */
			function addItemAtBeginOfBugs($item){
				$arTmp = array();

				//corro los indices en uno asi puedo insertar el item
				if (count($this->Bugs())>0)
				{
					foreach($this->Bugs() as $k => $r){
						$k++;
						$arTmp[$k] = $r;
					}
				}

				$this->bugs = $arTmp;
				$arTmp = null;
				$this->addItemAtBeginOf($this->bugs, $item);
			}
 

            /* Agrega los items en el array bugs */
			function addItemBug($project, $bug, $bugname){
				$arTmp = array("project_id" => $project,
								"id" => $bug,
								"summary" => $bugname	
								);
				return $this->addItem(0, $arTmp);
			}
            

			function generateHTMLcboBugs($selected="", $class="", $isEmpty=true){
				$arBugsTmp = array();
				return arraySelect($arBugsTmp, $this->getCboBugs(), "class=\"$class\" tabindex=\"10\" ", $selected, true);
			}

			function generateHTMLcboBugs_tabla($selected="", $class="", $isEmpty=true){
				$arBugsTmp = array();
				return arraySelectJs($arBugsTmp, "bug_id[]", "class=\"$class\"", $selected, true);
			}

			function setCboBugs($strName){
				$this->_cbo_bug_name = $strName;
			}
			function getCboBugs(){
				return $this->_cbo_bug_name;
			}
			
			function setJSSelectedBug($value){
				$this->_bug_id_selected = $value;
			}

			function getJSSelectedBug(){
				return $this->_bug_id_selected;
			}

			/*genera el array con los bugs */
			function _JSgenerateArrayBugs(){
				global $AppUI;
				$strJS = "var arBugs = new Array();\n";
				
				if($this->Bugs()){
					foreach($this->Bugs() as $rBug){
						$descbb =  str_replace("\""," ",$rBug["summary"]);
						if($rBug["project_id"]!=0){
                        $descbb =  "[".str_pad($rBug["id"], 7, "0", STR_PAD_LEFT)."] - ".substr($descbb ,0,18);  
						}
           
						$strJS .= "arBugs[arBugs.length] = new Array({$rBug["project_id"]}, {$rBug["id"]}, \"".$descbb."\");\n";
					}
				}
				
				return $strJS;
			}

		    
		   /*genera el codigo JS que va en la pagina*/
			function generateJSBug(){
				
				$strJS = "";
				
				$strJS .= "var intIdBug = ";
				
				if($this->getJSSelectedBug() != 0){
					$strJS .= $this->getJSSelectedBug()."\n";
				}else{
					$strJS .= "'';\n";
				}
				
				$strJS .= $this->_JSgenerateArrayBugs();
				$strJS .= $this->_JSgenerateFunctionsBug();
				
				return $strJS;
			}

			/*genera las funciones que realizan la actualizacion de los cbos*/
			function _JSgenerateFunctionsBug(){
				$strJS = "";
				$strJS .= "	function selectBug(){
								var f = document.".$this->getFrmName().";
								f.".$this->getCboBugs().".options[0].selected = true;
							}\n
							";

				$strJS .= "	function changeBug() {\n
								var sel = document.". $this->getFrmName().".".$this->getCboBugs().";
								var f = document.".$this->getFrmName().";
								// Remove options
								while ( sel.length != 0 ) {
									sel[0] = null;
								}
								var index = f.project_id_bug.selectedIndex;
								var jur = f.project_id_bug[index].value;
						
								for( i = 0 ; i < arBugs.length ; i++) {
										if ( arBugs[i][0] == jur ) {
										//  matches
										var opt = new Option(arBugs[i][2], arBugs[i][1], false, false);
										sel.options[sel.options.length] = opt;
									}
								}
								selectBug();
							}			
						";
				
				$strJS .= "function findBug(obj){
							var f = document.".$this->getFrmName().";
							if(obj != \"\"){
								for(x=0; x < f.".$this->getCboBugs().".options.length; x++){
									if(f.".$this->getCboBugs().".options[x].value == obj){
										f.".$this->getCboBugs().".options[x].selected = true;
										break;
									}
								}
								f.".$this->getCboBugs().".selectedValue;
							}
						}
						";
				
				return $strJS;
			}



} 

?>