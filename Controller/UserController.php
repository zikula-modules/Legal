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

use DateTime;
use DateTimeZone;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SessionUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\UsersModule\Entity\UserEntity;
use ZLanguage;

/**
 * Class UserController.
 */
class UserController extends AbstractController
{
    /**
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
    public function legalNoticeAction(Request $request)
    {
        $doc = $this->renderDocument('legalNotice', LegalConstant::MODVAR_LEGALNOTICE_ACTIVE, LegalConstant::MODVAR_LEGALNOTICE_URL, $request->getLocale());

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
    public function termsofuseAction(Request $request)
    {
        $doc = $this->renderDocument('termsOfUse', LegalConstant::MODVAR_TERMS_ACTIVE, LegalConstant::MODVAR_TERMS_URL, $request->getLocale());

        return new Response($doc);
    }

    /**
     * @Route("/privacy")
     *
     * Display Privacy Policy.
     *
     * Redirects to {@link privacyPolicy()}.
     *
     * @deprecated Since 1.6.1
     *
     * @return RedirectResponse
     */
    public function privacyAction()
    {
        return $this->redirectToRoute('zikulalegalmodule_user_privacypolicy');
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
    public function privacyPolicyAction(Request $request)
    {
        $doc = $this->renderDocument('privacyPolicy', LegalConstant::MODVAR_PRIVACY_ACTIVE, LegalConstant::MODVAR_PRIVACY_URL, $request->getLocale());

        return new Response($doc);
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
    public function accessibilitystatementAction(Request $request)
    {
        $doc = $this->renderDocument('accessibilityStatement', LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE, LegalConstant::MODVAR_ACCESSIBILITY_URL, $request->getLocale());

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
    public function cancellationRightPolicyAction(Request $request)
    {
        $doc = $this->renderDocument('cancellationRightPolicy', LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE, LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL, $request->getLocale());

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
    public function tradeConditionsAction(Request $request)
    {
        $doc = $this->renderDocument('tradeConditions', LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE, LegalConstant::MODVAR_TRADECONDITIONS_URL, $request->getLocale());

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
    private function renderDocument($documentName, $activeFlagKey, $customUrlKey, $locale = "en")
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

        try {
            $view = $this->renderView('@'.LegalConstant::MODNAME."/{$locale}/{$documentName}.html.twig");
        } catch (\Exception $e) {
            // template does not exist
            $view = $this->renderView('@'.LegalConstant::MODNAME."/User/{$documentName}.html.twig", [
                'languageCode' => 'en',
            ]);
        }

        // intentionally return non-Response
        return $view;
    }

    /**
     * @Route("/acceptpolicies")
     *
     * Allow the user to accept active terms of use and/or privacy policy.
     *
     * This function is currently used by the Legal module's handler for the users.login.veto event.
     *
     * @param Request $request
     *
     * @throws AccessDeniedException Thrown if the user is not logged in and the acceptance attempt is not a result of a login attempt
     * @throws \Exception            Thrown if the user is already logged in and the acceptance attempt is a result of a login attempt;
     *                               also thrown in cases where expected data is not present or not in an expected form;
     *                               also thrown if the call to this function is not the result of a POST operation or a GET operation
     *
     * @return Response
     */
    public function acceptPoliciesAction(Request $request)
    {
        // Retrieve and delete any session variables being sent in by the log-in process before we give the function a chance to
        // throw an exception. We need to make sure no sensitive data is left dangling in the session variables.
        $userIdAttemptingToLogin = $request->getSession()->get(LegalConstant::SESSION_ACCEPT_POLICIES_VAR, null);
        $request->getSession()->remove(LegalConstant::SESSION_ACCEPT_POLICIES_VAR);

        $currentUserApi = $this->get('zikula_users_module.current_user');
        $csrfTokenHandler = $this->get('zikula_core.common.csrf_token_handler');
        $sessionVars = $request->getSession()->all();

        $acceptPoliciesHelper = $this->get('zikula_legal_module.accept_policies_helper');
        if ($request->isMethod('POST')) {
            $csrfTokenHandler->validate($request->request->get('csrftoken'));

            $isLogin = isset($userIdAttemptingToLogin) && !empty($userIdAttemptingToLogin);
            $isLoggedIn = $currentUserApi->isLoggedIn();
            if (!$isLogin && !$isLoggedIn) {
                throw new AccessDeniedException();
            } elseif ($isLogin && $isLoggedIn) {
                throw new \Exception();
            }

            $policiesUid = $request->request->get('acceptedpolicies_uid', false);
            if (!isset($policiesUid) || empty($policiesUid) || !is_numeric($policiesUid)) {
                throw new \Exception();
            }

            $fieldErrors = [];

            $acceptedPolicies = $request->request->get('acceptedpolicies_policies', false);
            /** @var UserEntity $user */
            $user = $this->get('zikula_users_module.user_repository')->find($policiesUid);
            if (!$acceptedPolicies) {
                $fieldErrors['policies'] = $this->__('You must accept this site\'s policies in order to proceed.');
            } else {
                $activePolicies = $acceptPoliciesHelper->getActivePolicies();
                $now = new DateTime('now', new DateTimeZone('UTC'));
                $nowStr = $now->format(DateTime::ISO8601);
                if ($activePolicies['termsOfUse']) {
                    $user->setAttribute(LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $nowStr);
                }
                if ($activePolicies['privacyPolicy']) {
                    $user->setAttribute(LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $nowStr);
                }
                if ($activePolicies['agePolicy']) {
                    $user->setAttribute(LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED, $nowStr);
                }
                if ($activePolicies['tradeConditions']) {
                    $user->setAttribute(LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED, $nowStr);
                }
                if ($activePolicies['cancellationRightPolicy']) {
                    $user->setAttribute(LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED, $nowStr);
                }
                $this->get('doctrine')->getManager()->flush();
            }
            if ($isLogin) {
                $this->get('zikula_users_module.helper.access_helper')->login($user, isset($sessionVars['rememberme']) ? $sessionVars['rememberme'] : false);
            }

            return $this->redirectToRoute('home');
        } elseif ($request->isMethod('GET')) {
            $isLogin = $request->query->get('login', false);
            $fieldErrors = [];
        } else {
            throw new AccessDeniedException();
        }

        // If we are coming here from the login process, then there are certain things that must have been
        // send along in the session variable. If not, then error.
        if ($isLogin && !isset($sessionVars['authenticationMethod'])) {
            throw new \Exception();
        }

        $policiesUid = $isLogin ? $userIdAttemptingToLogin : $currentUserApi->get('uid');
        if (!$policiesUid || empty($policiesUid)) {
            throw new \Exception();
        }

        if ($isLogin) {
            // Pass along the session vars to updateAcceptance. We didn't want to just keep them in the session variable
            // Legal_Controller_User_acceptPolicies because if we hit an exception or got redirected, then the data
            // would have been orphaned, and it contains some sensitive information.
            SessionUtil::requireSession();
            $request->getSession()->set(LegalConstant::SESSION_ACCEPT_POLICIES_VAR, $userIdAttemptingToLogin);
        }

        $templateParameters = [
            'login'                    => $isLogin,
            'policiesUid'              => $policiesUid,
            'activePolicies'           => $acceptPoliciesHelper->getActivePolicies(),
            'acceptedPolicies'         => /*isset($acceptedPolicies) ? $acceptedPolicies : */$acceptPoliciesHelper->getAcceptedPolicies($policiesUid),
            'originalAcceptedPolicies' => isset($originalAcceptedPolicies) ? $originalAcceptedPolicies : $acceptPoliciesHelper->getAcceptedPolicies($policiesUid),
            'fieldErrors'              => $fieldErrors,
            'csrfToken'                => $csrfTokenHandler->generate(),
        ];

        return $this->render('@'.LegalConstant::MODNAME.'/User/acceptPolicies.html.twig', $templateParameters);
    }
}
