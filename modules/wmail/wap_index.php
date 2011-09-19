<?
//--------------- config -----------------------------

$sql="SELECT * FROM users WHERE user_id = $AppUI->user_id;";
$users = db_loadList( $sql );
if($users[0]["user_webmail_autologin"]=="Yes"){
  $login     = $users[0]["user_email_user"];
  $password  = $users[0]["user_email_password"];

  if($users[0]["user_mail_server_port"]==110)
    $host = $users[0]["user_pop3"];
  else
    $host = $users[0]["user_imap"];

  if($users[0]["user_mail_server_port"]==110)
    $protocol  = "pop3";
  else
    $protocol  = "imap";
}


//Defaul imap/pop3 host.
//$host_default="mail.omnisciens.com";

//if value is set to true, user can't change host.
$host_fixed=false;

//Defaul protocol.
$protocol_default="pop3";

//if value is set to true, user can't change protocol.
$protocol_fixed=false;

//Truncate messages greater then XX characters.
$message_maxlen=2048;

//Count of folders per one page.
$folders_perpage=10;

//Count of messages per one page.
$messages_perpage=10;

//====================================================
ini_set("url_rewriter.tags","");


$action=$_REQUEST["action"];
/*
if(empty($action) && !empty($_SERVER["QUERY_STRING"])) {
  list($login,$password,$host,$protocol)=explode(":", $_SERVER["QUERY_STRING"]);
  if(substr($protocol,0,4)=="pop3") $protocol="pop3";
  if(substr($protocol,0,4)=="imap") $protocol="imap";
  $action="login";
}
*/
if($action=="")$action="login";

if(!function_exists("imap_open")) {
  $action="error";
  $error="Imap module is not installed.";
}
else {
  if(!empty($_REQUEST["sid"])) {
    $sid=$_REQUEST["sid"];
    session_id($sid);
    session_start();
  }
  if(session_is_registered("connect")) {
    if( !empty($_REQUEST["folder"]) ) $_SESSION["folder"]=$_REQUEST["folder"];
    $box=@imap_open($_SESSION["connect"].$_SESSION["folder"], $_SESSION["login"], $_SESSION["password"]);
    if( $box===false ) {
      $action="auth";
      session_destroy();
    }
  }

}
//----------------------------------------------------
?>
<template><do type="prev" label="back"><prev/></do></template>
<?

