<?php
/**
 * This file is part of the Klarna DACH module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Dach\Model;

use Klarna\Dach\Helper\ConfigHelper;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\UrlInterface;

class DachConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ConfigHelper
     */
    protected $helper;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Constructor
     *
     * @param ConfigHelper $helper
     * @param UrlInterface $urlBuilder
     * @param Session      $session
     */
    public function __construct(
        ConfigHelper $helper,
        UrlInterface $urlBuilder,
        Session $session
    ) {
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
        $this->session = $session;
    }

    /**
     * Return config array to be inserted into checkout
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'klarna' => [
                'dach' => [
                    'accept_terms_url'       => $this->getAcceptTermsUrl(),
                    'user_terms_url'         => $this->helper->getUserTermsUrl($this->session->getQuote()->getStore()),
                    'prefill_notice_enabled' => $this->isEnabled(),
                ]
            ]
        ];
    }

    /**
     * Get url to continue to checkout
     */
    public function getAcceptTermsUrl()
    {
        $urlParams = [
            '_nosid'         => true,
            '_forced_secure' => true
        ];

        return $this->urlBuilder->getUrl('*/*/*/terms/accept', $urlParams);
    }

    /**
     * Determine if notice should display
     *
     * @return bool
     */
    public function isEnabled()
    {
        if (!$this->helper->isPrefillNoticeEnabled($this->session->getQuote()->getStore())) {
            return false;
        }
        $terms = $this->session->getData('klarna_fill_notice_terms');
        if ('accept' === $terms) {
            return false;
        }
        return true;
    }
}
