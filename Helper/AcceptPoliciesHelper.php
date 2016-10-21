<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\LegalModule\Helper;

use DateTime;
use DateTimeZone;
use ModUtil;
use SecurityUtil;
use UserUtil;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\PermissionsModule\Api\PermissionApi;

/**
 * Helper class to process acceptance of policies.
 */
class AcceptPoliciesHelper
{
    /**
     * The module name.
     *
     * @var string
     */
    protected $name;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * Construct a new instance of the helper, setting the $name attribute to the module name.
     *
     * @param PermissionApi  $permissionApi PermissionApi service instance
     * @param VariableApi    $variableApi   VariableApi service instance
     */
    public function __construct(PermissionApi $permissionApi, VariableApi $variableApi)
    {
        $this->name = LegalConstant::MODNAME;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
    }

    /**
     * Retrieves flags indicating which policies are active.
     *
     * @return array An array containing flags indicating whether each policy is active or not
     */
    public function getActivePolicies()
    {
        $termsOfUseActive = $this->variableApi->get($this->name, LegalConstant::MODVAR_TERMS_ACTIVE, false);
        $privacyPolicyActive = $this->variableApi->get($this->name, LegalConstant::MODVAR_PRIVACY_ACTIVE, false);
        $agePolicyActive = $this->variableApi->get($this->name, LegalConstant::MODVAR_MINIMUM_AGE, 0) != 0;
        $cancellationRightPolicyActive = $this->variableApi->get($this->name, LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE, false);
        $tradeConditionsActive = $this->variableApi->get($this->name, LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE, false);

        return [
            'termsOfUse'              => $termsOfUseActive,
            'privacyPolicy'           => $privacyPolicyActive,
            'agePolicy'               => $agePolicyActive,
            'cancellationRightPolicy' => $cancellationRightPolicyActive,
            'tradeConditions'         => $tradeConditionsActive,
        ];
    }

    /**
     * Helper method to determine acceptance / confirmation states for current user.
     *
     * @param numeric $uid            A valid user id
     * @param bool    $isRegistration Whether we are in registration process or not
     * @param string  $modVarName     Name of modvar storing desired state
     *
     * @return bool Fetched acceptance / confirmation state
     */
    private function determineAcceptanceState($uid, $isRegistration, $modVarName)
    {
        $acceptanceState = false;

        if (!is_null($uid) && !empty($uid) && is_numeric($uid) && $uid > 0) {
            if ($uid > 2) {
                $acceptanceState = UserUtil::getVar($modVarName, $uid, false, $isRegistration);
            } else {
                // The special users (uid == 2 for admin, and uid == 1 for guest) have always accepted all policies.
                $now = new DateTime('now', new DateTimeZone('UTC'));
                $nowStr = $now->format(DateTime::ISO8601);
                $acceptanceState = $nowStr;
            }
        }

        return $acceptanceState;
    }

