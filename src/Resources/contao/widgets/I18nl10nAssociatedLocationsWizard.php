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

namespace Verstaerker\I18nl10nBundle\Widgets;

use Verstaerker\I18nl10nBundle\Classes\I18nl10n;

class I18nl10nAssociatedLocationsWizard extends \Widget
{
    /**
     * Submit user input.
     *
     * @var bool
     */
    protected $blnSubmitInput = true;

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * Trim the values and add new languages if necessary.
     *
     * @param mixed $varInput
     *
     * @return mixed
     */
    public function validate()
    {
        // Get the items IDs sent and apply the current ID as their i18nl10n_id value
        $options = $this->getPost($this->strName);
        $stdModel = \Model::getClassFromTable($this->strTable);

        // Sync selected options
        foreach ($options as $lang => $item) {
            if (!$item) {
                continue;
            }

            // Get all available items for the lang
            $objItem = $stdModel::findByPk($item);
            $arrItemOptions = unserialize($objItem->i18nl10n_id);

            if (!$arrItemOptions) {
                $arrItemOptions = [];
            }

            $arrItemOptions[$this->activeRecord->i18nl10n_lang] = $this->activeRecord->id;
            $objItem->i18nl10n_id = serialize($arrItemOptions);
            $objItem->save();
        }

        // Always save the current ID
        $this->varValue = $options;
    }

    /**
     * Generate the widget and return it as string.
     *
     * @return string
     */
    public function generate()
    {
        try {
            $languages = I18nl10n::getInstance()->getAvailableLanguages(true, true);

            $this->import('Database');

            if (!$this->activeRecord->i18nl10n_lang) {
                return sprintf('<p class="tl_info">%s</p>', $GLOBALS['TL_LANG']['ERR']['i18nl10n_selectLangFirst']);
            }

            // Make sure there is at least an empty array
            if (empty($this->varValue) || !\is_array($this->varValue)) {
                if (\count($languages) > 0) {
                    $key = isset($languages[$GLOBALS['TL_LANGUAGE']]) ? $GLOBALS['TL_LANGUAGE'] : key($languages);
                    $this->varValue = [$key => []];
                } else {
                    return '<p class="tl_info">'.$GLOBALS['TL_LANG']['MSC']['metaNoLanguages'].'</p>';
                }
            }

            $options = [];

            // Fetch options
            foreach ($languages as $lang) {
                // Get all available items for the lang
                $stdModel = \Model::getClassFromTable($this->strTable);
                $objItems = $stdModel::findBy('i18nl10n_lang', $lang);

                if (!array_key_exists($lang, $options)) {
                    $options[$lang] = '
                        <option value="0"> - </option>
                    ';
                }

                // Skip if there is no items available
                if (!$objItems || 0 == $objItems->count()) {
                    continue;
                }

                while ($objItems->next()) {
                    $selected = ($objItems->id == $this->varValue[$lang]) ? ' selected' : '';
                    $options[$lang] .= '
                        <option value="'.$objItems->id.'"'.$selected.'>'.$objItems->title.'</option>
                    ';
                }
            }

            $return = '
                <div id="ctrl_'.$this->strId.'" class="tl_i18nl10nAssociatedLocationsWizard dcapicker">
            ';

            foreach ($languages as $lang) {
                if ($lang == $this->activeRecord->i18nl10n_lang) {
                    continue;
                }

                $return .= '
                    <label style="width:20%;display:inline-block;margin-right:5px;" for="'.$this->strId.'['.$lang.'][lang]">'.$GLOBALS['TL_LANG']['LNG'][$lang].'</label>
                    <select style="width:70%;" name="'.$this->strId.'['.$lang.']" class="tl_select tl_chosen">
                        '.$options[$lang].'
                    </select>
                ';
            }

            $return .= '
                </div>
            ';

            return $return;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
