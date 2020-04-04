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

namespace Verstaerker\I18nl10nBundle\Backend;

/**
 * Class Configure.
 *
 * Backend configuration module for i18nl10n
 */
class Configure extends \BackendModule
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'be_i18nl10n_configure';

    /**
     * Generate the module.
     *
     * @throws Exception
     */
    protected function compile(): void
    {
        // Load User
        $this->import('Contao\BackendUser', 'User');

        // Load all tables
        $arrTables = [];
        $arrI18nl10nTables = [];

        // Check if we chose certain of this tables in the config
        if (\Config::has('i18nl10n_tables') && !empty(deserialize(\Config::get('i18nl10n_tables')))) {
            $arrI18nl10nTables = deserialize(\Config::get('i18nl10n_tables'));
        }

        // If we updated the i18nl10n tables
        if ('tl_i18nl10n_configure' === \Input::post('FORM_SUBMIT')) {
            // Remove from config every table not sent but in config array
            if (\in_array($t, $arrI18nl10nTables, true)) {
                foreach ($arrI18nl10nTables as $k => $t) {
                    if (!\in_array($t, \Input::post('i18nl10n_tables'), true)) {
                        unset($arrI18nl10nTables[$k]);
                    }
                }
            }

            // Add into config every table sent but not in config array
            foreach (\Input::post('i18nl10n_tables') as $t) {
                if (!\in_array($t, $arrI18nl10nTables, true)) {
                    $arrI18nl10nTables[] = $t;
                }
            }

            // and save
            \Config::persist('i18nl10n_tables', serialize($arrI18nl10nTables));
        }

        foreach ($this->Database->listTables() as $t) {
            $o = [
                'value' => $t,
                'label' => $t,
            ];

            if (\in_array($t, $arrI18nl10nTables, true)) {
                $o['selected'] = true;
            }

            $arrTables[] = $o;
        }

        // Send data to template
        $this->Template->tables = $arrTables;
    }
}
