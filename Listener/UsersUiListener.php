<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\LegalModule\Listener;

use DateTime;
use DateTimeZone;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig_Environment;
use UserUtil;
use Zikula\Bundle\HookBundle\Hook\ValidationResponse;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Exception\FatalErrorException;
use Zikula\Core\Token\CsrfTokenHandler;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\LegalModule\Helper\AcceptPoliciesHelper;
use Zikula\UsersModule\Api\CurrentUserApi;

/**
 * Handles hook-like event notifications from log-in and registration for the acceptance of policies.
 */
class UsersUiListener implements EventSubscriberInterface
{
    /**
     * Similar to a hook area, the event.
     *
     * @var string
     */
    const EVENT_KEY = 'module.legal.users_ui_handler';

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * Access to the request instance.
     *
     * @var Request
     */
    private $request;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CsrfTokenHandler
     */
    private $csrfTokenHandler;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var CurrentUserApi
     */
    private $currentUserApi;

    /**
     * Access to the policy acceptance helper.
     *
     * @var AcceptPoliciesHelper
     */
    private $acceptPoliciesHelper;

    /**
     * @var ValidationResponse
     */
    private $validation;

    /**
     * Constructor.
     *
     * @param KernelInterface      $kernel               KernelInterface service instance
     * @param RequestStack         $requestStack         RequestStack service instance
     * @param Twig_Environment     $twig                 The twig templating service
     * @param TranslatorInterface  $translator           Translator service instance
     * @param RouterInterface      $router               RouterInterface service instance
     * @param CsrfTokenHandler     $csrfTokenHandler     CsrfTokenHandler service instance
     * @param VariableApi          $variableApi          VariableApi service instance
     * @param CurrentUserApi       $currentUserApi       CurrentUserApi service instance
     * @param AcceptPoliciesHelper $acceptPoliciesHelper AcceptPoliciesHelper service instance
     */
    public function __construct(
        KernelInterface $kernel,
        RequestStack $requestStack,
        Twig_Environment $twig,
        TranslatorInterface $translator,
        RouterInterface $router,
        CsrfTokenHandler $csrfTokenHandler,
        VariableApi $variableApi,
        CurrentUserApi $currentUserApi,
        AcceptPoliciesHelper $acceptPoliciesHelper)
    {
        $this->kernel = $kernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->twig = $twig;
        $this->translator = $translator;
        $this->router = $router;
        $this->csrfTokenHandler = $csrfTokenHandler;
        $this->variableApi = $variableApi;
        $this->currentUserApi = $currentUserApi;
        $this->acceptPoliciesHelper = $acceptPoliciesHelper;
    }

    /**
     * Establish the handlers for various events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'module.users.ui.display_view'                      => ['uiView'],
            'module.users.ui.form_edit.login_screen'            => ['uiEdit'],
            'module.users.ui.form_edit.new_user'                => ['uiEdit'],
            'module.users.ui.form_edit.modify_user'             => ['uiEdit'],
            'module.users.ui.form_edit.new_registration'        => ['uiEdit'],
            'module.users.ui.form_edit.modify_registration'     => ['uiEdit'],
            'module.users.ui.validate_edit.login_screen'        => ['validateEdit'],
            'module.users.ui.validate_edit.new_user'            => ['validateEdit'],
            'module.users.ui.validate_edit.modify_user'         => ['validateEdit'],
            'module.users.ui.validate_edit.new_registration'    => ['validateEdit'],
            'module.users.ui.validate_edit.modify_registration' => ['validateEdit'],
            'module.users.ui.process_edit.login_screen'         => ['processEdit'],
            'module.users.ui.process_edit.new_user'             => ['processEdit'],
            'module.users.ui.process_edit.modify_user'          => ['processEdit'],
            'module.users.ui.process_edit.new_registration'     => ['processEdit'],
            'module.users.ui.process_edit.modify_registration'  => ['processEdit'],
            'user.login.veto'                                   => ['acceptPolicies'],
        ];
    }

    /**
     * Cause redirect.
     *
     * @param string $url  Url to redirect to
     * @param int    $type Redirect code, 302 default
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
     * @param GenericEvent $event The event that triggered this function call
     *
     * @return void
     */
    public function uiView(GenericEvent $event)
    {
        if (null === $this->kernel->getModule(LegalConstant::MODNAME)) {
            return;
        }

        $activePolicies = $this->acceptPoliciesHelper->getActivePolicies();
        $activePolicyCount = array_sum($activePolicies);
        $user = $event->getSubject();
        if (!isset($user) || empty($user) || $activePolicyCount < 1) {
            return;
        }

        $acceptedPolicies = $this->acceptPoliciesHelper->getAcceptedPolicies($user['uid']);
        $viewablePolicies = $this->acceptPoliciesHelper->getViewablePolicies($user['uid']);
        if (array_sum($viewablePolicies) < 1) {
            return;
        }

        $templateParameters = [
            'activePolicies'   => $activePolicies,
            'viewablePolicies' => $viewablePolicies,
            'acceptedPolicies' => $acceptedPolicies,
        ];

        $event->data[self::EVENT_KEY] = $this->twig->render('@'.LegalConstant::MODNAME.'/UsersUI/view.html.twig', $templateParameters);
    }

