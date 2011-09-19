<?php

$locales = $AppUI->getConfig("host_locale_list");


for($i=0; $i<count($locales);$i++){
	
	ob_start();
	@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/common.inc" );
   
	switch ($m) {
		case 'departments':
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/companies.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/companies.inc" );
			break;
		case 'system':
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$AppUI->cfg['host_locale']}/styles.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$AppUI->cfg['host_locale']}/styles.inc" );
			break;
		case 'dashboard':
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/ticketsmith.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/ticketsmith.inc" );
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/calendar.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/calendar.inc" );
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/tasks.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/tasks.inc" );			
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/wmail.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/wmail.inc" );			
			break;
		case 'tasks':
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/timexp.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/timexp.inc" );		
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/files.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/files.inc" );	 					
			break;
		case 'privmsg':
				if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/calendar.inc"))
						@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/calendar.inc" );
				break;
		case 'articles':
				if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/articles.inc"))
						@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/articles.inc" );
				break;
		case 'timexp':
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/tasks.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/tasks.inc" );			
			break;
		case 'hhrr':
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/admin.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/admin.inc" );
			break;		
		case 'projects':
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/files.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/files.inc" );	
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/todo.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/todo.inc" );			
			break;
		case 'public':
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/tasks.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/tasks.inc" );			
			break;	
		case 'webtracking':
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/timexp.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/timexp.inc" );		
			break;
		case 'reports':
			if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/timexp.inc"))
				@readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/timexp.inc" );		
			break;
				
  }

   
	if (file_exists("{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/$m.inc")){
		readfile( "{$AppUI->cfg['root_dir']}/locales/{$locales[$i]}/$m.inc" );		
	}
	
	
	eval( "\$GLOBALS['translate']['{$locales[$i]}']=array(".ob_get_contents()."\n'0');" );
	 
	ob_end_clean();
	
}


?>