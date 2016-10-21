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

use Doctrine\Common\Persistence\ObjectManager;
use ModUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\PermissionsModule\Api\PermissionApi;

/**
 * Helper class for resetting agreements of users.
 */
class ResetAgreementHelper
{
    /**
     * @var ObjectManager The object manager to be used for determining the repository
     */
    protected $objectManager;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * ResetAgreementHelper constructor.
     *
     * @param ObjectManager $om            The Doctrine object manager
     * @param PermissionApi $permissionApi PermissionApi service instance
     */
    public function __construct(ObjectManager $om, PermissionApi $permissionApi)
    {
        $this->om = $om;
        $this->permissionApi = $permissionApi;
    }

    /**
     * Reset the agreement to the terms of use for a specific group of users, or all users.
     *
     * @param int groupId The group id; -1 = none, 0 = all groups
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     * @throws \Exception            Thrown in cases where expected data is not present or not in an expected form
     *
     * @return bool True if successfully reset, otherwise false
     */
    public function reset($groupId = -1)
    {
        // Security check
        if (!$this->permissionApi->hasPermission(LegalConstant::MODNAME.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        if (!is_numeric($groupId) || $groupId < 0) {
            throw new \Exception();
        }

        $attributeNames = [
            LegalConstant::ATTRIBUTE_TERMSOFUSE_ACCEPTED,
            LegalConstant::ATTRIBUTE_PRIVACYPOLICY_ACCEPTED,
            LegalConstant::ATTRIBUTE_AGEPOLICY_CONFIRMED,
            LegalConstant::ATTRIBUTE_CANCELLATIONRIGHTPOLICY_ACCEPTED,
            LegalConstant::ATTRIBUTE_TRADECONDITIONS_ACCEPTED
        ];

        $qb = $this->om->createQueryBuilder()
            ->update('ZikulaUsersModule:UserAttributeEntity', 'a')
            ->set('a.value', '\'\'')
            ->where('a.name IN (:attributeNames)')
            ->setParameter('attributeNames', $attributeNames);

        $query = null;

        if ($groupId == 0) {
            //all users
            $qb->andWhere('a.user NOT IN (1, 2)')
               ->getQuery()
               ->execute();

            return true;
        }

        // single group

        // get the group incl members
        // @todo legacy call
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $groupId]);
        if (false === $group) {
            return false;
        }

        // remove anonymous from members array
        if (array_key_exists(1, $group['members'])) {
            unset($group['members'][1]);
        }

        // remove admin from members array
        if (array_key_exists(2, $group['members'])) {
            unset($group['members'][2]);
        }

        // return if group is empty
        if (count($group['members']) == 0) {
            return false;
        }

        $qb->andWhere('a.user IN (:members)')
           ->setParameter('members', array_keys($group['members']))
           ->getQuery()
           ->execute();

        return true;
    }
}
