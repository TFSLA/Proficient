<?php

			$_POST["new_candidate"] = true;
			
			include_once($AppUI->getConfig("root_dir")."/modules/hhrr/do_hhrr_aed.php");
			

			$AppUI->redirect();
		

?>