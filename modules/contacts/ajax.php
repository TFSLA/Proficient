<?php
session_start();
require_once("./includes/xajax/xajax.inc.php");

class myXajaxResponse extends xajaxResponse  {
  function addCreateOptions($sSelectId, $options,$selected) {
    $this->addScript("document.getElementById('".$sSelectId."').length=0");
    if (sizeof($options) >0) {
       foreach ($options as $k => $v) {
       	 $sel=($selected==$k)?"true":"false";
         $this->addScript("addOption('".$sSelectId."','".$k."','".$v."',".$sel.");");
       }
     }
  }
}

$xajax = new xajax();

include("./modules/public/ajax.php");

$xajax->registerFunction("addContact");
$xajax->registerFunction("delContact");
$xajax->registerFunction("addItem");
$xajax->registerFunction("delItem");

function addContact($origen, $destino, $id_contact )
{
  global $AppUI;
  
  if($id_contact >0){
  	if(!isset($AppUI->related_contacts[$id_contact])){
	  $AppUI->related_contacts = arrayMerge( array( $id_contact=>$AppUI->contacts[$id_contact] ), $AppUI->related_contacts );
  	}
  }
  
  unset($AppUI->related_contacts['-1']);
  
  if(count($AppUI->related_contacts)>0)
  {
  asort($AppUI->related_contacts);
  }
  
  $objResponse = new myXajaxResponse();
  $objResponse->addCreateOptions($origen, $AppUI->contacts , '');
  $objResponse->addCreateOptions($destino, $AppUI->related_contacts , '');
  
  return $objResponse->getXML();
}


function delContact($origen, $destino, $id_contact  )
{
	global $AppUI;
	
	if ($id_contact > 0)
	{
	  unset($AppUI->contacts['0']);
	   $AppUI->contacts = arrayMerge( array( $id_contact=>$AppUI->related_contacts[$id_contact] ), $AppUI->contacts );
	   $AppUI->contacts = arrayMerge( array( '0'=>'' ), $AppUI->contacts );
	  
	  unset($AppUI->related_contacts[$id_contact]);
	  
	  $objResponse = new myXajaxResponse();
	  
	  $objResponse->addCreateOptions($origen, $AppUI->related_contacts,'');
	  $objResponse->addCreateOptions($destino, $AppUI->contacts , '');
	  
	  return $objResponse->getXML();
	}
}

function addItem($origen, $destino, $id_item)
{
	global $AppUI;
	
	if ($id_item > 0 && !empty($AppUI->items_av[$id_item]))
	{
		if(!isset($AppUI->related_items[$id_item]))
		{
		   $AppUI->related_items = arrayMerge( array( $id_item=>$AppUI->items_av[$id_item] ), $AppUI->related_items );
		}
	}
	
	unset($AppUI->items_av[$id_item]);
	
	if(count($AppUI->related_items)>0){
		asort($AppUI->related_items);
	}
	
	if(count($AppUI->items_av)>0){
		asort($AppUI->items_av);
	}
	
	$objResponse = new myXajaxResponse();
    $objResponse->addCreateOptions($origen, $AppUI->items_av,'');
    $objResponse->addCreateOptions($destino, $AppUI->related_items , '');
	
    return $objResponse->getXML();
}

function delItem($origen, $destino, $id_item)
{
	global $AppUI;
	
	if ($id_item > 0)
	{
		if(!isset($AppUI->items_av[$id_item]))
		{
		   $AppUI->items_av = arrayMerge( array( $id_item=>$AppUI->related_items[$id_item] ), $AppUI->items_av );
		}
	}
	
	unset($AppUI->related_items[$id_item]);
	
	if(count($AppUI->related_items)>0){
		asort($AppUI->related_items);
	}
	
	if(count($AppUI->items_av)>0){
		asort($AppUI->items_av);
	}
	
	$objResponse = new myXajaxResponse();
    $objResponse->addCreateOptions($destino, $AppUI->items_av,'');
    $objResponse->addCreateOptions($origen, $AppUI->related_items , '');
  
    return $objResponse->getXML();	
}

$xajax->processRequests();

$xajax->printJavascript('./includes/xajax/');

?>
<script type="text/javascript">
  function addOption(selectId, val, txt, sel) {
    var objOption = new Option(txt, val,false,sel);
     document.getElementById(selectId).options.add(objOption);
   }
</script>