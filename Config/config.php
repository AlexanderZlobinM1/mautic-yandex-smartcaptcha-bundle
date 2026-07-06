<?php

declare(strict_types=1);

use Mautic\CoreBundle\Helper\AppVersion;
use MauticPlugin\MauticYandexCaptchaBundle\EventListener\YandexSmartCaptchaFormSubscriber;
use MauticPlugin\MauticYandexCaptchaBundle\Integration\YandexSmartCaptchaIntegration;
use MauticPlugin\MauticYandexCaptchaBundle\Service\YandexSmartCaptchaClient;

$mauticVersion = str_replace('.', '', explode('-', (new AppVersion())->getVersion())[0]);
$mauticVersion = str_split((string) $mauticVersion);
$majorVersion = (int) ($mauticVersion[0] ?? 0);

switch (true) {
    case $majorVersion >= 6:
        $defaultIntegrationArguments = [
            'event_dispatcher',
            'mautic.helper.cache_storage',
            'doctrine.orm.entity_manager',
            'request_stack',
            'router',
            'translator',
            'monolog.logger.mautic',
            'mautic.helper.encryption',
            'mautic.lead.model.lead',
            'mautic.lead.model.company',
            'mautic.helper.paths',
            'mautic.core.model.notification',
            'mautic.lead.model.field',
            'mautic.plugin.model.integration_entity',
            'mautic.lead.model.dnc',
            'mautic.lead.field.fields_with_unique_identifier',
        ];
        break;

    case $majorVersion >= 5:
        $defaultIntegrationArguments = [
            'event_dispatcher',
            'mautic.helper.cache_storage',
            'doctrine.orm.entity_manager',
            'session',
            'request_stack',
            'router',
            'translator',
            'monolog.logger.mautic',
            'mautic.helper.encryption',
            'mautic.lead.model.lead',
            'mautic.lead.model.company',
            'mautic.helper.paths',
            'mautic.core.model.notification',
            'mautic.lead.model.field',
            'mautic.plugin.model.integration_entity',
            'mautic.lead.model.dnc',
            'mautic.lead.field.fields_with_unique_identifier',
        ];
        break;

    default:
        throw new RuntimeException('MauticYandexCaptchaBundle supports Mautic 5, 6 and 7.');
}

return [
    'name'        => 'Yandex SmartCaptcha',
    'description' => 'Adds a Yandex SmartCaptcha field and server-side validation to Mautic forms. Configure the Client key and Server key in the Auth tab.',
    'version'     => '1.0.5',
    'author'      => 'Sales Snap',

    'services' => [
        'events' => [
            'mautic.yandex_smartcaptcha.event_listener.form_subscriber' => [
                'class' => YandexSmartCaptchaFormSubscriber::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.yandex_smartcaptcha.service.client',
                    'mautic.lead.model.lead',
                    'request_stack',
                    'mautic.helper.integration',
                ],
            ],
        ],
        'others' => [
            'mautic.yandex_smartcaptcha.service.client' => [
                'class' => YandexSmartCaptchaClient::class,
                'arguments' => [
                    'mautic.helper.integration',
                ],
            ],
        ],
        'integrations' => [
            'mautic.integration.yandexsmartcaptcha' => [
                'class' => YandexSmartCaptchaIntegration::class,
                'arguments' => $defaultIntegrationArguments,
            ],
        ],
    ],
];
