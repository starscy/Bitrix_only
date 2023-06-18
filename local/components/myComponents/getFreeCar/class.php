<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Application;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

class FormGetCarApplication extends CBitrixComponent implements Controllerable, Errorable
{

    protected ErrorCollection $errorCollection;
    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code): \Bitrix\Main\Error
    {
        return $this->errorCollection->getErrorByCode($code);
    }

    public function onPrepareComponentParams($arParams)
    {
        $this->errorCollection = new ErrorCollection();

        if(!isset($arParams["CACHE_TIME"]))
            $arParams["CACHE_TIME"] = 36000000;

        $arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"]);

        return $arParams;
    }

    public function configureActions()
    {
        // сбрасываем фильтры по-умолчанию (Bitrix\Main\Engine\ActionFilter\Authentication() и Bitrix\Main\Engine\ActionFilter\HttpMethod() и Bitrix\Main\Engine\ActionFilter\Csrf()), предустановленные фильтры находятся в папке /bitrix/modules/main/lib/engine/actionfilter/
        return [
            'SubmitForm' => [
                'prefilters' => [],
                'postfilters' => []
            ],
        ];

    }

    public function executeComponent()
    {
        try {
            $this->checkModules();
            $this->checkRequest();
            $this->getResult();
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }

    protected function checkRequest():bool
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $needTimes = $request->getQueryList();

        if (isset($needTimes['start']) && isset($needTimes['end'])){
            if(strtotime($needTimes['start']) >= strtotime($needTimes['end'])) {
                $this->arResult['ERRORS'] = 'Ошибка в указании времени';
            }
        }

        return true;
    }
    protected function getWorkers ():array
    {
        $users = [];
        $arSelect = ['NAME', 'ID', 'PROPERTY_JOBTITLE'];
        $arFilter = ['IBLOCK_CODE' => 'workers', 'ACTIVE' => 'Y', '!PROPERTY_JOBTITLE_VALUE' => 'Водитель'];
        $rows = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while ($row = $rows->GetNext()) {
            $users[$row['ID']] = $row;
            unset($row);
        }
        unset($rows);

        $this->arResult['USERS'] = $users;

        return $users;
    }

    public function chooseComfortForCar ($jobTitle):int
    {
        $comfortLvl = 1;
        $arSelect = ['NAME', 'ID', 'PROPERTY_COMFORT'];
        $arFilter = ['IBLOCK_CODE' => 'jobTitle', 'ACTIVE' => 'Y', 'NAME' => $jobTitle];
        $rows = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while ($row = $rows->GetNext()) {
            $comfortLvl = $row['PROPERTY_COMFORT_VALUE'];
            unset($row);
        }

        return (int)$comfortLvl;
    }

    protected function getCars ():array
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $needTimes = $request->getQueryList();

        $cars = [];
        $userLvlComfort = $this->chooseComfortForCar($needTimes['usersList']);

        $order = ['SORT' => 'ASC'];
        $filter = ['IBLOCK_ID' => $this->arParams['IBLOCK_ID'], '<=PROPERTY_COMFORT_VALUE' => $userLvlComfort ];
        $arSelect = [ 'ID', 'NAME', 'IBLOCK_ID', 'ACTIVE_FROM', 'PREVIEW_TEXT' ];
        $rows = CIBlockElement::GetList($order, $filter, false, false, $arSelect);
        while ($row = $rows->GetNext()) {
            $row['PROPERTIES'] = [];
            $cars[$row['ID']] =& $row;
            unset($row);
        }
        unset($rows);

        $elements = [];
        $order = ['SORT' => 'ASC'];
        $filter = ['IBLOCK_CODE' => 'carOrderJournal'];
        $arSelect = ['ID', 'NAME', 'IBLOCK_ID', 'ACTIVE_FROM', 'PREVIEW_TEXT'];
        $rows = CIBlockElement::GetList($order, $filter, false, false, $arSelect);
        $iblock_id = '';
        while ($row = $rows->GetNext()) {
            $row['PROPERTIES'] = [];
            $elements[$row['ID']] =& $row;
            $iblock_id = $row['IBLOCK_ID'];
            unset($row);
        }

        CIBlockElement::GetPropertyValuesArray($elements, $iblock_id, $filter);
        unset($rows, $filter, $order);

        $date = date('d.m.Y');

        $dateStart = $date . ' ' . $needTimes['start'] . ':00';
        $dateEnd = $date . ' ' . $needTimes['end'] . ':00';

        foreach ($elements as $el) {
            $id = $el['PROPERTIES']['MODEL']['VALUE'];
            if (strtotime($dateStart) >= strtotime($el['PROPERTIES']['START']['VALUE'])
                && strtotime($dateStart) <= strtotime($el['PROPERTIES']['END']['VALUE'])
            ) {
                unset($cars[$id]);
                continue;
            }
            if (strtotime($dateEnd) >= strtotime($el['PROPERTIES']['START']['VALUE'])
                && strtotime($dateEnd) <= strtotime($el['PROPERTIES']['END']['VALUE'])
            ) {
                unset($cars[$id]);
                continue;
            }
            if (strtotime($dateStart) <= strtotime($el['PROPERTIES']['START']['VALUE'])
                && strtotime($dateEnd) >= strtotime($el['PROPERTIES']['END']['VALUE'])){
                unset($cars[$id]);
            }
        }

        $filter = ['IBLOCK_ID' => $this->arParams['IBLOCK_ID']];
        CIBlockElement::GetPropertyValuesArray($cars, $filter['IBLOCK_ID'], $filter);
        return $cars;
    }


    protected function getResult()
    {
        if ($this->startResultCache()) {

            $this->arResult['USERS'] = $this->getWorkers();
            $this->arResult['CARS'] = $this->getCars();

            $this->SetResultCacheKeys([]);
            $this->IncludeComponentTemplate();

        } else { // если выяснилось что кешировать данные не требуется, прерываем кеширование и выдаем сообщение «Страница не найдена»
            $this->AbortResultCache();
            \Bitrix\Iblock\Component\Tools::process404(
                Loc::getMessage('PAGE_NOT_FOUND'),
                true,
                true
            );
        }
    }

    protected function checkModules()
    {
        if (!Loader::includeModule('iblock'))
            throw new SystemException(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
    }

    public function onIncludeComponentLang()
    {
        Loc::loadMessages(__FILE__);
    }

}
