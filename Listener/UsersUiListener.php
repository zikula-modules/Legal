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

namespace Zikula\LegalModule\Listener;

use DateTime;
use DateTimeZone;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\LegalModule\Form\Type\PolicyType;
use Zikula\LegalModule\Helper\AcceptPoliciesHelper;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\AccessEvents;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Event\UserFormAwareEvent;
use Zikula\UsersModule\Event\UserFormDataEvent;
use Zikula\UsersModule\UserEvents;

/**
 * Handles hook-like event notifications from log-in and registration for the acceptance of policies.
 */
class UsersUiListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private const EVENT_KEY = 'module.legal.users_ui_handler';

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Environment
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
     * @var AcceptPoliciesHelper
     */
    private $acceptPoliciesHelper;

    /**
     * @var array
     */
    private $moduleVars;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var PermissionApiInterface
     */
    protected $permissionApi;

    public function __construct(
        RequestStack $requestStack,
        Environment $twig,
        TranslatorInterface $translator,
        RouterInterface $router,
        VariableApiInterface $variableApi,
        AcceptPoliciesHelper $acceptPoliciesHelper,
        FormFactoryInterface $formFactory,
        PermissionApiInterface $permissionApi
    ) {
        $this->requestStack = $requestStack;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->router = $router;
        $this->moduleVars = $variableApi->getAll('ZikulaLegalModule');
        $this->acceptPoliciesHelper = $acceptPoliciesHelper;
        $this->formFactory = $formFactory;
        $this->permissionApi = $permissionApi;
    }

    /**
     * Establish the handlers for various events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UserEvents::DISPLAY_VIEW => ['uiView'],
            AccessEvents::LOGIN_VETO => ['acceptPolicies'],
            UserEvents::EDIT_FORM => ['amendForm', -256],
            UserEvents::EDIT_FORM_HANDLE => ['editFormHandler'],
        ];
    }

    /**
     * Responds to ui.view hook-like event notifications.
     */
    public function uiView(GenericEvent $event): void
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
            'activePolicies' => $activePolicies,
            'viewablePolicies' => $viewablePolicies,
            'acceptedPolicies' => $acceptedPolicies,
        ];

        $event->data[self::EVENT_KEY] = $this->twig->render('@ZikulaLegalModule/UsersUI/view.html.twig', $templateParameters);
    }

    /**
     * Vetos (denies) a login attempt, and forces the user to accept policies.
     *
     * This handler is triggered by the 'user.login.veto' event.  It vetos (denies) a
     * login attempt if the users's Legal record is flagged to force the user to accept
     * one or more legal agreements.
     */
    public function acceptPolicies(GenericEvent $event): void
    {
        $termsOfUseActive = $this->moduleVars[LegalConstant::MODVAR_TERMS_ACTIVE] ?? false;
        $privacyPolicyActive = $this->moduleVars[LegalConstant::MODVAR_PRIVACY_ACTIVE] ?? false;
        $agePolicyActive = isset($this->moduleVars[LegalConstant::MODVAR_MINIMUM_AGE]) ? 0 !== $this->moduleVars[LegalConstant::MODVAR_MINIMUM_AGE] : 0;
        $cancellationRightPolicyActive = $this->moduleVars[LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE] ?? false;
        $tradeConditionsActive = $this->moduleVars[LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE] ?? false;

        if (!$termsOfUseActive && !$privacyPolicyActive && !$agePolicyActive && !$tradeConditionsActive && !$cancellationRightPolicyActive) {
            return;
        }

        /** @var UserEntity $userObj */
        $userObj = $event->getSubject();
        if (!isset($userObj) || $userObj->getUid() <= UsersConstant::USER_ID_ADMIN) {
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
        $event->setArgument('returnUrl', $this->router->generate('zikulalegalmodule_user_acceptpolicies'));

        $request = $this->requestStack->getMasterRequest();
        if ($request->hasSession() && ($session = $request->getSession())) {
            $session->set(LegalConstant::FORCE_POLICY_ACCEPTANCE_SESSION_UID_KEY, $userObj->getUid());
            $session->getFlashBag()->add('error', $this->translator->trans('Your log-in request was not completed. You must review and confirm your acceptance of one or more site policies prior to logging in.'));
        }
    }

    public function amendForm(UserFormAwareEvent $event): void
    {
        $activePolicies = $this->acceptPoliciesHelper->getActivePolicies();
        if (array_sum($activePolicies) < 1) {
            return;
        }
        $originalDomain = $this->translator->getDomain();
        $this->translator->setDomain('zikulalegalmodule');
        $user = $event->getFormData();
        $uid = !empty($user['uid']) ? $user['uid'] : null;
        $uname = !empty($user['uname']) ? $user['uname'] : null;
        $policyForm = $this->formFactory->create(PolicyType::class, [], [
            'error_bubbling' => true,
            'auto_initialize' => false,
            'mapped' => false,
            'userEditAccess' => $this->permissionApi->hasPermission('ZikulaUsersModule::', $uname . '::' . $uid, ACCESS_EDIT)
        ]);
        $acceptedPolicies = $this->acceptPoliciesHelper->getAcceptedPolicies($uid);
        $event
            ->formAdd($policyForm)
            ->addTemplate('@ZikulaLegalModule/UsersUI/editRegistration.html.twig', [
                'activePolicies' => $this->acceptPoliciesHelper->getActivePolicies(),
                'acceptedPolicies' => $acceptedPolicies,
            ])
        ;
        $this->translator->setDomain($originalDomain);
    }

    public function editFormHandler(UserFormDataEvent $event): void
    {
        $userEntity = $event->getUserEntity();
        $formData = $event->getFormData(LegalConstant::FORM_BLOCK_PREFIX);
        if (!isset($formData)) {
            return;
        }
        $policiesToCheck = [
            'termsOfUse' => LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED,
            'privacyPolicy' => LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED,
            'agePolicy' => LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED,
            'tradeConditions' => LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED,
            'cancellationRightPolicy' => LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED,
        ];
        $nowUTC = new DateTime('now', new DateTimeZone('UTC'));
        $nowUTCStr = $nowUTC->format(DateTime::ATOM);
        $activePolicies = $this->acceptPoliciesHelper->getActivePolicies();
        foreach ($policiesToCheck as $policyName => $acceptedVar) {
            if ($formData['acceptedpolicies_policies'] && $activePolicies[$policyName]) {
                $userEntity->setAttribute($acceptedVar, $nowUTCStr);
            } else {
                $userEntity->delAttribute($acceptedVar);
            }
        }

        // we do not call flush here on purpose because maybe
        // other modules need to care for certain things before
        // the Users module calls flush after all listeners finished
    }
}
