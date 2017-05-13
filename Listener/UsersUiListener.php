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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig_Environment;
use Zikula\Bundle\HookBundle\Hook\ValidationResponse;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Exception\FatalErrorException;
use Zikula\Core\Token\CsrfTokenHandler;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\LegalModule\Helper\AcceptPoliciesHelper;
use Zikula\UsersModule\AccessEvents;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;

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
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * Access to the policy acceptance helper.
     *
     * @var AcceptPoliciesHelper
     */
    private $acceptPoliciesHelper;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ValidationResponse
     */
    private $validation;

    /**
     * @var array
     */
    private $moduleVars;

    /**
     * Constructor.
     *
     * @param RequestStack $requestStack
     * @param Twig_Environment $twig
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     * @param CsrfTokenHandler $csrfTokenHandler
     * @param CurrentUserApiInterface $currentUserApi
     * @param AcceptPoliciesHelper $acceptPoliciesHelper
     * @param EntityManagerInterface $entityManager
     * @param array $moduleVars
     */
    public function __construct(
        RequestStack $requestStack,
        Twig_Environment $twig,
        TranslatorInterface $translator,
        RouterInterface $router,
        CsrfTokenHandler $csrfTokenHandler,
        CurrentUserApiInterface $currentUserApi,
        AcceptPoliciesHelper $acceptPoliciesHelper,
        EntityManagerInterface $entityManager,
        $moduleVars
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->twig = $twig;
        $this->translator = $translator;
        $this->router = $router;
        $this->csrfTokenHandler = $csrfTokenHandler;
        $this->currentUserApi = $currentUserApi;
        $this->acceptPoliciesHelper = $acceptPoliciesHelper;
        $this->entityManager = $entityManager;
        $this->moduleVars = $moduleVars;
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
            AccessEvents::LOGIN_VETO                            => ['acceptPolicies'],
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
        $response = new RedirectResponse($url, $type);
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
     * @param string $eventName
     * @return void
     */
    public function uiEdit(GenericEvent $event, $eventName)
    {
        $activePolicies = $this->acceptPoliciesHelper->getActivePolicies();
        $activePolicyCount = array_sum($activePolicies);
        if ($activePolicyCount < 1) {
            return;
        }

        $csrfToken = $this->csrfTokenHandler->generate();
        // Determine if the hook should be displayed, and also set up certain variables, based on the type of event
        // being handled, the state of the subject user account, and who is currently logged in.
        if (!$this->currentUserApi->isLoggedIn()) {
            // If the user is not logged in, then the only two scenarios where we would show the hook contents is if
            // the user is trying to log in and it was vetoed because one or more policies need to be accepted, or if
            // the user is looking at the new user registration form.
            $user = $event->getSubject();
            if (!isset($user) || empty($user)) {
                $user = new UserEntity();
            }
            if (false !== strpos($eventName, 'login_screen')) {
                // It is not shown unless we have a user record (meaning that the first log-in attempt was vetoed.
                if (null !== $user->getUid()) {
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
                $acceptedPolicies = isset($this->validation) ? $this->validation->getObject() : $this->acceptPoliciesHelper->getAcceptedPolicies($user['uid']);
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
     * @param string $eventName
     *
     * @throws AccessDeniedException                         Thrown if the user does not have the appropriate access level for the function, or to
     *                                                       modify the acceptance of policies on a user account other than his own
     * @throws FatalErrorException|\InvalidArgumentException Thrown if the user record retrieved from the POST is in an unexpected form or its data is
     *                                                       unexpected
     *
     * @return void
     */
    public function validateEdit(GenericEvent $event, $eventName)
    {
        // If there is no 'acceptedpolicies_uid' in the POST, then there is no attempt to update the acceptance of policies,
        // So there is nothing to validate.
        if (!$this->request->request->has('acceptedpolicies_uid')) {
            return;
        }

        // Set up the necessary objects for the validation response
        $policiesAccepted = $this->request->request->get('acceptedpolicies_policies', false);

        $uid = $this->request->request->get('acceptedpolicies_uid', false);
        $this->validation = new ValidationResponse($uid ? $uid : '', $policiesAccepted);
        // Get the user record from the event. If there is no user record, create a dummy.
        $user = $event->getSubject();
        if (!isset($user) || empty($user)) {
            $user = new UserEntity();
        }
        $goodUid = isset($uid) && !empty($uid) && is_numeric($uid);
        $goodUidUser = (is_array($user) || is_object($user)) && isset($user['uid']) && is_numeric($user['uid']);
        if (!$this->currentUserApi->isLoggedIn()) {
            // User is not logged in, so this should be either part of a login attempt or a new user registration.
            if (false !== strpos($eventName, 'login_screen')) {
                // A login attempt.
                $goodUid = $goodUid && $uid > UsersConstant::USER_ID_ADMIN;
                $goodUidUser = $goodUidUser && $user['uid'] > UsersConstant::USER_ID_ADMIN;
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
                if (false !== strpos($eventName, 'login_screen')) {
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
     * @param string $eventName
     */
    public function processEdit(GenericEvent $event, $eventName)
    {
        $activePolicies = $this->acceptPoliciesHelper->getActivePolicies();
        if (!isset($this->validation) || $this->validation->hasErrors()) {
            return;
        }

        /** @var UserEntity $user */
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

        if (!$isLoggedIn && false !== strpos($eventName, 'login_screen')) {
            // policies accepted during login
            $isRegistration = false;
        } else {
            // policies accepted during registration
            $uid = $this->currentUserApi->get('uid');
            if (empty($uid)) {
                throw new NotFoundHttpException($this->translator->__('A user account or registration does not exist for the specified uid.'));
            }
        }

        if ($policiesAccepted) {
            $nowUTC = new \DateTime('now', new \DateTimeZone('UTC'));
            $nowUTCStr = $nowUTC->format(\DateTime::ISO8601);
            if (!$isLoggedIn) {
                foreach ($policiesToCheck as $policyName => $acceptedVar) {
                    if ($activePolicies[$policyName]) {
                        $user->setAttribute($acceptedVar, $nowUTCStr);
                    }
                }
            } else {
                $editablePolicies = $this->acceptPoliciesHelper->getEditablePolicies();
                foreach ($policiesToCheck as $policyName => $acceptedVar) {
                    if ($activePolicies[$policyName] && $editablePolicies[$policyName]) {
                        $user->setAttribute($acceptedVar, $nowUTCStr);
                    }
                }
            }
        }
        $this->entityManager->flush();
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
        $termsOfUseActive = isset($this->moduleVars[LegalConstant::MODVAR_TERMS_ACTIVE]) ? $this->moduleVars[LegalConstant::MODVAR_TERMS_ACTIVE] : false;
        $privacyPolicyActive = isset($this->moduleVars[LegalConstant::MODVAR_PRIVACY_ACTIVE]) ? $this->moduleVars[LegalConstant::MODVAR_PRIVACY_ACTIVE] : false;
        $agePolicyActive = isset($this->moduleVars[LegalConstant::MODVAR_MINIMUM_AGE]) ? $this->moduleVars[LegalConstant::MODVAR_MINIMUM_AGE] != 0 : 0;
        $cancellationRightPolicyActive = isset($this->moduleVars[LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE]) ? $this->moduleVars[LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE] : false;
        $tradeConditionsActive = isset($this->moduleVars[LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE]) ? $this->moduleVars[LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE] : false;

        if (!$termsOfUseActive && !$privacyPolicyActive && !$agePolicyActive && !$tradeConditionsActive && !$cancellationRightPolicyActive) {
            return;
        }

        /** @var UserEntity $userObj */
        $userObj = $event->getSubject();
        if (!isset($userObj) || $userObj->getUid() <= 2) {
            return;
        }

        $attributeIsEmpty = function ($name) use ($userObj) {
            if ($userObj->hasAttribute($name)) {
                $v = $userObj->getAttributeValue($name);

                return empty($v);
            }

            return true;
        };
        $termsOfUseAccepted = $termsOfUseActive ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED) : true;
        $privacyPolicyAccepted = $privacyPolicyActive ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED) : true;
        $agePolicyAccepted = $agePolicyActive ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED) : true;
        $tradeConditionsAccepted = true; //$tradeConditionsActive ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED) : true;
        $cancellationRightPolicyAccepted = true; //$cancellationRightPolicyActive ? !$attributeIsEmpty(LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED) : true;

        if ($termsOfUseAccepted && $privacyPolicyAccepted && $agePolicyAccepted && $tradeConditionsAccepted && $cancellationRightPolicyAccepted) {
            return;
        }

        $event->stopPropagation();
        $event->setArgument('returnUrl', $this->router->generate('zikulalegalmodule_user_acceptpolicies', ['login' => true]));
        $this->request->getSession()->set(LegalConstant::SESSION_ACCEPT_POLICIES_VAR, $userObj->getUid());
        $this->request->getSession()->getFlashBag()->add('error', $this->translator->__('Your log-in request was not completed. You must review and confirm your acceptance of one or more site policies prior to logging in.'));
    }
}
