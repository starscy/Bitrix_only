<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

//Рекурсивный поиск имен

\Bitrix\Main\Loader::includeModule('iblock');

$rs_Section = CIBlockSection::GetList(
    ['DEPTH_LEVEL' => 'desc'],
    ['IBLOCK_ID' => 20],
    false,
    array('ID', 'NAME', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL', 'SORT')
);
$ar_SectionList = [];
$ar_DepthLavel = [];
while($ar_Section = $rs_Section->GetNext(true, false))
{
    $ar_SectionList[$ar_Section['ID']] = $ar_Section;
    $ar_DepthLavel[] = $ar_Section['DEPTH_LEVEL'];
}

$ar_DepthLavelResult = array_unique($ar_DepthLavel);
rsort($ar_DepthLavelResult);

$i_MaxDepthLevel = $ar_DepthLavelResult[0];

for( $i = $i_MaxDepthLevel; $i > 1; $i-- )
{
    foreach ( $ar_SectionList as $i_SectionID => $ar_Value )
    {
        if( $ar_Value['DEPTH_LEVEL'] == $i )
        {
            $ar_SectionList[$ar_Value['IBLOCK_SECTION_ID']]['SUB_SECTION'][] = $ar_Value;
            unset( $ar_SectionList[$i_SectionID] );
        }
    }
}

function __sectionSort($a, $b)
{
    if ($a['SORT'] == $b['SORT']) {
        return 0;
    }
    return ($a['SORT'] < $b['SORT']) ? -1 : 1;
}

usort($ar_SectionList, "__sectionSort");

function recursivRenderMenu($ar_Items)
{
    foreach ($ar_Items as $ar_Value)
    {
        if( isset($ar_Value['SUB_SECTION']))
        {
            echo '-> '. $ar_Value['NAME'];
            recursivRenderMenu($ar_Value['SUB_SECTION']);
        }
        else
        {
            echo  '-> '. $ar_Value['NAME'];
        }
    }
}

echo recursivRenderMenu($ar_SectionList);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>