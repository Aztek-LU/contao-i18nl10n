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

use Verstaerker\I18nl10nBundle\Model\I18nl10nTranslation;

$GLOBALS['TL_DCA']['tl_i18nl10n_translation'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'ptable' => '',
        'dynamicPtable' => true,
        'onload_callback' => [
            ['tl_i18nl10n_translation', 'adjustDcaByType'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid,ptable,field,language' => 'index',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'ptable' => [
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'field' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'sorting' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'tstamp' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'language' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['i18nl10n_fields']['language']['label'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'search' => true,
            'options_callback' => ['tl_i18nl10n_translation', 'languageOptions'],
            'reference' => &$GLOBALS['TL_LANG']['LNG'],
            'eval' => [
                'mandatory' => true,
                'rgxp' => 'language',
                'maxlength' => 20,
                'nospace' => true,
                'doNotCopy' => true,
                'tl_class' => 'w50 clr',
                'includeBlankOption' => true,
            ],
            'sql' => "varchar(20) NOT NULL default ''",
        ],
        'valueText' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'valueTextarea' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'helpwizard' => true],
            'explanation' => 'insertTags',
            'sql' => 'mediumtext NULL',
        ],
        'valueBinary' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'],
            'sql' => 'binary(16) NULL',
        ],
        'valueBlob' => [
            'exclude' => true,
            'inputType' => 'listWizard',
            'eval' => ['tl_class' => 'clr'],
            'sql' => 'mediumblob NULL',
        ],
    ],
];

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_i18nl10n_translation extends Contao\Backend
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
     * Adjust the DCA by type.
     *
     * @param object $dc
     */
    public function adjustDcaByType($dc): void
    {
        $objI18nl10nTranslation = I18nl10nTranslation::findByPk($dc->id);

        if (null === $objI18nl10nTranslation) {
            return;
        }
    }
}
