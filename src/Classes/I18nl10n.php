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

namespace Verstaerker\I18nl10nBundle\Classes;

use Contao\Controller;

/**
 * Class I18nl10n.
 *
 * Global Functions for i18nl10n module
 */
class I18nl10n extends Controller
{
    /**
     * Class instance.
     *
     * @var I18nl10n
     */
    protected static $instance = null;

    /**
     * Shared text columns of tl_page and tl_page_i18nl10n.
     *
     * @var array
     */
    protected $textTableFields = [
        'title',
        'language',
        'pageTitle',
        'description',
        'url',
        'cssClass',
        'dateFormat',
        'timeFormat',
        'datimFormat',
        'start',
        'stop',
    ];

    /**
     * Shared meta data columns of tl_page and tl_page_i18nl10n.
     *
     * @var array
     */
    protected $metaTableFields = [
        'id',
        'pid',
        'sorting',
        'tstamp',
        'alias',
        'i18nl10n_published',
    ];

    /**
     * Current time.
     *
     * @var int
     */
    private $time;

    /**
     * Initialize class.
     */
    public function __construct()
    {
        $this->time = time();

        // Import database handler
        $this->import('Database');
    }

    /**
     * Create instance of i18nl10n class.
     *
     * @return I18nl10n
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Load i18nl10n config from Contao settings.
     *
     * @return array [Config]
     */
    public function getI18nl10nConfig()
    {
        $config = \Config::get('i18nl10n_fields');

        if (null === $config) {
            return null;
        }

        return deserialize($config);
    }

    /**
     * Get i18nl10n tables.
     *
     * @return array [List of i18nl10n tables]
     */
    public function getI18nl10nTables()
    {
        $config = $this->getI18nl10nConfig();

        if (null === $config) {
            return [];
        }

        $tables = [];
        foreach ($config as $c) {
            $table = explode('::', $c);

            if (!\in_array($table[0], $tables, true)) {
                $tables[] = $table[0];
            }
        }

        return $tables;
    }

    /**
     * Get i18nl10n fields for a table.
     *
     * @param string $strTable [Table fields]
     *
     * @return array [List of i18nl10n fields]
     */
    public function getI18nl10nFields($strTable)
    {
        $config = $this->getI18nl10nConfig();

        if (null === $config) {
            return [];
        }

        $fields = [];
        foreach ($config as $c) {
            $field = explode('::', $c);

            if ($strTable === $field[0]) {
                $fields[] = $field[1];
            }
        }

        return $fields;
    }

    /**
     * Check is a field is in the i18nl10n config.
     *
     * @param string $strField [field to test]
     * @param string $strTable [table wanted]
     *
     * @return bool [True if field exists]
     */
    public function isI18nl10nField($strField, $strTable)
    {
        if (\in_array($strField, $this->getI18nl10nFields($strTable), true)) {
            return true;
        }

        return false;
    }

    /**
     * Get table columns.
     *
     * @param $blnIncludeMeta   boolean     Include meta data fields
     *
     * @return string|array
     */
    public function getTableFields($blnIncludeMeta = false)
    {
        // Get language specific page properties
        $fields = $this->textTableFields;

        if ($blnIncludeMeta) {
            $fields = array_merge($fields, $this->metaTableFields);
        }

        return $fields;
    }

    /**
     * Get first published sub page for given l10n id and language.
     *
     * @param int    $intId
     * @param string $strLang
     *
     * @return \Contao\PageModel|null
     */
    public function findL10nWithDetails($intId, $strLang)
    {
        // Get page by id
        $objCurrentPage = \Contao\PageModel::findWithDetails($intId);

        // Get localization
        return $this->findPublishedL10nPage($objCurrentPage, $strLang, false);
    }

