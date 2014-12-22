<?php

/**
 * Copyright (c) 2001-2012 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.html GNU/LGPLv3 (or at your option any later version).
 * @package Legal
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\LegalModule\Listener;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zikula\LegalModule\Constant as LegalConstant;
use ZLanguage;
use Zikula\LegalModule\Helper\AcceptPoliciesHelper;
use Zikula_View;
use ModUtil;
use UserUtil;
use Zikula\Module\UsersModule\Constant as UsersConstant;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Hook\ValidationResponse;
use Zikula\Core\Exception\FatalErrorException;
use LogUtil;
use DateTimeZone;
use DateTime;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Core\Event\GenericEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Handles hook-like event notifications from log-in and registration for the acceptance of policies.
 */
class UsersUiListener implements EventSubscriberInterface
{
    /**
     * Similar to a hook area, the event
     *
     * @var string
     */
    const EVENT_KEY = 'module.legal.users_ui_handler';
    /**
     * Access to the Zikula_View instance for this module.
     *
     * @var Zikula_View
     */
    private $view;
    /**
     * Access to the request instance.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;
    /**
     * Access to the helper.
     *
     * @var AcceptPoliciesHelper
     */
    private $helper;
    /**
     * @var ValidationResponse
     */
    private $validation;
    /**
     * The translation domain
     *
     * @var string
     */
    private $domain;
    /**
     * Constructs a new instance of this class.
     *
     * the request attribute is set to the current request service instance.
     * the domain attribute is initialized to the module name.
     * The helper attribute is initialized with an instance of {@link AcceptPoliciesHelper}.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->domain = ZLanguage::getModuleDomain(LegalConstant::MODNAME);
        $this->helper = new AcceptPoliciesHelper();
    }
    
    public function getView()
    {
        if (!$this->view) {
            $this->view = Zikula_View::getInstance(LegalConstant::MODNAME);
        }
        return $this->view;
    }
    
    /**
     * Establish the handlers for various events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'module.users.ui.display_view' => array('uiView'),
            'module.users.ui.form_edit.login_screen' => array('uiEdit'),
            'module.users.ui.form_edit.new_user' => array('uiEdit'),
            'module.users.ui.form_edit.modify_user' => array('uiEdit'),
            'module.users.ui.form_edit.new_registration' => array('uiEdit'),
            'module.users.ui.form_edit.modify_registration' => array('uiEdit'),
            'module.users.ui.validate_edit.login_screen' => array('validateEdit'),
            'module.users.ui.validate_edit.new_user' => array('validateEdit'),
            'module.users.ui.validate_edit.modify_user' => array('validateEdit'),
            'module.users.ui.validate_edit.new_registration' => array('validateEdit'),
            'module.users.ui.validate_edit.modify_registration' => array('validateEdit'),
            'module.users.ui.process_edit.login_screen' => array('processEdit'),
            'module.users.ui.process_edit.new_user' => array('processEdit'),
            'module.users.ui.process_edit.modify_user' => array('processEdit'),
            'module.users.ui.process_edit.new_registration' => array('processEdit'),
            'module.users.ui.process_edit.modify_registration' => array('processEdit'),
            'user.login.veto' => array('acceptPolicies'),
        );
    }
    
    /**
     * Cause redirect.
     *
     * @param string  $url  Url to redirect to.
     * @param integer $type Redirect code, 302 default.
     *
     * @return RedirectResponse
     */
    protected function redirect($url, $type = 302)
    {
        $response = new RedirectResponse(\System::normalizeUrl($url), $type);
        $response->send();
        exit;
    }
    
    /**
     * Responds to ui.view hook-like event notifications.
     *
     * @param GenericEvent $event The event that triggered this function call.
     *
     * @return void
     */
    public function uiView(GenericEvent $event)
    {
        $activePolicies = $this->helper->getActivePolicies();
        $activePolicyCount = array_sum($activePolicies);
        $user = $event->getSubject();
        if (isset($user) && !empty($user) && $activePolicyCount > 0) {
            $acceptedPolicies = $this->helper->getAcceptedPolicies($user['uid']);
            $viewablePolicies = $this->helper->getViewablePolicies($user['uid']);
            if (array_sum($viewablePolicies) > 0) {
                ModUtil::load(LegalConstant::MODNAME);
                // to enable translation domain
                $templateVars = array(
                    'activePolicies' => $activePolicies,
                    'viewablePolicies' => $viewablePolicies,
                    'acceptedPolicies' => $acceptedPolicies);
                $this->getView()->assign($templateVars);
                $event->data[self::EVENT_KEY] = $this->getView()->fetch('legal_acceptpolicies_ui_view.tpl');
            }
        }
    }
    
