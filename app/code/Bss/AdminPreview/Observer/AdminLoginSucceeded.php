<?php
namespace Bss\AdminPreview\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class AdminLoginSucceeded implements ObserverInterface
{
	protected $authSession;
	protected $backendSession;
	protected $sessionManager;
	protected $_scopeConfig;
	protected $_dataHelper;
	protected $cookieMetadataFactory;
	protected $_cookieManager;

	public function __construct(
		\Magento\Framework\View\Element\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Backend\Model\Session $backendSession,
		\Bss\AdminPreview\Helper\Data $dataHelper,
		\Magento\Framework\Session\SessionManagerInterface $sessionManager,
		\Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
		\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
		) {
		$this->_layout = $context->getLayout();
		$this->storeManager = $storeManager;
		$this->backendSession = $backendSession;
		$this->sessionManager = $sessionManager;
		$this->_scopeConfig = $context->getScopeConfig();
		$this->_dataHelper = $dataHelper;
		$this->_cookieManager = $cookieManager;
		$this->cookieMetadataFactory = $cookieMetadataFactory;
	}
	public function execute(\Magento\Framework\Event\Observer $observer)
	{	
		// var_dump($this->sessionManager->getCookieDomain());die('123123');
		$sessionId = $this->backendSession->getSessionId();
		$lifeTime = $this->_dataHelper->getSessionTimeout();	
		$metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath('/')
            ->setDuration($lifeTime);
        setcookie("adminLogged", "1", time(), "/", "lyskompaniet.no", 0, true);
            // ->setDomain($this->sessionManager->getCookieDomain());
 
        $this->_cookieManager->setPublicCookie(
            'adminLogged',
            '1',
            $metadata
        );
	}

}