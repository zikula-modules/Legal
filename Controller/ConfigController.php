<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\LegalModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ConfigController.
 *
 * @Route("/config")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template
     *
     * @param Request $request
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     *
     * @return array
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission(LegalConstant::MODNAME.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $booleanVars = [
            LegalConstant::MODVAR_LEGALNOTICE_ACTIVE,
            LegalConstant::MODVAR_TERMS_ACTIVE,
            LegalConstant::MODVAR_PRIVACY_ACTIVE,
            LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE,
            LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE,
            LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE,
        ];

        $dataValues = $this->getVars();
        foreach ($booleanVars as $booleanVar) {
            $dataValues[$booleanVar] = (bool) $dataValues[$booleanVar];
        }

        // build choices for user group selector
        $groupChoices = [
            $this->__('All users') => 0,
        ];

        // get all user groups
        $groups = $this->get('zikula_groups_module.group_repository')->findAll();
        foreach ($groups as $group) {
            $groupChoices[$group->getName()] = $group->getGid();
        }

        $form = $this->createForm('Zikula\LegalModule\Form\Type\ConfigType',
            $dataValues, [
                'translator'   => $this->get('translator.default'),
                'groupChoices' => $groupChoices,
            ]
        );

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();
                foreach ($booleanVars as $booleanVar) {
                    $formData[$booleanVar] = ($formData[$booleanVar] == true ? 1 : 0);
                }

                $resetAgreementGroupId = -1;
                if (isset($formData['resetagreement'])) {
                    $resetAgreementGroupId = $formData['resetagreement'];
                    unset($formData['resetagreement']);
                }

                // save modvars
                $this->setVars($formData);

                if ($resetAgreementGroupId != -1) {
                    $resetHelper = $this->get('zikula_legal_module.reset_agreement_helper');
                    $resetHelper->reset($resetAgreementGroupId);
                }

                $this->addFlash('status', $this->__('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