    /**
     * Find localized page for given page object and replace string values.
     *
     * @param \Contao\PageModel $objPage
     * @param string        [$strLang]              Search for a specific language
     * @param bool          [$blnTranslateOnly]     Get only translation. If false meta data will also be modified.
     *
     * @return \Contao\PageModel|null
     */
    public function findPublishedL10nPage($objPage, $strLang = null, $blnReplaceMetaFields = false)
    {
        // If no page alias is defined, don't continue
        if (empty($objPage->mainAlias)) {
            return $objPage;
        }

        // Get to be replaced fields
        $fields = $this->getTableFields($blnReplaceMetaFields);

        $sqlPublishedCondition = BE_USER_LOGGED_IN
            ? '' :
            "AND (start='' OR start < {$this->time}) AND (stop='' OR stop > {$this->time}) AND i18nl10n_published = 1";

        // Add identification fields and combine sql
        $sql = '
            SELECT
                pid AS l10nPid,
                alias AS l10nAlias, '
                .implode(',', $fields)."
            FROM
                tl_page_i18nl10n
            WHERE
                pid IN(?,?,?)
                AND language = ? $sqlPublishedCondition
            ORDER BY "
               .$this->Database->findInSet('pid', [$objPage->id, $objPage->pid, $objPage->rootId])
        ;

        // Fetch related pages
        $arrL10nRelatedPages = $this->Database
            ->prepare($sql)
            ->execute($objPage->id, $objPage->pid, $objPage->rootId, $strLang ?: $GLOBALS['TL_LANGUAGE'])
            ->fetchAllassoc()
        ;

        // Fetch main page of page branch
        $arrL10nMainPage = $this->Database
            ->prepare('
                SELECT pid AS l10nPid, alias AS l10nAlias, '.implode(',', $fields).'
                FROM tl_page_i18nl10n
                WHERE pid = (SELECT id FROM tl_page WHERE pid = ? AND alias = ?) AND language = ?')
            ->limit(1)
            ->execute($objPage->rootId, $objPage->mainAlias, $strLang ?: $GLOBALS['TL_LANGUAGE'])
            ->fetchAssoc()
        ;

        $arrL10nPage = $arrL10nRelatedPages[0];
        $arrL10nParentPage = $arrL10nRelatedPages[1];
        // Use parent page as root if no root page:
        $arrL10nRootPage = $arrL10nRelatedPages[2] ?: $arrL10nRelatedPages[1];

        // If fallback and localization are not published, return null
        if (!$objPage->i18nl10n_published && $arrL10nPage['l10nPid'] !== $objPage->id) {
            return null;
        }

        // Replace page information only if current page exists
        if ($arrL10nPage['l10nPid'] === $objPage->id) {
            // Replace current page information
            foreach ($fields as $field) {
                if ($arrL10nPage[$field]) {
                    $objPage->$field = $arrL10nPage[$field];
                } elseif ('pageTitle' === $field) { // If empty pageTitle use title
                    $objPage->$field = $arrL10nPage['title'];
                }
            }

            // Replace parent page information
            if ($arrL10nParentPage['l10nPid'] === $objPage->pid) {
                $objPage->parentAlias = $arrL10nParentPage['l10nAlias'];
                $objPage->parentTitle = $arrL10nParentPage['title'];
                $objPage->parentPageTitle = $arrL10nParentPage['pageTitle'] ?: $arrL10nParentPage['title'];
            }

            if (!empty($arrL10nMainPage)) {
                $objPage->mainAlias = $arrL10nMainPage['l10nAlias'];
                $objPage->mainTitle = $arrL10nParentPage['title'];
                $objPage->mainPageTitle = $arrL10nParentPage['pageTitle'] ?: $arrL10nParentPage['title'];
            }

            // replace root page information
            if ($arrL10nRootPage['l10nPid'] === $objPage->rootId) {
                $objPage->rootAlias = $arrL10nRootPage['l10nAlias'];
                $objPage->rootTitle = $arrL10nRootPage['title'];
                $objPage->rootPageTitle = $arrL10nRootPage['pageTitle'] ?: $arrL10nRootPage['title'];

                // Language was not replaced since this removes the options from language select
            }
        } else {
            // else at least keep current language to prevent language change and set flag
            $objPage->language = $GLOBALS['TL_LANGUAGE'];
            $objPage->useFallbackLanguage = true;
        }

        return $objPage;
    }

    /**
     * Get language definition for given page id and table.
     *
     * @param int    $intId
     * @param string $strTable
     * @param   bool     [$blnForCurrentUserOnly]  Get only languages for current BE users permission
     *
     * @return array
     */
    public function getLanguagesByPageId($intId, $strTable, $blnForCurrentUserOnly = false)
    {
        $intId = (int) $intId;

        if (\in_array($strTable, ['tl_page_i18nl10n', 'tl_page'], true)) {
            $rootId = $this->getRootIdByPageId($intId, $strTable);

            return $this->getLanguagesByRootId($rootId, $blnForCurrentUserOnly);
        }

        return [];
    }

    /**
     * Get root page by page id and table.
     *
     * @param int $intId
     * @param int $strTable
     *
     * @return mixed|null
     */
    public function getRootIdByPageId($intId, $strTable)
    {
        switch ($strTable) {
            case 'tl_page':
                return \PageModel::findWithDetails($intId)->rootId;

            case 'tl_page_i18nl10n':
                $arrPage = \Database::getInstance()
                    ->prepare('SELECT * FROM tl_page_i18nl10n WHERE id = ?')
                    ->execute($intId)
                    ->fetchAssoc()
                ;

                return \PageModel::findWithDetails($arrPage['pid'])->rootId;
        }

        return null;
    }

    /**
     * Get language definition by root page ID.
     *
     * @param int  $intId
     * @param bool $blnForCurrentUserOnly Get only languages based on current be user permissions
     *
     * @return array
     */
    public function getLanguagesByRootId($intId, $blnForCurrentUserOnly = false)
    {
        /** @var \Database\Mysqli\Result $objRootPage */
        $objRootPage = \Database::getInstance()
            ->prepare('SELECT * FROM tl_page WHERE id = ?')
            ->execute($intId)
        ;

        $arrLanguages = $this->mapLanguagesFromDatabaseRootPageResult($objRootPage, $blnForCurrentUserOnly);

        return array_shift($arrLanguages);
    }

    /**
     * Get languages by given or actual domain.
     *
     * @param   string  [$strDomain]
     *
     * @return array
     */
    public function getLanguagesByDomain($strDomain = null)
    {
        /** @var \Database\Result $objRootPage */
        $objRootPage = $this->getRootPageByDomain($strDomain);

        $arrLanguages = $this->mapLanguagesFromDatabaseRootPageResult($objRootPage);

        return array_shift($arrLanguages);
    }

    /**
     * Get all available languages.
     *
     * @param bool [$blnForCurrentUserOnly]  Only languages for current logged in user will be returned
     * @param bool [$blnReturnFlat]          Return a flat language array
     *
     * @return array
     */
    public function getAvailableLanguages($blnForCurrentUserOnly = false, $blnReturnFlat = false)
    {
        // Get root pages
        $objRootPages = $this->getAllRootPages();

        // @todo: add neutral

        // @todo: cache result

        return $this->mapLanguagesFromDatabaseRootPageResult($objRootPages, $blnForCurrentUserOnly, $blnReturnFlat);
    }

    /**
     * Get all root pages for current Contao setup.
     *
     * @return \Database\Result
     */
    public function getAllRootPages()
    {
        return \Database::getInstance()->query('SELECT * FROM tl_page WHERE type = "root" AND tstamp > 0');
    }

    /**
     * Get a root page by given or actual domain.
     *
     * @param string    [$strDomain]    Default: null
     *
     * @return \Database\Result
     */
    public function getRootPageByDomain($strDomain = null)
    {
        if (empty($strDomain)) {
            $strDomain = \Environment::get('host');
        }

        // Find page with related or global DNS
        return \Database::getInstance()
            ->prepare('
            (SELECT * FROM tl_page WHERE type = "root" AND dns = ?)
            UNION
            (SELECT * FROM tl_page WHERE type = "root" AND dns = "")')
            ->limit(1)
            ->execute($strDomain)
        ;
    }

    /**
     * Get native language names.
     *
     * @return array
     */
    public function getNativeLanguageNames()
    {
        // Var name defined by languages.php (Don't change!)
        $langsNative = [];

        // Include languages to get $langsNative
        include TL_ROOT.'/vendor/contao/core-bundle/src/Resources/contao/config/languages.php';

        return $langsNative;
    }

    /**
     * Count available root pages.
     *
     * @return int
     */
    public function countRootPages()
    {
        $objRootPages = $this->getAllRootPages();

        return $objRootPages->count();
    }

    /**
     * Get language options for user and group permission.
     *
     * @return array
     */
    public function getLanguageOptionsForUserOrGroup()
    {
        return $this->mapLanguageOptionsForUserOrGroup($this->getAvailableLanguages());
    }

    /**
     * Get language alternatives for given tl_page id and current language.
     *
     * @param \PageModel $objPage
     *
     * @return array
     */
    public function getLanguageAlternativesByPage($objPage)
    {
        $fields = implode(',', $this->getTableFields(true));

        return \Database::getInstance()
            ->prepare("
                SELECT $fields
                FROM tl_page_i18nl10n
                WHERE pid = ? AND i18nl10n_published = 1 AND language != ?
                UNION
                SELECT $fields
                FROM tl_page
                WHERE id = ? AND i18nl10n_published = 1 AND language != ?
            ")
            ->execute($objPage->id, $objPage->language, $objPage->id, $objPage->language)
            ->fetchAllAssoc()
        ;
    }

    /**
     * Map all default and localized languages from a database result and return as array.
     *
     * @param \Database\Result $objRootPage
     * @param bool  [$blnForCurrentUserOnly]    Will only return languages for which the current user has permissions
     * @param bool  [$blnReturnFlat]            Return a flat array with all languages
     *
     * @return array
     */
    private function mapLanguagesFromDatabaseRootPageResult(
        $objRootPage,
        $blnForCurrentUserOnly = false,
        $blnReturnFlat = false
    ) {
        $arrLanguages = [];

        if ($objRootPage->count()) {
            if ($blnReturnFlat) {
                // Loop domains
                while ($objRootPage->next()) {
                    // Add fallback language
                    if (!$blnForCurrentUserOnly ||
                        $this->userHasLanguagePermission($objRootPage->id, $objRootPage->language)
                    ) {
                        $arrLanguages[] = $objRootPage->language;
                    }

                    // Add localizations
                    foreach ((array) \StringUtil::deserialize($objRootPage->i18nl10n_localizations) as $localization) {
                        if (!empty($localization['language'])) {
                            if (!$blnForCurrentUserOnly ||
                                $this->userHasLanguagePermission($objRootPage->id, $localization['language'])
                            ) {
                                $arrLanguages[] = $localization['language'];
                            }
                        }
                    }
                }

                // Make entries unique and sort
                $arrLanguages = array_unique($arrLanguages);
                asort($arrLanguages);
            } else {
                // Loop root pages and collect languages
                while ($objRootPage->next()) {
                    $strDns = $objRootPage->dns ?: '*';

                    $arrLanguages[$strDns] = [
                        'rootId' => $objRootPage->id,
                        'default' => $objRootPage->language,
                        'localizations' => [],
                        'languages' => [],
                    ];

                    if (!$blnForCurrentUserOnly || $this->userHasLanguagePermission($objRootPage->id, '*')) {
                        $arrLanguages[$strDns]['languages'][] = $objRootPage->language;
                    }

                    foreach ((array) \StringUtil::deserialize($objRootPage->i18nl10n_localizations) as $localization) {
                        if (!empty($localization['language'])) {
                            if (!$blnForCurrentUserOnly
                                || $this->userHasLanguagePermission(
                                    $objRootPage->id,
                                    $localization['language']
                                )
                            ) {
                                $arrLanguages[$strDns]['localizations'][] = $localization['language'];
                                $arrLanguages[$strDns]['languages'][] = $localization['language'];
                            }
                        }
                    }

                    // Sort alphabetically
                    asort($arrLanguages[$strDns]['localizations']);
                    asort($arrLanguages[$strDns]['languages']);
                }
            }
        }

        return $arrLanguages;
    }

    /**
     * Create domain related language array for user and group permission.
     *
     * @return array
     */
    private function mapLanguageOptionsForUserOrGroup(array $arrLanguages)
    {
        $arrMappedLanguages = [];

        // Loop Domains
        foreach ($arrLanguages as $domain => $config) {
            // Create Domain identifier
            $arrDomainLanguages = [
                $config['rootId'].'::*' => '',
            ];

            // Loop languages
            foreach ($config['languages'] as $language) {
                // Create unique key by combining root id and language
                $strKey = $config['rootId'].'::'.$language;

                // Add rootId to make unique
                $arrDomainLanguages[$strKey] = $language;
            }

            $arrMappedLanguages[$domain] = $arrDomainLanguages;
        }

        return $arrMappedLanguages;
    }

    /**
     * Check if a user has permission to handle given language by root id.
     *
     * @param int    $intRootPageId
     * @param string $strLanguage
     *
     * @return bool
     */
    private function userHasLanguagePermission($intRootPageId, $strLanguage)
    {
        $arrUserData = \BackendUser::getInstance()->getData();

        return 1 === (int) ($arrUserData['admin']) ||
            \in_array($intRootPageId.'::'.$strLanguage, (array) $arrUserData['i18nl10n_languages'], true);
    }
}
