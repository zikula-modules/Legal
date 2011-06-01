<?php
/**
 * Copyright 2011 Zikula Foundation.
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
 * Handles hook-like event notifications from log-in and registration for the acceptance of policies.
 */
class Legal_Listener_AcceptPolicies extends Zikula_AbstractEventHandler
{
    /**
     * Similar to a hook area, the event
     *
     * @var string
     */
    const EVENT_KEY = 'acceptpolicies';

    /**
     * Convenience access to the module name.
     *
     * @var string
     */
    protected $name;

    /**
     * Access to the Zikula_View instance for this module.
     *
     * @var Zikula_View
     */
    protected $view;

    /**
     * Access to the request instance.
     *
     * @var Zikula_Request_Request
     */
    protected $request;

    /**
     * Access to the helper.
     *
     * @param Legal_Helper_AcceptPolicies
     */
    protected $helper;

    /**
     * Constructs a new instance of this class.
     *
     * @param Zikula_EventManager $serviceManager The current service manager instance.
     */
    public function  __construct(Zikula_EventManager $eventManager)
    {
        parent::__construct($eventManager);

        $this->name = Legal_Constant::MODNAME;
        $this->view = Zikula_View::getInstance($this->name);
        $this->request = $this->serviceManager->getService('request');
        $this->domain = ZLanguage::getModuleDomain($this->name);

        $this->helper = new Legal_Helper_AcceptPolicies();
    }

    public function setupHandlerDefinitions()
    {
        $this->addHandlerDefinition('module.users.ui.display_view', 'uiView');
        
        $this->addHandlerDefinition('module.users.ui.form_edit.login_screen', 'uiEdit');
        $this->addHandlerDefinition('module.users.ui.form_edit.new_user', 'uiEdit');
        $this->addHandlerDefinition('module.users.ui.form_edit.modify_user', 'uiEdit');
        $this->addHandlerDefinition('module.users.ui.form_edit.new_registration', 'uiEdit');
        $this->addHandlerDefinition('module.users.ui.form_edit.modify_registration', 'uiEdit');
        
        $this->addHandlerDefinition('module.users.ui.validate_edit.login_screen', 'validateEdit');
        $this->addHandlerDefinition('module.users.ui.validate_edit.new_user', 'validateEdit');
        $this->addHandlerDefinition('module.users.ui.validate_edit.modify_user', 'validateEdit');
        $this->addHandlerDefinition('module.users.ui.validate_edit.new_registration', 'validateEdit');
        $this->addHandlerDefinition('module.users.ui.validate_edit.modify_registration', 'validateEdit');
        
        $this->addHandlerDefinition('module.users.ui.process_edit.login_screen', 'processEdit');
        $this->addHandlerDefinition('module.users.ui.process_edit.new_user', 'processEdit');
        $this->addHandlerDefinition('module.users.ui.process_edit.modify_user', 'processEdit');
        $this->addHandlerDefinition('module.users.ui.process_edit.new_registration', 'processEdit');
        $this->addHandlerDefinition('module.users.ui.process_edit.modify_registration', 'processEdit');
    }

    /**
     * Responds to ui.view hook-like event notifications.
     *
     * @param Zikula_Event $event The event that triggered this function call.
     */
    public function uiView(Zikula_Event $event)
    {
        $activePolicies = $this->helper->getActivePolicies();
        $activePolicyCount = array_sum($activePolicies);

        $user = $event->getSubject();

        if (isset($user) && !empty($user) && ($activePolicyCount > 0)) {
            $showPolicies = false;
            $acceptedPolicies = $this->helper->getAcceptedPolicies($user['uid']);
            $viewablePolicies = $this->helper->getViewablePolicies($user['uid']);

            if (array_sum($viewablePolicies) > 0) {
                $templateVars = array(
                    'activePolicies'    => $activePolicies,
                    'viewablePolicies'  => $viewablePolicies,
                    'acceptedPolicies'  => $acceptedPolicies,
                );
                $this->view->assign($templateVars);

                $event->data[self::EVENT_KEY] = $this->view->fetch('legal_acceptpolicies_ui_view.tpl');
            }
        }
    }

