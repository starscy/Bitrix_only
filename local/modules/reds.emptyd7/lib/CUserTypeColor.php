<?php

use \Bitrix\Main,
    \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\UserField,
    Bitrix\Main\Page\Asset;

class CUserTypeColor
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


}

