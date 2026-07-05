<?php

declare(strict_types=1);

namespace MauticPlugin\MauticYandexCaptchaBundle\EventListener;

use Mautic\FormBundle\Event\FormBuilderEvent;
use Mautic\FormBundle\Event\ValidationEvent;
use Mautic\FormBundle\FormEvents;
use Mautic\LeadBundle\Event\LeadEvent;
use Mautic\LeadBundle\LeadEvents;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use MauticPlugin\MauticYandexCaptchaBundle\CaptchaEvents;
use MauticPlugin\MauticYandexCaptchaBundle\Form\Type\YandexSmartCaptchaType;
use MauticPlugin\MauticYandexCaptchaBundle\Integration\YandexSmartCaptchaIntegration;
use MauticPlugin\MauticYandexCaptchaBundle\Service\YandexSmartCaptchaClient;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class YandexSmartCaptchaFormSubscriber implements EventSubscriberInterface
{
    private ?TranslatorInterface $translator = null;
    private bool $isConfigured = false;
    private ?string $clientKey = null;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly YandexSmartCaptchaClient $captchaClient,
        private readonly LeadModel $leadModel,
        private readonly RequestStack $requestStack,
        IntegrationHelper $integrationHelper
    ) {
        $integrationObject = $integrationHelper->getIntegrationObject(YandexSmartCaptchaIntegration::NAME);

        if ($integrationObject instanceof AbstractIntegration) {
            $this->translator = $integrationObject->getTranslator();
            $keys = $integrationObject->getKeys();

            $this->clientKey = trim((string) ($keys[YandexSmartCaptchaIntegration::CLIENT_KEY_FIELD] ?? ''));
            $serverKey = trim((string) ($keys[YandexSmartCaptchaIntegration::SERVER_KEY_FIELD] ?? ''));
            $this->isConfigured = '' !== $this->clientKey && '' !== $serverKey;
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::FORM_ON_BUILD => ['onFormBuild', 0],
            CaptchaEvents::YANDEX_SMARTCAPTCHA_ON_FORM_VALIDATE => ['onFormValidate', 0],
        ];
    }

    public function onFormBuild(FormBuilderEvent $event): void
    {
        if (!$this->isConfigured) {
            return;
        }

        $event->addFormField('plugin.yandex_smartcaptcha', [
            'label' => 'mautic.form.field.type.plugin.yandex_smartcaptcha',
            'formType' => YandexSmartCaptchaType::class,
            'template' => '@MauticYandexCaptcha/Integration/yandex_smartcaptcha.html.twig',
            'client_key' => $this->clientKey,
            'builderOptions' => [
                'addLeadFieldList' => false,
                'addIsRequired' => false,
                'addDefaultValue' => false,
            ],
        ]);

        $event->addValidator('plugin.yandex_smartcaptcha.validator', [
            'eventName' => CaptchaEvents::YANDEX_SMARTCAPTCHA_ON_FORM_VALIDATE,
            'fieldType' => 'plugin.yandex_smartcaptcha',
        ]);
    }

    public function onFormValidate(ValidationEvent $event): void
    {
        if (!$this->isConfigured) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $token = (string) ($request?->request->get('smart-token') ?? ($_POST['smart-token'] ?? ''));
        $remoteIp = $request?->getClientIp();

        if ($this->captchaClient->verify($token, $remoteIp)) {
            return;
        }

        $event->failedValidation($this->trans('mautic.form.field.yandex_smartcaptcha.failure'));

        $this->eventDispatcher->addListener(LeadEvents::LEAD_POST_SAVE, function (LeadEvent $event): void {
            if (!$event->isNew()) {
                return;
            }

            $leadId = $event->getLead();

            $this->eventDispatcher->addListener('kernel.terminate', function () use ($leadId): void {
                $lead = $this->leadModel->getEntity($leadId);

                if ($lead) {
                    $this->leadModel->deleteEntity($lead);
                }
            });
        }, -255);
    }

    private function trans(string $key): string
    {
        if (!$this->translator) {
            return 'Yandex SmartCaptcha was not successful.';
        }

        return $this->translator->trans($key);
    }
}