    /**
     * Responds to ui.edit hook notifications.
     *
     * @param Zikula_Event $event The event that triggered this function call.
     */
    public function uiEdit(Zikula_Event $event)
    {
        $activePolicies = $this->helper->getActivePolicies();
        $activePolicyCount = array_sum($activePolicies);
        if ($activePolicyCount > 0) {
            $showPolicies = false;
            $eventName = $event->getName();

            // Determine if the hook should be displayed, and also set up certain variables, based on the type of event
            // being handled, the state of the subject user account, and who is currently logged in.
            if (!UserUtil::isLoggedIn()) {
                // If the user is not logged in, then the only two scenarios where we would show the hook contents is if
                // the user is trying to log in and it was vetoed because one or more policies need to be accepted, or if
                // the user is looking at the new user registration form.

                $user = $event->getSubject();
                if (!isset($user) || empty($user)) {
                    $user = array(
                        '__ATTRIBUTES__' => array(),
                    );
                }

                if ($eventName == 'module.users.ui.login_screen.form_edit') {
                    // It is not shown unless we have a user record (meaning that the first log-in attempt was vetoed.
                    if (isset($user) && !empty($user) && isset($user['uid']) && !empty($user['uid'])) {
                        $acceptedPolicies = $this->helper->getAcceptedPolicies($user['uid']);

                        // We only show the policies if one or more active policies have not been accepted by the user.
                        if (($activePolicies['termsOfUse'] && !$acceptedPolicies['termsOfUse'])
                                || ($activePolicies['privacyPolicy'] && !$acceptedPolicies['privacyPolicy'])
                                || ($activePolicies['agePolicy'] && !$acceptedPolicies['agePolicy'])
                                ) {
                            $templateVars = array(
                                'policiesUid'               => $user['uid'],
                                'activePolicies'            => $activePolicies,
                                'originalAcceptedPolicies'  => $acceptedPolicies,
                                'acceptedPolicies'          => (isset($this->validation)) ? $this->validation->getObject() : $acceptedPolicies,
                                'fieldErrors'               => (isset($this->validation) && $this->validation->hasErrors()) ? $this->validation->getErrors() : array(),
                            );
                            $this->view->assign($templateVars);

                            $event->data[self::EVENT_KEY] = $this->view->fetch('legal_acceptpolicies_ui_edit_login.tpl');
                        }
                    }
                } else {
                    $acceptedPolicies = (isset($this->validation)) ? $this->validation->getObject() : $this->helper->getAcceptedPolicies();

                    $templateVars = array(
                        'activePolicies'    => $activePolicies,
                        'acceptedPolicies'  => $acceptedPolicies,
                        'fieldErrors'       => (isset($this->validation) && $this->validation->hasErrors()) ? $this->validation->getErrors() : array(),
                    );
                    $this->view->assign($templateVars);

                    $event->data[self::EVENT_KEY] = $this->view->fetch('legal_acceptpolicies_ui_edit_registration.tpl');
                }
            } else {
                // The user is logged in. A few possibilities here. The user is editing his own account information,
                // the user is someone with ACCESS_MODERATE access to the policies, but ACCESS_EDIT to the account and is editing the
                // account information (view-only access to the policies in that case), or the user is someone with ACCESS_EDIT access
                // to the policies.
                $user = $event->getSubject();

                if (isset($this->validation)) {
                    $acceptedPolicies = $this->validation->getObject();
                } else {
                    $acceptedPolicies = $this->helper->getAcceptedPolicies(isset($user) ? $user['uid'] : null);
                }

                $viewablePolicies = $this->helper->getViewablePolicies(isset($user) ? $user['uid'] : null);
                $editablePolicies = $this->helper->getEditablePolicies();

                if ((array_sum($viewablePolicies) > 0) || (array_sum($editablePolicies) > 0)) {
                    $templateVars = array(
                        'policiesUid'       => isset($user) ? $user['uid'] : '',
                        'activePolicies'    => $activePolicies,
                        'viewablePolicies'  => $viewablePolicies,
                        'editablePolicies'  => $editablePolicies,
                        'acceptedPolicies'  => $acceptedPolicies,
                        'fieldErrors'       => (isset($this->validation) && $this->validation->hasErrors()) ? $this->validation->getErrors() : array(),
                    );
                    $this->view->assign($templateVars);

                    $event->data[self::EVENT_KEY] = $this->view->fetch('legal_acceptpolicies_ui_edit.tpl');
                }
            }
        }
    }

