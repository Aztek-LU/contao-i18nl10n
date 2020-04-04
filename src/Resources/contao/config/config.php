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

/**
 * BACK END MODULES.
 */
// Extend header includes
if (TL_MODE === 'BE') {
    // CSS files
    $GLOBALS['TL_CSS'][] = 'bundles/verstaerkeri18nl10n/css/style.css';

    // JS files
    $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/verstaerkeri18nl10n/js/i18nl10n.js';
}

// Append be module to sidebar
/*array_insert(
    $GLOBALS['BE_MOD']['design'],
    array_search('page', array_keys($GLOBALS['BE_MOD']['design']), true) + 1,
    [
        'i18nl10n' => [
            'tables' => ['tl_page_i18nl10n'],
            'icon' => 'bundles/verstaerkeri18nl10n/img/i18nl10n.png',
        ],
    ]
);*/

array_insert(
    $GLOBALS['BE_MOD']['system'],
    array_search('undo', array_keys($GLOBALS['BE_MOD']['system']), true) + 1,
    [
        'i18nl10n_settings' => [
            'callback' => "Verstaerker\I18nl10nBundle\Backend\Configure",
        ],
    ]
);

/*
 * FRONT END MODULES
 */
$GLOBALS['FE_MOD']['i18nl10n']['i18nl10nLanguageSelection'] = 'Verstaerker\I18nl10nBundle\Modules\ModuleI18nl10nLanguageSelection';

/*
 * MODELS
 */
$GLOBALS['TL_MODELS'][\Verstaerker\I18nl10nBundle\Model\I18nl10nTranslation::getTable()] = 'Verstaerker\I18nl10nBundle\Model\I18nl10nTranslation';

/*
 * HOOKS
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = ['Verstaerker\I18nl10nBundle\Hook\LoadDataContainerHook', 'addColumns'];
/*$GLOBALS['TL_HOOKS']['initializeSystem'][] = ['Verstaerker\I18nl10nBundle\Hook\InitializeSystemHook', 'initializeSystem'];
$GLOBALS['TL_HOOKS']['generateFrontendUrl'][] = ['Verstaerker\I18nl10nBundle\Hook\GenerateFrontendUrlHook', 'generateFrontendUrl'];
$GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = ['Verstaerker\I18nl10nBundle\Hook\GetPageIdFromUrlHook', 'getPageIdFromUrl'];
$GLOBALS['TL_HOOKS']['generateBreadcrumb'][] = ['Verstaerker\I18nl10nBundle\Hook\GenerateBreadcrumbHook', 'generateBreadcrumb'];
$GLOBALS['TL_HOOKS']['executePostActions'][] = ['Verstaerker\I18nl10nBundle\Hook\ExecutePostActionsHook', 'executePostActions'];
$GLOBALS['TL_HOOKS']['isVisibleElement'][] = ['Verstaerker\I18nl10nBundle\Hook\IsVisibleElementHook', 'isVisibleElement'];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = ['Verstaerker\I18nl10nBundle\Hook\ReplaceInsertTagsHook', 'replaceInsertTags'];
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = ['Verstaerker\I18nl10nBundle\Hook\LoadDataContainerHook', 'setLanguages'];
$GLOBALS['TL_HOOKS']['getArticle'][] = ['Verstaerker\I18nl10nBundle\Hook\GetArticleHook', 'checkIfEmpty'];

// Append language selection for tl_content
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = ['Verstaerker\I18nl10nBundle\Hook\LoadDataContainerHook', 'appendLanguageSelectCallback'];

// Append button callback for tl_content to introduce permission
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = ['Verstaerker\I18nl10nBundle\Hook\LoadDataContainerHook', 'appendButtonCallback'];

// Append label callback for tl_article labels
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = ['Verstaerker\I18nl10nBundle\Hook\LoadDataContainerHook', 'appendLabelCallback'];

// Append child record callback for tl_content labels
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = ['Verstaerker\I18nl10nBundle\Hook\LoadDataContainerHook', 'appendChildRecordCallback'];

// Search indexation
$GLOBALS['TL_HOOKS']['indexPage'][] = ['Verstaerker\I18nl10nBundle\Hook\IndexPageHook', 'indexPage'];
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = ['Verstaerker\I18nl10nBundle\Hook\GetSearchablePagesHook', 'getSearchablePages'];
$GLOBALS['TL_HOOKS']['customizeSearch'][] = ['Verstaerker\I18nl10nBundle\Hook\CustomizeSearchHook', 'customizeSearch'];*/

/*
 * PAGE TYPES
 */
$GLOBALS['TL_PTY']['regular'] = 'Verstaerker\I18nl10nBundle\Pages\PageI18nl10nRegular';

/*
 * Inherit language permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'i18nl10n_languages';

/*
 * Adding custom widgets
 */
$GLOBALS['BE_FFL']['i18nl10nMetaWizard'] = 'Verstaerker\I18nl10nBundle\Widgets\I18nl10nMetaWizard';
$GLOBALS['BE_FFL']['i18nl10nAssociatedLocationsWizard'] = 'Verstaerker\I18nl10nBundle\Widgets\I18nl10nAssociatedLocationsWizard';