$continue=true;
while($continue) {
  $continue=false;
  switch($action) {
//----------------------------------------------------
case "login":
  if(empty($login)) $login=$_REQUEST["login"];
  if(empty($password)) $password=$_REQUEST["password"];
  if(empty($host)) $host=$_REQUEST["host"];
  if(empty($host) || $host_fixed) $host=$host_default;
  if(empty($protocol)) $protocol=$_REQUEST["protocol"];
  if(empty($protocol) || $protocol_fixed) $protocol=$protocol_default;


  if(empty($host)) {
    $action="auth";
    $continue=true;
    break;
  }

  $connect="{".$host.($protocol=="pop3"?":110/pop3/notls/novalidate-cert":":143/notls/novalidate-cert")."}";
  //$connect="{".$host.($protocol=="pop3"?":110":":143/notls/novalidate-cert")."}";

/*
echo "Protocol:".$protocol."<br>";
echo "conn:".$connect."<br>";
echo "login:".$login."<br>";
echo "pass:".$password."<br>";
*/

  if( $box=@imap_open($connect, $login, $password) ) {
    $action="folders";
    if($protocol=="pop3") {
      $action="list";
      $_SESSION["folder"]="INBOX";
    }
    $continue=true;

    @session_start();
    $_SESSION["connect"]=$connect;
    $_SESSION["host"]=$host;
    $_SESSION["login"]=$login;
    $_SESSION["password"]=$password;
    $sid=session_id();

  }
  else {
    $action="error";
    $continue=true;
    $error=imap_last_error();
  }

  break;
//----------------------------------------------------
case "auth":
  if(empty($login)) $login=$_REQUEST["login"];
  if(empty($password)) $password=$_REQUEST["password"];
  if(empty($host)) $host=$_REQUEST["host"];
  if(empty($host) || $host_fixed) $host=$host_default;
  if(empty($protocol)) $protocol=$_REQUEST["protocol"];
  if(empty($protocol) || $protocol_fixed) $protocol=$protocol_default;
?>
<card id="intro" title="<?=$domain?>" ontimer="#<?=$action?>">
<timer value="20"/>
<p align="center">Email Reader</p>
</card>
<card id="<?=$action?>">
<p>
login:<br/><input type="text" name="login" title="login" value="<?=$login?>"/><br/>
password:<br/><input type="password" name="password" title="password" value="<?=$password?>"/><br/>
<?
  if(!$host_fixed) {
?>
host:<br/><input type="text" name="host" title="host" value="<?=$host?>"/><br/>
<?
  }
  if(!$protocol_fixed) {
?>
<select name="protocol" title="protocol"<?=(!empty($protocol)?" value=\"$protocol\"":"")?>>
<option value="imap">imap</option>
<option value="pop3">pop3</option>
</select>
<?
  }
?>
<anchor>[login &gt;&gt;&gt;]
<go method="post" href="<?=$PHP_SELF?>">
<postfield name="action" value="login"/>
<postfield name="login" value="$(login)"/>
<postfield name="password" value="$(password)"/>
<postfield name="host" value="$(host)"/>
<postfield name="protocol" value="$(protocol)"/>
</go></anchor>
</p>
</card>
<?
  break;
//----------------------------------------------------
case "folders":
  $folders=imap_getmailboxes($box, $_SESSION["connect"], "*");
  if(!count($folders)) {

?>
<card id="<?=$action?>" ontimer="#folders">
<timer value="20"/>
<p>no folders</p>
</card>
<?
    break;
  }
?>
<card id="<?=$action?>">
<p>
<?
  $pagenum=intval($_REQUEST["pagenum"]);
  $pages=ceil(count($folders)/$folders_perpage);
  $skip=$pagenum*$folders_perpage;

  reset($folders);
  if(count($folders)>$skip) {
    for($i=0; $i<$skip; $i++) next($folders);
  }
  $i=0;
  while((list($k,$afolder)=each($folders)) && ($i++<$folders_perpage)) {
    $folder=$afolder->name;
    $folder=ereg_replace("\{.*\}","",$folder);
?>
<a href="<?=$PHP_SELF?>?m=wmail&amp;action=list&amp;folder=<?=xmlspecialchars($folder)?>&amp;sid=<?=$sid?>"><?=xmlspecialchars($folder)?></a><br/>
<?
  }
?>
</p>
<p>
<?
  if($pages>1) {
?>
page <?=$pagenum+1?> of <?=$pages?><br/>
<?
  }
  if($pagenum>0) {
?>
<a href="<?=$PHP_SELF?>?m=wmail&amp;action=<?=$action?>&amp;sid=<?=$sid?>&amp;pagenum=<?=$pagenum-1?>">previous page</a><br/>
<?
  }
  if($pagenum<$pages-1) {
?>
<a href="<?=$PHP_SELF?>?m=wmail&amp;action=<?=$action?>&amp;sid=<?=$sid?>&amp;pagenum=<?=$pagenum+1?>">next page</a><br/>
<?
  }
?>
<br/>
<a href="<?=$PHP_SELF?>?m=wmail&amp;action=write&amp;sid=<?=$sid?>">[new message &gt;&gt;&gt;]</a><br/>
</p>
</card>
<?
  break;
//----------------------------------------------------
case "list":
  $list=imap_sort($box,SORTDATE,1);
  if(!count($list)) {
?>
<card id="<?=$action?>" ontimer="#folders">
<timer value="20"/>
<p>empty folder</p>
</card>
<?
    $action="folders";
    $continue=true;
    break;
  }
?>
<card id="<?=$action?>">
<?
  $pagenum=intval($_REQUEST["pagenum"]);
  $pages=ceil(count($list)/$messages_perpage);
  $skip=$pagenum*$messages_perpage;

  reset($list);
  if(count($list)>$skip) {
    for($i=0; $i<$skip; $i++) next($list);
  }
  $i=0;
  while((list($k,$msg)=each($list)) && ($i++<$messages_perpage)) {
    $headers=headerinfo(imap_fetchheader($box,$msg));
?>
<p>
from:<?=normheader($headers["from"])?> <br/>
subj:<?=normheader($headers["subject"])?> <br/>
<a href="<?=$PHP_SELF?>?m=wmail&amp;action=view&amp;msg=<?=$msg?>&amp;sid=<?=$sid?>">[view &gt;&gt;&gt;]</a><br/>
<br/>
</p>
<?
  }
?>
<p>
<?
  if($pages>1) {
?>
page <?=$pagenum+1?> of <?=$pages?><br/>
<?
  }
  if($pagenum>0) {
?>
<a href="<?=$PHP_SELF?>?m=wmail&amp;action=<?=$action?>&amp;sid=<?=$sid?>&amp;pagenum=<?=$pagenum-1?>">previous page</a><br/>
<?
  }
  if($pagenum<$pages-1) {
?>
<a href="<?=$PHP_SELF?>?m=wmail&amp;action=<?=$action?>&amp;sid=<?=$sid?>&amp;pagenum=<?=$pagenum+1?>">next page</a><br/>
<?
  }


?>
<br/>
<a href="<?=$PHP_SELF?>?m=wmail&amp;action=list&amp;r=<?=rand(10000,99999)?>&amp;sid=<?=$sid?>">[refresh &gt;&gt;&gt;]</a><br/>
<a href="<?=$PHP_SELF?>?m=wmail&amp;action=folders&amp;sid=<?=$sid?>">[folders &gt;&gt;&gt;]</a><br/>
</p>
</card>
<?
  break;
//----------------------------------------------------
case "view":
  $msg=$_REQUEST["msg"];
  $header=imap_fetchheader($box,$msg);
  $body=imap_body($box,$msg);
?>
<card id="<?=$action?>">
<p><?=normmessage($header, $body)?></p>
<p><br/>
<a href="<?=$PHP_SELF?>?m=wmail&amp;action=reply&amp;msg=<?=$msg?>&amp;sid=<?=$sid?>">[reply &gt;&gt;&gt;]</a><br/>
<a href="<?=$PHP_SELF?>?m=wmail&amp;action=delete&amp;msg=<?=$msg?>&amp;sid=<?=$sid?>">[delete &gt;&gt;&gt;]</a><br/>
</p>
</card>
<?
  break;
//----------------------------------------------------
case "delete":
  imap_delete($box,$_REQUEST["msg"]);
  imap_expunge($box);
?>
<card id="<?=$action?>" ontimer="<?=$PHP_SELF?>?action=list&amp;sid=<?=$sid?>">
<timer value="5"/>
<p>deleted.</p>
</card>
<?
  break;
//----------------------------------------------------
case "post":
  $hdrs="";
  $hdrs.="MIME-Version: 1.0\n";
  $hdrs.="Content-Type: text/plain; charset=utf-8\n";
  $hdrs.="Content-Transfer-Encoding: 8bit\n";
  $hdrs.="From: ".$_REQUEST["from"];

  imap_mail($_REQUEST["to"],$_REQUEST["subj"],$_REQUEST["body"],$hdrs);
?>
<card id="<?=$action?>" ontimer="<?=$PHP_SELF?>?action=list&amp;sid=<?=$sid?>">
<timer value="20"/>
<p>posted.</p>
</card>
<?
  break;
//----------------------------------------------------
case "write":
  $from=$_SESSION["login"];
  if(strpos($from,"@")===false) $from.="@".$_SESSION["host"];
?>
<card id="<?=$action?>">
<p>
from:<br/><input type="text" name="from" title="from" value="<?=xmlspecialchars($from)?>"/><br/>
to:<br/><input type="text" name="to" title="to" value="<?=xmlspecialchars($to)?>"/><br/>
subj:<br/><input type="text" name="subj" title="subj" value="<?=xmlspecialchars($subj)?>"/><br/>
body:<br/><input type="text" name="body" title="body" value="<?=xmlspecialchars($body)?>"/><br/>

<anchor>[post it &gt;&gt;&gt;]
<go method="get" href="<?=$PHP_SELF?>?m=wmail&amp;">
<postfield name="action" value="post"/>
<postfield name="sid" value="<?=$sid?>"/>
<postfield name="from" value="$(from)"/>
<postfield name="to" value="$(to)"/>
<postfield name="subj" value="$(subj)"/>
<postfield name="body" value="$(body)"/>
</go></anchor>
</p>
</card>
<?
  break;
//----------------------------------------------------
case "reply":
  $headers=headerinfo(imap_fetchheader($box,$_REQUEST["msg"]));
  $to=$headers["reply-to"];
  if(empty($to)) $to=$headers["from"];

  $subj=$headers["subject"];
  if( ereg("Re\[([[:digit:]]+)\]: (.*)", $subj, $regs) ) {
    $subj="Re[".($regs[1]+1)."]: ".$regs[2];
  }
  else {
    if( ereg("Re: (.*)", $subj, $regs) ) {
      $subj="Re[2]: ".$regs[1];
    }
    else {
      $subj="Re: $subj";
    }
  }
  $action="write";
  $continue=true;
  break;

//----------------------------------------------------
case "error":
?>
<card id="Error" title="Error">
<p>
The connection to the mail server has failed. Check you email configuration.
</p>
</card>
<?
  break;
//----------------------------------------------------
default:
  $action="auth";
  $continue=true;
//----------------------------------------------------
  }// switch
}//while


