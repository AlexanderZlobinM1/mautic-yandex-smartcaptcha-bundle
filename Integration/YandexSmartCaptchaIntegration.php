<?php

declare(strict_types=1);

namespace MauticPlugin\MauticYandexCaptchaBundle\Integration;

use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;

class YandexSmartCaptchaIntegration extends AbstractIntegration
{
    public const NAME = 'YandexSmartCaptcha';

    public const CLIENT_KEY_FIELD = 'client_key';
    public const SERVER_KEY_FIELD = 'server_key';
    public const FAIL_OPEN_FIELD = 'fail_open';

    public function getName()
    {
        return self::NAME;
    }

    public function getDisplayName()
    {
        return 'Yandex SmartCaptcha';
    }

    public function getAuthenticationType()
    {
        return 'none';
    }

    public function getPriority()
    {
        return 10;
    }

    public function getRequiredKeyFields()
    {
        return [
            self::CLIENT_KEY_FIELD => 'mautic.integration.yandex_smartcaptcha.client_key',
            self::SERVER_KEY_FIELD => 'mautic.integration.yandex_smartcaptcha.server_key',
        ];
    }

    /**
     * @param FormBuilder|Form $builder
     * @param array            $data
     * @param string           $formArea
     */
    public function appendToForm(&$builder, $data, $formArea): void
    {
        if ('keys' !== $formArea) {
            return;
        }

        $builder->add(
            self::FAIL_OPEN_FIELD,
            YesNoButtonGroupType::class,
            [
                'label' => 'mautic.integration.yandex_smartcaptcha.fail_open',
                'data'  => array_key_exists(self::FAIL_OPEN_FIELD, $data)
                    ? (bool) $data[self::FAIL_OPEN_FIELD]
                    : false,
                'attr'  => [
                    'tooltip' => 'mautic.integration.yandex_smartcaptcha.fail_open.tooltip',
                ],
            ]
        );
    }

    public function getClientKey(): string
    {
        return trim((string) ($this->keys[self::CLIENT_KEY_FIELD] ?? ''));
    }

    public function getServerKey(): string
    {
        return trim((string) ($this->keys[self::SERVER_KEY_FIELD] ?? ''));
    }

    public function shouldFailOpen(): bool
    {
        return (bool) ($this->keys[self::FAIL_OPEN_FIELD] ?? false);
    }
}
