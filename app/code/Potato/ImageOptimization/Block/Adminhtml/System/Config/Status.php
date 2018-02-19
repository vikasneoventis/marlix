<?php

namespace Potato\ImageOptimization\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Potato\ImageOptimization\Model\Source\Image\Status as ImageStatus;
use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Potato\ImageOptimization\Model\ResourceModel\Image\CollectionFactory as ImageCollectionFactory;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;

/**
 * Class Status
 */
class Status extends Field
{

    /**
     * @var ImageRepositoryInterface
     */
    protected $imageRepository;

    /**
     * @var ImageCollectionFactory
     */
    protected $imageCollectionFactory;

    /**
     * @var ErrorSource
     */
    protected $errorSource;

    public function __construct(
        Context $context,
        ImageRepositoryInterface $imageRepository,
        ImageCollectionFactory $imageCollectionFactory,
        ErrorSource $errorSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->errorSource = $errorSource;
        $this->imageRepository = $imageRepository;
        $this->imageCollectionFactory = $imageCollectionFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Potato_ImageOptimization::system/config/status.phtml');
    }
    
    /**
     * @param  AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return float
     */
    public function getProgressPercentValue()
    {
        $totalImages = $this->getTotalCount();
        $completedImages = $this->getOptimazedCount();
        if ($totalImages === 0) {
            return 0;
        }
        return round(100 / $totalImages * $completedImages);
    }

    /**
     * @return int
     */
    public function getOptimazedCount()
    {
        $processedImages = $this->imageRepository->getListByStatus(ImageStatus::STATUS_OPTIMIZED)->getTotalCount() +
            $this->imageRepository->getListByStatus(ImageStatus::STATUS_SKIPPED)->getTotalCount();
        return $processedImages;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->imageRepository->getAllList()->getTotalCount();
    }

    public function getSkippedCount()
    {
        return $this->imageRepository->getListByStatus(ImageStatus::STATUS_SKIPPED)->getTotalCount();
    }

    /**
     * @return array
     */
    public function getErrorByGroup()
    {
        /** @var \Potato\ImageOptimization\Model\ResourceModel\Image\Collection $imageCollection */
        $imageCollection = $this->imageCollectionFactory->create();
        $errorTypes = $imageCollection->selectErrorInfoByGroup()->getItems();
        foreach ($errorTypes as $key => $row) {
            $errorTypes[$key]['text'] = $this->errorSource->getLabelByCode($row['code']);
        }
        return $imageCollection->selectErrorInfoByGroup()->getItems();
    }

    public function getSkippedUrl()
    {
        return $this->getUrl('po_image/filter/status', ['status' => ImageStatus::STATUS_SKIPPED]);
    }

    public function getErrorUrlByCode($code)
    {
        return $this->getUrl('po_image/filter/error', ['error_type' => $code]);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        $html = $this->_renderValue($element);
        return $this->_decorateRowHtml($element, $html);
    }
}
