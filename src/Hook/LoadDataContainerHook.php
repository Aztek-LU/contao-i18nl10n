<?php

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
    public function addColumns($strName)
    {
        // Check if Datacontainer must have i18nl10n columns
        if (\Config::has('i18nl10n_tables') && !empty(deserialize(\Config::get('i18nl10n_tables')))) {
            $arrI18nl10nTables = deserialize(\Config::get('i18nl10n_tables'));
            if (!empty($arrI18nl10nTables) && in_array($strName, $arrI18nl10nTables)) {
                \System::loadLanguageFile('languages');

                $GLOBALS['TL_DCA'][$strName]['fields']['title']['eval']['tl_class'] .= ' wizard'; 
                $GLOBALS['TL_DCA'][$strName]['fields']['title']['wizard'][] = [\Verstaerker\I18nl10nBundle\Callback\WizardFieldCallback::class, 'addI18nl10nFields'];

                // Update palettes
                /*$GLOBALS['TL_DCA'][$strName]['palettes']['default'] .= ';{i18nl10n_legend},i18nl10n_lang,i18nl10n_id';
                $GLOBALS['TL_LANG'][$strName]['i18nl10n_legend'] = $GLOBALS['TL_LANG']['MSC']['i18nl10n_legend'];

                $GLOBALS['TL_DCA'][$strName]['fields']['i18nl10n_lang'] = [
                    'label' => &$GLOBALS['TL_LANG']['MSC']['i18nl10n_fields']['language']['label'],
                    'exclude' => true,
                    'filter' => true,
                    'inputType' => 'select',
                    'sorting' => true,
                    'flag' => 11,
                    'options_callback' => function () {
                        $l = [];
                        foreach (I18nl10n::getInstance()->getAvailableLanguages(true, true) as $lang) {
                            $l[$lang] = $GLOBALS['TL_LANG']['LNG'][$lang];
                        }

                        return $l;
                    },
                    'reference' => &$GLOBALS['TL_LANG']['LNG'],
                    'eval' => [
                        'mandatory' => true,
                        //'rgxp'               => 'language',
                        'maxlength' => 5,
                        'nospace' => true,
                        'doNotCopy' => true,
                        'tl_class' => 'w50 clr',
                        'includeBlankOption' => true,
                    ],
                    'sql' => "varchar(5) NOT NULL default ''",
                ];
                $GLOBALS['TL_DCA'][$strName]['fields']['i18nl10n_id'] = [
                    'label' => &$GLOBALS['TL_LANG']['MSC']['i18nl10n_id']['language']['label'],
                    'exclude' => true,
                    'inputType' => 'i18nl10nAssociatedLocationsWizard',
                    'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
                    'sql' => 'blob NULL',
                ];*/
            }
        }
    }

    /**
     * @param $strName
     */
    public function setLanguages($strName)
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
    public function appendLanguageSelectCallback($strName)
    {
        if ('tl_content' === $strName &&
            !in_array(\Input::get('do'), I18nl10n::getInstance()->getUnsupportedModules())
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
    public function appendButtonCallback($strName)
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
     * Set button callback for given table and operation.
     *
     * @param $strTable
     * @param $strOperation
     */
    private function setButtonCallback($strTable, $strOperation)
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
                $arrArgs = func_get_args();

                return call_user_func_array(
                    [$objCallback, 'createButton'],
                    [$strOperation, $arrArgs, $arrVendorCallback]
                );
            };
    }

    /**
     * List label callback for loadDataContainer hook.
     *
     * Appending label callback for tl_article while keeping original callback
     *
     * @param $strName
     */
    public function appendLabelCallback($strName)
    {
        // Append tl_content callbacks
        if ('tl_article' === $strName && 'article' === \Input::get('do')) {
            $arrVendorCallback = $GLOBALS['TL_DCA']['tl_article']['list']['label']['label_callback'];
            $objCallback = new \tl_article_l10n();

            // Create an anonymous function to handle callback from different DCAs
            $GLOBALS['TL_DCA']['tl_article']['list']['label']['label_callback'] =
                function () use ($objCallback, $arrVendorCallback) {
                    // Get callback arguments
                    $arrArgs = func_get_args();

                    return call_user_func_array(
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
    public function appendChildRecordCallback($strName)
    {
        // Append tl_content callbacks
        if ('tl_content' === $strName &&
            !in_array(\Input::get('do'), I18nl10n::getInstance()->getUnsupportedModules())
        ) {
            $arrVendorCallback = $GLOBALS['TL_DCA']['tl_content']['list']['sorting']['child_record_callback'];
            $objCallback = new \tl_content_l10n();

            // Create an anonymous function to handle callback from different DCAs
            $GLOBALS['TL_DCA']['tl_content']['list']['sorting']['child_record_callback'] =
                function () use ($objCallback, $arrVendorCallback) {
                    // Get callback arguments
                    $arrArgs = func_get_args();

                    return call_user_func_array(
                        [$objCallback, 'childRecordCallback'],
                        [$arrArgs, $arrVendorCallback]
                    );
                };
        }
    }
}
