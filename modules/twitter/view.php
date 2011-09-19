<?
	include_once('modules/twitter/ajax.php');
	include_once('modules/twitter/functions.php');
	
	$faces = 8;
	
	//Obtengo el mensaje personal
	if(!$AppUI->getState('twitter_save'))
	{
		$twitter_data = getTwitterUserData($AppUI->user_id);

		if(count($twitter_data) > 0)
		{
			$AppUI->setState('twitter_message', $twitter_data[0]['twitter_message']);
			$AppUI->setState('twitter_status', $twitter_data[0]['twitter_status']);
			$AppUI->setState('twitter_save', true);
		}
	}
	
	//Obtengo los usuarios que yo superviso
	$users_owners = CProject::getUsersMyOwnerProjects('', 0, 0, 0, '', false, $AppUI->user_company);

	//Obtengo usuarios activos
	$users_active = getTwitterActiveUsers();
	
	$users_twitter = arrayMerge( $users_owners, $users_active );
	
	$users_picture = null;
	
	//Recorro los usuarios supervisados y armo vector para las fotos
	foreach ( $users_twitter as $user_key => $user_name )
		$users_picture[$user_key] = $user_key;

	//Obtengo las fotos de los usuarios
	if(count($users_picture) > 0)
		$users_picture = CUser::getUsersPicture($users_picture);
?>

<script src="./includes/javascript/scroller/dw_event.js" type="text/javascript"></script>
<script src="./includes/javascript/scroller/dw_scroll.js" type="text/javascript"></script>
<script src="./includes/javascript/scroller/scroll_controls.js" type="text/javascript"></script>

<script type="text/javascript">

	var twitter_x;
	var twitter_y;
	var objImageTemp;
	var altImage;

	function init_dw_Scroll()
	{
	    var wndo = new dw_scrollObj('scrollerTwitterParent', 'scrollerTwitter', '');
	    wndo.setUpScrollControls('scrollLinks');
	}

	if ( dw_scrollObj.isSupported() )
	{
    	dw_Event.add( window, 'load', init_dw_Scroll);
	}
	
	function activeMyAssigment(user_id, assigment_id, assigment_type){
		xajax_changeTwitterStatus(user_id, assigment_id, assigment_type);
	}

	function save_message_twitter()
	{
		var objMessage = document.getElementById('twitter_message');
		xajax_changeTwitterMessage(<?=$AppUI->user_id?>, objMessage.value);
	}
	
	function save_status_twitter(objCheck)
	{
		xajax_changeTwitterStatus(<?=$AppUI->user_id?>, objCheck.checked);
	}

	function ismaxlength(obj)
	{
		var mlength=obj.getAttribute? parseInt(obj.getAttribute("maxlength")) : "";
		if (obj.getAttribute && obj.value.length>mlength)
			obj.value=obj.value.substring(0,mlength)
	}
	
	function setTwitterXY()
	{
	    if (isIE)
        	twitter_x = posX();
    	else
    	    twitter_x = netX;
	}
		
	function loadDialogTwitter(objImage, user_id, fullname, picture)
	{
		altImage = objImage.alt;
		objImageTemp = objImage;
		setTwitterXY();

		if((document.body.clientWidth-twitter_x) <= 200)
		{
        	twitter_x = (twitter_x - 150);
        	arrow_x = 230;
        }
        else
        	arrow_x = 75;
        	
		xajax_getTwitterPopUpData(user_id, fullname, picture, arrow_x);
		objImage.alt='';
	}
	
	function showDialogTwitter()
	{
		tooltipLinkXY(document.getElementById('hidden_htmlToolTip').value, '', 'asd', twitter_x, 98);
		document.getElementById('hidden_htmlToolTip').value = '';
	}
	
	function closeDialogTwitter()
	{
		if(objImageTemp)
		{
			tooltipClose();
			objImageTemp.alt = altImage;
		}
	}
	
	function showHideTwitter()
	{			
	  	var imgExpand = new Image;
		var imgCollapse = new Image;
		imgExpand.src = '../../images/icons/expand_alter.gif';
		imgExpand.alt = '<?=$AppUI->_('Show Twitter')?>';
		imgCollapse.src = '../../images/icons/collapse_alter.gif';
		imgCollapse.alt = '<?=$AppUI->_('Hide')?>';
		
		if( document.getElementById('divtwitter').style.display=='none' )
		{
		   document.getElementById('divtwitter').style.display = '';
		   document.getElementById('imgCollapse').src = imgCollapse.src;
		   xajax_changeTwitterShowHide(1);
		}
 		else
 		{
			document.getElementById('divtwitter').style.display = 'none';
			document.getElementById('imgCollapse').src = imgExpand.src;
			xajax_changeTwitterShowHide(0);
		}
	}

