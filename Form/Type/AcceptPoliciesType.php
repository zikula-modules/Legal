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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\LegalModule\Constant;

class AcceptPoliciesType extends AbstractType
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
        $login = $builder->getData()['login'];

        $builder
            ->add('uid', HiddenType::class)
            ->add('login', HiddenType::class)
            ->add('acceptedpolicies_policies', CheckboxType::class, [
                'data' => true,
                'help' => $this->trans('Check this box to indicate your acceptance of this site\'s policies.'),
                'label' => $this->trans('Policies'),
                'label_attr' => ['class' => 'switch-custom'],
                'constraints' => [
                    new IsTrue(['message' => $this->trans('you must accept this site\'s policies')])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => $login ? $this->trans('Save and continue logging in') : $this->trans('Save'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn-success']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return Constant::FORM_BLOCK_PREFIX;
    }
}
