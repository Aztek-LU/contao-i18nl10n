<?php

namespace Verstaerker\I18nl10nBundle\Callback;

use Verstaerker\I18nl10nBundle\Classes\I18nl10n;
use Verstaerker\I18nl10nBundle\Model\I18nl10nTranslation;

class WizardFieldCallback {
	/**
	 * Return the edit form wizard
	 *
	 * @param Contao\DataContainer $dc
	 *
	 * @return string
	 */
	public function addI18nl10nFields($dc)
	{
		return ' <a href="contao/main.php?do=' . \Input::get('do') . '&amp;table=tl_i18nl10n_translation&amp;id=' . $dc->id . '&amp;popup=1&amp;nb=1&amp;field=' . $dc->field  . '&amp;rt=' . REQUEST_TOKEN . '" title="' . \Contao\StringUtil::specialchars($title) . '" onclick="Backend.openModalIframe({\'title\':\'' . \Contao\StringUtil::specialchars(str_replace("'", "\\'", $title)) . '\',\'url\':this.href});return false">' . \Contao\Image::getHtml('bundles/verstaerkeri18nl10n/img/i18nl10n.png', $title) . '</a>';
	}

	/**
	 * Display number of translations for the field
	 * 
	 * @param Contao\DataContainer $dc
	 *
	 * @return string
	 */
	public function addI18nl10nLabel($dc) {
		// Retrieve the number of translations 
		$intTranslations = I18nl10nTranslation::countItems(['pid'=>$dc->id, 'ptable'=>$dc->table, 'field'=>$dc->field]);
		$arrLanguages = I18nl10n::getInstance()->getAvailableLanguages(false, true);

		return sprintf($GLOBALS['TL_LANG']['MSC']['i18nl10n_nbTranslations'], $intTranslations, count($arrLanguages));
	}	
}