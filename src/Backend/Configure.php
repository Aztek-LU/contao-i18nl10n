<?php
/**
 * i18nl10n Contao Module.
 *
 * The i18nl10n module for Contao allows you to manage multilingual content
 * on the element level rather than with page trees.
 *
 *
 * @copyright   Copyright (c) 2014-2015 Verstärker, Patric Eberle
 * @author      Patric Eberle <line-in@derverstaerker.ch>
 * @license     LGPLv3 http://www.gnu.org/licenses/lgpl-3.0.html
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
     *
     * @throws Exception
     */
    protected function compile()
    {
        // Load all tables
        $arrTables = [];
        $arrI18nl10nTables = [];

        // Check if we chose certain of this tables in the config
        if (\Config::has('i18nl10n_tables') && !empty(deserialize(\Config::get('i18nl10n_tables')))) {
            $arrI18nl10nTables = deserialize(\Config::get('i18nl10n_tables'));
        }

        // If we updated the i18nl10n tables
        if ('tl_i18nl10n_configure' == \Input::post('FORM_SUBMIT')) {
            // Remove from config every table not sent but in config array
            if (in_array($t, $arrI18nl10nTables)) {
                foreach ($arrI18nl10nTables as $k => $t) {
                    if (!in_array($t, \Input::post('i18nl10n_tables'))) {
                        unset($arrI18nl10nTables[$k]);
                    }
                }
            }

            // Add into config every table sent but not in config array
            foreach (\Input::post('i18nl10n_tables') as $t) {
                if (!in_array($t, $arrI18nl10nTables)) {
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

            if (in_array($t, $arrI18nl10nTables)) {
                $o['selected'] = true;
            }

            $arrTables[] = $o;
        }

        // Send data to template
        $this->Template->tables = $arrTables;
    }
}
