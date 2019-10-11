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

namespace Zikula\LegalModule\Controller;

use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\LegalModule\Form\Type\ConfigType;
use Zikula\LegalModule\Helper\ResetAgreementHelper;
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
     * @Template("ZikulaLegalModule:Config:config.html.twig")
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @throws Exception
     *
     * @return array|RedirectResponse
     */
    public function configAction(
        Request $request,
        GroupRepositoryInterface $groupRepository,
        ResetAgreementHelper $resetAgreementHelper
    ) {
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
        $groups = $groupRepository->findAll();
        foreach ($groups as $group) {
            $groupChoices[$group->getName()] = $group->getGid();
        }

        $form = $this->createForm(ConfigType::class, $dataValues, [
            'groupChoices' => $groupChoices,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();
                foreach ($booleanVars as $booleanVar) {
                    $formData[$booleanVar] = (true === $formData[$booleanVar] ? 1 : 0);
                }

                $resetAgreementGroupId = -1;
                if (isset($formData['resetagreement'])) {
                    $resetAgreementGroupId = $formData['resetagreement'];
                    unset($formData['resetagreement']);
                }

                // save modvars
                $this->setVars($formData);

                if (-1 !== $resetAgreementGroupId) {
                    $resetAgreementHelper->reset($resetAgreementGroupId);
                }

                $this->addFlash('status', $this->__('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            // redirecting prevents values from being repeated in the form
            return $this->redirectToRoute('zikulalegalmodule_config_config');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
