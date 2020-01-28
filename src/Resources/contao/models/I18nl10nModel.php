<?php

namespace Verstaerker\I18nl10nBundle\Models;

/*
 */
abstract class I18nl10nModel extends \Model
{
	/**
	 * Find records and return the model or model collection
	 *
	 * Supported options:
	 *
	 * * column: the field name
	 * * value:  the field value
	 * * limit:  the maximum number of rows
	 * * offset: the number of rows to skip
	 * * order:  the sorting order
	 * * eager:  load all related records eagerly
	 *
	 * @param array $arrOptions The options array
	 *
	 * @return Model|Model[]|Model\Collection|null A model, model collection or null if the result is empty
	 */
	protected static function find(array $arrOptions)
	{
		if (static::$strTable == '')
		{
			return null;
		}

		// Try to load from the registry
		if ($arrOptions['return'] == 'Model')
		{
			$arrColumn = (array) $arrOptions['column'];

			if (\count($arrColumn) == 1)
			{
				// Support table prefixes
				$arrColumn[0] = preg_replace('/^' . preg_quote(static::getTable(), '/') . '\./', '', $arrColumn[0]);

				if ($arrColumn[0] == static::$strPk || \in_array($arrColumn[0], static::getUniqueFields()))
				{
					$varKey = \is_array($arrOptions['value']) ? $arrOptions['value'][0] : $arrOptions['value'];
					$objModel = Registry::getInstance()->fetch(static::$strTable, $varKey, $arrColumn[0]);

					if ($objModel !== null)
					{
						return $objModel;
					}
				}
			}
		}

		// Add i18nl10n language settings if table 
		if (\Config::has('i18nl10n_tables') && !empty(deserialize(\Config::get('i18nl10n_tables')))) {
            $arrI18nl10nTables = deserialize(\Config::get('i18nl10n_tables'));
            if (!empty($arrI18nl10nTables) && in_array(static::$strTable, $arrI18nl10nTables)) {
				$arrOptions['column'][0] .= ' AND '.static::$strTable.'.i18nl10n_lang="'.$GLOBALS['TL_LANGUAGE'].'"';
			}
		}

		$arrOptions['table'] = static::$strTable;
		$strQuery = static::buildFindQuery($arrOptions);

		$objStatement = \Database::getInstance()->prepare($strQuery);

		// Defaults for limit and offset
		if (!isset($arrOptions['limit']))
		{
			$arrOptions['limit'] = 0;
		}
		if (!isset($arrOptions['offset']))
		{
			$arrOptions['offset'] = 0;
		}

		// Limit
		if ($arrOptions['limit'] > 0 || $arrOptions['offset'] > 0)
		{
			$objStatement->limit($arrOptions['limit'], $arrOptions['offset']);
		}

		$objStatement = static::preFind($objStatement);
		$objResult = $objStatement->execute($arrOptions['value']);

		if ($objResult->numRows < 1)
		{
			return $arrOptions['return'] == 'Array' ? array() : null;
		}

		$objResult = static::postFind($objResult);

		// Try to load from the registry
		if ($arrOptions['return'] == 'Model')
		{
			$objModel = Registry::getInstance()->fetch(static::$strTable, $objResult->{static::$strPk});

			if ($objModel !== null)
			{
				return $objModel->mergeRow($objResult->row());
			}

			return static::createModelFromDbResult($objResult);
		}
		elseif ($arrOptions['return'] == 'Array')
		{
			return static::createCollectionFromDbResult($objResult, static::$strTable)->getModels();
		}
		else
		{
			return static::createCollectionFromDbResult($objResult, static::$strTable);
		}
	}
}

class_alias(I18nl10nModel::class, 'I18nl10nModel');