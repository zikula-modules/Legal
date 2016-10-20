<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\LegalModule\Api;

use DBUtil;
use ModUtil;
use SecurityUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Administrative API functions.
 */
class AdminApi extends \Zikula_AbstractApi
{
    /**
     * Reset the agreement to the terms of use for a specific group of users, or all users.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * int $args['gid'] The group id; -1 = none, 0 = all groups.
     *
     * @param array $args All arguments passed to the function.
     *
     * @return bool True if successfully reset, otherwise false.
     *
     * @throws AccessDeniedException Thrown if the user does not have the appropriate access level for the function.
     *
     * @throws \Exception Thrown in cases where expected data is not present or not in an expected form.
     */
    public function resetagreement($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        if (!isset($args['gid']) || $args['gid'] == -1) {
            throw new \Exception();
        }
        $qb = $this->entityManager->createQueryBuilder();
        if ($args['gid'] == 0) {
            //all users
            $query = $qb->update('ZikulaUsersModule:UserEntity', 'u')
                ->set('u.activated', 2)
                ->where('u.uid NOT IN (1,2)')
                ->getQuery();
            $query->execute();
        } else {
            // single group
            // get the group incl members
            $grp = ModUtil::apiFunc('Groups', 'user', 'get', ['gid' => $args['gid']]);
            if ($grp == false) {
                return false;
            }
            // remove anonymous from members array
            if (array_key_exists(1, $grp['members'])) {
                unset($grp['members'][1]);
            }
            // remove admin from members array
            if (array_key_exists(2, $grp['members'])) {
                unset($grp['members'][2]);
            }
            // return if group is empty
            if (count($grp['members']) == 0) {
                return false;
            }
            $query = $qb->update('ZikulaUsersModule:UserEntity', 'u')
                ->set('u.activated', 2)
                ->where('u.uid IN (:members)')
                ->setParameter('members', array_keys($grp['members']))
                ->getQuery();
            $query->execute();
        }

        return true;
    }
    
    /**
     * Get available admin panel links.
     *
     * @return array Array of adminpanel links.
     */
    public function getLinks()
    {
        $links = [];
        if (SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->get('router')->generate('zikulalegalmodule_admin_modifyconfig'),
                'text' => $this->__('Settings'),
                'class' => 'z-icon-es-config'
            ];
        }

        return $links;
    }
}
