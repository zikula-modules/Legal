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

namespace Zikula\LegalModule\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension class.
 */
class TwigExtension extends AbstractExtension
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * Constructor.
     *
     * @param Environment $twig The twig templating service
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Returns a list of custom Twig functions.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('zikulalegalmodule_inlineLink', [$this, 'inlineLink'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * The zikulalegalmodule_inlineLink function displays a single inline user link of a
     * specific policy for the Legal module.
     *
     * Example
     *     {{ zikulalegalmodule_inlineLink('termsOfUse') }}
     *
     * Templates used:
     *      InlineLink/accessibilityStatement.html.twig
     *      InlineLink/cancellationRightPolicy.html.twig
     *      InlineLink/legalNotice.html.twig
     *      InlineLink/notFound.html.twig
     *      InlineLink/privacyPolicy.html.twig
     *      InlineLink/termsOfUse.html.twig
     *      InlineLink/tradeConditions.html.twig
     *
     * @param string $policy The unique string identifier of the policy type whose inline link is to be returned; required
     * @param string $target The target for the generated link, such as "_blank" to open the policy in a new window; optional, default is blank (same effect as "_self")
     *
     * @return string The rendered template output for the specified policy type
     */
    public function inlineLink($policy = '', $target = '')
    {
        $defaultTemplate = '@ZikulaLegalModule/InlineLink/notFound.html.twig';

        $templateParameters = [
            'target' => $target,
        ];

        if (!empty($policy)) {
            try {
                $output = $this->twig->render('@ZikulaLegalModule/InlineLink/'.$policy.'.html.twig', $templateParameters);

                return $output;
            } catch (\Exception $e) {
                // template does not exist
                return $this->twig->render($defaultTemplate, $templateParameters);
            }
        }

        return $this->twig->render($defaultTemplate, $templateParameters);
    }
}