    /**
     * Responds to ui.edit hook notifications.
     *
     * @param GenericEvent $event The event that triggered this function call
     *
     * @return void
     */
    public function uiEdit(GenericEvent $event)
    {
        if (null === $this->kernel->getModule(LegalConstant::MODNAME)) {
            return;
        }

        $activePolicies = $this->acceptPoliciesHelper->getActivePolicies();
        $activePolicyCount = array_sum($activePolicies);
        if ($activePolicyCount < 1) {
            return;
        }

        $eventName = $event->getName();
        $csrfToken = $this->csrfTokenHandler->generate();
        // Determine if the hook should be displayed, and also set up certain variables, based on the type of event
        // being handled, the state of the subject user account, and who is currently logged in.
        if (!$this->currentUserApi->isLoggedIn()) {
            // If the user is not logged in, then the only two scenarios where we would show the hook contents is if
            // the user is trying to log in and it was vetoed because one or more policies need to be accepted, or if
            // the user is looking at the new user registration form.
            $user = $event->getSubject();
            if (!isset($user) || empty($user)) {
                $user = ['__ATTRIBUTES__' => []];
            }
            if ($eventName == 'module.users.ui.form_edit.login_screen') {
                // It is not shown unless we have a user record (meaning that the first log-in attempt was vetoed.
                if (isset($user) && !empty($user) && isset($user['uid']) && !empty($user['uid'])) {
                    $acceptedPolicies = $this->acceptPoliciesHelper->getAcceptedPolicies($user['uid']);
                    // We only show the policies if one or more active policies have not been accepted by the user.
                    if ($activePolicies['termsOfUse'] && !$acceptedPolicies['termsOfUse']
                        || $activePolicies['privacyPolicy'] && !$acceptedPolicies['privacyPolicy']
                        || $activePolicies['agePolicy'] && !$acceptedPolicies['agePolicy']) {
                        $templateParameters = [
                            'policiesUid'              => $user['uid'],
                            'activePolicies'           => $activePolicies,
                            'originalAcceptedPolicies' => $acceptedPolicies,
                            'acceptedPolicies'         => isset($this->validation) ? $this->validation->getObject() : $acceptedPolicies,
                            'fieldErrors'              => isset($this->validation) && $this->validation->hasErrors() ? $this->validation->getErrors() : [],
                            'csrfToken'                => $csrfToken,
                        ];
                        $event->data[self::EVENT_KEY] = $this->twig->render('@'.LegalConstant::MODNAME.'/UsersUI/editLogin.html.twig', $templateParameters);
                    }
                }
            } else {
                $acceptedPolicies = isset($this->validation) ? $this->validation->getObject() : $this->acceptPoliciesHelper->getAcceptedPolicies();
                $templateParameters = [
                    'activePolicies'           => $activePolicies,
                    'originalAcceptedPolicies' => [],
                    'acceptedPolicies'         => $acceptedPolicies,
                    'fieldErrors'              => isset($this->validation) && $this->validation->hasErrors() ? $this->validation->getErrors() : [],
                    'csrfToken'                => $csrfToken,
                ];
                $event->data[self::EVENT_KEY] = $this->twig->render('@'.LegalConstant::MODNAME.'/UsersUI/editRegistration.html.twig', $templateParameters);
            }

            return;
        }

        // The user is logged in. A few possibilities here. The user is editing his own account information,
        // the user is someone with ACCESS_MODERATE access to the policies, but ACCESS_EDIT to the account and is editing the
        // account information (view-only access to the policies in that case), or the user is someone with ACCESS_EDIT access
        // to the policies.
        $user = $event->getSubject();
        if (isset($this->validation)) {
            $acceptedPolicies = $this->validation->getObject();
        } else {
            $acceptedPolicies = $this->acceptPoliciesHelper->getAcceptedPolicies(isset($user) ? $user['uid'] : null);
        }
        $viewablePolicies = $this->acceptPoliciesHelper->getViewablePolicies(isset($user) ? $user['uid'] : null);
        $editablePolicies = $this->acceptPoliciesHelper->getEditablePolicies();
        if (array_sum($viewablePolicies) < 1 && array_sum($editablePolicies) < 1) {
            return;
        }

        $templateParameters = [
            'policiesUid'      => isset($user) ? $user['uid'] : '',
            'activePolicies'   => $activePolicies,
            'viewablePolicies' => $viewablePolicies,
            'editablePolicies' => $editablePolicies,
            'acceptedPolicies' => $acceptedPolicies,
            'fieldErrors'      => isset($this->validation) && $this->validation->hasErrors() ? $this->validation->getErrors() : [],
            'csrfToken'        => $csrfToken,
        ];
        $event->data[self::EVENT_KEY] = $this->twig->render('@'.LegalConstant::MODNAME.'/UsersUI/edit.html.twig', $templateParameters);
    }

