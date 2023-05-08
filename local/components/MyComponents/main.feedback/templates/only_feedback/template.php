<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
?>
<div class="contact-form">
    <div class="contact-form__head">
        <div class="contact-form__head-title">Связаться</div>
        <div class="contact-form__head-text">Наши сотрудники помогут выполнить подбор услуги и&nbsp;расчет цены с&nbsp;учетом
            ваших требований
        </div>
    </div>
    <?if(!empty($arResult["ERROR_MESSAGE"]))
    {
        foreach($arResult["ERROR_MESSAGE"] as $v)
            ShowError($v);
    }
    if($arResult["OK_MESSAGE"] <> '')
    {
        ?><div class="mf-ok-text"><?=$arResult["OK_MESSAGE"]?></div><?
    }
    ?>

    <form class="contact-form__form" action="<?=POST_FORM_ACTION_URI?>" method="POST">
    <?=bitrix_sessid_post()?>
        <div class="contact-form__form-inputs">

            <div class="input contact-form__input"><label class="input__label" for="user_name">
                    <div class="input__label-text">
                        <?=GetMessage("MFT_NAME")?><?if(empty($arParams["REQUIRED_FIELDS"]) || in_array("NAME", $arParams["REQUIRED_FIELDS"])):?><span class="mf-req">*</span><?endif?>
                    </div>
                    <input class="input__input" type="text" id="medicine_name" name="user_name"  value="<?=$arResult["AUTHOR_NAME"]?>"
                           required="">
                    <div class="input__notification">Поле должно содержать не менее 3-х символов</div>
                </label>
            </div>
            <div class="input contact-form__input"><label class="input__label" for="medicine_company">
                    <div class="input__label-text">
                        <?=GetMessage("MFT_COMPANY")?><?if(empty($arParams["REQUIRED_FIELDS"]) || in_array("COMPANY", $arParams["REQUIRED_FIELDS"])):?><span class="mf-req">*</span><?endif?>
                    </div>
                    <input class="input__input" type="text" id="medicine_company" name="medicine_company" value="<?=$arResult["COMPANY"]?>"
                           required="">
                    <div class="input__notification">Поле должно содержать не менее 3-х символов</div>
                </label>
            </div>
            <div class="input contact-form__input"><label class="input__label" for="user_email">
                    <div class="input__label-text">
                        <?=GetMessage("MFT_EMAIL")?><?if(empty($arParams["REQUIRED_FIELDS"]) || in_array("EMAIL", $arParams["REQUIRED_FIELDS"])):?><span class="mf-req">*</span><?endif?>
                    </div>
                    <input class="input__input" type="email" id="medicine_email" name="user_email" value="<?=$arResult["AUTHOR_EMAIL"]?>"
                           required="">
                    <div class="input__notification">Неверный формат почты</div>
                </label>
            </div>
            <div class="input contact-form__input">
                <label class="input__label" for="medicine_phone">
                    <div class="input__label-text">
                        <?=GetMessage("MFT_PHONE")?><?if(empty($arParams["REQUIRED_FIELDS"]) || in_array("PHONE", $arParams["REQUIRED_FIELDS"])):?><span class="mf-req">*</span><?endif?>
                    </div>
                    <input class="input__input" type="tel" id="medicine_phone"
                           data-inputmask="'mask': '+79999999999', 'clearIncomplete': 'true'" maxlength="12"
                           x-autocompletetype="phone-full" name="medicine_phone"  value="<?=$arResult["PHONE"]?>" required=""></label>
            </div>
        </div>
        <div class="contact-form__form-message">
            <div class="input"><label class="input__label" for="medicine_message">
                    <div class="input__label-text">
                        <?=GetMessage("MFT_MESSAGE")?><?if(empty($arParams["REQUIRED_FIELDS"]) || in_array("MESSAGE", $arParams["REQUIRED_FIELDS"])):?><span class="mf-req">*</span><?endif?>
                    </div>
                    <textarea class="input__input" type="text" id="medicine_message" name="medicine_message"
                              value="<?=$arResult["MESSAGE"]?>"></textarea>
                    <div class="input__notification"></div>
                </label>
            </div>
        </div>
        <div class="contact-form__bottom">
            <div class="contact-form__bottom-policy">Нажимая &laquo;Отправить&raquo;, Вы&nbsp;подтверждаете, что
                ознакомлены, полностью согласны и&nbsp;принимаете условия &laquo;Согласия на&nbsp;обработку персональных
                данных&raquo;.
            </div>
            <input type="hidden" name="PARAMS_HASH" value="<?=$arResult["PARAMS_HASH"]?>">
            <input type="submit" class="form-button contact-form__bottom-button" name="submit" value="<?=GetMessage("MFT_SUBMIT")?>" data-success="Отправлено" data-error="Ошибка отправки" >
        </div>
    </form>
</div>

