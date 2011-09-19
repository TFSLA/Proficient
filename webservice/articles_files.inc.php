<?
	function GetSections ( $username, $password)
	{
		include_once('common.inc.php');

		if($AppUI->login($username,$password, true, false))
		{
			$sql = "SELECT * FROM articlesections";
			$file_sections = db_loadHashList( $sql);
			$file_sections = arrayMerge( array( '0'=>$AppUI->_('Ninguna')), $file_sections );
			$file_sections = arrayMerge( array( '-1'=>$AppUI->_('Top')), $file_sections );

			$xml = "<sections>";

			foreach($file_sections as $k => $v)
			{
				$xml .= "<section>";
				$xml .= "<id>".$k ."</id>";
				$xml .= "<name>".$v."</name>";
				$xml .= "</section>";
			}

			$xml .= "</sections>";

			return ($xml);
		}
		else
		{
			return("<error><code>101</code><description>Invalid credentials.</description></error>");
		}
	}
	
	function GetCategories ( $username, $password) 
	{
		include_once('common.inc.php');

		if($AppUI->login($username,$password, true, false))
		{
			$sql_categorys = "SELECT category_id, name_".$AppUI->user_locale." AS name FROM files_category ORDER BY name_".$AppUI->user_locale;
			$file_categorys = db_loadHashList( $sql_categorys);
			$file_categorys = arrayMerge( array( Ninguna), $file_categorys );

			$xml = "<categories>";

			foreach($file_categorys as $k => $v)
			{
				$xml .= "<category>";
				$xml .= "<id>".$k ."</id>";
				$xml .= "<name>".$v."</name>";
				$xml .= "</category>";
			}

			$xml .= "</categories>";

			return ($xml);
		}
		else
		{
			return("<error><code>101</code><description>Invalid credentials.</description></error>");
		}
	}
	
	function GetNotifications ( $username, $password)
	{	
		include_once('common.inc.php');
		include_once( "../locales/core.php" );
		
		if($AppUI->login($username,$password, true, false))
		{
			$fileNotifications = array( 0 => $AppUI->_('No Notify'), 1 => $AppUI->_('Project Users'), 2 => $AppUI->_('Project Administrators') );
			
			$xml = "<notifications>";

			foreach($fileNotifications as $k => $v)
			{
				$xml .= "<notification>";
				$xml .= "<id>".$k ."</id>";
				$xml .= "<name>".$v."</name>";
				$xml .= "</notification>";
			}

			$xml .= "</notifications>";

			return ($xml);
		}
		else
		{
			return("<error><code>101</code><description>Invalid credentials.</description></error>");
		}
	}
?>