    /**
     * Responds to validate.edit hook notifications.
     *
     * @param Zikula_Event $event The event that triggered this function call.
     */
    public function validateEdit(Zikula_Event $event)
    {
        $activePolicies = $this->helper->getActivePolicies();
        $eventName = $event->getName();

        if (!UserUtil::isLoggedIn()) {
            $user = $event->getSubject();
            if (!isset($user) || empty($user)) {
                $user = array(
                    '__ATTRIBUTES__' => array(),
                );
            }

            if ($eventName == 'users.login.validate_edit') {
                // See if there is anything for validation to do.
                if ($this->request->isPost() && $this->request->getPost()->has('acceptedpolicies_uid')) {
                    $policiesAcceptedAtRegistration = array(
                        'termsOfUse'    => $this->request->getPost()->get('acceptedpolicies_termsofuse', false),
                        'privacyPolicy' => $this->request->getPost()->get('acceptedpolicies_privacypolicy', false),
                        'agePolicy'     => $this->request->getPost()->get('acceptedpolicies_agepolicy', false),
                    );
                    $uid = $this->request->getPost()->get('acceptedpolicies_uid', false);
                    $goodUidAcceptPolicies = isset($uid) && !empty($uid) && is_numeric($uid) && ($uid > 2);

                    $user = $event->getSubject();
                    $goodUidUser = isset($user) && !empty($user) && is_array($user) && isset($user['uid']) && is_numeric($user['uid']) && ($user['uid'] > 2);

                    // Fail if the uid of the subject does not match the uid from the form. The user changed his
                    // login information, so not only should we not validate what was posted, we should not allow the user
                    // to proceed with this login attempt at all.
                    if ($goodUidUser && $goodUidAcceptPolicies && ($user['uid'] == $uid)) {
                        $acceptedPolicies = $this->helper->getAcceptedPolicies($uid);

                        $this->validation = new Zikula_Hook_ValidationResponse($uid, $policiesAcceptedAtRegistration);

                        if ($activePolicies['termsOfUse'] && !$acceptedPolicies['termsOfUse'] && (!isset($policiesAcceptedAtRegistration['termsOfUse']) || empty($policiesAcceptedAtRegistration['termsOfUse']) || !$policiesAcceptedAtRegistration['termsOfUse'])) {
                            $this->validation->addError('termsofuse', __('In order to log in, you must accept this site\'s Terms of Use.', $this->domain));
                        }

                        if ($activePolicies['privacyPolicy'] && !$acceptedPolicies['privacyPolicy'] && (!isset($policiesAcceptedAtRegistration['privacyPolicy']) || empty($policiesAcceptedAtRegistration['privacyPolicy']) || !$policiesAcceptedAtRegistration['privacyPolicy'])) {
                            $this->validation->addError('privacypolicy', __('In order to log in, you must accept this site\'s Privacy Policy.', $this->domain));
                        }

                        if ($activePolicies['agePolicy'] && !$acceptedPolicies['agePolicy'] && (!isset($policiesAcceptedAtRegistration['agePolicy']) || empty($policiesAcceptedAtRegistration['agePolicy']) || !$policiesAcceptedAtRegistration['agePolicy'])) {
                            $this->validation->addError('agepolicy', __f('In order to log in, you must confirm that you meet the requirements of this site\'s Minimum Age Policy. If you are not %1$s years of age or older, and you do not have a parent\'s permission to use this site, then please ask your parent to contact a site administrator.', array(ModUtil::getVar('Legal', Legal_Constant::MODVAR_MINIMUM_AGE, 0)), $this->domain));
                        }


                        $event->data->set(self::EVENT_KEY, $this->validation);
                    } elseif (!$goodUidUser || !$goodUidAcceptPolicies) {
                        throw new Zikula_Exception_Fatal();
                    } else {
                        LogUtil::registerError(__('Sorry! You changed your authentication information, and one or more items displayed on the login screen may not have been applicable for your account. Please try logging in again.', $this->domain));
                        $this->request->getSession()->clearNamespace('Zikula_Users');
                        $this->request->getSession()->clearNamespace('Legal');
                        throw new Zikula_Exception_Redirect(ModUtil::url('Users', 'user', 'login'));
                    }
                } elseif (!$this->request->isPost()) {
                    throw new Zikula_Exception_Forbidden();
                }
            } else {
                // See if there is anything for validation to do.
                if ($this->request->isPost() && $this->request->getPost()->has('acceptedpolicies_uid')) {
                    $policiesAcceptedAtRegistration = array(
                        'termsOfUse'    => $this->request->getPost()->get('acceptedpolicies_termsofuse', false),
                        'privacyPolicy' => $this->request->getPost()->get('acceptedpolicies_privacypolicy', false),
                        'agePolicy'     => $this->request->getPost()->get('acceptedpolicies_agepolicy', false),
                    );

                    $this->validation = new Zikula_Hook_ValidationResponse('', $policiesAcceptedAtRegistration);

                    if ($activePolicies['termsOfUse'] && (!isset($policiesAcceptedAtRegistration['termsOfUse']) || empty($policiesAcceptedAtRegistration['termsOfUse']) || !$policiesAcceptedAtRegistration['termsOfUse'])) {
                        $this->validation->addError('termsofuse', __('In order to register for a new account, you must accept this site\'s Terms of Use.', $this->domain));
                    }

                    if ($activePolicies['privacyPolicy'] && (!isset($policiesAcceptedAtRegistration['privacyPolicy']) || empty($policiesAcceptedAtRegistration['privacyPolicy']) || !$policiesAcceptedAtRegistration['privacyPolicy'])) {
                        $this->validation->addError('privacypolicy', __('In order to register for a new account, you must accept this site\'s Privacy Policy.', $this->domain));
                    }

                    if ($activePolicies['agePolicy'] && (!isset($policiesAcceptedAtRegistration['agePolicy']) || empty($policiesAcceptedAtRegistration['agePolicy']) || !$policiesAcceptedAtRegistration['agePolicy'])) {
                        $this->validation->addError('agepolicy', __f('In order to register for a new account, you must confirm that you meet the requirements of this site\'s Minimum Age Policy. If you are not %1$s years of age or older, and you do not have a parent\'s permission to use this site, then you should not continue registering for access to this site.', array(ModUtil::getVar('Legal', Legal_Constant::MODVAR_MINIMUM_AGE, 0)), $this->domain));
                    }


                    $event->data->set(self::EVENT_KEY, $this->validation);
                } elseif (!$this->request->isPost()) {
                    throw new Zikula_Exception_Forbidden();
                }
            }
        } else {
            // Someone is logged in, so either user looking at own record, or an admin creating or editing a user or registration.
            // See if there is anything for validation to do.
            if ($this->request->isPost()) {
                $user = $event->getSubject();

                $isNewUser = (!isset($user['uid']) || empty($user['uid']));
                $isRegistration = !$isNewUser && UserUtil::isRegistration($user['uid']);

                $editablePolicies = $this->helper->getEditablePolicies();
                $policiesAcceptedAtRegistration = array(
                    'termsOfUse'    => $this->request->getPost()->get('acceptedpolicies_termsofuse', false),
                    'privacyPolicy' => $this->request->getPost()->get('acceptedpolicies_privacypolicy', false),
                    'agePolicy'     => $this->request->getPost()->get('acceptedpolicies_agepolicy', false),
                );
                $uid = $this->request->getPost()->get('acceptedpolicies_uid', false);

                $this->validation = new Zikula_Hook_ValidationResponse($uid ? $uid : '', $policiesAcceptedAtRegistration);

                if ($isNewUser) {
                    if (isset($policiesAcceptedAtRegistration['termsOfUse']) && !$editablePolicies['termsOfUse']) {
                        throw new Zikula_Exception_Forbidden();
                    }
                    if (isset($policiesAcceptedAtRegistration['privacyPolicy']) && !$editablePolicies['privacyPolicy']) {
                        throw new Zikula_Exception_Forbidden();
                    }
                    if (isset($policiesAcceptedAtRegistration['agePolicy']) && !$editablePolicies['agePolicy']) {
                        throw new Zikula_Exception_Forbidden();
                    }
                } else {
                    $goodUidAcceptPolicies = isset($uid) && !empty($uid) && is_numeric($uid) && ($uid > 2);

                    $user = $event->getSubject();
                    $goodUidUser = isset($user) && !empty($user) && is_array($user) && isset($user['uid']) && is_numeric($user['uid']) && ($user['uid'] > 2);

                    // Fail if the uid of the subject does not match the uid from the form. The user changed his
                    // login information, so not only should we not validate what was posted, we should not allow the user
                    // to proceed with this login attempt at all.
                    if ($goodUidUser && $goodUidAcceptPolicies && ($user['uid'] == $uid)) {
                        if (isset($policiesAcceptedAtRegistration['termsOfUse']) && !$editablePolicies['termsOfUse']) {
                            throw new Zikula_Exception_Forbidden();
                        }
                        if (isset($policiesAcceptedAtRegistration['privacyPolicy']) && !$editablePolicies['privacyPolicy']) {
                            throw new Zikula_Exception_Forbidden();
                        }
                        if (isset($policiesAcceptedAtRegistration['agePolicy']) && !$editablePolicies['agePolicy']) {
                            throw new Zikula_Exception_Forbidden();
                        }
                    } elseif (!$goodUidUser || !$goodUidAcceptPolicies) {
                        throw new Zikula_Exception_Fatal();
                    }
                }

                $event->data->set(self::EVENT_KEY, $this->validation);
            } elseif (!$this->request->isPost()) {
                throw new Zikula_Exception_Forbidden();
            }
        }
    }