    /**
     * Responds to ui.edit hook notifications.
     *
     * @param GenericEvent $event The event that triggered this function call.
     *
     * @return void
     */
    public function uiEdit(GenericEvent $event)
    {
        $activePolicies = $this->helper->getActivePolicies();
        $activePolicyCount = array_sum($activePolicies);
        if ($activePolicyCount > 0) {
            ModUtil::load(LegalConstant::MODNAME);
            // to enable translation domain
            $eventName = $event->getName();
            // Determine if the hook should be displayed, and also set up certain variables, based on the type of event
            // being handled, the state of the subject user account, and who is currently logged in.
            if (!UserUtil::isLoggedIn()) {
                // If the user is not logged in, then the only two scenarios where we would show the hook contents is if
                // the user is trying to log in and it was vetoed because one or more policies need to be accepted, or if
                // the user is looking at the new user registration form.
                $user = $event->getSubject();
                if (!isset($user) || empty($user)) {
                    $user = array('__ATTRIBUTES__' => array());
                }
                if ($eventName == 'module.users.ui.form_edit.login_screen') {
                    // It is not shown unless we have a user record (meaning that the first log-in attempt was vetoed.
                    if (isset($user) && !empty($user) && isset($user['uid']) && !empty($user['uid'])) {
                        $acceptedPolicies = $this->helper->getAcceptedPolicies($user['uid']);
                        // We only show the policies if one or more active policies have not been accepted by the user.
                        if ($activePolicies['termsOfUse']
                            && !$acceptedPolicies['termsOfUse']
                            || $activePolicies['privacyPolicy']
                            && !$acceptedPolicies['privacyPolicy']
                            || $activePolicies['agePolicy']
                            && !$acceptedPolicies['agePolicy']) {
                            $templateVars = array(
                                'policiesUid' => $user['uid'],
                                'activePolicies' => $activePolicies,
                                'originalAcceptedPolicies' => $acceptedPolicies,
                                'acceptedPolicies' => isset($this->validation) ? $this->validation->getObject() : $acceptedPolicies,
                                'fieldErrors' => isset($this->validation) && $this->validation->hasErrors() ? $this->validation->getErrors() : array());
                            $this->getView()->assign($templateVars);
                            $event->data[self::EVENT_KEY] = $this->getView()->fetch('legal_acceptpolicies_ui_edit_login.tpl');
                        }
                    }
                } else {
                    $acceptedPolicies = isset($this->validation) ? $this->validation->getObject() : $this->helper->getAcceptedPolicies();
                    $templateVars = array(
                        'activePolicies' => $activePolicies,
                        'acceptedPolicies' => $acceptedPolicies,
                        'fieldErrors' => isset($this->validation) && $this->validation->hasErrors() ? $this->validation->getErrors() : array());
                    $this->getView()->assign($templateVars);
                    $event->data[self::EVENT_KEY] = $this->getView()->fetch('legal_acceptpolicies_ui_edit_registration.tpl');
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
                if (array_sum($viewablePolicies) > 0 || array_sum($editablePolicies) > 0) {
                    $templateVars = array(
                        'policiesUid' => isset($user) ? $user['uid'] : '',
                        'activePolicies' => $activePolicies,
                        'viewablePolicies' => $viewablePolicies,
                        'editablePolicies' => $editablePolicies,
                        'acceptedPolicies' => $acceptedPolicies,
                        'fieldErrors' => isset($this->validation) && $this->validation->hasErrors() ? $this->validation->getErrors() : array());
                    $this->getView()->assign($templateVars);
                    $event->data[self::EVENT_KEY] = $this->getView()->fetch('legal_acceptpolicies_ui_edit.tpl');
                }
            }
        }
    }
    
    /**
     * Responds to validate.edit hook notifications.
     *
     * @param GenericEvent $event The event that triggered this function call.
     *
     * @return void
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function, or to
     *      modify the acceptance of policies on a user account other than his own.
     *
     * @throws FatalErrorException|\InvalidArgumentException Thrown if the user record retrieved from the POST is in an unexpected form or its data is
     *      unexpected.
     */
    public function validateEdit(GenericEvent $event)
    {
        if (!$this->request->isMethod('POST')) {
            // Check if we got here by a reentrant login method.
            $sessionVars = $this->request->getSession()->get(
                'User_login',
                array(),
                UsersConstant::SESSION_VAR_NAMESPACE
            );
            $getReentrantToken = $this->request->query->get('reentranttoken', null);
            if (!isset($sessionVars['reentranttoken']) || !isset($getReentrantToken) || $getReentrantToken != $sessionVars['reentranttoken']) {
                // Not reentrant login method,  it is probably a hack attempt.
                throw new AccessDeniedException();
            }
        }
        // If there is no 'acceptedpolicies_uid' in the POST, then there is no attempt to update the acceptance of policies,
        // So there is nothing to validate.
        if ($this->request->request->has('acceptedpolicies_uid')) {
            ModUtil::load(LegalConstant::MODNAME);
            // to enable translation domain
            // Set up the necessary objects for the validation response
            $policiesAcceptedAtRegistration = array(
                'termsOfUse' => $this->request->request->get('acceptedpolicies_termsofuse', false),
                'privacyPolicy' => $this->request->request->get('acceptedpolicies_privacypolicy', false),
                'agePolicy' => $this->request->request->get('acceptedpolicies_agepolicy', false),
                'cancellationRightPolicy' => $this->request->request->get('acceptedpolicies_cancellationrightpolicy', false),
                'tradeConditions' => $this->request->request->get('acceptedpolicies_tradeconditions', false));
            $uid = $this->request->request->get('acceptedpolicies_uid', false);
            $this->validation = new ValidationResponse($uid ? $uid : '', $policiesAcceptedAtRegistration);
            $activePolicies = $this->helper->getActivePolicies();
            // Get the user record from the event. If there is no user record, create a dummy one.
            $user = $event->getSubject();
            if (!isset($user) || empty($user)) {
                $user = array('__ATTRIBUTES__' => array());
            }
            $goodUidAcceptPolicies = isset($uid) && !empty($uid) && is_numeric($uid);
            $goodUidUser = is_array($user) && isset($user['uid']) && is_numeric($user['uid']);
            if (!UserUtil::isLoggedIn()) {
                // User is not logged in, so this should be either part of a login attempt or a new user registration.
                $eventName = $event->getName();
                $isRegistration = $eventName != 'users.login.validate_edit';
                if ($isRegistration) {
                    // A registration. There will be no accepted policies stored yet (function returns the appropriate
                    // array for a null uid),
                    // and there is no (or at least there *should* be no) uid to set on the validation response.
                    $acceptedPolicies = $this->helper->getAcceptedPolicies();
                } else {
                    // A login attempt.
                    $goodUidAcceptPolicies = $goodUidAcceptPolicies && $uid > 2;
                    $goodUidUser = $goodUidUser && $user['uid'] > 2;
                    if (!$goodUidUser || !$goodUidAcceptPolicies) {
                        // Critical fail if the $user record is bad, or if the uid used for Legal is bad.
                        throw new \InvalidArgumentException(__("The UID is invalid.", $this->domain));
                    } elseif ($user['uid'] != $uid) {
                        // Fail if the uid of the subject does not match the uid from the form. The user changed his
                        // login information, so not only should we not validate what was posted, we should not allow the user
                        // to proceed with this login attempt at all.
                        LogUtil::registerError(__('Sorry! You changed your authentication information, and one or more items displayed on the login screen may not have been applicable for your account. Please try logging in again.', $this->domain));
                        $this->request->getSession()->remove('Zikula_Users');
                        $this->request->getSession()->remove(LegalConstant::MODNAME);
                        $this->redirect(ModUtil::url('Users', 'user', 'login'));
                    }
                    $acceptedPolicies = $this->helper->getAcceptedPolicies($uid);
                }
                // Do the validation
                if ($activePolicies['termsOfUse']
                    && !$acceptedPolicies['termsOfUse']
                    && (!isset($policiesAcceptedAtRegistration['termsOfUse'])
                        || empty($policiesAcceptedAtRegistration['termsOfUse'])
                        || !$policiesAcceptedAtRegistration['termsOfUse'])) {
                    if ($isRegistration) {
                        $validationErrorMsg = __('In order to register for a new account, you must accept this site\'s Terms of Use.', $this->domain);
                    } else {
                        $validationErrorMsg = __('In order to log in, you must accept this site\'s Terms of Use.', $this->domain);
                    }
                    $this->validation->addError('termsofuse', $validationErrorMsg);
                }
                if ($activePolicies['privacyPolicy']
                    && !$acceptedPolicies['privacyPolicy']
                    && (!isset($policiesAcceptedAtRegistration['privacyPolicy'])
                        || empty($policiesAcceptedAtRegistration['privacyPolicy'])
                        || !$policiesAcceptedAtRegistration['privacyPolicy'])) {
                    if ($isRegistration) {
                        $validationErrorMsg = __('In order to register for a new account, you must accept this site\'s Privacy Policy.', $this->domain);
                    } else {
                        $validationErrorMsg = __('In order to log in, you must accept this site\'s Privacy Policy.', $this->domain);
                    }
                    $this->validation->addError('privacypolicy', $validationErrorMsg);
                }
                if ($activePolicies['agePolicy']
                    && !$acceptedPolicies['agePolicy']
                    && (!isset($policiesAcceptedAtRegistration['agePolicy'])
                        || empty($policiesAcceptedAtRegistration['agePolicy'])
                        || !$policiesAcceptedAtRegistration['agePolicy'])) {
                    if ($isRegistration) {
                        $validationErrorMsg = __f('In order to register for a new account, you must confirm that you meet the requirements of this site\'s Minimum Age Policy. If you are not %1$s years of age or older, and you do not have a parent\'s permission to use this site, then you should not continue registering for access to this site.', array(ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_MINIMUM_AGE, 0)), $this->domain);
                    } else {
                        $validationErrorMsg = __f('In order to log in, you must confirm that you meet the requirements of this site\'s Minimum Age Policy. If you are not %1$s years of age or older, and you do not have a parent\'s permission to use this site, then please ask your parent to contact a site administrator.', array(ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_MINIMUM_AGE, 0)), $this->domain);
                    }
                    $this->validation->addError('agepolicy', $validationErrorMsg);
                }
                if ($activePolicies['cancellationRightPolicy']
                    && !$acceptedPolicies['cancellationRightPolicy']
                    && (!isset($policiesAcceptedAtRegistration['cancellationRightPolicy'])
                        || empty($policiesAcceptedAtRegistration['cancellationRightPolicy'])
                        || !$policiesAcceptedAtRegistration['cancellationRightPolicy'])) {
                    if ($isRegistration) {
                        $validationErrorMsg = __('In order to register for a new account, you must accept our cancellation right policy.', $this->domain);
                    } else {
                        $validationErrorMsg = __('In order to log in, you must accept our cancellation right policy.', $this->domain);
                    }
                    $this->validation->addError('cancellationrightpolicy', $validationErrorMsg);
                }
                if ($activePolicies['tradeConditions']
                    && !$acceptedPolicies['tradeConditions']
                    && (!isset($policiesAcceptedAtRegistration['tradeConditions'])
                        || empty($policiesAcceptedAtRegistration['tradeConditions'])
                        || !$policiesAcceptedAtRegistration['tradeConditions'])) {
                    if ($isRegistration) {
                        $validationErrorMsg = __('In order to register for a new account, you must accept our general terms and conditions of trade.', $this->domain);
                    } else {
                        $validationErrorMsg = __('In order to log in, you must accept our general terms and conditions of trade.', $this->domain);
                    }
                    $this->validation->addError('tradeconditions', $validationErrorMsg);
                }
            } else {
                // Someone is logged in, so either user looking at own record, an admin creating a new user,
                // an admin editing a user, or an admin editing a registration.
                // In this instance, we are only checking to see if the user has edit permission for the policy acceptance status
                // being changed.
                $editablePolicies = $this->helper->getEditablePolicies();
                if (!isset($user) || empty($user) || !is_array($user)) {
                    throw new \InvalidArgumentException(__("The &dollar;user is invalid.", $this->domain));
                }
                $isNewUser = !isset($user['uid']) || empty($user['uid']);
                if (!$isNewUser && !is_numeric($user['uid'])) {
                    throw new \InvalidArgumentException(__("The UID is invalid.", $this->domain));
                }
                if ($isNewUser || $user['uid'] > 2) {
                    if (!$isNewUser) {
                        // Only check this stuff if the admin is not creating a new user. It doesn't make sense otherwise.
                        if (!$goodUidUser || !$goodUidAcceptPolicies || $user['uid'] != $uid) {
                            // Fail if the uid of the subject does not match the uid from the form. The user changed the uid
                            // on the account (is that even possible?!) or somehow the main user form and the part for Legal point
                            // to different user account. In any case, that is a bad situation that should cause a critical failure.
                            // Also fail if the $user record is bad, or if the uid used for Legal is bad.
                            throw new FatalErrorException(__("The &dollar;user record or the UID is invalid or the UID does not match."));
                        }
                    }
                    // Fail on any attempt to accept a policy that is not editable.
                    if (isset($policiesAcceptedAtRegistration['termsOfUse']) && !$editablePolicies['termsOfUse']) {
                        throw new AccessDeniedException();
                    }
                    if (isset($policiesAcceptedAtRegistration['privacyPolicy']) && !$editablePolicies['privacyPolicy']) {
                        throw new AccessDeniedException();
                    }
                    if (isset($policiesAcceptedAtRegistration['agePolicy']) && !$editablePolicies['agePolicy']) {
                        throw new AccessDeniedException();
                    }
                    if (isset($policiesAcceptedAtRegistration['cancellationRightPolicy']) && !$editablePolicies['cancellationRightPolicy']) {
                        throw new AccessDeniedException();
                    }
                    if (isset($policiesAcceptedAtRegistration['tradeConditions']) && !$editablePolicies['tradeConditions']) {
                        throw new AccessDeniedException();
                    }
                }
            }
            $event->data->set(self::EVENT_KEY, $this->validation);
        }
    }
    
    /**
     * Responds to process_edit hook-like event notifications.
     *
     * @param GenericEvent $event The event that triggered this function call.
     *
     * @return void
     *
     * @throws NotFoundHttpException Thrown if a user account does not exist for the uid specified by the event.
     */
    public function processEdit(GenericEvent $event)
    {
        $activePolicies = $this->helper->getActivePolicies();
        $eventName = $event->getName();
        if (isset($this->validation) && !$this->validation->hasErrors()) {
            ModUtil::load(LegalConstant::MODNAME);
            // to enable translation domain
            $user = $event->getSubject();
            $uid = $user['uid'];
            if (!UserUtil::isLoggedIn()) {
                if ($eventName == 'module.users.ui.process_edit.login_screen' || $eventName == 'module.users.ui.process_edit.login_block') {
                    $policiesAcceptedAtLogin = $this->validation->getObject();
                    $nowUTC = new DateTime('now', new DateTimeZone('UTC'));
                    $nowUTCStr = $nowUTC->format(DateTime::ISO8601);
                    if ($activePolicies['termsOfUse'] && $policiesAcceptedAtLogin['termsOfUse']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $nowUTCStr, $uid);
                    }
                    if ($activePolicies['privacyPolicy'] && $policiesAcceptedAtLogin['privacyPolicy']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $nowUTCStr, $uid);
                    }
                    if ($activePolicies['agePolicy'] && $policiesAcceptedAtLogin['agePolicy']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED, $nowUTCStr, $uid);
                    }
                    if ($activePolicies['cancellationRightPolicy'] && $policiesAcceptedAtLogin['cancellationRightPolicy']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED, $nowUTCStr, $uid);
                    }
                    if ($activePolicies['tradeConditions'] && $policiesAcceptedAtLogin['tradeConditions']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED, $nowUTCStr, $uid);
                    }
                    // Force the reload of the user record
                    $user = UserUtil::getVars($uid, true);
                } else {
                    $isRegistration = UserUtil::isRegistration($uid);
                    $user = UserUtil::getVars($uid, false, 'uid', $isRegistration);
                    if (!$user) {
                        throw new NotFoundHttpException(__('A user account or registration does not exist for the specified uid.', $this->domain));
                    }
                    $policiesAcceptedAtRegistration = $this->validation->getObject();
                    $nowUTC = new DateTime('now', new DateTimeZone('UTC'));
                    $nowUTCStr = $nowUTC->format(DateTime::ISO8601);
                    if ($activePolicies['termsOfUse'] && $policiesAcceptedAtRegistration['termsOfUse']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $nowUTCStr, $uid);
                    }
                    if ($activePolicies['privacyPolicy'] && $policiesAcceptedAtRegistration['privacyPolicy']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $nowUTCStr, $uid);
                    }
                    if ($activePolicies['agePolicy'] && $policiesAcceptedAtRegistration['agePolicy']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED, $nowUTCStr, $uid);
                    }
                    if ($activePolicies['cancellationRightPolicy'] && $policiesAcceptedAtRegistration['cancellationRightPolicy']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED, $nowUTCStr, $uid);
                    }
                    if ($activePolicies['tradeConditions'] && $policiesAcceptedAtRegistration['tradeConditions']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED, $nowUTCStr, $uid);
                    }
                    // Force the reload of the user record
                    $user = UserUtil::getVars($uid, true, 'uid', $isRegistration);
                }
            } else {
                $isRegistration = UserUtil::isRegistration($uid);
                $user = UserUtil::getVars($uid, false, 'uid', $isRegistration);
                if (!$user) {
                    throw new NotFoundHttpException(__('A user account or registration does not exist for the specified uid.', $this->domain));
                }
                $policiesAcceptedAtRegistration = $this->validation->getObject();
                $editablePolicies = $this->helper->getEditablePolicies();
                $nowUTC = new DateTime('now', new DateTimeZone('UTC'));
                $nowUTCStr = $nowUTC->format(DateTime::ISO8601);
                if ($activePolicies['termsOfUse'] && $editablePolicies['termsOfUse']) {
                    if ($policiesAcceptedAtRegistration['termsOfUse']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $nowUTCStr, $uid);
                    } elseif ($policiesAcceptedAtRegistration['termsOfUse'] === 0 || $policiesAcceptedAtRegistration['termsOfUse'] === '0') {
                        UserUtil::delVar(LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $uid);
                    }
                }
                if ($activePolicies['privacyPolicy'] && $editablePolicies['privacyPolicy']) {
                    if ($policiesAcceptedAtRegistration['privacyPolicy']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $nowUTCStr, $uid);
                    } elseif ($policiesAcceptedAtRegistration['privacyPolicy'] === 0 || $policiesAcceptedAtRegistration['termsOfUse'] === '0') {
                        UserUtil::delVar(LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $uid);
                    }
                }
                if ($activePolicies['agePolicy'] && $editablePolicies['agePolicy']) {
                    if ($policiesAcceptedAtRegistration['agePolicy']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED, $nowUTCStr, $uid);
                    } elseif ($policiesAcceptedAtRegistration['agePolicy'] === 0 || $policiesAcceptedAtRegistration['termsOfUse'] === '0') {
                        UserUtil::delVar(LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED, $uid);
                    }
                }
                if ($activePolicies['cancellationRightPolicy'] && $editablePolicies['cancellationRightPolicy']) {
                    if ($policiesAcceptedAtRegistration['cancellationRightPolicy']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED, $nowUTCStr, $uid);
                    } elseif ($policiesAcceptedAtRegistration['cancellationRightPolicy'] === 0 || $policiesAcceptedAtRegistration['cancellationRightPolicy'] === '0') {
                        UserUtil::delVar(LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED, $uid);
                    }
                }
                if ($activePolicies['tradeConditions'] && $editablePolicies['tradeConditions']) {
                    if ($policiesAcceptedAtRegistration['tradeConditions']) {
                        UserUtil::setVar(LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED, $nowUTCStr, $uid);
                    } elseif ($policiesAcceptedAtRegistration['tradeConditions'] === 0 || $policiesAcceptedAtRegistration['tradeConditions'] === '0') {
                        UserUtil::delVar(LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED, $uid);
                    }
                }
                // Force the reload of the user record
                $user = UserUtil::getVars($uid, true, 'uid', $isRegistration);
            }
        }
    }

    /**
     * Vetos (denies) a login attempt, and forces the user to accept policies.
     *
     * This handler is triggered by the 'user.login.veto' event.  It vetos (denies) a
     * login attempt if the users's Legal record is flagged to force the user to accept
     * one or more legal agreements.
     *
     * @param GenericEvent $event The event that triggered this handler.
     *
     * @return void
     */
    public function acceptPolicies(GenericEvent $event)
    {
        $domain = ZLanguage::getModuleDomain(LegalConstant::MODNAME);
        $termsOfUseActive = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_TERMS_ACTIVE, false);
        $privacyPolicyActive = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_PRIVACY_ACTIVE, false);
        $agePolicyActive = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_MINIMUM_AGE, 0) > 0;
        $cancellationRightPolicyActive = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE, false);
        $tradeConditionsActive = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE, false);
        if ($termsOfUseActive || $privacyPolicyActive || $agePolicyActive || $cancellationRightPolicyActive || $tradeConditionsActive) {
            $userObj = $event->getSubject();
            if (isset($userObj) && $userObj['uid'] > 2) {
                if ($termsOfUseActive) {
                    $termsOfUseAcceptedDateTimeStr = UserUtil::getVar(LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $userObj['uid'], false);
                    $termsOfUseAccepted = isset($termsOfUseAcceptedDateTimeStr) && !empty($termsOfUseAcceptedDateTimeStr);
                } else {
                    $termsOfUseAccepted = true;
                }
                if ($privacyPolicyActive) {
                    $privacyPolicyAcceptedDateTimeStr = UserUtil::getVar(LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $userObj['uid'], false);
                    $privacyPolicyAccepted = isset($privacyPolicyAcceptedDateTimeStr) && !empty($privacyPolicyAcceptedDateTimeStr);
                } else {
                    $privacyPolicyAccepted = true;
                }
                if ($agePolicyActive) {
                    $agePolicyAcceptedDateTimeStr = UserUtil::getVar(LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED, $userObj['uid'], false);
                    $agePolicyAccepted = isset($agePolicyAcceptedDateTimeStr) && !empty($agePolicyAcceptedDateTimeStr);
                } else {
                    $agePolicyAccepted = true;
                }
                if ($cancellationRightPolicyActive) {
                    $cancellationRightPolicyAcceptedDateTimeStr = UserUtil::getVar(LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED, $userObj['uid'], false);
                    $cancellationRightPolicyAccepted = isset($cancellationRightPolicyAcceptedDateTimeStr) && !empty($cancellationRightPolicyAcceptedDateTimeStr);
                } else {
                    $cancellationRightPolicyAccepted = true;
                }
                if ($tradeConditionsActive) {
                    $tradeConditionsAcceptedDateTimeStr = UserUtil::getVar(LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED, $userObj['uid'], false);
                    $tradeConditionsAccepted = isset($tradeConditionsAcceptedDateTimeStr) && !empty($tradeConditionsAcceptedDateTimeStr);
                } else {
                    $tradeConditionsAccepted = true;
                }
                if (!$termsOfUseAccepted || !$privacyPolicyAccepted || !$agePolicyAccepted || !$cancellationRightPolicyAccepted || !$tradeConditionsAccepted) {
                    $event->stopPropagation();
                    $event->data['redirect_func'] = array(
                        'modname' => LegalConstant::MODNAME,
                        'type' => 'user',
                        'func' => 'acceptPolicies',
                        'args' => array('login' => true),
                        'session' => array(
                            'var' => 'Legal_Controller_User_acceptPolicies',
                            'namespace' => LegalConstant::MODNAME
                        )
                    );
                    LogUtil::registerError(__('Your log-in request was not completed. You must review and confirm your acceptance of one or more site policies prior to logging in.', $domain));
                }
            }
        }
    }

}