    /**
     * Responds to validate.edit hook notifications.
     *
     * @param GenericEvent $event The event that triggered this function call
     *
     * @throws AccessDeniedException                         Thrown if the user does not have the appropriate access level for the function, or to
     *                                                       modify the acceptance of policies on a user account other than his own
     * @throws FatalErrorException|\InvalidArgumentException Thrown if the user record retrieved from the POST is in an unexpected form or its data is
     *                                                       unexpected
     *
     * @return void
     */
    public function validateEdit(GenericEvent $event)
    {
        if (null === $this->kernel->getModule(LegalConstant::MODNAME)) {
            return;
        }

        // If there is no 'acceptedpolicies_uid' in the POST, then there is no attempt to update the acceptance of policies,
        // So there is nothing to validate.
        if (!$this->request->request->has('acceptedpolicies_uid')) {
            return;
        }

        // Set up the necessary objects for the validation response
        $policiesAccepted = $this->request->request->get('acceptedpolicies_policies', false);

        $uid = $this->request->request->get('acceptedpolicies_uid', false);
        $this->validation = new ValidationResponse($uid ? $uid : '', $policiesAccepted);
        $activePolicies = $this->acceptPoliciesHelper->getActivePolicies();
        // Get the user record from the event. If there is no user record, create a dummy one.
        $user = $event->getSubject();
        if (!isset($user) || empty($user)) {
            $user = ['__ATTRIBUTES__' => []];
        }
        $goodUid = isset($uid) && !empty($uid) && is_numeric($uid);
        $goodUidUser = (is_array($user) || is_object($user)) && isset($user['uid']) && is_numeric($user['uid']);
        if (!$this->currentUserApi->isLoggedIn()) {
            // User is not logged in, so this should be either part of a login attempt or a new user registration.
            if ($event->getName() == 'users.login.validate_edit') {
                // A login attempt.
                $goodUid = $goodUid && $uid > 2;
                $goodUidUser = $goodUidUser && $user['uid'] > 2;
                if (!$goodUid || !$goodUidUser) {
                    // Critical fail if the $user record is bad, or if the uid used for Legal is bad.
                    throw new \InvalidArgumentException($this->translator->__('The UID is invalid.'));
                } elseif ($user['uid'] != $uid) {
                    // Fail if the uid of the subject does not match the uid from the form. The user changed his
                    // login information, so not only should we not validate what was posted, we should not allow the user
                    // to proceed with this login attempt at all.
                    $this->request->getSession()->getFlashBag()->add('error', $this->translator->__('Sorry! You changed your authentication information, and one or more items displayed on the login screen may not have been applicable for your account. Please try logging in again.'));
                    $this->request->getSession()->remove('Zikula_Users');
                    $this->request->getSession()->remove(LegalConstant::MODNAME);
                    $this->redirect($this->router->generate('zikulausersmodule_access_login'));
                }
            }

            // Do the validation
            if (!$policiesAccepted) {
                if ($isRegistration) {
                    $validationErrorMsg = $this->translator->__('In order to register for a new account, you must accept this site\'s policies.');
                } else {
                    $validationErrorMsg = $this->translator->__('In order to log in, you must accept this site\'s policies.');
                }
                $this->validation->addError('policies', $validationErrorMsg);
            }
        } else {
            // Someone is logged in, so either user looking at own record, an admin creating a new user,
            // an admin editing a user, or an admin editing a registration.
            // In this instance, we are only checking to see if the user has edit permission for the policy acceptance status
            // being changed.
            if (!isset($user) || empty($user)) {
                throw new \InvalidArgumentException($this->translator->__('The user is invalid.'));
            }
            $isNewUser = !isset($user['uid']) || empty($user['uid']);
            if (!$isNewUser && !is_numeric($user['uid'])) {
                throw new \InvalidArgumentException($this->translator->__('The UID is invalid.'));
            }
            if (!$isNewUser && $user['uid'] > 2) {
                // Only check this stuff if the admin is not creating a new user. It doesn't make sense otherwise.
                if (!$goodUid || !$goodUidUser || $user['uid'] != $uid) {
                    // Fail if the uid of the subject does not match the uid from the form. The user changed the uid
                    // on the account (is that even possible?!) or somehow the main user form and the part for Legal point
                    // to different user account. In any case, that is a bad situation that should cause a critical failure.
                    // Also fail if the $user record is bad, or if the uid used for Legal is bad.
                    throw new FatalErrorException($this->translator->__('The user record or the UID is invalid or the UID does not match.'));
                }
            }
        }
        $event->data->set(self::EVENT_KEY, $this->validation);
    }

