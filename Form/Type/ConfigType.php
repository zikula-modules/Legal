<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\LegalModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\LegalModule\Constant as LegalConstant;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add(LegalConstant::MODVAR_LEGALNOTICE_ACTIVE, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Legal notice'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_TERMS_ACTIVE, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Terms of use'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_PRIVACY_ACTIVE, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Privacy policy'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('General Terms and Conditions of Trade'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Cancellation Right Policy'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Accessibility statement'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_LEGALNOTICE_URL, 'Symfony\Component\Form\Extension\Core\Type\UrlType', [
                'label' => $translator->__('Legal notice'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_TERMS_URL, 'Symfony\Component\Form\Extension\Core\Type\UrlType', [
                'label' => $translator->__('Terms of use'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_PRIVACY_URL, 'Symfony\Component\Form\Extension\Core\Type\UrlType', [
                'label' => $translator->__('Privacy policy'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_TRADECONDITIONS_URL, 'Symfony\Component\Form\Extension\Core\Type\UrlType', [
                'label' => $translator->__('General Terms and Conditions of Trade'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL, 'Symfony\Component\Form\Extension\Core\Type\UrlType', [
                'label' => $translator->__('Cancellation Right Policy'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_ACCESSIBILITY_URL, 'Symfony\Component\Form\Extension\Core\Type\UrlType', [
                'label' => $translator->__('Accessibility statement'),
                'required' => false
            ])
            ->add(LegalConstant::MODVAR_EUCOOKIE, 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Enable cookie warning for EU Compliance'),
                'choices' => [
                    $translator->__('Yes') => 1,
                    $translator->__('No') => 0
                ],
                'required' => false,
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false,
                'help' => $translator->__('Notice: This setting controls the EU cookie warning which is injected into the view and requires user assent.')
            ])
            ->add(LegalConstant::MODVAR_MINIMUM_AGE, 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Minimum age permitted to register'),
                'empty_data' => 13,
                'scale' => 0,
                'max_length' => 2,
                'help' => $translator->__('Enter a positive integer, or 0 for no age check.')
            ])
            ->add('resetagreement', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Reset user group\'s acceptance of \'Terms of use\''),
                'choices' => $options['groupChoices'],
                'choices_as_values' => true,
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'alert' => [$translator->__('Notice: This setting resets the acceptance of the \'Terms of use\' for all users in this group. Next time they want to log-in, they will have to acknowledge their acceptance of them again, and will not be able to log-in if they do not. This action does not affect the main administrator account. You can perform the same operation for individual users by visiting the Users manager in the site admin panel.') => 'info']
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulalegalmodule_config';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'groupChoices' => []
        ]);
    }
}
