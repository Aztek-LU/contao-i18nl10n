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

namespace Verstaerker\I18nl10nBundle\Controller;

use Contao\BackendTemplate;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\Message;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Verstaerker\I18nl10nBundle\Classes\I18nl10n;
use Verstaerker\I18nl10nBundle\Model\I18nl10nTranslation;

class I18nl10nTranslatorController
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var array
     */
    private $languages;

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $field;

    /**
     * @var int
     */
    private $pid;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $widgets;

    /**
     * @var array
     */
    private $models;

    /**
     * @internal Do not inherit from this class; decorate the "Verstaerker\I18nl10nBundle\Controller\I18nl10nTranslatorController" service instead
     */
    public function __construct(ContaoFramework $framework, Connection $connection, RequestStack $requestStack, TranslatorInterface $translator, string $projectDir)
    {
        $this->framework = $framework;
        $this->connection = $connection;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->projectDir = $projectDir;

        $this->languages = I18nl10n::getInstance()->getAvailableLanguages(false, true);
        $this->widgets = [];
        $this->models = [];
    }

    public function i18nl10nTranslatorWizardAction(DataContainer $dc): Response
    {
        $this->table = \Input::get('table') ?: $dc->table;
        $this->field = \Input::get('field');
        $this->pid = (int) $dc->id;
        $this->config = $GLOBALS['TL_DCA'][$this->table]['fields'][$this->field];

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new InternalServerErrorException('No request object given.');
        }

        $this->framework->initialize();

        // Get the model & widget of each language
        foreach ($this->languages as $l) {
            // Get the value for this field
            $objTranslation = I18nl10nTranslation::findItems(['pid' => $this->pid, 'ptable' => $this->table, 'field' => $this->field, 'language' => $l], 1);

            // If there is no translation, create it
            if (!$objTranslation) {
                $objTranslation = new I18nl10nTranslation();
                $objTranslation->pid = $this->pid;
                $objTranslation->ptable = $this->table;
                $objTranslation->tstamp = time();
                $objTranslation->field = $this->field;
                $objTranslation->language = $l;
                $objTranslation->save();
            }

            $value = $objTranslation->{$this->getValueField()};

            $strClass = '\\'.$GLOBALS['BE_FFL'][$this->config['inputType']];
            $objWidget = new $strClass(
                $strClass::getAttributesFromDca(
                    $this->config,
                    $this->getFieldName($l),
                    $value,
                    $this->field,
                    $this->table,
                    null
                )
            );
            $this->widgets[$l] = $objWidget;
            $this->models[$l] = $objTranslation;
        }

        if ($request->request->get('FORM_SUBMIT') === $this->getFormId($request)) {
            try {
                foreach ($this->languages as $l) {
                    $value = $request->request->get($this->getFieldName($l));

                    // If the value sent is different, save the model and update the widget
                    if ($value !== $this->models[$l]->{$this->getValueField()}) {
                        $this->models[$l]->{$this->getValueField()} = $value;
                        $this->models[$l]->tstamp = time();
                        $this->models[$l]->save();

                        $this->widgets[$l]->value = $value;
                    }
                }
            } catch (\RuntimeException $e) {
                /** @var Message $message */
                $message = $this->framework->getAdapter(Message::class);
                $message->addError($e->getMessage());

                return new RedirectResponse($request->getUri(), 303);
            }
        }

        foreach($this->languages as $l) {
            $this->widgets[$l] = $this->parse($this->widgets[$l], $l);
        }

        $template = $this->prepareTemplate($request);

        return new Response($template->parse());
    }

    private function prepareTemplate(Request $request): BackendTemplate
    {
        $template = new BackendTemplate('be_i18nl10n_translator_wizard');

        $template->widgets = $this->widgets;
        $template->languages = $this->languages;
        $template->formId = $this->getFormId($request);
        $template->backUrl = $this->getBackUrl($request);
        $template->submitLabel = $this->translator->trans('MSC.apply', [], 'contao_default');

        return $template;
    }

    private function getValueField()
    {
        if (false !== strpos($this->config['sql'], 'blob')) {
            return 'valueBlob';
        }
        if (false !== strpos($this->config['sql'], 'binary')) {
            return 'valueBinary';
        }
        if (false !== strpos($this->config['sql'], 'text')) {
            return 'valueTextarea';
        }

        return 'valueText';
    }

    private function getBackUrl(Request $request): string
    {
        return str_replace('&key='.$request->query->get('key'), '', $request->getRequestUri());
    }

    private function getFormId(Request $request): string
    {
        return 'tl_i18nl10n_translator_'.$request->query->get('key');
    }

    private function getFieldName(string $l): string {
        return sprintf('i18nl10n_%s_%s_%s_%s', $this->table, $this->field, $this->pid, $l);
    }

    private function parse($objWidget, $l) {
        // Replace the textarea with an RTE instance
        if (!empty($this->config['eval']['rte']))
        {
            list ($file, $type) = explode('|', $this->config['eval']['rte'], 2);

            $fileBrowserTypes = array();
            $pickerBuilder = \System::getContainer()->get('contao.picker.builder');

            foreach (array('file' => 'image', 'link' => 'file') as $context => $fileBrowserType)
            {
                if ($pickerBuilder->supportsContext($context))
                {
                    $fileBrowserTypes[] = $fileBrowserType;
                }
            }

            $objTemplate = new \BackendTemplate('be_' . $file);
            $objTemplate->selector = 'ctrl_' . $this->getFieldName($l);
            $objTemplate->type = $type;
            $objTemplate->fileBrowserTypes = $fileBrowserTypes;
            $objTemplate->source = $this->table . '.' . $this->pid;

            // Deprecated since Contao 4.0, to be removed in Contao 5.0
            $objTemplate->language = \Backend::getTinyMceLanguage();

            $updateMode = $objTemplate->parse();

            unset($file, $type, $pickerBuilder, $fileBrowserTypes, $fileBrowserType);
        }

        return '
<div' . ($this->config['eval']['tl_class'] ? ' class="' . trim($this->config['eval']['tl_class']) . '"' : '') . '>' . $objWidget->parse() . $updateMode . (!$objWidget->hasErrors() ? $this->help($strHelpClass) : '') . '
</div>';
    }

    /**
     * Return the field explanation as HTML string
     *
     * @param string $strClass
     *
     * @return string
     */
    public function help($strClass='')
    {
        $return = $GLOBALS['TL_DCA'][$this->table]['fields'][$this->field]['label'][1];

        if ($return == '' || $GLOBALS['TL_DCA'][$this->table]['fields'][$this->field]['inputType'] == 'password' || !\Config::get('showHelp'))
        {
            return '';
        }

        return '
  <p class="tl_help tl_tip' . $strClass . '">' . $return . '</p>';
    }
}
