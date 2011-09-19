<?php

$uploads_dir = $AppUI->getConfig('hhrr_uploads_dir');
$dirs = $AppUI->readDirs($uploads_dir);
echo "<pre>";
//var_dump($dirs);
echo "</pre>";

//$sql = "ALTER TABLE `users` CHANGE `resume` `resume` TEXT NOT NULL;";
//db_exec($sql);
$sql = "select user_id, concat(user_first_name, ' ', user_last_name) fullname, user_pic, resume from users order by user_id";
$usuarios = db_loadList($sql);
$sql="";
echo "<table class='std'><tr><th>User</th><th>Pic</th><th>CV</th><th>Files</th><th>Message</th></tr>";
for ($i=0;$i<count($usuarios);$i++){
	extract($usuarios[$i]);
	
	$files = $AppUI->readFiles($uploads_dir."/".$user_id);
//echo "<pre>";
//var_dump($files);
//echo "</pre>";	
	$message = "";
	$bgcolor1 = "#ffffff";
	$bgcolor2 = "#ffffff";
	if (! in_array($user_pic,array( "ninguna","","NULL"))){
		$notexists = !file_exists($uploads_dir."/".$user_id."/".$user_pic);
		/*
		$notexists = true;
		foreach ($files as $file_name){
			if ($file_name == $user_pic)
				$notexists = false;
		}*/
		if ($notexists){
			$bgcolor1 = "#ff0000";
			$message .= "La imagen '$user_pic' no se encuentra.<br>";
			$sql .= "update users set user_pic = 'ninguna' where user_id ='$user_id';<br>";
		}
	}
	if (! in_array($resume,array( "ninguna","","NULL"))){
		$notexists = !file_exists($uploads_dir."/".$user_id."/".$resume);
		
		if ($notexists){
			foreach ($files as $file_name){
				if (substr($file_name,0,strlen($resume)) == $resume){
					$resume_new = $file_name;
					$notexists = false;
				}
			}
			if (!$notexists){
				$bgcolor = "#22ff22";
				$sql .= "update users set resume = '$resume_new' where user_id ='$user_id';<br>";
				$message .= "update users set resume = '$resume_new' where user_id ='$user_id';<br>";			
				
			}
		}
		if ($notexists){
			$bgcolor2 = "#ff0000";
			$message .= "El CV '$resume' no se encuentra.<br>";
			$sql .= "update users set resume = 'ninguna' where user_id ='$user_id';<br>";
		}
	}	
if ($message != "")
	echo "
<tr bgcolor='#ffffff'>
	<td><a href='index.php?m=hhrr&a=viewhhrr&id=$user_id'>$user_id - $fullname</a></td>	
	<td bgcolor='$bgcolor1'>$user_pic</td>
	<td bgcolor='$bgcolor2'>$resume</td>
	<td>".implode(",<br>",$files)."&nbsp;</td>
	<td>$message&nbsp;</td>
</tr>";
	unset ($files);
	unset ($user_id);
	unset ($user_pic);
	unset ($resume);
}
echo "</table>";
echo "<hr>".$sql;
?>