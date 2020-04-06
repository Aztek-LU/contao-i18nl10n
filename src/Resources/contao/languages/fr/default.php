<?php
/**
 * i18nl10n Contao Module
 *
 * @copyright   Copyright (c) 2014-2015 Verstärker, Patric Eberle
 * @author      Patric Eberle <line-in@derverstaerker.ch>
 * @author      Web ex Machina (FR Translation) <https://www.webexmachina.fr>
 * @package     i18nl10n
 * @license     LGPLv3 http://www.gnu.org/licenses/lgpl-3.0.html
 */


/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['editL10n'] = 'Éditer les versions multilingues de la page %s';
$GLOBALS['TL_LANG']['MSC']['language'] = 'langue';
$GLOBALS['TL_LANG']['MSC']['i18nl10n_legend'] = 'Traductions';

$GLOBALS['TL_LANG']['LNG'][''] = 'Neutre';

/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['i18nl10n_aliasExists'] = 'L\'alias "%s" existe déjà pour la langue sélectionnée.';
$GLOBALS['TL_LANG']['ERR']['i18nl10n_selectLangFirst'] = 'Veuillez sélectionner une langue pour cet item.';
$GLOBALS['TL_LANG']['ERR']['i18nl10n_noLocalizationsFound'] = 'Aucune alternative existante trouvée.';

/**
 * I18NL10N Fields
 */
$GLOBALS['TL_LANG']['MSC']['i18nl10n_fields']['language']['label'] = array
(
    'Langue',
    'Sélectionner l\'une des langues disponibles.'
);
$GLOBALS['TL_LANG']['MSC']['i18nl10n_id']['language']['label'] = array
(
    'Traductions',
    'Sélectionnez les traductions de cet élément.'
);

/**
 * I18NL10N Labels
 */
$GLOBALS['TL_LANG']['MSC']['i18nl10n_nbTranslations'] = '<span style="vertical-align:middle;">(%s/%s trad.)</span>';