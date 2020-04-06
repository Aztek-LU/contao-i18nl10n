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

use Verstaerker\I18nl10nBundle\Classes\I18nl10n;

/**
 * Class LoadDataContainerHook.
 */
class LoadDataContainerHook
{
    /**
     * Update DCA concerned by i18nl10n configuration.
     *
     * @param [type] $strName [description]
     */
    public function addColumns($strName): void
    {
        $objI18nl10n = I18nl10n::getInstance();

        if (\is_array($GLOBALS['TL_DCA'][$strName]['fields'])) {
            foreach ($GLOBALS['TL_DCA'][$strName]['fields'] as $f => $fc) {
                if ($objI18nl10n->isI18nl10nField($f, $strName)) {
                    $GLOBALS['TL_DCA'][$strName]['fields'][$f]['eval']['tl_class'] .= ' wizard';
                    $GLOBALS['TL_DCA'][$strName]['fields'][$f]['wizard'][] = [\Verstaerker\I18nl10nBundle\Callback\WizardFieldCallback::class, 'addI18nl10nFields'];
                    $GLOBALS['TL_DCA'][$strName]['fields'][$f]['xlabel'][] = [\Verstaerker\I18nl10nBundle\Callback\WizardFieldCallback::class, 'addI18nl10nLabel'];
                }
            }
        }
    }

    /**
     * @param $strName
     */
    public function setLanguages($strName): void
    {
        // Some modules are not able to support user permission base languages, so get all
        $arrLanguages = I18nl10n::getInstance()->getAvailableLanguages(false, true);

        // @todo: add neutral?

        // @todo: refactor modules to get languages from config too
        $GLOBALS['TL_DCA'][$strName]['config']['languages'] = $arrLanguages;
    }

    /**
     * loadDataContainer hook.
     *
     * Add onload_callback definition when loadDataContainer hook is
     * called to define onload_callback as late as possible
     *
     * @param string $strName
     */
    public function appendLanguageSelectCallback($strName): void
    {
        if ('tl_content' === $strName &&
            !\in_array(\Input::get('do'), I18nl10n::getInstance()->getUnsupportedModules(), true)
        ) {
            $GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] =
                ['tl_content_l10n', 'appendLanguageInput'];
        }
    }

    /**
     * loadDataContainer hook.
     *
     * Redefine button_callback for tl_content elements to allow permission
     * based display/hide.
     *
     * @param string $strName
     */
    public function appendButtonCallback($strName): void
    {
        // Append tl_content callbacks
        if ('tl_content' === $strName && 'article' === \Input::get('do')) {
            $this->setButtonCallback('tl_content', 'edit');
            $this->setButtonCallback('tl_content', 'copy');
            $this->setButtonCallback('tl_content', 'cut');
            $this->setButtonCallback('tl_content', 'delete');
            $this->setButtonCallback('tl_content', 'toggle');
        }

        // Append tl_page callbacks
        if ('tl_page' === $strName && 'page' === \Input::get('do')) {
            $this->setButtonCallback('tl_page', 'edit');
            $this->setButtonCallback('tl_page', 'copy');
            $this->setButtonCallback('tl_page', 'copyChilds');  // Copy with children button
            $this->setButtonCallback('tl_page', 'cut');
            $this->setButtonCallback('tl_page', 'delete');
            $this->setButtonCallback('tl_page', 'toggle');
        }
    }

    /**
     * List label callback for loadDataContainer hook.
     *
     * Appending label callback for tl_article while keeping original callback
     *
     * @param $strName
     */
    public function appendLabelCallback($strName): void
    {
        // Append tl_content callbacks
        if ('tl_article' === $strName && 'article' === \Input::get('do')) {
            $arrVendorCallback = $GLOBALS['TL_DCA']['tl_article']['list']['label']['label_callback'];
            $objCallback = new \tl_article_l10n();

            // Create an anonymous function to handle callback from different DCAs
            $GLOBALS['TL_DCA']['tl_article']['list']['label']['label_callback'] =
                function () use ($objCallback, $arrVendorCallback) {
                    // Get callback arguments
                    $arrArgs = \func_get_args();

                    return \call_user_func_array(
                        [$objCallback, 'labelCallback'],
                        [$arrArgs, $arrVendorCallback]
                    );
                };
        }
    }

    /**
     * Child record callback for loadDataContainer hook.
     *
     * Appending child record callback for tl_content while keeping original callback
     *
     * @param $strName
     */
    public function appendChildRecordCallback($strName): void
    {
        // Append tl_content callbacks
        if ('tl_content' === $strName &&
            !\in_array(\Input::get('do'), I18nl10n::getInstance()->getUnsupportedModules(), true)
        ) {
            $arrVendorCallback = $GLOBALS['TL_DCA']['tl_content']['list']['sorting']['child_record_callback'];
            $objCallback = new \tl_content_l10n();

            // Create an anonymous function to handle callback from different DCAs
            $GLOBALS['TL_DCA']['tl_content']['list']['sorting']['child_record_callback'] =
                function () use ($objCallback, $arrVendorCallback) {
                    // Get callback arguments
                    $arrArgs = \func_get_args();

                    return \call_user_func_array(
                        [$objCallback, 'childRecordCallback'],
                        [$arrArgs, $arrVendorCallback]
                    );
                };
        }
    }

    /**
     * Set button callback for given table and operation.
     *
     * @param $strTable
     * @param $strOperation
     */
    private function setButtonCallback($strTable, $strOperation): void
    {
        $arrVendorCallback = $GLOBALS['TL_DCA'][$strTable]['list']['operations'][$strOperation]['button_callback'];

        switch ($strTable) {
            case 'tl_page':
                $objCallback = new \tl_page_l10n();
                break;

            case 'tl_content':
                $objCallback = new \tl_content_l10n();
                break;

            default:
                return;
        }

        // Create an anonymous function to handle callback from different DCAs
        $GLOBALS['TL_DCA'][$strTable]['list']['operations'][$strOperation]['button_callback'] =
            function () use ($strTable, $objCallback, $strOperation, $arrVendorCallback) {
                // Get callback arguments
                $arrArgs = \func_get_args();

                return \call_user_func_array(
                    [$objCallback, 'createButton'],
                    [$strOperation, $arrArgs, $arrVendorCallback]
                );
            };
    }
}
