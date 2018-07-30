<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\LegalModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\LegalModule\Form\Type\AcceptPoliciesType;

/**
 * Class UserController.
 */
class UserController extends AbstractController
{
    /**
     * @deprecated
     * Route not needed here because method is legacy-only.
     *
     * Legal Module main user function.
     *
     * Redirects to the Terms of Use legal document.
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        $url = $this->getVar(LegalConstant::MODVAR_TERMS_URL, '');
        if (empty($url)) {
            $url = $this->get('router')->generate('zikulalegalmodule_user_termsofuse');
        }

        return new RedirectResponse($url);
    }

    /**
     * @Route("")
     *
     * Legal Module main user function.
     *
     * Redirects to the Terms of Use legal document.
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        $url = $this->getVar(LegalConstant::MODVAR_TERMS_URL, '');
        if (empty($url)) {
            $url = $this->get('router')->generate('zikulalegalmodule_user_termsofuse');
        }

        return new RedirectResponse($url);
    }

    /**
     * @Route("/legalnotice")
     *
     * Display Legal notice.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     *
     * @return Response
     */
    public function legalNoticeAction()
    {
        $doc = $this->renderDocument('legalNotice', LegalConstant::MODVAR_LEGALNOTICE_ACTIVE, LegalConstant::MODVAR_LEGALNOTICE_URL);

        return new Response($doc);
    }

    /**
     * @Route("/termsofuse")
     *
     * Display Terms of Use
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     *
     * @return Response
     */
    public function termsofuseAction()
    {
        $doc = $this->renderDocument('termsOfUse', LegalConstant::MODVAR_TERMS_ACTIVE, LegalConstant::MODVAR_TERMS_URL);

        return new Response($doc);
    }

    /**
     * @Route("/privacypolicy")
     *
     * Display Privacy Policy
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     *
     * @return Response
     */
    public function privacyPolicyAction()
    {
        $doc = $this->renderDocument('privacyPolicy', LegalConstant::MODVAR_PRIVACY_ACTIVE, LegalConstant::MODVAR_PRIVACY_URL);

        $response = new Response($doc);

        $response->headers->set('X-Robots-Tag', 'noindex');

        return $response;
    }

    /**
     * @Route("/accessibilitystatement")
     *
     * Display Accessibility statement
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     *
     * @return Response
     */
    public function accessibilitystatementAction()
    {
        $doc = $this->renderDocument('accessibilityStatement', LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE, LegalConstant::MODVAR_ACCESSIBILITY_URL);

        return new Response($doc);
    }

    /**
     * @Route("/cancellationrightpolicy")
     *
     * Display Cancellation right policy
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     *
     * @return Response
     */
    public function cancellationRightPolicyAction()
    {
        $doc = $this->renderDocument('cancellationRightPolicy', LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE, LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL);

        return new Response($doc);
    }

    /**
     * @Route("/tradeconditions")
     *
     * Display Trade conditions
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     *
     * @return Response
     */
    public function tradeConditionsAction()
    {
        $doc = $this->renderDocument('tradeConditions', LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE, LegalConstant::MODVAR_TRADECONDITIONS_URL);

        return new Response($doc);
    }

    /**
     * Render and display the specified legal document, or redirect to the specified custom URL if it exists.
     *
     * If a custom URL for the legal document exists, as specified by the module variable identified by $customUrlKey, then
     * this function will redirect the user to that URL.
     *
     * If no custom URL exists, then this function will render and return the appropriate template for the legal document, as
     * specified by $documentName. If the legal document
     *
     * @param string $documentName  The "name" of the document, as specified by the names of the user and text template
     *                              files in the format 'documentname.html.twig'
     * @param string $activeFlagKey The string used to name the module variable that indicates whether this legal document is
     *                              active or not; typically this is a constant from {@link LegalConstant}, such as
     *                              {@link LegalConstant::MODVAR_LEGALNOTICE_ACTIVE}
     * @param string $customUrlKey  The string used to name the module variable that contains a custom static URL for the
     *                              legal document; typically this is a constant from {@link LegalConstant}, such as
     *                              {@link LegalConstant::MODVAR_TERMS_URL}
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     *
     * @return RedirectResponse|string HTML output string
     */
    private function renderDocument($documentName, $activeFlagKey, $customUrlKey)
    {
        // Security check
        if (!$this->hasPermission(LegalConstant::MODNAME.'::'.$documentName, '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        if (!$this->getVar($activeFlagKey)) {
            // intentionally return non-Response
            return $this->renderView('@'.LegalConstant::MODNAME.'/User/policyNotActive.html.twig');
        }

        $customUrl = $this->getVar($customUrlKey, '');
        if (!empty($customUrl)) {
            return $this->redirect($customUrl);
        }

        $view = $this->renderView('@'.LegalConstant::MODNAME."/User/{$documentName}.html.twig");

        // intentionally return non-Response
        return $view;
    }

    /**
     * @Route("/acceptpolicies")
     * @Template("ZikulaLegalModule:User:acceptPolicies.html.twig")
     *
     * @param Request $request
     * @return Response|array
     */
    public function acceptPoliciesAction(Request $request)
    {
        // Retrieve and delete any session variables being sent in by the log-in process before we give the function a chance to
        // throw an exception. We need to make sure no sensitive data is left dangling in the session variables.
        $uid = $request->getSession()->get(LegalConstant::FORCE_POLICY_ACCEPTANCE_SESSION_UID_KEY, null);
        $request->getSession()->remove(LegalConstant::FORCE_POLICY_ACCEPTANCE_SESSION_UID_KEY);
        $currentUserApi = $this->get('zikula_users_module.current_user');

        if (isset($uid)) {
            $login = true;
        } else {
            $login = false;
            $uid = $currentUserApi->get('uid');
        }

        $acceptPoliciesHelper = $this->get('zikula_legal_module.accept_policies_helper');
        $form = $this->createForm(AcceptPoliciesType::class, [
            'uid' => $uid,
            'login' => $login
        ]);
        if ($form->handleRequest($request)->isValid()) {
            $data = $form->getData();
            $userEntity = $this->get('zikula_users_module.user_repository')->find($data['uid']);
            $policiesToCheck = [
                'termsOfUse' => LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED,
                'privacyPolicy' => LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED,
                'agePolicy' => LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED,
                'tradeConditions' => LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED,
                'cancellationRightPolicy' => LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED,
            ];
            $nowUTC = new \DateTime('now', new \DateTimeZone('UTC'));
            $nowUTCStr = $nowUTC->format(\DateTime::ISO8601);
            $activePolicies = $acceptPoliciesHelper->getActivePolicies();
            foreach ($policiesToCheck as $policyName => $acceptedVar) {
                if ($data['acceptedpolicies_policies'] && $activePolicies[$policyName]) {
                    $userEntity->setAttribute($acceptedVar, $nowUTCStr);
                } else {
                    $userEntity->delAttribute($acceptedVar);
                }
            }
            $this->get('doctrine')->getManager()->flush();
            if ($data['acceptedpolicies_policies'] && $data['login']) {
                $this->get('zikula_users_module.helper.access_helper')->login($userEntity);

                return $this->redirectToRoute('zikulausersmodule_account_menu');
            }

            return $this->redirectToRoute('home');
        }

        return $templateParameters = [
            'login' => $login,
            'form' => $form->createView(),
            'activePolicies' => $acceptPoliciesHelper->getActivePolicies(),
            'acceptedPolicies' => $acceptPoliciesHelper->getAcceptedPolicies($uid),
        ];
    }
}
