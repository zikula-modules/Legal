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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Zikula\Common\Translator\IdentityTranslator;
use Zikula\LegalModule\Constant;

class PolicyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];
        $constraints = !$options['userEditAccess']
            ? [new IsTrue(['message' => $translator->__('you must accept this site\'s policies')])]
            : [];

        $builder
            ->add('acceptedpolicies_policies', CheckboxType::class, [
                'data' => false,
                'help' => $translator->__('Check this box to indicate your acceptance of this site\'s policies.'),
                'label' => $translator->__('Policies'),
                'constraints' => $constraints,
                'required' => !$options['userEditAccess']
            ]);
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
            'userEditAccess' => false
        ]);
    }
}
