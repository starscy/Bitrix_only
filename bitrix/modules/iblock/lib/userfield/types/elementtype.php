<?php

namespace Bitrix\Iblock\UserField\Types;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Iblock;
use CDBResult;
use CUserTypeManager;
use CIBlockElementEnum;

/**
 * Class ElementType
 * @package Bitrix\Iblock\UserField\Types
 */
class ElementType extends EnumType
{
	public const
		USER_TYPE_ID = 'iblock_element',
		RENDER_COMPONENT = 'bitrix:iblock.field.element';

	protected static ?bool $iblockIncluded = null;

	/**
	 * @return array
	 */
	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => Loc::getMessage('USER_TYPE_IBEL_DESCRIPTION'),
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_INT,
		];
	}

	/**
	 * @param array $userField
	 * @return array
	 */
	public static function prepareSettings(array $userField): array
	{
		$height = (int)($userField['SETTINGS']['LIST_HEIGHT'] ?? 1);
		$disp = ($userField['SETTINGS']['DISPLAY'] ?? '');

		if ($disp !== static::DISPLAY_CHECKBOX && $disp !== static::DISPLAY_LIST)
		{
			$disp = static::DISPLAY_LIST;
		}

		$iblockId = (int)($userField['SETTINGS']['IBLOCK_ID'] ?? 0);

		if($iblockId <= 0)
		{
			$iblockId = '';
		}

		$elementId = (int)($userField['SETTINGS']['DEFAULT_VALUE'] ?? 0);

		if($elementId <= 0)
		{
			$elementId = '';
		}

		$activeFilter = (($userField['SETTINGS']['ACTIVE_FILTER'] ?? '') === 'Y' ? 'Y' : 'N');

		return [
			'DISPLAY' => $disp,
			'LIST_HEIGHT' => (max($height, 1)),
			'IBLOCK_ID' => $iblockId,
			'DEFAULT_VALUE' => $elementId,
			'ACTIVE_FILTER' => $activeFilter,
		];
	}

	/**
	 * @param array $userField
	 * @return string|null
	 */
	public static function onSearchIndex(array $userField): ?string
	{
		$res = '';
		if (!isset($userField['VALUE']))
		{
			return $res;
		}

		if (is_array($userField['VALUE']))
		{
			$val = $userField['VALUE'];
		}
		else
		{
			$val = [$userField['VALUE']];
		}

		Type\Collection::normalizeArrayValuesByInt($val);

		if (!empty($val) && Loader::includeModule('iblock'))
		{
			$iterator = Iblock\ElementTable::getList([
				'select' => [
					'NAME',
				],
				'filter' => [
					'@ID' => $val,
				],
			]);
			while ($row = $iterator->fetch())
			{
				$res .= $row['NAME'] . "\r\n";
			}
			unset($row, $iterator);
		}
		unset($val);

		return $res;
	}

	/**
	 * @param array $userField
	 * @return bool|CDBResult
	 */
	public static function getList(array $userField)
	{
		if(self::$iblockIncluded === null)
		{
			self::$iblockIncluded = Loader::includeModule('iblock');
		}

		$elementEnumList = false;

		if(self::$iblockIncluded && (int)$userField['SETTINGS']['IBLOCK_ID'])
		{
			$elementEnumList = CIBlockElementEnum::getTreeList(
				(int)$userField['SETTINGS']['IBLOCK_ID'],
				$userField['SETTINGS']['ACTIVE_FILTER']
			);
		}
		return $elementEnumList;
	}

	/**
	 * @param array $userField
	 * @param array $additionalParameters
	 */
	public static function getEnumList(array &$userField, array $additionalParameters = []): void
	{
		if (self::$iblockIncluded === null)
		{
			self::$iblockIncluded = Loader::includeModule('iblock');
		}

		$userField['SETTINGS']['IBLOCK_ID'] ??= 0;
		$userField['SETTINGS']['SHOW_NO_VALUE'] ??= 'Y';
		$userField['SETTINGS']['DISPLAY'] ??= '';
		$userField['SETTINGS']['ACTIVE_FILTER'] ??= 'N';

		if (
			!self::$iblockIncluded
			|| (int)$userField['SETTINGS']['IBLOCK_ID']<= 0
		)
		{
			return;
		}

		$result = [];
		$showNoValue = (
			$userField['MANDATORY'] !== 'Y'
			|| $userField['SETTINGS']['SHOW_NO_VALUE'] !== 'N'
			|| (
				isset($additionalParameters['SHOW_NO_VALUE'])
				&& $additionalParameters['SHOW_NO_VALUE'] === true
			)
		);

		if (
			$showNoValue
			&& (
				$userField['SETTINGS']['DISPLAY'] !== 'CHECKBOX'
				|| $userField['MULTIPLE'] !== 'Y'
			)
		)
		{
			$result = [
				null => static::getEmptyCaption($userField)
			];
		}

		$elements = self::getElements(
			(int)$userField['SETTINGS']['IBLOCK_ID'],
			$userField['SETTINGS']['ACTIVE_FILTER']
		);

		if (!is_array($elements))
		{
			return;
		}

		$result = array_replace($result, $elements);

		$userField['USER_TYPE']['FIELDS'] = $result;
	}

	public static function getDefaultValue(array $userField, array $additionalParameters = [])
	{
		$value = ($userField['SETTINGS']['DEFAULT_VALUE'] ?? '');
		return ($userField['MULTIPLE'] === 'Y' ? [$value] : $value);
	}

	protected static function getElements($iblockId, $activeFilter = 'N')
	{
		if ($iblockId <= 0 || !Loader::includeModule('iblock'))
		{
			return false;
		}

		$cacheTtl = 86400;

		$iblock = Iblock\IblockTable::getRow([
			'select' => [
				'ID',
				'RIGHTS_MODE',
			],
			'filter' => [
				'=ID' => $iblockId
			],
			'cache' => [
				'ttl' => $cacheTtl,
			],
		]);
		if ($iblock === null)
		{
			return false;
		}

		if ($iblock['RIGHTS_MODE'] === Iblock\IblockTable::RIGHTS_SIMPLE)
		{
			$currentCache = \Bitrix\Main\Data\Cache::createInstance();

			$cacheId = md5('CIBlockElementEnum::getTreeList_' . $iblockId . '_' . $activeFilter);
			$cacheDir = '/iblock/elementtype/' . $iblockId;

			if ($currentCache->initCache($cacheTtl, $cacheId, $cacheDir))
			{
				$result = $currentCache->getVars();
			}
			else
			{
				$currentCache->startDataCache();

				$taggedCache = Application::getInstance()->getTaggedCache();
				$taggedCache->startTagCache($cacheDir);

				$filter = ['IBLOCK_ID' => $iblockId];
				if($activeFilter === 'Y')
				{
					$filter['=ACTIVE'] = 'Y';
				}

				$result = [];
				$elements = \Bitrix\Iblock\ElementTable::getList([
					'select' => [
						'ID',
						'NAME',
					],
					'filter' => \CIBlockElement::getPublicElementsOrmFilter($filter),
					'order' => [
						'NAME' => 'ASC',
						'ID' => 'ASC',
					],
				]);

				while($element = $elements->fetch())
				{
					$result[$element['ID']] = $element['NAME'];
				}
				unset($elements);

				$taggedCache->registerTag('iblock_id_' . $iblockId);
				$taggedCache->endTagCache();

				if (empty($result))
				{
					$result = false;
				}

				$currentCache->endDataCache($result);
			}
			unset($currentCache);
		}
		else
		{
			$filter = [
				'IBLOCK_ID' => $iblockId,
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => \CIBlockRights::PUBLIC_READ,
			];
			if ($activeFilter === 'Y')
			{
				$filter['ACTIVE'] = 'Y';
			}

			$result = [];
			$iterator = \CIBlockElement::GetList(
				[
					'NAME' => 'ASC',
					'ID' => 'ASC',
				],
				$filter,
				false,
				false,
				[
					'ID',
					'NAME',
				]
			);

			while ($element = $iterator->Fetch())
			{
				$result[$element['ID']] = $element['NAME'];
			}
			unset($iterator);
		}

		return $result;
	}
}
