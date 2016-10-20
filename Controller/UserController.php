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
use ModUtil;
use SecurityUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SessionUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use System;
use UserUtil;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\LegalModule\Helper\AcceptPoliciesHelper;
use ZLanguage;

/**
 * Class UserController
 */
class UserController extends \Zikula_AbstractController
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
            $url = $this->get('router')->generate('zikulalegalmodule_user_termsofuse', [], RouterInterface::ABSOLUTE_URL);
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
            $url = $this->get('router')->generate('zikulalegalmodule_user_termsofuse', [], RouterInterface::ABSOLUTE_URL);
        }

        return new RedirectResponse($url);
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
     * @param string $documentName      The "name" of the document, as specified by the names of the user and text template
     *                                  files in the format 'legal_user_documentname.tpl' and 'legal_text_documentname.tpl'.
     * @param string $accessInstanceKey The string used in the instance_right part of the permission access key for this document.
     * @param string $activeFlagKey     The string used to name the module variable that indicates whether this legal document is
     *                                  active or not; typically this is a constant from {@link LegalConstant}, such as
     *                                  {@link LegalConstant::MODVAR_LEGALNOTICE_ACTIVE}.
     * @param string $customUrlKey      The string used to name the module variable that contains a custom static URL for the
     *                                  legal document; typically this is a constant from {@link LegalConstant}, such as
     *                                  {@link LegalConstant::MODVAR_TERMS_URL}.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     *
     * @return RedirectResponse|string HTML output string
     */
    private function renderDocument($documentName, $accessInstanceKey, $activeFlagKey, $customUrlKey)
    {
        // Security check
        if (!SecurityUtil::checkPermission($this->name.'::'.$accessInstanceKey, '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }
        if (!$this->getVar($activeFlagKey)) {
            return $this->view->fetch('User/policynotactive.tpl');
        } else {
            $customUrl = $this->getVar($customUrlKey, '');
            if (empty($customUrl)) {
                // work out the template path
                $template = "User/{$documentName}.tpl";
                // get the current users language
                $languageCode = ZLanguage::transformFS(ZLanguage::getLanguageCode());
                if (!$this->view->template_exists("{$languageCode}/legal_text_{$documentName}.tpl")) {
                    $languageCode = 'en';
                }

                // intentionally return non-Response
                return $this->view->assign('languageCode', $languageCode)->fetch($template);
            } else {
                return new RedirectResponse($customUrl);
            }
        }
    }

    /**
     * @Route("/legalnotice")
     *
     * Display Legal notice.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     *
     * @return Response
     */
    public function legalNoticeAction()
    {
        $doc = $this->renderDocument('legalnotice', 'legalNotice', LegalConstant::MODVAR_LEGALNOTICE_ACTIVE, LegalConstant::MODVAR_LEGALNOTICE_URL);

        return new Response($doc);
    }

    /**
     * @Route("/termsofuse")
     *
     * Display Terms of Use
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     *
     * @return Response
     */
    public function termsofuseAction()
    {
        $doc = $this->renderDocument('termsofuse', 'termsOfUse', LegalConstant::MODVAR_TERMS_ACTIVE, LegalConstant::MODVAR_TERMS_URL);

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
        return new RedirectResponse($this->get('router')->generate('zikulalegalmodule_user_privacypolicy', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/privacypolicy")
     *
     * Display Privacy Policy
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     *
     * @return Response
     */
    public function privacyPolicyAction()
    {
        $doc = $this->renderDocument('privacypolicy', 'privacyPolicy', LegalConstant::MODVAR_PRIVACY_ACTIVE, LegalConstant::MODVAR_PRIVACY_URL);

        return new Response($doc);
    }

    /**
     * @Route("/accessibilitystatement")
     *
     * Display Accessibility statement
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     *
     * @return Response
     */
    public function accessibilitystatementAction()
    {
        $doc = $this->renderDocument('accessibilitystatement', 'accessibilityStatement', LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE, LegalConstant::MODVAR_ACCESSIBILITY_URL);

        return new Response($doc);
    }

    /**
     * @Route("/cancellationrightpolicy")
     *
     * Display Cancellation right policy
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     *
     * @return Response
     */
    public function cancellationRightPolicyAction()
    {
        $doc = $this->renderDocument('cancellationrightpolicy', 'cancellationRightPolicy', LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE, LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL);

        return new Response($doc);
    }

    /**
     * @Route("/tradeconditions")
     *
     * Display Trade conditions
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     *
     * @return Response
     */
    public function tradeConditionsAction()
    {
        $doc = $this->renderDocument('tradeconditions', 'tradeConditions', LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE, LegalConstant::MODVAR_TRADECONDITIONS_URL);

        return new Response($doc);
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
     * @throws AccessDeniedException Thrown if the user is not logged in and the acceptance attempt is not a result of a login attempt.
     * @throws \Exception            Thrown if the user is already logged in and the acceptance attempt is a result of a login attempt;
     *                               also thrown in cases where expected data is not present or not in an expected form;
     *                               also thrown if the call to this function is not the result of a POST operation or a GET operation.
     *
     * @return Response
     */
    public function acceptPoliciesAction(Request $request)
    {
        // Retrieve and delete any session variables being sent in by the log-in process before we give the function a chance to
        // throw an exception. We need to make sure no sensitive data is left dangling in the session variables.
        $sessionVars = $request->getSession()->get(
            // @todo check on this value
            'Legal_Controller_User_acceptPolicies',
            null,
            $this->name
        );
        // @todo check this value
        $request->getSession()->remove('Legal_Controller_User_acceptPolicies', $this->name);
        $processed = false;
        $helper = new AcceptPoliciesHelper();
        if ($request->isMethod('POST')) {
            $this->checkCsrfToken();
            $isLogin = isset($sessionVars) && !empty($sessionVars);
            if (!$isLogin && !UserUtil::isLoggedIn()) {
                throw new AccessDeniedException();
            } elseif ($isLogin && UserUtil::isLoggedIn()) {
                throw new \Exception();
            }
            $policiesUid = $request->request->get('acceptedpolicies_uid', false);
            $acceptedPolicies = [
                'termsOfUse'              => $request->request->get('acceptedpolicies_termsofuse', false),
                'privacyPolicy'           => $request->request->get('acceptedpolicies_privacypolicy', false),
                'agePolicy'               => $request->request->get('acceptedpolicies_agepolicy', false),
                'cancellationRightPolicy' => $request->request->get('acceptedpolicies_cancellationrightpolicy', false),
                'tradeConditions'         => $request->request->get('acceptedpolicies_tradeconditions', false),
            ];
            if (!isset($policiesUid) || empty($policiesUid) || !is_numeric($policiesUid)) {
                throw new \Exception();
            }
            $activePolicies = $helper->getActivePolicies();
            $originalAcceptedPolicies = $helper->getAcceptedPolicies($policiesUid);
            $fieldErrors = [];
            if ($activePolicies['termsOfUse'] && !$originalAcceptedPolicies['termsOfUse'] && !$acceptedPolicies['termsOfUse']) {
                $fieldErrors['termsofuse'] = $this->__('You must accept this site\'s Terms of Use in order to proceed.');
            }
            if ($activePolicies['privacyPolicy'] && !$originalAcceptedPolicies['privacyPolicy'] && !$acceptedPolicies['privacyPolicy']) {
                $fieldErrors['privacypolicy'] = $this->__('You must accept this site\'s Privacy Policy in order to proceed.');
            }
            if ($activePolicies['agePolicy'] && !$originalAcceptedPolicies['agePolicy'] && !$acceptedPolicies['agePolicy']) {
                $fieldErrors['agepolicy'] = $this->__f('In order to log in, you must confirm that you meet the requirements of this site\'s Minimum Age Policy. If you are not %1$s years of age or older, and you do not have a parent\'s permission to use this site, then please ask your parent to contact a site administrator.', [ModUtil::getVar($this->name, LegalConstant::MODVAR_MINIMUM_AGE, 0)]);
            }
            if ($activePolicies['cancellationRightPolicy'] && !$originalAcceptedPolicies['cancellationRightPolicy'] && !$acceptedPolicies['cancellationRightPolicy']) {
                $fieldErrors['cancellationrightpolicy'] = $this->__('You must accept our cancellation right policy in order to proceed.');
            }
            if ($activePolicies['tradeConditions'] && !$originalAcceptedPolicies['tradeConditions'] && !$acceptedPolicies['tradeConditions']) {
                $fieldErrors['tradeconditions'] = $this->__('You must accept our general terms and conditions of trade in order to proceed.');
            }
            if (empty($fieldErrors)) {
                $now = new DateTime('now', new DateTimeZone('UTC'));
                $nowStr = $now->format(DateTime::ISO8601);
                if ($activePolicies['termsOfUse'] && $acceptedPolicies['termsOfUse']) {
                    $termsOfUseProcessed = UserUtil::setVar(LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $nowStr, $policiesUid);
                } else {
                    $termsOfUseProcessed = !$activePolicies['termsOfUse'] || $originalAcceptedPolicies['termsOfUse'];
                }
                if ($activePolicies['privacyPolicy'] && $acceptedPolicies['privacyPolicy']) {
                    $privacyPolicyProcessed = UserUtil::setVar(LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $nowStr, $policiesUid);
                } else {
                    $privacyPolicyProcessed = !$activePolicies['privacyPolicy'] || $originalAcceptedPolicies['privacyPolicy'];
                }
                if ($activePolicies['agePolicy'] && $acceptedPolicies['agePolicy']) {
                    $agePolicyProcessed = UserUtil::setVar(LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED, $nowStr, $policiesUid);
                } else {
                    $agePolicyProcessed = !$activePolicies['agePolicy'] || $originalAcceptedPolicies['agePolicy'];
                }
                if ($activePolicies['cancellationRightPolicy'] && $acceptedPolicies['cancellationRightPolicy']) {
                    $cancellationRightPolicyProcessed = UserUtil::setVar(LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED, $nowStr, $policiesUid);
                } else {
                    $cancellationRightPolicyProcessed = !$activePolicies['cancellationRightPolicy'] || $originalAcceptedPolicies['cancellationRightPolicy'];
                }
                if ($activePolicies['tradeConditions'] && $acceptedPolicies['tradeConditions']) {
                    $tradeConditionsProcessed = UserUtil::setVar(LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED, $nowStr, $policiesUid);
                } else {
                    $tradeConditionsProcessed = !$activePolicies['tradeConditions'] || $originalAcceptedPolicies['tradeConditions'];
                }
                $processed = $termsOfUseProcessed && $privacyPolicyProcessed && $agePolicyProcessed && $cancellationRightPolicyProcessed && $tradeConditionsProcessed;
            }
            if ($processed) {
                if ($isLogin) {
                    $path = $request->getSession()->get(
                        // @todo check on this value
                        'Users_Controller_User_login',
                        [],
                        'ZikulaUsersModule'
                    );
                    $path['authentication_method'] = $sessionVars['authentication_method'];
                    $path['authentication_info'] = $sessionVars['authentication_info'];
                    $path['rememberme'] = $sessionVars['rememberme'];
                    $path['_controller'] = 'zikulausersmodule_user_login';

                    $subRequest = $request->duplicate([], null, $path);
                    $httpKernel = $this->get('http_kernel');
                    $response = $httpKernel->handle(
                        $subRequest,
                        HttpKernelInterface::SUB_REQUEST
                    );

                    return $response;
                } else {
                    return new RedirectResponse(System::getHomepageUrl());
                }
            }
        } elseif ($request->isMethod('GET')) {
            $isLogin = $request->query->get('login', false);
            $fieldErrors = [];
        } else {
            throw new AccessDeniedException();
        }
        // If we are coming here from the login process, then there are certain things that must have been
        // send along in the session variable. If not, then error.
        if ($isLogin && (!isset($sessionVars['user_obj'])
                || !is_array($sessionVars['user_obj'])
                || !isset($sessionVars['authentication_info'])
                || !is_array($sessionVars['authentication_info'])
                || !isset($sessionVars['authentication_method'])
                || !is_array($sessionVars['authentication_method']))) {
            throw new \Exception();
        }
        if ($isLogin) {
            $policiesUid = $sessionVars['user_obj']['uid'];
        } else {
            $policiesUid = UserUtil::getVar('uid');
        }
        if (!$policiesUid || empty($policiesUid)) {
            throw new \Exception();
        }
        if ($isLogin) {
            // Pass along the session vars to updateAcceptance. We didn't want to just keep them in the session variable
            // Legal_Controller_User_acceptPolicies because if we hit an exception or got redirected, then the data
            // would have been orphaned, and it contains some sensitive information.
            SessionUtil::requireSession();
            $request->getSession()->set(
                // @todo check this value
                'Legal_Controller_User_acceptPolicies',
                $sessionVars,
                $this->name
            );
        }
        $templateVars = [
            'login'                    => $isLogin,
            'policiesUid'              => $policiesUid,
            'activePolicies'           => $helper->getActivePolicies(),
            'acceptedPolicies'         => isset($acceptedPolicies) ? $acceptedPolicies : $helper->getAcceptedPolicies($policiesUid),
            'originalAcceptedPolicies' => isset($originalAcceptedPolicies) ? $originalAcceptedPolicies : $helper->getAcceptedPolicies($policiesUid),
            'fieldErrors'              => $fieldErrors,
        ];

        return $this->view->assign($templateVars)->fetch('User/acceptpolicies.tpl');
    }
}
