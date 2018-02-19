<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Plugin;


use Amasty\ProductAttachment\Helper\Config;
use Magento\Framework\Registry;

class CustomerSession
{

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var Config
     */
    protected $configHelper;

    public function __construct(\Magento\Customer\Model\Session $customerSession,
        Registry $registry,
        Config $configHelper
    )
    {
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->configHelper = $configHelper;

    }

    public function afterGenerateXml(\Magento\Framework\View\LayoutInterface $subject, $result)
    {
        $customerIdSessionKey = $this->configHelper->getCustomerIdSessionKey();
        if (!$this->registry->registry($customerIdSessionKey)) {
            $this->registry->unregister($customerIdSessionKey);
            $this->registry->register(
                $customerIdSessionKey,
                $this->customerSession->getCustomerId()
            );
        }

        return $result;
    }

}