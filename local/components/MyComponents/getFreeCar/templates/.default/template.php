<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?php
use Bitrix\Main\Application;

$request = Application::getInstance()->getContext()->getRequest();

$page = $APPLICATION->GetCurPage();
$timeStart = date('H:i');
$timeEnd = date('H:i', strtotime($timeStart)+60*60);

?>

        <?php if (!empty($arResult['ERRORS'])) {
            echo $arResult['ERRORS'];
        } ?>


<form method="get" action="<?=$page?>">
    <h1 class="h3 mb-3 fw-normal">Поиск автомобиля</h1>

    <label for="usersList">Выберите сотрудника</label>
    <select name="usersList" id="usersList" class="form-control">
    <?php foreach ($arResult['USERS'] as $arItem):?>
        <option value="<?=$arItem['PROPERTY_JOBTITLE_VALUE']?>"><?=$arItem['NAME']?></option>
    <?php endforeach;?>
    </select>
    <div class="form-group">
        <label for="start">Начало поездки</label>
        <input type="time"  min="06:00" max="23:00" value="<?=$timeStart?>" name="start" required id="start" class="form-control">
    </div>
    <div class="form-group">
        <label for="end">Конец поездки</label>
        <input type="time" name="end" min="06:00" max="23:00" value="<?=$timeEnd?>" id="end" class="form-control" >
    </div>
    <br>
    <button class="btn btn-primary w-100 py-2" type="submit">Найти</button>
</form>

<h2>Свободные автомобили</h2>
<ul>
<?php foreach ($arResult['CARS'] as $arItem):?>
    <li><?=$arItem['NAME']?></li>
<?php endforeach;?>
</ul>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

