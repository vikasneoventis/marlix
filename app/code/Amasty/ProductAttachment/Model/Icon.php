<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model;

/**
 *
 * @method string getType
 * @method string getImage
 * @method string getIsActive
 * @method string setType
 * @method string setImage
 * @method string setIsActive
 *
 * @package Amasty\ProductAttachment\Model
 */
class Icon extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Amasty\ProductAttachment\Helper\File
     */
    protected $fileHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\ProductAttachment\Helper\File $fileHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context, $registry, $resource, $resourceCollection, $data
        );
        $this->fileHelper = $fileHelper;
    }

    /**
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\ProductAttachment\Model\ResourceModel\Icon');
    }

    public function getIconPath()
    {
        return $this->fileHelper->getPathToIconFolder();
    }

    public function getIconRelativePathByExtension($fileExtension)
    {
        $iconFileName = $this->getIconFileName($fileExtension);
        return $iconFileName != ''
            ? $this->getIconRelativePathByName($iconFileName)
            : '';
    }

    public function getIconFileName($fileExtension)
    {
        return $this->getResource()->getIcon($fileExtension);
    }

    public function getIconRelativePathByName($iconFileName)
    {
        return $this->fileHelper->getIconRelativePathByName($iconFileName);
    }

    public function getIconUrlByExtension($iconExtension)
    {
        $relativePath = $this->getIconRelativePathByExtension($iconExtension);
        return $this->fileHelper->getIconUrl($relativePath);
    }

    /**
     * Retrieve Base files path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->fileHelper->getPathToIconFolder();
    }
}
