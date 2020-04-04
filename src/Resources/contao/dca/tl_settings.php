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

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_settings']['config']['onload_callback'][] = ['tl_settings_i18nl10n', 'addFields'];
$GLOBALS['TL_DCA']['tl_settings']['fields']['i18nl10n_fields'] = [
    'inputType' => 'checkbox',
    'options_callback' => ['tl_settings_i18nl10n', 'getExcludedFields'],
    'eval' => ['multiple' => true, 'size' => 36],
];

/**
 * Class tl_settings_i18nl10n.
 */
class tl_settings_i18nl10n extends Contao\Backend
{
    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('Contao\BackendUser', 'User');
    }

    /**
     * Extend palette on onload_callback.
     *
     * @see DC_File::__construct
     */
    public function addFields(): void
    {
        PaletteManipulator::create()
            ->addLegend('i18nl10n_legend', 'chmod_legend', PaletteManipulator::POSITION_AFTER)
            ->addField('i18nl10n_fields', 'i18nl10n_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_settings')
        ;
    }

    /**
     * Return all excluded fields as HTML drop down menu.
     *
     * @return array
     */
    public function getExcludedFields()
    {
        $processed = [];

        /** @var SplFileInfo[] $files */
        $files = \Contao\System::getContainer()->get('contao.resource_finder')->findIn('dca')->depth(0)->files()->name('*.php');

        foreach ($files as $file) {
            if (\in_array($file->getBasename(), $processed, true)) {
                continue;
            }

            $processed[] = $file->getBasename();

            $strTable = $file->getBasename('.php');

            \Contao\System::loadLanguageFile($strTable);
            $this->loadDataContainer($strTable);
        }

        $arrReturn = [];

        // Get all excluded fields
        foreach ($GLOBALS['TL_DCA'] as $k => $v) {
            if (\is_array($v['fields'])) {
                foreach ($v['fields'] as $kk => $vv) {
                    // Hide the "admin" field if the user is not an admin (see #184)
                    if ('tl_user' === $k && 'admin' === $kk && !$this->User->isAdmin) {
                        continue;
                    }

                    if ($vv['exclude'] || $vv['orig_exclude']) {
                        $arrReturn[$k][\Contao\StringUtil::specialchars($k.'::'.$kk)] = isset($vv['label'][0]) ? $vv['label'][0].' <span style="color:#999;padding-left:3px">['.$kk.']</span>' : $kk;
                    }
                }
            }
        }

        ksort($arrReturn);

        return $arrReturn;
    }
}