</script>

<style type="text/css">
	textarea.noborde
	{
		border-style: dashed;
		border-width: 1;
		border-color: #FF9900;
		padding: 0;
		width: 98%;
		height: 24px;
		overflow: auto;
	}
</style>

<input type="hidden" id="hidden_htmlToolTip" name="hidden_htmlToolTip" />

<div id="divtwitter" style="display:<?=($showTwitter ? '' : 'none');?>">
	<table width="100%">
		<tr>
			<td>
				<div id="scrollLinks">				
					<table width="100%" border="0">
						<tr nowrap>
							<td>
								<table width="100%" border="0" cellspacing="0" cellpadding="0">
									<tr>
										<td colspan="2">
											<textarea class="noborde" maxlength="150" id="twitter_message" name="twitter_message" onkeyup="return ismaxlength(this);" style="font-size:10px"><?=$AppUI->getState('twitter_message');?></textarea>
										</td>
									</tr>
									<tr>
										<td align="left"><input type="checkbox" onclick="javascript:save_status_twitter(this)" <?=($AppUI->getState('twitter_status') == '1' ? 'checked' : '')?>><font class="small-caption"><?=$AppUI->_('Publish my state');?></font></td>
										<td align="right"><a href="javascript:save_message_twitter();"><?=$AppUI->_('save');?></a>&nbsp;&nbsp;</td>
									</tr>
								</table>
							</td>
							<?if (count($users_picture) > $faces){?>
								<td align="left" width="13" valign="middle" nowrap>
									<a class="mouseover_left"><img src="../../images/arrow_scroll_left.jpg" border="0" /></a>&nbsp;
								</td>
							<?}?>
							<td align="right" width="1%" nowrap>
								<div id="scrollerTwitterParent" style="width:432px; height:50px; position:relative; overflow:hidden;">
									<div id="scrollerTwitter">		
										<?									
										$countUsers = 0;
										for($i=0;$i<count($users_picture);$i++)
										{
											$user_id = $users_picture[$i]['user_id'];
											$user_pic = $users_picture[$i]['user_pic'];

											if($user_id != $AppUI->user_id)
											{
												$countUsers++;
												
												$fullnametwitter = $users_twitter[$user_id];
												
												if(strrpos($user_pic,'.'))
													$picture_file = $AppUI->getConfig('hhrr_uploads_dir')."/".$user_id."/".rawurlencode($user_pic);
												else
													$picture_file = "../../images/twitter.bmp";

												echo("<img width=\"50\" height=\"50\" src=\"".$picture_file."\" style=\"cursor:pointer;\" alt=\"".$fullnametwitter."\" onclick=\"loadDialogTwitter(this, ".$user_id.", '".$fullnametwitter."', '".$picture_file."');\" onmouseout=\"closeDialogTwitter();\" />&nbsp");
											}
										}

										for($i=$countUsers;$i<=$faces;$i++)
										{
											echo("<img width=\"50\" height=\"50\" src=\".././images/twitterdisabled.jpg\" />&nbsp");
										}
										?>
									</div>
								</div>					
							</td>
							<?if (count($users_picture) > $faces){?>
								<td align="left" width="13" valign="middle" nowrap>
									<a class="mouseover_right"><img src="../../images/arrow_scroll_right.jpg" border="0" /></a>
								</td>
							<?}?>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</table>
</div>