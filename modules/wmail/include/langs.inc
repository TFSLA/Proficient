<?php
/********************************************************

	AUTHOR: Ryo Chijiiwa <ryo@ilohamail.org>
	FILE: include/langs.inc
	PURPOSE:
		Provide centralized location for keeping track of 
		available languages and character sets

********************************************************/

$languages=array(
	"eng/"=>"English",
	"cat/"=>"Catalan",
	"tw/"=>"Chinese (BIG5)",
	"cn/"=>"Chinese(GB2312)",
	"dk/"=>"Dansk",
	"de/" => "Deutsch",
	"es/" => "Espa&ntilde;ol",
	"fr/"=>"Fran&ccedil;ais", 
	"ga/"=>"Galician",
	"heb/"=>"Hebrew",
	"hu/"=>"Hungarian",
	"it/"=>"Italiano",
	"jp/"=>"Japanese", 
	"kr/"=>"Korean",
	"nl/"=>"Nederlands",
	"no/"=>"Norwegian (bm)",
	"nn_NO/"=>"Norwegian (nn)",
	"pl/"=>"Polski",
	"pt/"=>"Portuguese (Standard)",
	"br/"=>"Portuguese (Brazilian)",
	"ro/"=>"Romanian",
	"ru/"=>"Russian",
	"se/"=>"Swedish",
	"vn/"=>"Vietnamese"
);

$charsets=array(
	"US-ASCII"=>"ASCII (English)", 
	"X-EUC-JP"=>"EUC-JP (Japanese)", 
	"EUC-KR"=>"EUC-KR (Korean)",
	"ISO-8859-1"=>"ISO-8859-1 (Latin-1)",
	"ISO-8859-2"=>"ISO-8895-2",
	"BIG5"=>"BIG5 (Chinese)",
	"GB2312"=>"GB2312 (Chinese)",
	"UTF-8" => "UTF-8",
	"Windows-1251" => "Windows-1251 (Cyrillic)",
	"Windows-1255" => "Windows-1255 (Hebrew)"
);
?>
