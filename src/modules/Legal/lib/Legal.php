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
 * Module-wide constants for the Legal module.
 *
 * NOTE: Do not define anything other than constants in this interface! Do not convert it to a class!
 */
interface Legal
{
    /**
     * The official internal module name.
     *
     * @var string
     */
    const MODNAME = 'Legal';

    /**
     * The module variable name indicating that the terms of use is active.
     *
     * @var string
     */
    const MODVAR_TERMS_ACTIVE = 'termsOfUseActive';

    /**
     * The module variable name indicating that the privacy policy is active.
     *
     * @var string
     */
    const MODVAR_PRIVACY_ACTIVE = 'privacyPolicyActive';

    /**
     * The module variable name indicating that the accessibility statement is active.
     *
     * @var string
     */
    const MODVAR_ACCESSIBILITY_ACTIVE = 'accessibilityStatementActive';

    /**
     * The module variable containing the minimum age.
     *
     * @var string
     */
    const MODVAR_MINIMUM_AGE = 'minimumAge';

    /**
     * Users account record attribute key for terms of use acceptance
     *
     * @var string
     */
    const ATTRIBUTE_TERMSOFUSE_ACCEPTED = '_Legal_termsOfUseAccepted';

    /**
     * Users account record attribute key for terms of use acceptance
     *
     * @var string
     */
    const ATTRIBUTE_PRIVACYPOLICY_ACCEPTED = '_Legal_privacyPolicyAccepted';

    /**
     * Users account record attribute key for age policy confirmation.
     *
     * @var string
     */
    const ATTRIBUTE_AGEPOLICY_CONFIRMED = '_Legal_agePolicyConfirmed';
}
