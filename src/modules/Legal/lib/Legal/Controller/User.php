<?php
/**
 * Copyright Zikula Foundation 2001 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Legal
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Module controller for user-related operations.
 */
class Legal_Controller_User extends Zikula_AbstractController
{

    /**
     * Legal Module main user function
     *
     * @return string HTML output string
     */
    public function main()
    {
        $this->redirect(ModUtil::url($this->name, 'User', 'termsOfUse'));
    }

    /**
     * Display Terms of Use
     *
     * @return string HTML output string
     */
    public function termsofuse()
    {
        // Security check
        if (!SecurityUtil::checkPermission($this->name . '::termsofuse', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        // work out the template path
        if (!$this->getVar(Legal_Constant::MODVAR_TERMS_ACTIVE)) {
            $template = 'legal_user_policynotactive.tpl';
        } else {
            $template = 'legal_user_termsofuse.tpl';

            // get the current users language
            $languageCode = ZLanguage::transformFS(ZLanguage::getLanguageCode());

            if (!$this->view->template_exists($languageCode.'/legal_text_termsofuse.tpl')) {
                $languageCode = 'en';
            }
        }

        return $this->view->assign('languageCode', $languageCode)
                ->fetch($template);
    }

    /**
     * Display Privacy Policy
     *
     * @deprecated Since 1.6.1
     *
     * @return string HTML output string
     */
    public function privacy()
    {
        $this->redirect(ModUtil::url($this->name, 'user', 'privacyPolicy'));
    }

    /**
     * Display Privacy Policy
     *
     * @return string HTML output string
     */
    public function privacyPolicy()
    {
        // Security check
        if (!SecurityUtil::checkPermission($this->name . '::privacy', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        // work out the template path
        if (!$this->getVar(Legal_Constant::MODVAR_PRIVACY_ACTIVE)) {
            $template = 'legal_user_policynotactive.tpl';
        } else {
            $template = 'legal_user_privacypolicy.tpl';

            // get the current users language
            $languageCode = ZLanguage::transformFS(ZLanguage::getLanguageCode());

            if (!$this->view->template_exists($languageCode.'/legal_text_privacypolicy.tpl')) {
                $languageCode = 'en';
            }
        }

        return $this->view->assign('languageCode', $languageCode)
                ->fetch($template);
    }

    /**
     * Display Accessibility statement
     * 
     * @return string HTML output string
     */
    public function accessibilitystatement()
    {
        // Security check
        if (!SecurityUtil::checkPermission($this->name . '::accessibilitystatement', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        // work out the template path
        if (!$this->getVar(Legal_Constant::MODVAR_ACCESSIBILITY_ACTIVE)) {
            $template = 'legal_user_policynotactive.tpl';
        } else {
            $template = 'legal_user_accessibilitystatement.tpl';

            // get the current users language
            $languageCode = ZLanguage::transformFS(ZLanguage::getLanguageCode());

            if (!$this->view->template_exists($languageCode.'/legal_text_accessibilitystatement.tpl')) {
                $languageCode = 'en';
            }
        }

        return $this->view->assign('languageCode', $languageCode)
                ->fetch($template);
    }

    /**
     * Allow the user to accept active terms of use and/or privacy policy.
     *
     * This function is currently used by the Legal module's handler for the users.login.veto event.
     *
     * @return string The rendered output from the template.
     */
    public function acceptPolicies()
    {
        // Retrieve and delete any session variables being sent in by the log-in process before we give the function a chance to
        // throw an exception. We need to make sure no sensitive data is left dangling in the session variables.
        $sessionVars = $this->request->getSession()->get('Legal_Controller_User_acceptPolicies', null, $this->name);
        $this->request->getSession()->del('Legal_Controller_User_acceptPolicies', $this->name);

        $processed = false;
        $helper = new Legal_Helper_AcceptPolicies();
        
        if ($this->request->isPost()) {
            $this->checkCsrfToken();

            $isLogin = isset($sessionVars) && !empty($sessionVars);

            if (!$isLogin && !UserUtil::isLoggedIn()) {
                throw new Zikula_Exception_Forbidden();
            } elseif ($isLogin && UserUtil::isLoggedIn()) {
                throw new Zikula_Exception_Fatal();
            }

            $policiesUid = $this->request->getPost()->get('acceptedpolicies_uid', false);
            $acceptedPolicies = array(
                'termsOfUse'    => $this->request->getPost()->get('acceptedpolicies_termsofuse', false),
                'privacyPolicy' => $this->request->getPost()->get('acceptedpolicies_privacypolicy', false),
                'agePolicy'     => $this->request->getPost()->get('acceptedpolicies_agepolicy', false),
            );

            if (!isset($policiesUid) || empty($policiesUid) || !is_numeric($policiesUid)) {
                throw new Zikula_Exception_Fatal();
            }
            
            $activePolicies = $helper->getActivePolicies();
            $originalAcceptedPolicies = $helper->getAcceptedPolicies($policiesUid);

            $fieldErrors = array();

            if ($activePolicies['termsOfUse'] && !$originalAcceptedPolicies['termsOfUse'] && !$acceptedPolicies['termsOfUse']) {
                $fieldErrors['termsofuse'] = $this->__('You must accept this site\'s Terms of Use in order to proceed.');
            }

            if ($activePolicies['privacyPolicy'] && !$originalAcceptedPolicies['privacyPolicy'] && !$acceptedPolicies['privacyPolicy']) {
                $fieldErrors['privacypolicy'] = $this->__('You must accept this site\'s Privacy Policy in order to proceed.');
            }

            if ($activePolicies['agePolicy'] && !$originalAcceptedPolicies['agePolicy'] && !$acceptedPolicies['agePolicy']) {
                $fieldErrors['agepolicy'] = $this->__f('In order to log in, you must confirm that you meet the requirements of this site\'s Minimum Age Policy. If you are not %1$s years of age or older, and you do not have a parent\'s permission to use this site, then please ask your parent to contact a site administrator.', array(ModUtil::getVar('Legal', Legal_Constant::MODVAR_MINIMUM_AGE, 0)));
            }

            if (empty($fieldErrors)) {
                $now = new DateTime('now', new DateTimeZone('UTC'));
                $nowStr = $now->format(DateTime::ISO8601);

                if ($activePolicies['termsOfUse'] && $acceptedPolicies['termsOfUse']) {
                    $termsOfUseProcessed = UserUtil::setVar(Legal_Constant::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $nowStr, $policiesUid);
                } else {
                    $termsOfUseProcessed = !$activePolicies['termsOfUse'] || $originalAcceptedPolicies['termsOfUse'];
                }

                if ($activePolicies['privacyPolicy'] && $acceptedPolicies['privacyPolicy']) {
                    $privacyPolicyProcessed = UserUtil::setVar(Legal_Constant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $nowStr, $policiesUid);
                } else {
                    $privacyPolicyProcessed = !$activePolicies['privacyPolicy'] || $originalAcceptedPolicies['privacyPolicy'];
                }

                if ($activePolicies['agePolicy'] && $acceptedPolicies['agePolicy']) {
                    $agePolicyProcessed = UserUtil::setVar(Legal_Constant::ATTRIBUTE_AGEPOLICY_CONFIRMED, $nowStr, $policiesUid);
                } else {
                    $agePolicyProcessed = !$activePolicies['agePolicy'] || $originalAcceptedPolicies['agePolicy'];
                }

                $processed = $termsOfUseProcessed && $privacyPolicyProcessed && $agePolicyProcessed;
            }

            if ($processed) {
                if ($isLogin) {
                    $loginArgs = $this->request->getSession()->get('Users_Controller_User_login', array(), 'Zikula_Users');
                    $loginArgs['authentication_method'] = $sessionVars['authentication_method'];
                    $loginArgs['authentication_info']   = $sessionVars['authentication_info'];
                    $loginArgs['rememberme']            = $sessionVars['rememberme'];
                    return ModUtil::func('Users', 'user', 'login', $loginArgs);
                } else {
                    $this->redirect(System::getHomepageUrl());
                }
            }
        } elseif ($this->request->isGet()) {
            $isLogin = $this->request->getGet()->get('login', false);
            $fieldErrors = array();
        } else {
            throw new Zikula_Exception_Forbidden();
        }

        // If we are coming here from the login process, then there are certain things that must have been
        // send along in the session variable. If not, then error.
        if ($isLogin && (!isset($sessionVars['user_obj']) || !is_array($sessionVars['user_obj'])
                || !isset($sessionVars['authentication_info']) || !is_array($sessionVars['authentication_info'])
                || !isset($sessionVars['authentication_method']) || !is_array($sessionVars['authentication_method']))
                ) {
            throw new Zikula_Exception_Fatal();
        }

        if ($isLogin) {
            $policiesUid = $sessionVars['user_obj']['uid'];
        } else {
            $policiesUid = UserUtil::getVar('uid');
        }

        if (!$policiesUid || empty($policiesUid)) {
            throw new Zikula_Exception_Fatal();
        }

        if ($isLogin) {
            // Pass along the session vars to updateAcceptance. We didn't want to just keep them in the session variable
            // Legal_Controller_User_acceptPolicies because if we hit an exception or got redirected, then the data
            // would have been orphaned, and it contains some sensitive information.
            SessionUtil::requireSession();
            $this->request->getSession()->set('Legal_Controller_User_acceptPolicies', $sessionVars, $this->name);
        }
        
        $templateVars = array(
            'login'                     => $isLogin,
            'policiesUid'               => $policiesUid,
            'activePolicies'            => $helper->getActivePolicies(),
            'acceptedPolicies'          => isset($acceptedPolicies) ? $acceptedPolicies : $helper->getAcceptedPolicies($policiesUid),
            'originalAcceptedPolicies'  => isset($originalAcceptedPolicies) ? $originalAcceptedPolicies : $helper->getAcceptedPolicies($policiesUid),
            'fieldErrors'               => $fieldErrors,
        );

        return $this->view->assign($templateVars)
                ->fetch('legal_user_acceptpolicies.tpl');
    }

    /**
     * Update the user's acceptance of terms of use and/or privacy policy.
     *
     * Available Post Parameters:
     * - array acceptPolicies The array of form values posted.
     *
     * @return void The user is redirected.
     */
    public function updatePolicyAcceptance()
    {
    }
}