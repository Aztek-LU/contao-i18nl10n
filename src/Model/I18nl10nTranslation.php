<?php

declare(strict_types=1);

/**
 * i18nl10n Contao Module
 *
 * The i18nl10n module for Contao allows you to manage multilingual content
 * on the element level rather than with page trees.
 *
 * @copyright   Copyright (c) 2014-2020 Verstärker, Patric Eberle
 * @author      Patric Eberle <line-in@derverstaerker.ch>
 * @author      Claudio De Facci <claudio@exploreimpact.de>
 * @author      Web ex Machina <contact@webexmachina.fr>
 * @category    ContaoBundle
 * @package     exploreimpact/contao-i18nl10n
 * @link        https://github.com/exploreimpact/contao-i18nl10n
 */

namespace Verstaerker\I18nl10nBundle\Model;

/**
 * Reads and writes items.
 */
class I18nl10nTranslation extends \Contao\Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_i18nl10n_translation';

    /**
     * Find items, depends on the arguments.
     *
     * @param array
     * @param int
     * @param int
     * @param array
     *
     * @return Collection
     */
    public static function findItems($arrConfig = [], $intLimit = 0, $intOffset = 0, $arrOptions = [])
    {
        $t = static::$strTable;
        $arrColumns = static::formatColumns($arrConfig);

        if ($intLimit > 0) {
            $arrOptions['limit'] = $intLimit;
        }

        if ($intOffset > 0) {
            $arrOptions['offset'] = $intOffset;
        }

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = "$t.tstamp DESC";
        }

        if (empty($arrColumns)) {
            return static::findAll($arrOptions);
        }

        return static::findBy($arrColumns, null, $arrOptions);
    }

    /**
     * Count items, depends on the arguments.
     *
     * @param array
     * @param array
     *
     * @return int
     */
    public static function countItems($arrConfig = [], $arrOptions = [])
    {
        $t = static::$strTable;
        $arrColumns = static::formatColumns($arrConfig);

        if (empty($arrColumns)) {
            return static::countAll($arrOptions);
        }

        return static::countBy($arrColumns, null, $arrOptions);
    }

    /**
     * Format ItemModel columns.
     *
     * @param [Array] $arrConfig [Configuration to format]
     *
     * @return [Array] [The Model columns]
     */
    public static function formatColumns($arrConfig)
    {
        $t = static::$strTable;
        $arrColumns = [];

        if ($arrConfig['pid']) {
            $arrColumns[] = $t.'.pid = '.$arrConfig['pid'];
        }

        if ($arrConfig['ptable']) {
            $arrColumns[] = $t.'.ptable = "'.$arrConfig['ptable'].'"';
        }

        if ($arrConfig['field']) {
            $arrColumns[] = "$t.field = '".$arrConfig['field']."'";
        }

        if ($arrConfig['language']) {
            $arrColumns[] = "$t.language = '".$arrConfig['language']."'";
        }

        if (1 === $arrConfig['invisible']) {
            $arrColumns[] = "$t.invisible='1'";
        }

        if ($arrConfig['not']) {
            $arrColumns[] = $arrConfig['not'];
        }

        return $arrColumns;
    }
}
