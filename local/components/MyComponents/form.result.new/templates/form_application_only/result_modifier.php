<?php

foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion) {
    if ($arQuestion['CAPTION'] == 'Сообщение') {
        $arResult["QUESTIONS"]['TEXTAREA'] = $arQuestion;
    }
}
