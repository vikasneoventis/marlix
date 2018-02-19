<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Yosto\Slider\Model\SlideImageFactory;
use Yosto\Slider\Model\ResourceModel\SlideImage\CollectionFactory as SlideImageCollectionFactory;
/**
 * Class Image
 * @package Yosto\Slider\Controller\Adminhtml
 */
abstract class Image extends \Magento\Backend\App\Action
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Yosto\Slider\Model\ImageFactory
     */
    protected $imageFactory;

    /**
     * @var AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var SlideImageFactory
     */
    protected $_slideImageFactory;

    protected $_slideImageCollectionFactory;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var
     */
    protected $timezoneInterface;

    /**
     * @var \Psr\log\LoggerInterface
     */
    protected $logger;

    /**
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param \Yosto\Slider\Model\ImageFactory $imageFactory
     * @param SlideImageFactory $slideImageFactory
     * @param AdapterFactory $adapterFactory
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param \Psr\log\LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        \Yosto\Slider\Model\ImageFactory $imageFactory,
        SlideImageFactory $slideImageFactory,
        SlideImageCollectionFactory $slideImageCollectionFactory,
        AdapterFactory $adapterFactory,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        \Psr\log\LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->imageFactory = $imageFactory;
        $this->_slideImageCollectionFactory = $slideImageCollectionFactory;
        $this->_slideImageFactory = $slideImageFactory;
        $this->adapterFactory = $adapterFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Yosto_Slider::manage_image');
    }
}