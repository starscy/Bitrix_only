<?php
/*
 * Файл local/modules/scrollup/lib/Main.php
 */
// Реализация свойство «Расписание врача»

namespace ScrollUp\general;

use Bitrix\Main\Config\Option;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Page\Asset;
use Bitrix\Seo\Engine\Bitrix;
use stdClass;



class MainWorked {

    /**
     * Метод возвращает массив описания собственного типа свойств
     * @return array
     */
    public static function GetUserTypeDescription()
    {
        return array(
            'USER_TYPE_ID' => 'user_timesheet', //Уникальный идентификатор типа свойств
            'USER_TYPE' => 'TIMESHEET',
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => 'Расписание специалиста',
            'PROPERTY_TYPE' => \Bitrix\Iblock\PropertyTable::TYPE_STRING,
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
            'ConvertFromDB' => [__CLASS__, 'ConvertFromDB'],
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
        );
    }

    /**
     * Конвертация данных перед сохранением в БД
     * @param $arProperty
     * @param $value
     * @return mixed
     */
    public static function ConvertToDB($arProperty, $value)
    {
            try {
                $value['VALUE'] = base64_encode(serialize($value['VALUE']));
            } catch(\Bitrix\Main\ObjectException $exception) {
                echo $exception->getMessage();
            }
        return $value;
    }

    /**
     * Конвертируем данные при извлечении из БД
     * @param $arProperty
     * @param $value
     * @param string $format
     * @return mixed
     */
    public static function ConvertFromDB($arProperty, $value, $format = '')
    {
        if ($value['VALUE'] != '')
        {
            try {
                $value['VALUE'] = base64_decode($value['VALUE']);
            } catch(\Bitrix\Main\ObjectException $exception) {
                echo $exception->getMessage();
            }
        }

        return $value;
    }

    /**
     * Представление формы редактирования значения
     * @param $arUserField
     * @param $arHtmlControl
     */
    public static function GetPropertyFieldHtml($arProperty, $value, $arHtmlControl)
    {

        $weekDays = [
            'mon' => 'Понедельник',
            'tue' => 'Вторник',
            'wed' => 'Среда',
            'thu' => 'Четверг',
            'fri' => 'Пятница',
            'sat' => 'Суббота',
            'sun' => 'Воскресенье',
        ];

        $itemId = 'row_' . substr(md5($arHtmlControl['VALUE']), 0, 10); //ID для js
        $fieldName =  htmlspecialcharsbx($arHtmlControl['VALUE']);
        //htmlspecialcharsback нужен для того, чтобы избавиться от многобайтовых символов из-за которых не работает unserialize()
        $arValue = unserialize(htmlspecialcharsback($value['VALUE']), [stdClass::class]);

        $select = '<select class="week_day" name="'. $fieldName .'[WEEK_DAY]">';
        foreach ($weekDays as $key => $day){
            if($arValue['WEEK_DAY'] == $key){
                $select .= '<option value="'. $key .'" selected="selected">'. $day .'</option>';
            } else {
                $select .= '<option value="'. $key .'">'. $day .'</option>';
            }

        }
        $select .= '</select>';

        $html = '<div class="property_row" id="'. $itemId .'">';

        $html .= '<div class="reception_time">';
        $html .= $select;
        $timeFrom = ($arValue['TIME_FROM']) ? $arValue['TIME_FROM'] : '';
        $timeTo = ($arValue['TIME_TO']) ? $arValue['TIME_TO'] : '';

        $html .='&nbsp;время приёма: с&nbsp;<input type="time" name="'. $fieldName .'[TIME_FROM]" value="'. $timeFrom . '">';
        $html .='&nbsp;по&nbsp;<input type="time" name="'. $fieldName .'[TIME_TO]" value="'. $timeTo .'">';
        if($timeFrom!='' && $timeTo!=''){
            $html .= '&nbsp;&nbsp;<input type="button" style="height: auto;" value="x" title="Удалить" onclick="document.getElementById(\''. $itemId .'\').parentNode.parentNode.remove()" />';
        }
        $html .= '</div>';

        $html .= '</div><br/>';


        return $html;
    }

    public static function test(){
        return 1;
    }

}