    /**
     * Responds to process_edit hook-like event notifications.
     *
     * @param GenericEvent $event The event that triggered this function call
     *
     * @throws NotFoundHttpException Thrown if a user account does not exist for the uid specified by the event
     *
     * @return void
     */
    public function processEdit(GenericEvent $event)
    {
        if (null === $this->kernel->getModule(LegalConstant::MODNAME)) {
            return;
        }

        $activePolicies = $this->acceptPoliciesHelper->getActivePolicies();
        $eventName = $event->getName();
        if (!isset($this->validation) || $this->validation->hasErrors()) {
            return;
        }

        $user = $event->getSubject();
        $uid = $user['uid'];

        $policiesToCheck = [
            'termsOfUse'              => LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED,
            'privacyPolicy'           => LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED,
            'agePolicy'               => LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED,
            'tradeConditions'         => LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED,
            'cancellationRightPolicy' => LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED,
        ];

        $isLoggedIn = $this->currentUserApi->isLoggedIn();
        $policiesAccepted = $this->validation->getObject();

        if (!$isLoggedIn && ($eventName == 'module.users.ui.process_edit.login_screen' || $eventName == 'module.users.ui.process_edit.login_block')) {
            $isRegistration = false;
            // policies accepted during login
        } else {
            // policies accepted during registration
            $isRegistration = UserUtil::isRegistration($uid);
            $user = UserUtil::getVars($uid, false, 'uid', $isRegistration);
            if (!$user) {
                throw new NotFoundHttpException($this->translator->__('A user account or registration does not exist for the specified uid.'));
            }
        }

        if ($policiesAccepted) {
            $nowUTC = new DateTime('now', new DateTimeZone('UTC'));
            $nowUTCStr = $nowUTC->format(DateTime::ISO8601);
            if (!$isLoggedIn) {
                foreach ($policiesToCheck as $policyName => $acceptedVar) {
                    if ($activePolicies[$policyName]) {
                        UserUtil::setVar($acceptedVar, $nowUTCStr, $uid);
                    }
                }
            } else {
                $editablePolicies = $this->acceptPoliciesHelper->getEditablePolicies();
                foreach ($policiesToCheck as $policyName => $acceptedVar) {
                    if ($activePolicies[$policyName] && $editablePolicies[$policyName]) {
                        UserUtil::setVar($acceptedVar, $nowUTCStr, $uid);
                    }
                }
            }
        }
        // Force the reload of the user record
        $user = UserUtil::getVars($uid, true, 'uid', $isRegistration);
    }

