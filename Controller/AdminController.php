<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\LegalModule\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Core\Controller\AbstractController;

/**
 * Class AdminController.
 *
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * Route not needed here because method is legacy-only.
     *
     * The legacy administration entry point.
     *
     * @deprecated
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        @trigger_error('The zikulalegalmodule_admin_main route is deprecated. please use zikulalegalmodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulalegalmodule_config_config');
    }

    /**
     * @Route("")
     *
     * The main administration entry point.
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        @trigger_error('The zikulalegalmodule_admin_index route is deprecated. please use zikulalegalmodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulalegalmodule_config_config');
    }
}