if(isset($box) && function_exists("imap_close") ) {
  @imap_close($box);
}

?>
<?
//===========================================================================================================================
//===========================================================================================================================

function headerinfo($header)
{
  $lines=explode("\n",$header);
  $text=str_replace("\t"," ",$text);

  $name="x-xxx";
  reset($lines);
  while(list($i,$header)=each($lines)) {
    if($header[0]!=" ") {
      list($name,$value)=explode(": ", $header, 2);
      $name=strtolower($name);
      $headers[$name]=trim($value);
    }
    else {
      $headers[$name].=trim($header);
    }
  }

  $charset="us-ascii";
  $cta=explode(";", $headers["content-type"]);
  while(list($i,$ct)=each($cta)) {
    list($name,$value)=explode("=",$ct);
    $name=strtolower(trim($name));
    $value=strtolower(trim($value));
    if($name=="charset") $charset=$value;
  }

  reset($headers);
  while(list($name,$value)=each($headers)) {
    $elements=imap_mime_header_decode($value);
    while(list($e,$element)=each($elements)) {
      if($element->charset=="default") {
        $info[$name].=xiconv($charset, "utf-8", $element->text);
      }
      else {
        $info[$name].=xiconv($element->charset, "utf-8", $element->text);
      }
    }
  }
  $info["charset"]=$charset;
  return $info;
}

