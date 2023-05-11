<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if ($arResult["isFormErrors"] == "Y"):?><?=$arResult["FORM_ERRORS_TEXT"];?><?endif;?>
<?=$arResult["FORM_NOTE"]?>
<?if ($arResult["isFormNote"] != "Y")
{
?>
    <?=str_replace('<form ', '<form class="contact-form__form"', $arResult["FORM_HEADER"])?>
    <div class="contact-form__form-inputs">
        <? foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion)
        {
            if ($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] == 'hidden')
            {
                echo $arQuestion["HTML_CODE"];
            }
            else
            {
                if ($arQuestion['CAPTION'] == 'Сообщение') {
                    $messageArr[] = $arQuestion;
                    continue;
                }
                ?>
                <div class="input contact-form__input"><label class="input__label" >
                        <div class="input__label-text">
                            <?=$arQuestion["CAPTION"]?><?if ($arQuestion["REQUIRED"] == "Y"):?><?=$arResult["REQUIRED_SIGN"];?><?endif;?>
                        </div>
                        <?=str_replace('<input ', ' <input class="input__input"', $arQuestion["HTML_CODE"])?>

                        <div class="input__notification">Поле должно содержать не менее 3-х символов</div>
                    </label>
                </div>

                <?
            }
        } //endwhile
        ?>
    </div>

    <div class="contact-form__form-message">
        <div class="input">
            <label class="input__label" for="medicine_message">
                <div class="input__label-text">
                    <?=$arResult['QUESTIONS']['TEXTAREA']["CAPTION"]?>
                    <?if ($arResult['QUESTIONS']['TEXTAREA'] == "Y"):?><?=$arResult["REQUIRED_SIGN"];?><?endif;?>
                </div>
                <?=str_replace('<textarea', '<textarea class="input__input" name="medicine_message"', $arResult['QUESTIONS']['TEXTAREA']["HTML_CODE"])?>
                <div class="input__notification"></div>
            </label>
        </div>
    </div>

    <div class="contact-form__bottom">
        <div class="contact-form__bottom-policy">Нажимая &laquo;Отправить&raquo;, Вы&nbsp;подтверждаете, что
            ознакомлены, полностью согласны и&nbsp;принимаете условия &laquo;Согласия на&nbsp;обработку персональных
            данных&raquo;.
        </div>
        <input <?=(intval($arResult["F_RIGHT"]) < 10 ? "disabled=\"disabled\"" : "");?> type="submit" name="web_form_submit" value="Оставить заявку"  class="form-button contact-form__bottom-button" data-success="Отправлено"  data-error="Ошибка отправки"/>

    </div>


    <?=$arResult["FORM_FOOTER"]?>
    <?
} //endif (isFormNote)?>




