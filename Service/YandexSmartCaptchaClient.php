<?php

declare(strict_types=1);

namespace MauticPlugin\MauticYandexCaptchaBundle\Service;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use MauticPlugin\MauticYandexCaptchaBundle\Integration\YandexSmartCaptchaIntegration;

class YandexSmartCaptchaClient
{
    public const VALIDATION_URL = 'https://smartcaptcha.cloud.yandex.ru/validate';

    private ?string $serverKey = null;
    private bool $failOpen = false;

    public function __construct(IntegrationHelper $integrationHelper)
    {
        $integrationObject = $integrationHelper->getIntegrationObject(YandexSmartCaptchaIntegration::NAME);

        if ($integrationObject instanceof AbstractIntegration) {
            $keys = $integrationObject->getKeys();

            $this->serverKey = trim((string) ($keys[YandexSmartCaptchaIntegration::SERVER_KEY_FIELD] ?? ''));
            $this->failOpen = (bool) ($keys[YandexSmartCaptchaIntegration::FAIL_OPEN_FIELD] ?? false);
        }
    }

    public function verify(string $token, ?string $remoteIp = null): bool
    {
        $token = trim($token);

        if ('' === $token || empty($this->serverKey)) {
            return false;
        }

        $params = [
            'secret' => $this->serverKey,
            'token'  => $token,
        ];

        if ($remoteIp) {
            $params['ip'] = $remoteIp;
        }

        try {
            $client = new GuzzleClient(['timeout' => 10]);
            $response = $client->post(self::VALIDATION_URL, ['form_params' => $params]);
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            if (200 !== $statusCode) {
                return $this->failOpen;
            }

            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) && 'ok' === ($decoded['status'] ?? null);
        } catch (GuzzleException | JsonException) {
            return $this->failOpen;
        }
    }
}
