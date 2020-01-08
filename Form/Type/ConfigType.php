<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\LegalModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\LegalModule\Constant as LegalConstant;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(LegalConstant::MODVAR_LEGALNOTICE_ACTIVE, CheckboxType::class, [
                'label'    => $this->trans('Legal notice'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_TERMS_ACTIVE, CheckboxType::class, [
                'label'    => $this->trans('Terms of use'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_PRIVACY_ACTIVE, CheckboxType::class, [
                'label'    => $this->trans('Privacy policy'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE, CheckboxType::class, [
                'label'    => $this->trans('General terms and conditions of trade'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE, CheckboxType::class, [
                'label'    => $this->trans('Cancellation right policy'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE, CheckboxType::class, [
                'label'    => $this->trans('Accessibility statement'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_LEGALNOTICE_URL, UrlType::class, [
                'label'    => $this->trans('Legal notice'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_TERMS_URL, UrlType::class, [
                'label'    => $this->trans('Terms of use'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_PRIVACY_URL, UrlType::class, [
                'label'    => $this->trans('Privacy policy'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_TRADECONDITIONS_URL, UrlType::class, [
                'label'    => $this->trans('General terms and conditions of trade'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL, UrlType::class, [
                'label'    => $this->trans('Cancellation right policy'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_ACCESSIBILITY_URL, UrlType::class, [
                'label'    => $this->trans('Accessibility statement'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_EUCOOKIE, ChoiceType::class, [
                'label'   => $this->trans('Enable cookie warning for EU compliance'),
                'label_attr' => ['class' => 'radio-custom'],
                'choices' => [
                    $this->trans('Yes') => 1,
                    $this->trans('No')  => 0,
                ],
                'expanded'    => true,
                'multiple'    => false,
                'help'        => $this->trans('Notice: This setting controls the EU cookie warning which is injected into the view and requires user assent.')
            ])
            ->add(LegalConstant::MODVAR_MINIMUM_AGE, IntegerType::class, [
                'label'       => $this->trans('Minimum age permitted to register'),
                'constraints' => [
                    new GreaterThanOrEqual(0),
                    new LessThanOrEqual(99)
                ],
                'empty_data'  => 13,
                'attr'        => [
                    'maxlength' => 2
                ],
                'help'        => $this->trans('Enter a positive integer, or 0 for no age check.')
            ])
            ->add('resetagreement', ChoiceType::class, [
                'label'             => $this->trans('Reset user group\'s acceptance of site policies'),
                'choices'           => $options['groupChoices'],
                'required'          => false,
                'expanded'          => false,
                'multiple'          => false,
                'help'              => $this->trans('Leave blank to leave users unaffected.'),
                'alert'             => [$this->trans('Notice: This setting resets the acceptance of the site policies for all users in this group. Next time they want to log-in, they will have to acknowledge their acceptance of them again, and will not be able to log-in if they do not. This action does not affect the main administrator account. You can perform the same operation for individual users by visiting the Users manager in the site admin panel.') => 'info']
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->trans('Save'),
                'icon'  => 'fa-check',
                'attr'  => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->trans('Cancel'),
                'icon'  => 'fa-times',
                'attr'  => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulalegalmodule_config';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'groupChoices' => []
        ]);
    }
}