    /**
     * Responds to process.edit hook notifications.
     *
     * @param Zikula_Event $event The event that triggered this function call.
     */
    public function processEdit(Zikula_Event $event)
    {
        $activePolicies = $this->helper->getActivePolicies();
        $eventName = $event->getName();

        if (isset($this->validation) && !$this->validation->hasErrors()) {
            $user = $event->getSubject();
            $uid = $event->getArg('id');

            if (!UserUtil::isLoggedIn()) {
                if ($eventName == 'users.hook.login.process.edit') {
                    $policiesAcceptedAtLogin = $this->validation->getObject();

                    $nowUTC = new DateTime('now', new DateTimeZone('UTC'));
                    $nowUTCStr = $nowUTC->format(DateTime::ISO8601);

                    if ($activePolicies['termsOfUse'] && $policiesAcceptedAtLogin['termsOfUse']) {
                        UserUtil::setVar(Legal_Constant::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $nowUTCStr, $uid);
                    }

                    if ($activePolicies['privacyPolicy'] && $policiesAcceptedAtLogin['privacyPolicy']) {
                        UserUtil::setVar(Legal_Constant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $nowUTCStr, $uid);
                    }

                    if ($activePolicies['agePolicy'] && $policiesAcceptedAtLogin['agePolicy']) {
                        UserUtil::setVar(Legal_Constant::ATTRIBUTE_AGEPOLICY_CONFIRMED, $nowUTCStr, $uid);
                    }

                    // Force the reload of the user record
                    $user = UserUtil::getVars($uid, true);
                } else {
                    $isRegistration = UserUtil::isRegistration($uid);

                    $user = UserUtil::getVars($uid, false, 'uid', $isRegistration);
                    if (!$user) {
                        throw new Zikula_Exception_Fatal(__('A user account or registration does not exist for the specified uid.', $this->domain));
                    }

                    $policiesAcceptedAtRegistration = $this->validation->getObject();

                    $nowUTC = new DateTime('now', new DateTimeZone('UTC'));
                    $nowUTCStr = $nowUTC->format(DateTime::ISO8601);

                    if ($activePolicies['termsOfUse'] && $policiesAcceptedAtRegistration['termsOfUse']) {
                        UserUtil::setVar(Legal_Constant::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $nowUTCStr, $uid);
                    }

                    if ($activePolicies['privacyPolicy'] && $policiesAcceptedAtRegistration['privacyPolicy']) {
                        UserUtil::setVar(Legal_Constant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $nowUTCStr, $uid);
                    }

                    if ($activePolicies['agePolicy'] && $policiesAcceptedAtRegistration['agePolicy']) {
                        UserUtil::setVar(Legal_Constant::ATTRIBUTE_AGEPOLICY_CONFIRMED, $nowUTCStr, $uid);
                    }

                    // Force the reload of the user record
                    $user = UserUtil::getVars($uid, true, 'uid', $isRegistration);
                }
            } else {
                $isRegistration = UserUtil::isRegistration($uid);

                $user = UserUtil::getVars($uid, false, 'uid', $isRegistration);
                if (!$user) {
                    throw new Zikula_Exception_Fatal(__('A user account or registration does not exist for the specified uid.', $this->domain));
                }

                $policiesAcceptedAtRegistration = $this->validation->getObject();
                $editablePolicies = $this->helper->getEditablePolicies();

                $nowUTC = new DateTime('now', new DateTimeZone('UTC'));
                $nowUTCStr = $nowUTC->format(DateTime::ISO8601);

                if ($activePolicies['termsOfUse'] && $editablePolicies['termsOfUse']) {
                    if ($policiesAcceptedAtRegistration['termsOfUse']) {
                        UserUtil::setVar(Legal_Constant::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $nowUTCStr, $uid);
                    } elseif (($policiesAcceptedAtRegistration['termsOfUse'] === 0) || ($policiesAcceptedAtRegistration['termsOfUse'] === "0")) {
                        UserUtil::delVar(Legal_Constant::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $uid);
                    }
                }

                if ($activePolicies['privacyPolicy'] && $editablePolicies['privacyPolicy']) {
                    if ($policiesAcceptedAtRegistration['privacyPolicy']) {
                        UserUtil::setVar(Legal_Constant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $nowUTCStr, $uid);
                    } elseif (($policiesAcceptedAtRegistration['privacyPolicy'] === 0) || ($policiesAcceptedAtRegistration['termsOfUse'] === "0")) {
                        UserUtil::delVar(Legal_Constant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $uid);
                    }
                }

                if ($activePolicies['agePolicy'] && $editablePolicies['agePolicy']) {
                    if ($policiesAcceptedAtRegistration['agePolicy']) {
                        UserUtil::setVar(Legal_Constant::ATTRIBUTE_AGEPOLICY_CONFIRMED, $nowUTCStr, $uid);
                    } elseif (($policiesAcceptedAtRegistration['agePolicy'] === 0) || ($policiesAcceptedAtRegistration['termsOfUse'] === "0")) {
                        UserUtil::delVar(Legal_Constant::ATTRIBUTE_AGEPOLICY_CONFIRMED, $uid);
                    }
                }

                // Force the reload of the user record
                $user = UserUtil::getVars($uid, true, 'uid', $isRegistration);
            }
        }
    }
}
