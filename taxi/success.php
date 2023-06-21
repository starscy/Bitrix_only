<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("taxi");

use Bitrix\Main\Application;
use Bitrix\Main\Loader;

$request = Application::getInstance()->getContext()->getRequest();

$needTimes = $request->getQueryList();

//ищем автомобили, подходящие под параметры

Loader::includeModule('iblock');


$cars = [];
$arId = [];
$order = ['SORT' => 'ASC'];
$userLvl = 1;
$filter = ['IBLOCK_ID' => 9, '>=PROPERTY_COMFORT_VALUE' => $userLvl ];
$arSelect = [ 'ID', 'NAME', 'IBLOCK_ID', 'ACTIVE_FROM', 'PREVIEW_TEXT' ] ;
$rows = CIBlockElement::GetList($order, $filter, false, false, $arSelect);
while ($row = $rows->GetNext()) {
    $row['PROPERTIES'] = [];
    $cars[$row['ID']] =& $row;
    $arId[] = $row['ID'];
    unset($row);
}
unset($rows);

$elements = [];
$order = ['SORT' => 'ASC'];
$filter = ['IBLOCK_ID' => 11, 'PROPERTY_MODEL' => $arId,  ];
$arSelect = [ 'ID', 'NAME', 'IBLOCK_ID', 'ACTIVE_FROM', 'PREVIEW_TEXT'] ;
$rows = CIBlockElement::GetList($order, $filter, false, false, $arSelect);
while ($row = $rows->GetNext()) {
    $row['PROPERTIES'] = [];
    $elements[$row['ID']] =& $row;
    unset($row);
}

CIBlockElement::GetPropertyValuesArray($elements, $filter['IBLOCK_ID'], $filter);
unset($rows, $filter, $order);

$error = '';
$freeCars = [];
$dateStart = '16.06.2023 ' . $needTimes['start'] . ':00';
$dateEnd = '16.06.2023 ' . $needTimes['end'] . ':00';

foreach ($elements as $key => $el) {
    $id = $el['PROPERTIES']['MODEL']['VALUE'];
    if (strtotime($dateStart) >= strtotime($el['PROPERTIES']['START']['VALUE'])
        && strtotime($dateStart) <= strtotime($el['PROPERTIES']['END']['VALUE'])
    ) {
        $error = 'не подходящее начало дня ';
        unset($cars[$id]);
        continue;
    }
    if (strtotime($dateEnd) >= strtotime($el['PROPERTIES']['START']['VALUE'])
        && strtotime($dateEnd) <= strtotime($el['PROPERTIES']['END']['VALUE'])
    ) {
        $error = 'не подходящее конца дня ';
        unset($cars[$id]);
        continue;
    }
    if (strtotime($dateStart) <= strtotime($el['PROPERTIES']['START']['VALUE'])
        && strtotime($dateEnd) >= strtotime($el['PROPERTIES']['END']['VALUE'])){
        $error = 'не подходящее время начала и конца';
        unset($cars[$id]);
    }
}

$filter = ['IBLOCK_ID' => 9];
CIBlockElement::GetPropertyValuesArray($cars, $filter['IBLOCK_ID'], $filter);

//узнаем водителя


    ?>

    <pre>
<?php print_r($cars);?>
</pre>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>