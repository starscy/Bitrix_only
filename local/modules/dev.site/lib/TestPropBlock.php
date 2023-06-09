<?php
namespace Only\Site;

use \Bitrix\Main,
    \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\UserField,
    Bitrix\Main\Page\Asset;


class TestPropBlock
{
    const USER_TYPE_ID = 'color';

    /**
     * Обработчик события OnUserTypeBuildList.
     *
     * <p>Эта функция регистрируется в качестве обработчика события OnUserTypeBuildList.
     * Возвращает массив описывающий тип пользовательских свойств.</p>
     * <p>Элементы массива:</p>
     * <ul>
     * <li>USER_TYPE_ID - уникальный идентификатор
     * <li>CLASS_NAME - имя класса методы которого формируют поведение типа
     * <li>DESCRIPTION - описание для показа в интерфейсе (выпадающий список и т.п.)
     * <li>BASE_TYPE - базовый тип на котором будут основаны операции фильтра (int, double, string, date, datetime)
     * </ul>
     * @return array
     * @static
     */
    function GetUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => static::USER_TYPE_ID,
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => 'Выбор цвета',
            "BASE_TYPE" => \CUserTypeManager::BASE_TYPE_STRING,
        );
    }

    /**
     * Эта функция вызывается при выводе формы редактирования значения свойства.
     *
     * <p>Возвращает html для встраивания в ячейку таблицы.
     * в форму редактирования сущности (на вкладке "Доп. свойства")</p>
     * <p>Элементы $arHtmlControl приведены к html безопасному виду.</p>
     * @param array $arUserField Массив описывающий поле.
     * @param array $arHtmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
     * @return string HTML для вывода.
     * @static
     */
    function GetEditFormHTML($arUserField, $arHtmlControl)
    {

        if(!$arUserField['VALUE']){
            $arHtmlControl['VALUE'] = htmlspecialcharsbx($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
        } else {
            $arHtmlControl['VALUE'] = $arUserField['VALUE'];
        }

        //CSS файлвы не захотели подключаться через Asset::getInstance()->addCss() поэтому подтягиваем
        // их через HTML загружаемый на странице редактирования свойства
        $return = '	<link rel="stylesheet" href="' . APP_MEDIA_FOLDER .'css/colorpicker.css?v='. md5(date("h:i:s")) .'" type="text/css" />
        <link rel="stylesheet" media="screen" type="text/css" href="' . APP_MEDIA_FOLDER .'css/layout.css?v='. md5(date("h:i:s")) .'" />';

        \CJSCore::Init(['jquery2']);

        Asset::getInstance()->addJs(APP_MEDIA_FOLDER . 'js/colorpicker.js');
        Asset::getInstance()->addJs(APP_MEDIA_FOLDER . 'js/CUserTypeColor.js');

        $return = $return . '<div id="colorpickerHolder"></div><input id="colorpickerHolderInput" type="text" name="' . $arHtmlControl['NAME'] . '" value="'. $arHtmlControl['VALUE'] .'">';

        return $return;
    }
}
