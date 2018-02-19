<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Block\Catalog\Product;

/**
 * Class Attachment
 *
 * custom paste to template
 * <?php echo $block->getLayout()->createBlock('Amasty\ProductAttachment\Block\Catalog\Product\Attachment', '', ['data'=>['custom_mode'=> true, 'skip_head'=>true]])->toHtml(); ?>
 *
 * @package Amasty\ProductAttachment\Block\Catalog\Product
 */
class Attachment extends \Magento\Framework\View\Element\Template
{
    const TABS_BLOCK_NAME = 'product.info.details';
    const TABS_GROUP_NAME = 'detailed_info';

    /**
     * @var \Amasty\ProductAttachment\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Amasty\ProductAttachment\Model\File
     */
    protected $fileModel;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\ProductAttachment\Helper\Config $helper,
        \Magento\Customer\Model\Session $session,
        \Amasty\ProductAttachment\Model\File $file,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->configHelper = $helper;

        $this->customerSession = $session;
        $this->fileModel = $file;
        $this->coreRegistry = $coreRegistry;

    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Amasty_ProductAttachment::product/files.phtml');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->configHelper->isTab()) {
            $this->addToTabs();
        } elseif($this->configHelper->isAnyInsert() && !$this->getCustomMode()) {
            $this->addToAnyBlock();
        }
       $this->setTitle($this->getBlockLabel());
    }

    public function addToTabs()
    {
        $this->addToBlock(self::TABS_BLOCK_NAME);
        $this->getLayout()->addToParentGroup($this->getNameInLayout(), self::TABS_GROUP_NAME);
    }

    public function addToAnyBlock()
    {
        $parentBlockName = $this->configHelper->getBlockParentName();
        $this->addToBlock($parentBlockName);

    }

    protected function addToBlock($blockName)
    {
        if ($this->getLayout()->isBlock($blockName) || $this->getLayout()->isContainer($blockName) ) {
            $siblingBlockName = $this->configHelper->getBlockSiblingName();
            $isAfterPosition = $this->configHelper->getBlockPosition() == 'after';
            $selfBlockName = $this->getNameInLayout();

            $this->getLayout()->setChild(
                $blockName, $selfBlockName, 'amfile_files'
            );
            $this->getLayout()->reorderChild(
                $blockName, $selfBlockName, $siblingBlockName,
                $isAfterPosition
            );
        }
    }

    /**
     * @return \Amasty\ProductAttachment\Model\ResourceModel\File\Collection
     */
    public function getAttachmentCollection()
    {
        if ($this->hasData('attachment_files')) {
            return $this->getData('attachment_files');
        }
        $this->initAttachmentFiles();

        return $this->getData('attachment_files');
    }

    public function getCountAttachmentFiles()
    {
        $attachmentCollection = $this->getAttachmentCollection();

        return $attachmentCollection->count();
    }

    protected function initAttachmentFiles()
    {

        $productId = $this->getProduct()->getId();
        $storeId = $this->_storeManager->getStore()->getId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $customerId = $this->getCustomerId();

        $fileCollection = $this->getFileCollection()->getFilesFrontend(
            $productId, $storeId, $customerId, $customerGroupId
        );



        $this->setData('attachment_files', $fileCollection);

    }

    /**
     * @param \Amasty\ProductAttachment\Model\File $file
     *
     * @return string
     */
    public function getDownloadUrl($file)
    {
        $fileId = $file->getFileId() ? $file->getFileId() : $file->getId();

        return $file->getDownloadUrlFrontend($fileId, $file->getProductId());
    }

    /**
     * Get product that is being edited
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    public function getCustomerId()
    {
        return $this->coreRegistry->registry($this->configHelper->getCustomerIdSessionKey());
    }

    /**
     * @return \Amasty\ProductAttachment\Model\ResourceModel\File\Collection
     */
    public function getFileCollection()
    {
        return $this->fileModel->getCollection();
    }

    public function toHtml()
    {
        return $this->isAllowed() ? parent::toHtml() : '';
    }

    public function isAllowed()
    {
        return $this->configHelper->getDisplayBlock()
        && $this->getCountAttachmentFiles() > 0
        ;
    }

    public function getBlockLabel()
    {
        return $this->configHelper->getBlockLabel();
    }

    public function displayHead()
    {
        return !$this->configHelper->isTab() && !$this->getSkipHead();
    }

}
