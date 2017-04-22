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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\ThemeModule\Engine\Asset;

/**
 * EuCookieWarningInjectorListener injects a warning to the user that cookies are
 * in use in order to comply with EU regulations.
 *
 * The onKernelResponse method must be connected to the kernel.response event.
 *
 * The Warning is only injected on well-formed HTML (with a proper <body> tag).
 * This means that the Warning is never included in sub-requests or ESI requests.
 */
class EuCookieWarningInjectorListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Asset
     */
    private $assetHelper;

    /**
     * @var string
     */
    private $stylesheetOverride;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     * @param Asset $assetHelper
     * @param string $stylesheetOverride Custom path to css file (optional)
     * @param bool $enabled
     */
    public function __construct(
        TranslatorInterface $translator,
        Asset $assetHelper,
        $stylesheetOverride = null,
        $enabled
    ) {
        $this->translator = $translator;
        $this->assetHelper = $assetHelper;
        $this->stylesheetOverride = $stylesheetOverride;
        $this->enabled = (bool) $enabled;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$this->enabled) {
            return;
        }
        if (!$event->isMasterRequest()) {
            return;
        }
        $response = $event->getResponse();
        $request = $event->getRequest();

        // do not capture redirects or modify XML HTTP Requests or routing or toolbar requests
        if ($request->isXmlHttpRequest()
            || $response->isRedirect()
            || $request->getPathInfo() == '/js/routing'
            || strpos($request->getPathInfo(), '/_wdt')) {
            return;
        }

        // is cookie set?
        if ($request->cookies->has('cb-enabled') && $request->cookies->get('cb-enabled') == 'accepted') {
            return;
        }

        $this->injectWarning($request, $response);
    }

    /**
     * Injects the warning into the Response.
     *
     * @param Request  $request  A Request instance
     * @param Response $response A Response instance
     */
    protected function injectWarning(Request $request, Response $response)
    {
        $content = $response->getContent();

        $posA = strripos($content, '</body>');
        $posB = strripos($content, '</head>');

        if (false === $posA || false == $posB) {
            return;
        }

        // add javascript to bottom of body - jquery is assumed to be present
        $path = $this->assetHelper->resolve('@' . LegalConstant::MODNAME . ':js/jquery.cookiebar/jquery.cookiebar.js');
        $javascript = '<script type="text/javascript" src="'.$path.'"></script>';

        $message = $this->translator->__('We use cookies to track usage and preferences');
        $acceptText = $this->translator->__('I Understand');
        $javascript .= '
<script type="text/javascript">
jQuery(document).ready(function() {
jQuery.cookieBar({
    message: \''.$message.'\',
    acceptText: \''.$acceptText.'\'
});
});
</script>';
        $content = substr($content, 0, $posA) . $javascript . substr($content, $posA);

        // add stylesheet to head
        if (!empty($this->stylesheetOverride) && file_exists($this->stylesheetOverride)) {
            $path = $this->stylesheetOverride;
        } else {
            $path = $this->assetHelper->resolve('@' . LegalConstant::MODNAME . ':js/jquery.cookiebar/jquery.cookiebar.css');
        }
        $css = '<link rel="stylesheet" type="text/css" href="'.$path.'" />';
        $content = substr($content, 0, $posB).$css.substr($content, $posB);

        $response->setContent($content);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -4],
        ];
    }
}
