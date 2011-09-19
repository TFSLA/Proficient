<?php
	
	function GetKbProjectsItems ( $username, $password, $date, $indexItems, $firstItem) {

		include_once('common.inc.php');

		if($AppUI->login($username,$password, true, true))
		{
			include("../includes/permissions.php");
			include_once("functions.inc.php");
			include_once( "../modules/companies/companies.class.php" );

			if ($AppUI->user_type <> 1)
				$prjs = getAllowedRecords($AppUI->user_id, "project_id");

			if ($AppUI->user_type == 1)
			{
				$sql = "SELECT articles.title, articles.type, articles.abstract as content1, articles.body as content2, articles.article_id as id, date_modified, articlesections.name as section_name, projects.project_name as project_name";
				$sql .= " FROM articles";
				$sql .= " LEFT JOIN articlesections ON articles.articlesection_id = articlesections.articlesection_id";
				$sql .= " LEFT JOIN projects ON projects.project_id = articles.project";
				$sql .= " WHERE articles.date_modified >= '".$date."'";
			}
			else
			{
				if (!getDenyRead( 'articles' ))
				{
					$sqlExtra = " AND (articles.articlesection_id <> 0";
				}
				else
				{
					$objCompany = new CCompany();
					$companies = $objCompany->getCompanies($AppUI->user_id);

					$query_sections = "SELECT DISTINCT(id) FROM articlesections_projects";
					$query_sections .= " WHERE (";
					$query_sections .= " project_id IN (". implode( ',', array_keys($prjs) ) .")";
					$query_sections .= " OR (company_id IN (". implode( ',', array_keys($companies) ) .") AND project_id = -1))";

					$sections = db_loadColumn($query_sections);

					if(count($sections) > 0)
						$sqlExtra = " AND (articles.articlesection_id IN (".implode( ',', $sections).")";
				}

				if (count($prjs) > 0)
					$sqlExtra .= " OR ".(strlen($sqlExtra) == 0 ? '(' : '')."articles.project IN (".implode( ',', array_keys($prjs) ).")";

				$sqlExtra .= ")";

				$sql = "SELECT articles.title, articles.type, articles.abstract as content1, articles.body as content2, articles.article_id as id, date_modified, articlesections.name as section_name, projects.project_name as project_name";
				$sql .= " FROM articles";
				$sql .= " LEFT JOIN articlesections ON articles.articlesection_id = articlesections.articlesection_id";
				$sql .= " LEFT JOIN projects ON projects.project_id = articles.project";
				$sql .= " WHERE articles.date_modified >= '".$date."'";
				$sql .= " AND (articles.is_private = 0 OR (articles.is_private = 1 AND user_id = ".$AppUI->user_id."))";
				$sql .= $sqlExtra;
			}

			$sqlExtra = "";

			if ($AppUI->user_type == 1)
			{
				$sql .= " UNION";
				$sql .= " SELECT file_name as title, '' as type, file_description as content1, '' as content2, file_id as id, date_modified, articlesections.name as section_name, projects.project_name as project_name";
				$sql .= " FROM files";
				$sql .= " LEFT JOIN articlesections ON files.file_section = articlesections.articlesection_id";
				$sql .= " LEFT JOIN projects ON files.file_project = projects.project_id";
				$sql .= " WHERE date_modified >= '".$date."'";
			}
			else
			{
				if (!getDenyRead( 'articles' ))
				{
					$sqlExtra = " AND (files.file_section <> 0";
				}
				else
				{
					if(count($sections) > 0)
						$sqlExtra = " AND (files.file_section IN (".implode( ',', $sections).")";
				}

				if (count($prjs) > 0)
					$sqlExtra .= " OR ".(strlen($sqlExtra) == 0 ? '(' : '')."files.file_project IN (".implode( ',', $prjs ).")";

				if (!getDenyRead( 'files' ))
					$sqlExtra .= " OR ".(strlen($sqlExtra) == 0 ? '(' : '')."files.file_project = 0";

				$sqlExtra .= ")";

				$sql .= " UNION";
				$sql .= " SELECT file_name as title, '' as type, file_description as content1, '' as content2, file_id as id, date_modified, articlesections.name as section_name, projects.project_name as project_name";
				$sql .= " FROM files";
				$sql .= " LEFT JOIN articlesections ON files.file_section = articlesections.articlesection_id";
				$sql .= " LEFT JOIN projects ON files.file_project = projects.project_id";
				$sql .= " WHERE files.date_modified >= '".$date."'";
				$sql .= " AND (files.is_private = 0 OR (files.is_private = 1 AND file_owner = ".$AppUI->user_id."))";
				$sql .= $sqlExtra;
			}
			
			if ($AppUI->user_type == 1)
			{
				$sql .= " UNION";				
				$sql .= " SELECT btpsa_bug_table.summary as title, -1 as type, btpsa_bug_table.summary as content1, btpsa_bug_text_table.description as content2, btpsa_bug_table.id, btpsa_bug_table.date_submitted as date_modified, '' as section_name, projects.project_name";
				$sql .= " FROM btpsa_bug_table";
				$sql .= " INNER JOIN projects ON projects.project_id = btpsa_bug_table.project_id";
				$sql .= " LEFT OUTER JOIN btpsa_bug_text_table ON btpsa_bug_table.id = btpsa_bug_text_table.id";
				$sql .= " WHERE btpsa_bug_table.date_submitted >= '".$date."'";				
			}
			else
			{
				if (!getDenyRead('webtracking') && count($prjs) > 0)
				{
					$sql .= " UNION";				
					$sql .= " SELECT btpsa_bug_table.summary as title, -1 as type, btpsa_bug_table.summary as content1, btpsa_bug_text_table.description as content2, btpsa_bug_table.id, btpsa_bug_table.date_submitted as date_modified, '' as section_name, projects.project_name";
					$sql .= " FROM btpsa_bug_table";
					$sql .= " INNER JOIN projects ON projects.project_id = btpsa_bug_table.project_id";
					$sql .= " LEFT OUTER JOIN btpsa_bug_text_table ON btpsa_bug_table.id = btpsa_bug_text_table.id";
					$sql .= " WHERE btpsa_bug_table.date_submitted >= '".$date."'";	
					$sql .= " AND btpsa_bug_table.project_id IN (".implode( ',', $prjs ).")";
				}
			}

			$sql .= " ORDER BY date_modified limit ".$firstItem.",".($indexItems).";";

			$baseUrl = $AppUI->getConfig("base_url");
			$resultKb = mysql_query($sql);

			$xmlItems = "<items>";

			$countItems = 1;

			while ($rowKb = mysql_fetch_array($resultKb, MYSQL_ASSOC))
			{
			 	if($countItems <= $indexItems)
			 	{
					$countItems++;
					$xmlItems .= "<item>";

					if($rowKb["type"] == "-1")
					{
						$xmlItems .= "<title><![CDATA[[PROFICIENT ID ".$rowKb["id"].(($rowKb["project_name"]) ? ' / '.$rowKb["project_name"] : (($rowKb[	"section_name"]) ? ' / '.$rowKb["section_name"] : '')).'] '.$rowKb["title"]."]]></title>";
					}
					else
					{
						$xmlItems .= "<title><![CDATA[[PROFICIENT".(($rowKb["project_name"]) ? ' / '.$rowKb["project_name"] : (($rowKb[	"section_name"]) ? ' / '.$rowKb["section_name"] : '')).'] '.$rowKb["title"]."]]></title>";					
					}

					switch($rowKb["type"])
					{
						case "-1"://Incidentes
							$xmlItems .= "<content><![CDATA[".$rowKb["content1"]."<hr/>".$rowKb["content2"]."]]></content>";
							$xmlItems .= "<link><![CDATA[".$baseUrl."/index.php?m=webtracking&a=bug_view_page&bug_id=".$rowKb["id"]."]]></link>";							
							break;
						case "0"://Articulo
							$xmlItems .= "<content><![CDATA[".$rowKb["content1"]."<hr/>".$rowKb["content2"]."]]></content>";
							$xmlItems .= "<link><![CDATA[".$baseUrl."/index_inc.php?inc=./modules/articles/viewarticle.php&m=articles&id=".$rowKb["id"]."]]></link>";
							break;
						case "1"://Enlace
							$xmlItems .= "<content><![CDATA[".$rowKb["content1"]."<hr/>".$rowKb["content2"]."]]></content>";
							$xmlItems .= "<link><![CDATA[".$baseUrl."/index_inc.php?inc=./modules/articles/vwlink.php&m=articles&id=".$rowKb["id"]."]]></link>";
							break;
						default://Otro
							$xmlItems .= "<content><![CDATA[".$rowKb["content1"]."]]></content>";
							$xmlItems .= "<link><![CDATA[".$baseUrl."/index_inc.php?inc=./modules/files/show_versions.php&m=files&file_id=".$rowKb["id"]."]]></link>";
							break;
					}

					$xmlItems .= "<dateModified>".$rowKb["date_modified"]."</dateModified>";

					$xmlItems .= "</item>";
				}
			}

			$xmlItems .= "</items>";

			$xmlQuery = "<query>";

			if($countItems == $indexItems+1)
			{
				$xmlQuery .= "<index_mode>PARTIAL</index_mode>";
				$xmlQuery .= "<nextFirstItem>".($firstItem+$indexItems)."</nextFirstItem>";
			}
			else
			{
				$xmlQuery .= "<index_mode>COMPLETE</index_mode>";
				$xmlQuery .= "<nextFirstItem>0</nextFirstItem>";
			}

			$xmlQuery .= "</query>";

			return("<indexer>".$xmlQuery.$xmlItems."</indexer>");
		}
		else
		{
			return("<error><code>101</code><description>Invalid credentials.</description></error>");
		}
	}
?>