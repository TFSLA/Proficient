<?
$contact_id = intval( dPgetParam( $_GET, 'contact_id', 0 ) );
if (!$canRead) {
	die();
}
$row = new CContact();
$row->load( $contact_id );


?>
	<template><do type="prev" label="back"><prev/></do></template>
	<card title="PSA - Contacts">
	<p> 
<?
echo $row->contact_order_by."<br/>";
//echo $row->contact_last_name." ".$row->contact_first_name."<br/>";
if($row->contact_phone!="")     echo $row->contact_phone."<br/>";
if($row->contact_phone2!="")    echo $row->contact_phone2."<br/>";
if($row->contact_mobile!="")    echo $row->contact_mobile."<br/>";
if($row->contact_business_phone!="")    echo $row->contact_business_phone."<br/>";
if($row->contact_business_phone2!="")   echo $row->contact_business_phone2."<br/>";
if($row->contact_email!="")     echo $row->contact_email."<br/>";
if($row->contact_email2!="")    echo $row->contact_email2."<br/>";
if($row->contact_address1!="")  echo $row->contact_address1."<br/>";
if($row->contact_address2!="")  echo $row->contact_address2."<br/>";
if($row->contact_website!="")   echo $row->contact_website."<br/>";
if($row->contact_birthday!="")  echo $row->contact_birthday."<br/>";
if($row->contact_company!="")   echo $row->contact_company."<br/>";
if($row->contact_fax!="")       echo $row->contact_fax."<br/>";
if($row->contact_notes!="")     echo $row->contact_notes."<br/>";
?>

	</p>
	</card>