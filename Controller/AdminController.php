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

use ModUtil;
use SecurityUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\LegalModule\Constant as LegalConstant;

/**
 * @Route("/admin")
 *
 * Administrator-initiated actions for the Legal module.
 *
 * Class AdminController
 * @package Zikula\LegalModule\Controller
 */
class AdminController extends \Zikula_AbstractController
{
    /**
     * Route not needed here because method is legacy-only
     *
     * The legacy administration entry point.
     *
     * @deprecated
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        return new RedirectResponse($this->get('router')->generate('zikulalegalmodule_admin_modifyconfig', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("")
     *
     * The main administration entry point.
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        return new RedirectResponse($this->get('router')->generate('zikulalegalmodule_admin_modifyconfig', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/config")
     * @Method("GET")
     *
     * Modify configuration.
     *
     * Modify the configuration parameters of the module.
     *
     * @return Response The rendered output of the modifyconfig template.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function modifyconfigAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        // get all groups
        $groups = ModUtil::apiFunc('Groups', 'user', 'getall');
        // add dummy group "all groups" on top
        array_unshift($groups, ['gid' => 0, 'name' => $this->__('All users')]);
        // add dummy group "no groups" on top
        array_unshift($groups, ['gid' => -1, 'name' => $this->__('No groups')]);
        // Assign all the module vars
        $this->view->assign(ModUtil::getVar('legal'))
            ->assign('groups', $groups);

        return new Response($this->view->fetch('Admin/modifyconfig.tpl'));
    }

    /**
     * @Route("/config")
     * @Method("POST")
     *
     * Update the configuration.
     *
     * Save the results of modifying the configuration parameters of the module. Redirects to the module's main page
     * when completed.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     */
    public function updateconfigAction(Request $request)
    {
        // Security check
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        // Confirm the forms authorisation key
        $this->checkCsrfToken();
        // set our module variables
        $legalNoticeActive = $request->request->get(LegalConstant::MODVAR_LEGALNOTICE_ACTIVE, true);
        $this->setVar(LegalConstant::MODVAR_LEGALNOTICE_ACTIVE, $legalNoticeActive);
        $termsOfUseActive = $request->request->get(LegalConstant::MODVAR_TERMS_ACTIVE, false);
        $this->setVar(LegalConstant::MODVAR_TERMS_ACTIVE, $termsOfUseActive);
        $privacyPolicyActive = $request->request->get(LegalConstant::MODVAR_PRIVACY_ACTIVE, false);
        $this->setVar(LegalConstant::MODVAR_PRIVACY_ACTIVE, $privacyPolicyActive);
        $accessibilityStmtActive = $request->request->get(LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE, false);
        $this->setVar(LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE, $accessibilityStmtActive);
        $tradeConditionsActive = $request->request->get(LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE, false);
        $this->setVar(LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE, $tradeConditionsActive);
        $cancellationRightPolicyActive = $request->request->get(LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE, false);
        $this->setVar(LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE, $cancellationRightPolicyActive);
        $legalNoticeUrl = $request->request->get(LegalConstant::MODVAR_LEGALNOTICE_URL, '');
        $this->setVar(LegalConstant::MODVAR_LEGALNOTICE_URL, $legalNoticeUrl);
        $termsOfUseUrl = $request->request->get(LegalConstant::MODVAR_TERMS_URL, '');
        $this->setVar(LegalConstant::MODVAR_TERMS_URL, $termsOfUseUrl);
        $privacyPolicyUrl = $request->request->get(LegalConstant::MODVAR_PRIVACY_URL, '');
        $this->setVar(LegalConstant::MODVAR_PRIVACY_URL, $privacyPolicyUrl);
        $accessibilityStmtUrl = $request->request->get(LegalConstant::MODVAR_ACCESSIBILITY_URL, '');
        $this->setVar(LegalConstant::MODVAR_ACCESSIBILITY_URL, $accessibilityStmtUrl);
        $tradeConditionsUrl = $request->request->get(LegalConstant::MODVAR_TRADECONDITIONS_URL, '');
        $this->setVar(LegalConstant::MODVAR_TRADECONDITIONS_URL, $tradeConditionsUrl);
        $cancellationRightPolicyUrl = $request->request->get(LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL, '');
        $this->setVar(LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL, $cancellationRightPolicyUrl);
        $minimumAge = $request->request->get(LegalConstant::MODVAR_MINIMUM_AGE, 0);
        $this->setVar(LegalConstant::MODVAR_MINIMUM_AGE, $minimumAge);
        $euCookieAccepted = $request->request->get(LegalConstant::MODVAR_EUCOOKIE, false);
        $this->setVar(LegalConstant::MODVAR_EUCOOKIE, $euCookieAccepted);
        $resetagreement = $request->request->get('resetagreement', -1);
        if ($resetagreement != -1) {
            ModUtil::apiFunc($this->name, 'admin', 'resetagreement', ['gid' => $resetagreement]);
        }
        // the module configuration has been updated successfuly
        $request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved module configuration.'));

        return new RedirectResponse($this->get('router')->generate('zikulalegalmodule_admin_index', [], RouterInterface::ABSOLUTE_URL));
    }
}