    /**
     * Retrieves flags indicating which policies the user with the given uid has already accepted.
     *
     * @param numeric $uid A valid user id
     *
     * @return array An array containing flags indicating whether each policy has been accepted by the user or not
     */
    public function getAcceptedPolicies($uid = null)
    {
        if (!is_null($uid)) {
            $isRegistration = UserUtil::isRegistration($uid);
        } else {
            $isRegistration = false;
        }

        $termsOfUseAcceptedDateStr = $this->determineAcceptanceState($uid, $isRegistration,
            LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED
        );
        $privacyPolicyAcceptedDateStr = $this->determineAcceptanceState($uid, $isRegistration,
            LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED
        );
        $agePolicyConfirmedDateStr = $this->determineAcceptanceState($uid, $isRegistration,
            LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED
        );
        $cancellationRightPolicyAcceptedDateStr = $this->determineAcceptanceState($uid, $isRegistration,
            LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED
        );
        $tradeConditionsAcceptedDateStr = $this->determineAcceptanceState($uid, $isRegistration,
            LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED
        );

        $termsOfUseAcceptedDate = $termsOfUseAcceptedDateStr ? new DateTime($termsOfUseAcceptedDateStr) : false;
        $privacyPolicyAcceptedDate = $privacyPolicyAcceptedDateStr ? new DateTime($privacyPolicyAcceptedDateStr) : false;
        $agePolicyConfirmedDate = $agePolicyConfirmedDateStr ? new DateTime($agePolicyConfirmedDateStr) : false;
        $cancellationRightPolicyAcceptedDate = $cancellationRightPolicyAcceptedDateStr ? new DateTime($cancellationRightPolicyAcceptedDateStr) : false;
        $tradeConditionsAcceptedDate = $tradeConditionsAcceptedDateStr ? new DateTime($tradeConditionsAcceptedDateStr) : false;

        $now = new DateTime();
        $termsOfUseAccepted = $termsOfUseAcceptedDate ? $termsOfUseAcceptedDate <= $now : false;
        $privacyPolicyAccepted = $privacyPolicyAcceptedDate ? $privacyPolicyAcceptedDate <= $now : false;
        $agePolicyConfirmed = $agePolicyConfirmedDate ? $agePolicyConfirmedDate <= $now : false;
        $cancellationRightPolicyAccepted = $cancellationRightPolicyAcceptedDate ? $cancellationRightPolicyAcceptedDate <= $now : false;
        $tradeConditionsAccepted = $tradeConditionsAcceptedDate ? $tradeConditionsAcceptedDate <= $now : false;

        return [
            'termsOfUse'              => $termsOfUseAccepted,
            'privacyPolicy'           => $privacyPolicyAccepted,
            'agePolicy'               => $agePolicyConfirmed,
            'cancellationRightPolicy' => $cancellationRightPolicyAccepted,
            'tradeConditions'         => $tradeConditionsAccepted,
        ];
    }

    /**
     * Determine whether the current user can view the acceptance/confirmation status of certain policies.
     *
     * If the current user is the subject user, then the user can always see his status for each policy. If the current user is not the
     * same as the subject user, then the current user can only see the status if he has ACCESS_MODERATE access for the policy.
     *
     * @param numeric $userId The user id of the subject account record (not the current user, but the subject user); optional
     *
     * @return array An array containing flags indicating whether the current user is permitted to view the specified policy
     */
    public function getViewablePolicies($userId = null)
    {
        $currentUid = UserUtil::getVar('uid');
        $isCurrentUser = !is_null($userId) && $userId == $currentUid;

        return [
            'termsOfUse'              => $isCurrentUser ? true : $this->permissionApi->hasPermission($this->name.'::termsOfUse', '::', ACCESS_MODERATE),
            'privacyPolicy'           => $isCurrentUser ? true : $this->permissionApi->hasPermission($this->name.'::privacyPolicy', '::', ACCESS_MODERATE),
            'agePolicy'               => $isCurrentUser ? true : $this->permissionApi->hasPermission($this->name.'::agePolicy', '::', ACCESS_MODERATE),
            'cancellationRightPolicy' => $isCurrentUser ? true : $this->permissionApi->hasPermission($this->name.'::cancellationRightPolicy', '::', ACCESS_MODERATE),
            'tradeConditions'         => $isCurrentUser ? true : $this->permissionApi->hasPermission($this->name.'::tradeConditions', '::', ACCESS_MODERATE),
        ];
    }

    /**
     * Determine whether the current user can edit the acceptance/confirmation status of certain policies.
     *
     * The current user can only edit the status if he has ACCESS_EDIT access for the policy, whether he is the subject user or not. The ability to edit
     * status for login and new registrations is handled differently, and does not count on the output of this function.
     *
     * @return array An array containing flags indicating whether the current user is permitted to edit the specified policy
     */
    public function getEditablePolicies()
    {
        return [
            'termsOfUse'              => $this->permissionApi->hasPermission($this->name.'::termsOfUse', '::', ACCESS_EDIT),
            'privacyPolicy'           => $this->permissionApi->hasPermission($this->name.'::privacyPolicy', '::', ACCESS_EDIT),
            'agePolicy'               => $this->permissionApi->hasPermission($this->name.'::agePolicy', '::', ACCESS_EDIT),
            'cancellationRightPolicy' => $this->permissionApi->hasPermission($this->name.'::cancellationRightPolicy', '::', ACCESS_EDIT),
            'tradeConditions'         => $this->permissionApi->hasPermission($this->name.'::tradeConditions', '::', ACCESS_EDIT),
        ];
    }
}
