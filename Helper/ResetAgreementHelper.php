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

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserAttributeRepositoryInterface;

/**
 * Helper class for resetting agreements of users.
 */
class ResetAgreementHelper
{
    /**
     * @var UserAttributeRepositoryInterface
     */
    private $userAttributeRepository;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * ResetAgreementHelper constructor.
     *
     * @param UserAttributeRepositoryInterface $attributeRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param PermissionApiInterface $permissionApi
     */
    public function __construct(
        UserAttributeRepositoryInterface $attributeRepository,
        GroupRepositoryInterface $groupRepository,
        PermissionApiInterface $permissionApi
    ) {
        $this->userAttributeRepository = $attributeRepository;
        $this->groupRepository = $groupRepository;
        $this->permissionApi = $permissionApi;
    }

    /**
     * Reset the agreement to the terms of use for a specific group of users, or all users.
     *
     * @param int $groupId The group id; -1 = none, 0 = all groups
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function
     * @throws \Exception            Thrown in cases where expected data is not present or not in an expected form
     *
     * @return bool True if successfully reset, otherwise false
     */
    public function reset($groupId = -1)
    {
        if (!$this->permissionApi->hasPermission(LegalConstant::MODNAME . '::', '::', ACCESS_ADMIN)) {
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

        if ($groupId == 0) {
            $members = [];
        } else {
            $group = $this->groupRepository->find($groupId);
            if (empty($group)) {
                return false;
            }
            $members = $group->getUsers();
            if (count($members) == 0) {
                return false;
            }
        }

        $this->userAttributeRepository->setEmptyValueWhereAttributeNameIn($attributeNames, $members);

        return true;
    }
}
