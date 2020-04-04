<?php

namespace Verstaerker\I18nl10nBundle\Callback;

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
		
		return ' <a href="contao/main.php?do=' . \Input::get('do') . '&amp;table=tl_i18nl10n_translation&amp;id=' . $dc->id . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . \Contao\StringUtil::specialchars($title) . '" onclick="Backend.openModalIframe({\'title\':\'' . \Contao\StringUtil::specialchars(str_replace("'", "\\'", $title)) . '\',\'url\':this.href});return false">' . \Contao\Image::getHtml('bundles/verstaerkeri18nl10n/img/i18nl10n.png', $title) . '</a>';
	}
}