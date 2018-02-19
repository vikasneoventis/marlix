<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_AdminPreview
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdminPreview\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;

class Domain extends Template
{
    protected $request;

    protected $storeManager;

    protected $helperBackend;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    protected $authSession;

    protected $_cookieManager;

    public function __construct(
        Template\Context $context,
        Http $request,
        StoreManagerInterface $storeManager,
        \Magento\Backend\Helper\Data $helperBackend,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->helperBackend = $helperBackend;
        $this->jsonHelper = $jsonHelper;
        $this->authSession = $authSession;
        $this->_cookieManager = $cookieManager;
    }

    public function getListDomain()
    {
        $adminLogged = $this->_cookieManager->getCookie('adminLogged');
        $list_domain = $info_domain =  [];
        if ($adminLogged) {
            $phpSESSID = $this->_cookieManager->getCookie('PHPSESSID');
            $websites = $this->storeManager->getWebsites();
            $domain_backend = $this->getRootDomain($this->helperBackend->getHomePageUrl());
            foreach($websites as $website){
                foreach($website->getStores() as $store){
                    $storeObj = $this->storeManager->getStore($store);
                    $storeName = $storeObj->getName();
                    $url = $storeObj->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB).'adminpreview/preview/adminLogined';
                    if ($phpSESSID) {
                       $url = $url.'?SID='.$phpSESSID;
                    }
                    $domain = $this->getRootDomain($url);
                    if(in_array($domain, $list_domain) || $domain == $domain_backend) continue;
                    $list_domain[] = $domain;
                    $info_domain = ['name' => $storeName, 'url' => $url];
                }
            }
        }
        return $this->jsonHelper->jsonEncode($info_domain);
    }


    public function getAdminUrl()
    {
        return $this->helperBackend->getHomePageUrl();
    }

    public function getRootDomain($url)
    {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }

        $domain = implode('.', array_slice(explode('.', parse_url($url, PHP_URL_HOST)), -2));
        return $domain;
    }
}