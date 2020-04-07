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
use Contao\Config;
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
     * @internal Do not inherit from this class; decorate the "Contao\CoreBundle\Controller\BackendCsvImportController" service instead
     */
    public function __construct(ContaoFramework $framework, Connection $connection, RequestStack $requestStack, TranslatorInterface $translator, string $projectDir)
    {
        $this->framework = $framework;
        $this->connection = $connection;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->projectDir = $projectDir;
    }

    public function i18nl10nTranslatorWizardAction(DataContainer $dc): Response
    {
        return $this->importFromTemplate(
            $dc->table,
            \Input::get('field'),
            (int) $dc->id
        );
    }

    /**
     * @throws InternalServerErrorException
     */
    private function importFromTemplate(string $table, string $field, int $id): Response
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new InternalServerErrorException('No request object given.');
        }

        $this->framework->initialize();
        $template = $this->prepareTemplate($request);

        if ($request->request->get('FORM_SUBMIT') === $this->getFormId($request)) {
            try {
            } catch (\RuntimeException $e) {
                /** @var Message $message */
                $message = $this->framework->getAdapter(Message::class);
                $message->addError($e->getMessage());

                return new RedirectResponse($request->getUri(), 303);
            }

            /*$this->connection->update(
                $table,
                [$field => serialize($data)],
                ['id' => $id]
            );*/

            return new RedirectResponse($this->getBackUrl($request));
        }

        return new Response($template->parse());
    }

    private function prepareTemplate(Request $request): BackendTemplate
    {
        $template = new BackendTemplate('be_i18nl10n_translator_wizard');

        /** @var Config $config */
        $config = $this->framework->getAdapter(Config::class);

        $template->formId = $this->getFormId($request);
        $template->backUrl = $this->getBackUrl($request);
        $template->fileMaxSize = $config->get('maxFileSize');
        $template->submitLabel = $this->translator->trans('MSC.apply', [], 'contao_default');
        $template->backBT = $this->translator->trans('MSC.backBT', [], 'contao_default');
        $template->backBTTitle = $this->translator->trans('MSC.backBTTitle', [], 'contao_default');
        $template->separatorLabel = $this->translator->trans('MSC.separator.0', [], 'contao_default');
        $template->separatorHelp = $this->translator->trans('MSC.separator.1', [], 'contao_default');
        $template->sourceLabel = $this->translator->trans('MSC.source.0', [], 'contao_default');
        $template->sourceLabelHelp = $this->translator->trans('MSC.source.1', [], 'contao_default');

        return $template;
    }

    private function getBackUrl(Request $request): string
    {
        return str_replace('&key='.$request->query->get('key'), '', $request->getRequestUri());
    }

    private function getFormId(Request $request): string
    {
        return 'tl_i18nl10n_translator_'.$request->query->get('key');
    }
}
