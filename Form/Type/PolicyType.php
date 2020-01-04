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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\LegalModule\Constant;

class PolicyType extends AbstractType
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
        $constraints = !$options['userEditAccess']
            ? [new IsTrue(['message' => $this->__('you must accept this site\'s policies')])]
            : []
        ;

        $builder->add('acceptedpolicies_policies', CheckboxType::class, [
            'label' => $this->__('Policies'),
            'label_attr' => ['class' => 'switch-custom'],
            'data' => false,
            'help' => $this->__('Check this box to indicate your acceptance of this site\'s policies.'),
            'constraints' => $constraints,
            'required' => !$options['userEditAccess']
        ]);
    }

    public function getBlockPrefix()
    {
        return Constant::FORM_BLOCK_PREFIX;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'userEditAccess' => false
        ]);
    }
}
