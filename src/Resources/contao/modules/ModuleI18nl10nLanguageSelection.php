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

namespace Verstaerker\I18nl10nBundle\Modules;

use Verstaerker\I18nl10nBundle\Classes\I18nl10n;
use Verstaerker\I18nl10nBundle\Model\I18nl10nTranslation;

/**
 * Class ModuleI18nl10nLanguageSelection.
 *
 * Generates a languages menu.
 * The site visitor is able to switch between available languages.
 *
 * @author     Patric Eberle <line-in@derverstaerker.ch>
 */
class ModuleI18nl10nLanguageSelection extends \Module
{
    /**
     * Module wrapper template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_i18nl10n_nav';

    /**
     * Return a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### '
                .utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['i18nl10n_languageSelection'][0])
                .' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        $result = parent::generate();

        return empty($this->Template->items) ? '' : $result;
    }

    /**
     * Generate the module.
     *
     * @hooks ModuleI18nl10nLanguageSelection manipulate translation options
     */
    protected function compile(): void
    {
        global $objPage;

        $time = time();
        $items = [];
        $langNative = I18nl10n::getInstance()->getNativeLanguageNames();

        // Get all possible languages for this page tree
        $arrLanguages = I18nl10n::getInstance()->getLanguagesByRootId($objPage->rootId);
        $arrTranslations = I18nl10nTranslation::findItems(['ptable' => 'tl_page', 'pid' => $objPage->id, 'field' => 'alias'])->fetchAll();

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['i18nl10nLanguageSelection'])
            && \is_array($GLOBALS['TL_HOOKS']['i18nl10nLanguageSelection'])
        ) {
            foreach ($GLOBALS['TL_HOOKS']['i18nl10nLanguageSelection'] as $callback) {
                $this->import($callback[0]);
                $arrTranslations = $this->$callback[0]->$callback[1]($arrTranslations);
            }
        }

        if (!empty($arrTranslations)) {
            $this->loadLanguageFile('languages');

            // Add default language
            array_unshift(
                $arrTranslations,
                [
                    'id' => $objPage->id,
                    'language' => $objPage->rootLanguage,
                    'title' => $objPage->title,
                    'pageTitle' => $objPage->pageTitle,
                    'alias' => $objPage->alias,
                ]
            );

            // keep the order in $i18nl10nLanguages and assign to $items
            // only if page translation is found in database
            foreach ($arrLanguages['languages'] as $language) {
                // check if current language has not to be shown
                if ($language === $GLOBALS['TL_LANGUAGE'] && $this->i18nl10n_langHide) {
                    continue;
                }

                // loop translations
                foreach ($arrTranslations as $row) {
                    // Get all the available translations 
                    $item = [
                        'id' => $objPage->id,
                        'alias' => $objPage->alias,
                        'title' => $objPage->title,
                        'pageTitle' => $objPage->pageTitle,
                        'language' => $language,
                        'isActive' => $language === $GLOBALS['TL_LANGUAGE'],
                        'forceRowLanguage' => true,
                    ];

                    $objPageTranslations = I18nl10nTranslation::findItems(['ptable' => 'tl_page', 'pid' => $objPage->id]);

                    while($objPageTranslations->next()) {
                        $item[$objPageTranslations->field] = $objPageTranslations->valueText;
                    }

                    // HOOK: add custom logic
                    if (isset($GLOBALS['TL_HOOKS']['i18nl10nUpdateLanguageSelectionItem'])
                        && \is_array($GLOBALS['TL_HOOKS']['i18nl10nUpdateLanguageSelectionItem'])
                    ) {
                        foreach ($GLOBALS['TL_HOOKS']['i18nl10nUpdateLanguageSelectionItem'] as $callback) {
                            $stdClass = \System::importStatic($callback[0]);
                            $item = $stdClass::{$callback[1]}($item);
                        }
                    }

                    // check if language is needed
                    if ($row['language'] === $language) {
                        array_push(
                            $items,
                            $item
                        );
                        break;
                    }
                }
            }

            // Add classes first and last
            $last = \count($items) - 1;
            $items[0]['class'] = trim($items[0]['class'].' first');
            $items[$last]['class'] = trim($items[$last]['class'].' last');

            $objTemplate = new \BackendTemplate($this->i18nl10n_langTpl);

            $objTemplate->type = \get_class($this);
            $objTemplate->items = $items;
            $objTemplate->languages = $langNative;

            dump($items);
        }

        // Add stylesheets
        if ('disable' !== $this->i18nl10n_langStyle) {
            $assetsUrl = 'bundles/verstaerkeri18nl10n/';

            // Add global and selected style
            $GLOBALS['TL_CSS'][] = $assetsUrl.'css/i18nl10n_lang.css';

            // Add additional styles if needed
            if (\in_array($this->i18nl10n_langStyle, ['text', 'image', 'iso'], true)) {
                $GLOBALS['TL_CSS'][] = $assetsUrl.'css/i18nl10n_lang_'.$this->i18nl10n_langStyle.'.css';
            }
        }

        // Create URI params
        $strUriParams = '';

        foreach ($_GET as $key => $value) {
            if ('id' === $key) {
                continue;
            }
            $strUriParams .= '/'.$key.'/'.\Input::get($key);
        }

        $this->Template->items = !empty($items) && isset($objTemplate) ? $objTemplate->parse() : '';
        $this->Template->uriParams = $strUriParams;
    }
}
