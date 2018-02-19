<?php
/**
 * This file is part of the Klarna Order Management module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Ordermanagement\Plugin;

use Klarna\Core\Helper\ConfigHelper;
use Klarna\Core\Helper\VersionInfo;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\AuthorizationInterface;

class AddPaymentStatusButton
{
    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var VersionInfo
     */
    protected $versionInfo;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * AddAcknowledgeButton constructor.
     *
     * @param UrlInterface           $urlBuilder
     * @param VersionInfo            $versionInfo
     * @param AuthorizationInterface $authorization
     * @param ConfigHelper           $configHelper
     */
    public function __construct(UrlInterface $urlBuilder, VersionInfo $versionInfo, AuthorizationInterface $authorization, ConfigHelper $configHelper)
    {
        $this->urlBuilder = $urlBuilder;
        $this->versionInfo = $versionInfo;
        $this->authorization = $authorization;
        $this->configHelper = $configHelper;
    }

    /**
     * Intercept setLayout method to add custom button
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\View $view
     * @return void
     */
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view)
    {
        if (!$this->authorization->isAllowed('Klarna_Ordermanagement::payment_status')) {
            return;
        }

        $order = $view->getOrder();
        if ($order->getPayment()->getMethod() !== 'klarna_kco') {
            return;
        }
        if (!$order->isPaymentReview()) {
            return;
        }
        if (!$this->configHelper->getPaymentConfigFlag('status_update_enabled', $order->getStore(), 'klarna_kco')) {
            return;
        }

        $message = 'Are you sure you want to do this?';
        $url = $this->urlBuilder->getUrl('klarna/om/paymentstatus/id/' . $order->getId() . '/store/' . $order->getStore()->getCode());

        $view->addButton(
            'order_acknowledge',
            [
                'label'   => __('Update Payment Status'),
                'onclick' => "confirmSetLocation('{$message}', '{$url}')"
            ]
        );
    }
}