    /**
     * Vetos (denies) a login attempt, and forces the user to accept policies.
     *
     * This handler is triggered by the 'user.login.veto' event.  It vetos (denies) a
     * login attempt if the users's Legal record is flagged to force the user to accept
     * one or more legal agreements.
     *
     * @param GenericEvent $event The event that triggered this handler
     *
     * @return void
     */
    public function acceptPolicies(GenericEvent $event)
    {
        if (null === $this->kernel->getModule(LegalConstant::MODNAME)) {
            return;
        }

        $termsOfUseActive = $this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_TERMS_ACTIVE, false);
        $privacyPolicyActive = $this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_PRIVACY_ACTIVE, false);
        $agePolicyActive = $this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_MINIMUM_AGE, 0) > 0;
        $tradeConditionsActive = $this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE, false);
        $cancellationRightPolicyActive = $this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE, false);
        if (!$termsOfUseActive && !$privacyPolicyActive && !$agePolicyActive && !$tradeConditionsActive && !$cancellationRightPolicyActive) {
            return;
        }

        $userObj = $event->getSubject();
        if (!isset($userObj) || $userObj->getUid() <= 2) {
            return;
        }

        if ($termsOfUseActive) {
            $termsOfUseAcceptedDateTimeStr = $this->currentUserApi->get(LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $userObj['uid'], false);
            $termsOfUseAccepted = isset($termsOfUseAcceptedDateTimeStr) && !empty($termsOfUseAcceptedDateTimeStr);
        } else {
            $termsOfUseAccepted = true;
        }
        if ($privacyPolicyActive) {
            $privacyPolicyAcceptedDateTimeStr = $this->currentUserApi->get(LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $userObj['uid'], false);
            $privacyPolicyAccepted = isset($privacyPolicyAcceptedDateTimeStr) && !empty($privacyPolicyAcceptedDateTimeStr);
        } else {
            $privacyPolicyAccepted = true;
        }
        if ($agePolicyActive) {
            $agePolicyAcceptedDateTimeStr = $this->currentUserApi->get(LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED, $userObj['uid'], false);
            $agePolicyAccepted = isset($agePolicyAcceptedDateTimeStr) && !empty($agePolicyAcceptedDateTimeStr);
        } else {
            $agePolicyAccepted = true;
        }
        if ($tradeConditionsActive) {
            $tradeConditionsAcceptedDateTimeStr = $this->currentUserApi->get(LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED, $userObj['uid'], false);
            $tradeConditionsAccepted = isset($tradeConditionsAcceptedDateTimeStr) && !empty($tradeConditionsAcceptedDateTimeStr);
        } else {
            $tradeConditionsAccepted = true;
        }
        if ($cancellationRightPolicyActive) {
            $cancellationRightPolicyAcceptedDateTimeStr = $this->currentUserApi->get(LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED, $userObj['uid'], false);
            $cancellationRightPolicyAccepted = isset($cancellationRightPolicyAcceptedDateTimeStr) && !empty($cancellationRightPolicyAcceptedDateTimeStr);
        } else {
            $cancellationRightPolicyAccepted = true;
        }
        if ($termsOfUseAccepted && $privacyPolicyAccepted && $agePolicyAccepted && $tradeConditionsAccepted && $cancellationRightPolicyAccepted) {
            return;
        }

        $event->stopPropagation();
        $event->setArgument('returnUrl', $this->router->generate('zikulalegalmodule_user_acceptpolicies', ['login' => true]));
        $this->request->getSession()->set(LegalConstant::SESSION_ACCEPT_POLICIES_VAR, $userObj->getUid());
        $this->request->getSession()->getFlashBag()->add('error', $this->translator->__('Your log-in request was not completed. You must review and confirm your acceptance of one or more site policies prior to logging in.'));
    }
}
