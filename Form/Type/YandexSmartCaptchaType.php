<?php

declare(strict_types=1);

namespace MauticPlugin\MauticYandexCaptchaBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use MauticPlugin\MauticYandexCaptchaBundle\Integration\YandexSmartCaptchaIntegration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class YandexSmartCaptchaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('language', ChoiceType::class, [
                'label'    => 'mautic.form.field.yandex_smartcaptcha.language',
                'required' => false,
                'data'     => $options['data']['language'] ?? 'auto',
                'choices'  => [
                    'mautic.form.field.yandex_smartcaptcha.language.auto' => 'auto',
                    'English' => 'en',
                    'Russian' => 'ru',
                    'Belarusian' => 'be',
                    'Kazakh' => 'kk',
                    'Tatar' => 'tt',
                    'Ukrainian' => 'uk',
                    'Uzbek' => 'uz',
                    'Turkish' => 'tr',
                ],
                'label_attr' => [
                    'class' => 'control-label',
                ],
                'attr' => [
                    'tooltip' => 'mautic.form.field.yandex_smartcaptcha.language.tooltip',
                ],
            ])
            ->add('reserveHeight', YesNoButtonGroupType::class, [
                'label' => 'mautic.form.field.yandex_smartcaptcha.reserve_height',
                'data'  => array_key_exists('reserveHeight', $options['data'] ?? [])
                    ? (bool) $options['data']['reserveHeight']
                    : true,
                'attr' => [
                    'tooltip' => 'mautic.form.field.yandex_smartcaptcha.reserve_height.tooltip',
                ],
            ]);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function getBlockPrefix()
    {
        return YandexSmartCaptchaIntegration::NAME;
    }
}
