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

use Contao\Database\Result;
use Contao\PageModel;
use Contao\System;

class I18nl10nPageModel extends PageModel
{
    /**
     * Modify the database result before the model is created.
     *
     * @param Result $objResult The database result object
     *
     * @return Result The database result object
     */
    protected static function postFind(Result $objResult)
    {
        $objPageTranslations = I18nl10nTranslation::findItems(['ptable' => static::$strTable, 'pid' => $objResult->id]);
        dump($objResult);

        if (!$objPageTranslations || 0 === $objPageTranslations->count()) {
            return $objResult;
        }

        while ($objPageTranslations->next()) {
            $objResult->{$objPageTranslations->field} = $objPageTranslations->valueText;
        }

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['i18nl10nModelPostFind'])
            && \is_array($GLOBALS['TL_HOOKS']['i18nl10nModelPostFind'])
        ) {
            foreach ($GLOBALS['TL_HOOKS']['i18nl10nModelPostFind'] as $callback) {
                $stdClass = System::importStatic($callback[0]);
                $objResult = $stdClass::{$callback[1]}(static::$strTable, $objResult);
            }
        }

        dump($objResult);

        return $objResult;
    }
}
