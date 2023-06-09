<?
global $DB, $MESS, $DBType;
IncludeModuleLangFile(__FILE__);

CModule::AddAutoloadClasses(
	"embx.iblock",
	array(
		"EMBXPropertyAdvElementList" => "classes/prop_adv_list.php",
		"EMBXPropertyDeliveries" => "classes/prop_delivery.php",
		"EMBXPropertyStore" => "classes/prop_store.php",
		"EMBXPropertyLocations" => "classes/prop_locations.php",
	    "EMBXPropertyPS" => "classes/prop_paysystem.php",
	    "EMBXUserTypeStore" => "classes/usertype_store.php",
		"EMBXUserTypePS" => "classes/usertype_paysystem.php",
		"EMBXUserTypeDelivery" => "classes/usertype_delivery.php"
	)
);

?>