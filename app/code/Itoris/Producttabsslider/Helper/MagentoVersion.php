<?php
/**
 * Created by PhpStorm.
 * User: Workstation1
 * Date: 17.06.2016
 * Time: 8:56
 */

namespace Itoris\Producttabsslider\Helper;


class MagentoVersion extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_objectManager;
    protected $_version;
    /**
     * @return string
     */
    public function getMagentoVersion(){
        if(!$this->_version) {
            /* @var \Magento\Framework\App\ProductMetadata $productMetadata */
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productMetadata = $this->_objectManager->create('Magento\Framework\App\ProductMetadata');
            $vers=explode('.',$productMetadata->getVersion());
            $vers=$vers[0]+($vers[1]/10);
            $this->_version = $vers;
        }

        return $this->_version;
    }
}