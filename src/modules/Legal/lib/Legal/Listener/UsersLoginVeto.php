<?php
/**
 * Copyright Zikula Foundation 2011 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Users
 * @subpackage Listeners
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Provides listeners (handlers) for several events, including 'get.pendingcontent', and 'users.login.veto'.
 *
 * Simple listeners for the Users module can be added here. For more complex listeners/handlers, a separate
 * purpose-built class can be created.
 */
class Legal_Listener_UsersLoginVeto
{
    /**
     * Vetos (denies) a login attempt, and forces the user to accept policies.
     *
     * This handler is triggered by the 'user.login.veto' event.  It vetos (denies) a
     * login attempt if the users's Legal record is flagged to force the user to accept
     * one or more legal agreements.
     *
     * @param Zikula_Event $event The event that triggered this handler.
     *
     * @return void
     */
    public static function acceptPoliciesListener(Zikula_Event $event)
    {
        $domain = ZLanguage::getModuleDomain(Legal::MODNAME);

        $termsOfUseActive = ModUtil::getVar(Legal::MODNAME, Legal::MODVAR_TERMS_ACTIVE, false);
        $privacyPolicyActive = ModUtil::getVar(Legal::MODNAME, Legal::MODVAR_PRIVACY_ACTIVE, false);
        $agePolicyActive = (ModUtil::getVar(Legal::MODNAME, Legal::MODVAR_MINIMUM_AGE, 0) > 0);

        if ($termsOfUseActive || $privacyPolicyActive) {
            $userObj = $event->getSubject();

            if (isset($userObj) && ($userObj['uid'] > 2)) {
                if ($termsOfUseActive) {
                    $termsOfUseAcceptedDateTimeStr = UserUtil::getVar(Legal::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $userObj['uid'], false);
                    $termsOfUseAccepted = isset($termsOfUseAcceptedDateTimeStr) && !empty($termsOfUseAcceptedDateTimeStr);
                } else {
                    $termsOfUseAccepted = true;
                }

                if ($privacyPolicyActive) {
                    $privacyPolicyAcceptedDateTimeStr = UserUtil::getVar(Legal::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $userObj['uid'], false);
                    $privacyPolicyAccepted = isset($privacyPolicyAcceptedDateTimeStr) && !empty($privacyPolicyAcceptedDateTimeStr);
                } else {
                    $privacyPolicyAccepted = true;
                }

                if ($agePolicyActive) {
                    $agePolicyAcceptedDateTimeStr = UserUtil::getVar(Legal::ATTRIBUTE_AGEPOLICY_CONFIRMED, $userObj['uid'], false);
                    $agePolicyAccepted = isset($agePolicyAcceptedDateTimeStr) && !empty($agePolicyAcceptedDateTimeStr);
                } else {
                    $agePolicyAccepted = true;
                }

                if (!$termsOfUseAccepted || !$privacyPolicyAccepted || !$agePolicyAccepted) {
                    $event->setNotified();
                    if (!HookUtil::bindingBetweenAreas('modulehook_area.users.login', 'modulehook_area.legal.acceptpolicies')) {
                        // Only force the redirect if the Legal module's acceptpolicies hook area is not bound to the Users module.
                        $event->data['redirectFunc']  = array(
                            'modname'   => Legal::MODNAME,
                            'type'      => 'user',
                            'func'      => 'acceptPolicies',
                            'args'      => array(
                                'login'     => true,
                            ),
                            'session'   => array(
                                'var'       => 'Legal_Controller_User_acceptPolicies',
                                'namespace' => Legal::MODNAME,
                            )
                        );
                    } else {
                        $event->data['retry'] = true;
                    }

                    if (!$termsOfUseAccepted || !$privacyPolicyAccepted || !$agePolicyAccepted) {
                        LogUtil::registerError(__('Your log-in request was not completed. You must review and confirm your acceptance of one or more site policies prior to logging in.', $domain));
                    }
                }
            }
        }
    }
}
