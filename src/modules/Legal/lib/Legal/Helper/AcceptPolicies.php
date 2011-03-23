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
 * AcceptPolicies
 */
class Legal_Helper_AcceptPolicies
{
    /**
     * The module name.
     * 
     * @var string
     */
    protected $name;

    public function __construct() {
        $this->name = Legal::MODNAME;
    }
    
    /**
     * Retrieves flags indicating which policies are active.
     *
     * @return array An array containing flags indicating whether each policy is active or not.
     */
    public function getActivePolicies()
    {
        $termsOfUseActive = ModUtil::getVar(Legal::MODNAME, Legal::MODVAR_TERMS_ACTIVE, false);
        $privacyPolicyActive = ModUtil::getVar(Legal::MODNAME, Legal::MODVAR_PRIVACY_ACTIVE, false);
        $agePolicyActive = (ModUtil::getVar(Legal::MODNAME, Legal::MODVAR_MINIMUM_AGE, 0) != 0);
        
        return array(
            'termsOfUse'    => $termsOfUseActive,
            'privacyPolicy' => $privacyPolicyActive,
            'agePolicy'     => $agePolicyActive,
        );
    }

    /**
     * Retrieves flags indicating which policies the user with the given uid has already accepted.
     *
     * @param numeric $uid A valid uid.
     * 
     * @return array An array containing flags indicating whether each policy has been accepted by the user or not.
     */
    public function getAcceptedPolicies($uid = null)
    {
        if (isset($uid)) {
            $isRegistration = UserUtil::isRegistration($uid);
        } else {
            $isRegistration = false;
        }
        
        $termsOfUseAcceptedDateStr      = (isset($uid) && !empty($uid)) ? UserUtil::getVar(Legal::ATTRIBUTE_TERMSOFUSE_ACCEPTED, $uid, false, $isRegistration)  : false;
        $privacyPolicyAcceptedDateStr   = (isset($uid) && !empty($uid)) ? UserUtil::getVar(Legal::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED, $uid, false, $isRegistration): false;
        $agePolicyConfirmedDateStr      = (isset($uid) && !empty($uid)) ? UserUtil::getVar(Legal::ATTRIBUTE_AGEPOLICY_CONFIRMED, $uid, false, $isRegistration)  : false;
        
        $termsOfUseAcceptedDate     = $termsOfUseAcceptedDateStr    ? new DateTime($termsOfUseAcceptedDateStr)      : false;
        $privacyPolicyAcceptedDate  = $privacyPolicyAcceptedDateStr ? new DateTime($privacyPolicyAcceptedDateStr)   : false;
        $agePolicyConfirmedDate     = $agePolicyConfirmedDateStr    ? new DateTime($agePolicyConfirmedDateStr)      : false;
        
        $now = new DateTime();
        
        $termsOfUseAccepted     = $termsOfUseAcceptedDate   ? ($termsOfUseAcceptedDate <= $now)     : false;
        $privacyPolicyAccepted  = $privacyPolicyAcceptedDate? ($privacyPolicyAcceptedDate <= $now)  : false;
        $agePolicyConfirmed     = $agePolicyConfirmedDate   ? ($agePolicyConfirmedDate <= $now)     : false;
        
        return array(
            'termsOfUse'    => $termsOfUseAccepted,
            'privacyPolicy' => $privacyPolicyAccepted,
            'agePolicy'     => $agePolicyConfirmed,
        );
    }

    /**
     * Determine whether the current user can view the acceptance/confirmation status of certain policies.
     * 
     * If the current user is the subject user, then the user can always see his status for each policy. If the current user is not the
     * same as the subject user, then the current user can only see the status if he has ACCESS_MODERATE access for the policy.
     *
     * @param numeric $uid The uid of the subject account record (not the current user, but the subject user); optional.
     * 
     * @return array An array containing flags indicating whether the current user is permitted to view the specified policy.
     */
    public function getViewablePolicies($uid = null)
    {
        $currentUid = UserUtil::getVar('uid');
        
        return array(
            'termsOfUse'    => (isset($uid) && ($uid == $currentUid)) ? true : SecurityUtil::checkPermission($this->name . '::termsofuse', '::', ACCESS_MODERATE),
            'privacyPolicy' => (isset($uid) && ($uid == $currentUid)) ? true : SecurityUtil::checkPermission($this->name . '::privacypolicy', '::', ACCESS_MODERATE),
            'agePolicy'     => (isset($uid) && ($uid == $currentUid)) ? true : SecurityUtil::checkPermission($this->name . '::agepolicy', '::', ACCESS_MODERATE),
        );
    }
    
    /**
     * Determine whether the current user can edit the acceptance/confirmation status of certain policies.
     * 
     * The current user can only edit the status if he has ACCESS_EDIT access for the policy, whether he is the subject user or not. The ability to edit
     * status for login and new registrations is handled differently, and does not count on the output of this function.
     *
     * @param numeric $uid The uid of the subject account record (not the current user, but the subject user); optional.
     * 
     * @return array An array containing flags indicating whether the current user is permitted to edit the specified policy.
     */
    public function getEditablePolicies()
    {
        return array(
            'termsOfUse'    => SecurityUtil::checkPermission($this->name . '::termsofuse', '::', ACCESS_EDIT),
            'privacyPolicy' => SecurityUtil::checkPermission($this->name . '::privacypolicy', '::', ACCESS_EDIT),
            'agePolicy'     => SecurityUtil::checkPermission($this->name . '::agepolicy', '::', ACCESS_EDIT),
        );
    }
    
}
