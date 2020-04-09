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

namespace Verstaerker\I18nl10nBundle\Callback;

use Verstaerker\I18nl10nBundle\Classes\I18nl10n;
use Verstaerker\I18nl10nBundle\Model\I18nl10nTranslation;

class WizardFieldCallback
{
    /**
     * Display number of translations for the field.
     *
     * @param Contao\DataContainer $dc
     *
     * @return string
     */
    public function addI18nl10nLabel($dc)
    {
        // Retrieve the number of translations
        $intTranslations = I18nl10nTranslation::countItems(['pid' => $dc->id, 'ptable' => $dc->table, 'field' => $dc->field]);
        $arrLanguages = I18nl10n::getInstance()->getAvailableLanguages(false, true);
        $table = \Input::get('table') ?: $dc->table;

        $strXlabel = sprintf($GLOBALS['TL_LANG']['MSC']['i18nl10n_nbTranslations'], $intTranslations, \count($arrLanguages));
        $strXlabel .= ' <a href="contao/main.php?do='.\Input::get('do').'&amp;key=i18nl10nTranslatorWizardAction&amp;table='.$table.'&amp;id='.$dc->id.'&amp;field='.$dc->field.'&amp;popup=1&amp;rt='.REQUEST_TOKEN.'" title="'.\Contao\StringUtil::specialchars($title).'" onclick="Backend.openModalIframe({\'title\':\''.\Contao\StringUtil::specialchars(str_replace("'", "\\'", $title)).'\',\'url\':this.href});return false">'.\Contao\Image::getHtml('bundles/verstaerkeri18nl10n/img/i18nl10n.png', $title).'</a>';

        return $strXlabel;
    }
}
