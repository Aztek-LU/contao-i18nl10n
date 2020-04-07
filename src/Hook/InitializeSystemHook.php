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

namespace Verstaerker\I18nl10nBundle\Hook;

use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Verstaerker\I18nl10nBundle\Classes\I18nl10n;
use Verstaerker\I18nl10nBundle\Exception\NoRootPageException;

/**
 * Class InitializeSystemHook.
 */
class InitializeSystemHook extends System
{
    /** @var Request */
    protected $request;

    public function __construct()
    {
        parent::__construct();

        $this->import('request_stack', 'request_stack');
        $this->request = $this->request_stack->getCurrentRequest();
    }

    /**
     * Add tl_i18nl10n_translation into backend modules.
     */
    public function authorizeI18nl10nTables(): void
    {
        if ('BE' === TL_MODE) {
            $arrI18nl10nTables = I18nl10n::getInstance()->getI18nl10nTables();
            foreach ($GLOBALS['BE_MOD'] as &$arrGroup) {
                foreach ($arrGroup as &$arrModule) {
                    if (!\is_array($arrModule) || !\array_key_exists('tables', $arrModule) || !\is_array($arrModule['tables'])) {
                        continue;
                    }

                    foreach ($arrModule['tables'] as $strTable) {
                        if (\in_array($strTable, $arrI18nl10nTables, true)) {
                            $arrModule['tables'][] = 'tl_i18nl10n_translation';
                            $arrModule['i18nl10nTranslatorWizardAction'] = ['Verstaerker\I18nl10nBundle\Controller\I18nl10nTranslatorController', 'i18nl10nTranslatorWizardAction'];
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws NoRootPageException
     */
    public function initializeSystem()
    {
        // Show all contents in Backend
        if (TL_MODE !== 'FE') {
            return true;
        }

        // Catch Facebook token fbclid and redirect without him (trigger 404 errors)...
        if (strpos(\Contao\Environment::get('request'), '?fbclid')) {
            \Contao\Controller::redirect(strtok(\Contao\Environment::get('request'), '?'));
        }

        // Get locale information for system and user
        $arrLanguages = I18nl10n::getInstance()->getAvailableLanguages();
        $userLanguage = $this->request->getLocale();

        // Fail if no languages were configured
        if (0 === \count($arrLanguages)) {
            throw new NoRootPageException();
        }

        // Fallback to default language if language of request does not exist
        $languages = $arrLanguages[$_SERVER['HTTP_HOST']] ?: $arrLanguages['*'];
        if (!\in_array($userLanguage, $languages['languages'], true)) {
            $GLOBALS['TL_LANGUAGE'] = $languages['default'];
        }
    }
}
