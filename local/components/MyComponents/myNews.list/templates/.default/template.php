<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?php if (!empty($arResult['ITEMS'])) :?>
    <?php foreach ($arResult['ITEMS'] as $arIblock) :?>
        <?php foreach ($arIblock as $arItem) :?>
            <p><?=$arItem['NAME']?></p>
            <?foreach($arItem["PROPERTIES"] as $pid=>$arProperty):?>
                <small>
                    <?=$arProperty["NAME"]?>:&nbsp;
                    <?if(is_array($arProperty["VALUE"])):?>
                        <?=implode("&nbsp;/&nbsp;", $arProperty["VALUE"]);?>
                    <?else:?>
                        <?=$arProperty["VALUE"];?>
                    <?endif?>
                </small><br />
            <?endforeach;?>
        <?php endforeach;?>
    <?php endforeach;?>
<?php endif;?>