//----------------------------------------------------
function normheader($text)
{
  global $header_maxlen;

  $text=xtrim($text);
  //if(iconv_strlen($text, "utf-8")>$header_maxlen) $text=iconv_substr($text, 0, $header_maxlen, "utf-8");
  $text=xmlspecialchars($text);

  return $text;
}

//----------------------------------------------------
function normmessage($header, $text)
{
  global $message_maxlen;

  $info=headerinfo($header);

  switch($info["content-transfer-encoding"]) {
    case "base64":
      $text=imap_base64($text);
      break;
    case "quoted-printable":
      $text=imap_qprint($text);
      break;
  }

  if(strpos($info["content-type"], "text/html")!==false) $text=xstriptags($text);
  $text=xtrim($text);
  if(strlen($text)>$message_maxlen) $text=substr($text, 0, $message_maxlen);
  $text=xmlspecialchars($text);
  
  $text=wordwrap($text, 70, "\n");

  if(!empty($info["charset"]) && $info["charset"]!="utf-8") {
    $text=xiconv($info["charset"], "utf-8", $text);
  }

  return $text;
}

//----------------------------------------------------
function xstriptags($text)
{
  $text=eregi_replace("<style.*>.*</style>","",$text);
  $text=eregi_replace("<script.*>.*</script>","",$text);
  $text=ereg_replace("&[[:alpha:]]+;","",$text);
  $text=strip_tags($text);
  return $text;
}

//----------------------------------------------------
function xtrim($text)
{
  $text=str_replace("\r"," ",$text);
  $text=str_replace("\n"," ",$text);
  $text=str_replace("\t"," ",$text);
  $text=trim($text);
  do {
    $len=strlen($text);
    $text=str_replace("  "," ",$text);
  } while($len!=strlen($text));
  return $text;
}

//----------------------------------------------------
function xmlspecialchars($text)
{
  $text=str_replace( array("\"","'","&","<",">"), array("&quot;","&apos;","&amp;","&lt;","&gt;"), $text);
  return $text;
}

//----------------------------------------------------
function xclear($text)
{
  $x="";
  for($i=0; $i<strlen($text); $i++) {
    if(ord($text[$i])>0) $x.=$text[$i];
  }
  $text=$x;
  return $text;
}

//----------------------------------------------------
function xiconv($frpm, $to, $text)
{
  //$text=iconv($frpm, $to, $text);
  if( ($pos=strpos($text, chr(0)))!==false) $text=substr($text, 0, $pos );
  return $text;
}

?>