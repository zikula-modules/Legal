<?php

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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Zikula\Common\Translator\IdentityTranslator;
use Zikula\LegalModule\Constant;

class AcceptPoliciesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];
        $login = $builder->getData()['login'];

        $builder
            ->add('uid', HiddenType::class)
            ->add('login', HiddenType::class)
            ->add('acceptedpolicies_policies', CheckboxType::class, [
                'data' => true,
                'help' => $translator->__('Check this box to indicate your acceptance of this site\'s policies.'),
                'label' => $translator->__('Policies'),
                'constraints' => [new IsTrue(['message' => $translator->__('you must accept this site\'s policies')])],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $login ? $translator->__('Save and continue logging in') : $translator->__('Save'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn-success']
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return Constant::FORM_BLOCK_PREFIX;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => new IdentityTranslator(),
        ]);
    }
}
