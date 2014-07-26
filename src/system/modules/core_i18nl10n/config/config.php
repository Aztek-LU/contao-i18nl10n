<?php

/**
 * i18nl10n Contao Module
 *
 * The i18nl10n module for Contao allows you to manage multilingual content
 * on the element level rather than with page trees.
 *
 *
 * PHP version 5
 * @copyright   Verstärker, Patric Eberle 2014
 * @copyright   Krasimir Berov 2010-2013
 * @author      Patric Eberle <line-in@derverstaerker.ch>
 * @author      Krasimir Berov
 * @package     i18nl10n
 * @license     LGPLv3 http://www.gnu.org/licenses/lgpl-3.0.html
 */


namespace Verstaerker\I18nl10n;

/**
 * -------------------------------------------------------------------------
 * BACK END MODULES
 * -------------------------------------------------------------------------
 */

/**
 * Extend header includes
 */
if (TL_MODE == 'BE')
{
    // CSS files
    $strCss = 'system/modules/core_i18nl10n/assets/css/style.css';

    if (is_array($GLOBALS['TL_CSS']))
    {
        $GLOBALS['TL_CSS'][] = $strCss;
    }
    else
    {
        $GLOBALS['TL_CSS'] = array($strCss);
    }

    // JS files
    $strJs = 'system/modules/core_i18nl10n/assets/js/I18nl10n.js';

    if (is_array($GLOBALS['TL_JAVASCRIPT']))
    {
        $GLOBALS['TL_JAVASCRIPT'][] = $strJs;
    }
    else
    {
        $GLOBALS['TL_JAVASCRIPT'] = array($strJs);
    }
}


/**
 * Append be module to sidebar
 */
array_insert(
    $GLOBALS['BE_MOD']['design'],
    array_search("page", array_keys($GLOBALS['BE_MOD']['design'])) + 1,
    array
    (
        'core_i18nl10n' => array
        (
            'tables' => array('tl_page_i18nl10n'),
            'icon'   => 'system/modules/core_i18nl10n/assets/img/i18nl10n.png'
        )
    )
);


/**
 * -------------------------------------------------------------------------
 * FRONT END MODULES
 * -------------------------------------------------------------------------
 */
$GLOBALS['FE_MOD']['navigationMenu']['i18nl10nLanguageNavigation'] = '\I18nl10n\Modules\ModuleI18nl10nLanguageNavigation';
//$GLOBALS['FE_MOD']['navigationMenu']['breadcrumb']  = 'ModuleI18nl10nBreadcrumb';


/**
 * -------------------------------------------------------------------------
 * HOOKS
 * -------------------------------------------------------------------------
 */
$GLOBALS['TL_HOOKS']['generateFrontendUrl'][] = array('\I18nl10n\Classes\I18nl10nHooks', 'generateFrontendUrl');
$GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = array('\I18nl10n\Classes\I18nl10nHooks', 'getPageIdFromUrl');
$GLOBALS['TL_HOOKS']['getContentElement'][] = array('\I18nl10n\Classes\I18nl10nHooks', 'getContentElement');
$GLOBALS['TL_HOOKS']['generateBreadcrumb'][] = array('\I18nl10n\Classes\I18nl10nHooks', 'generateBreadcrumb');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('\I18nl10n\Pages\PageI18nl10nRegular', 'insertI18nl10nArticle');
$GLOBALS['TL_HOOKS']['executePostActions'][] = array('tl_page_l10n', 'executePostActions');


/**
 * -------------------------------------------------------------------------
 * PAGE TYPES
 * -------------------------------------------------------------------------
 */
$GLOBALS['TL_PTY']['regular'] =  '\I18nl10n\Pages\PageI18nl10nRegular';

if(!$GLOBALS['TL_CONFIG']['i18nl10n_languages']){
    $GLOBALS['TL_CONFIG']['i18nl10n_languages'] = serialize(array('en'));
}