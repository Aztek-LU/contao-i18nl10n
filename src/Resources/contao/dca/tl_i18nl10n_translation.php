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

use Verstaerker\I18nl10nBundle\Classes\I18nl10n;
use Verstaerker\I18nl10nBundle\Model\I18nl10nTranslation;
$this->loadLanguageFile('languages');

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

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 1,
            'fields'                  => array('language'),
            'flag'                    => 1,
            'panelLayout'             => 'filter;search'
        ),
        'label' => array
        (
            'fields'                  => array('language', 'valueText'),
            'format'                  => '%s <span style="color:#999;padding-left:3px">[%s]</span>',
            //'label_callback'          => array('tl_article', 'addIcon')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'href'                => 'act=edit',
                'icon'                => 'edit.svg'
            ),
            'copy' => array
            (
                'href'                => 'act=paste&amp;mode=copy',
                'icon'                => 'copy.svg',
                'attributes'          => 'onclick="Backend.getScrollOffset()"'
            ),
            'delete' => array
            (
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            )
        )
    ),

    // Palettes
    'palettes' => [
        'default' => '{title_legend},language,valueText',
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
            'eval' => ['filesOnly' => true, 'fieldType' => 'radio'],
            'sql' => 'binary(16) NULL',
        ],
        'valueBlob' => [
            'exclude' => true,
            'inputType' => 'listWizard',
            'sql' => 'mediumblob NULL',
        ],
        'invisible' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default ''",
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

    public function getField($varValue, $dc)
    {
        if (\Input::get('field')) {
            $varValue = \Input::get('field');
        }

        return $varValue;
    }

    public function saveField($varValue, $dc)
    {       
        return $varValue;
    }

    /**
     * Create language options based on root page and already used languages.
     *
     * @return array
     */
    public function languageOptions(DataContainer $dc)
    {
        $arrOptions = [];
        $i18nl10nLanguages = I18nl10n::getInstance()->getAvailableLanguages(false, true);

        // Create options array base on root page languages
        foreach ($i18nl10nLanguages as $language) {
            $arrOptions[$language] = $GLOBALS['TL_LANG']['LNG'][$language];
        }

        return $arrOptions;
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
