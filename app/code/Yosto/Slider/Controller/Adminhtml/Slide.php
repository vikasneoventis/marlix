<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Yosto\Slider\Model\ResourceModel\SlideImage\CollectionFactory as SlideImageCollectionFactory;

/**
 * Class Slide
 * @package Yosto\Slider\Controller\Adminhtml
 */
abstract class Slide extends \Magento\Backend\App\Action
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
     * @var \Yosto\Slider\Model\SlideFactory
     */
    protected $slideFactory;

    /**
     * @var \Yosto\Slider\Model\ImageFactory
     */
    protected $imageFactory;

    protected $_slideImageCollectionFactory;

    /**
     * @var \Yosto\Slider\Model\TypeFactory
     */
    protected $typeFactory;

    /**
     * @var \Yosto\Slider\Model\SlideImageFactory
     */
    protected $slideImageFactory;

    /**
     * @var \Psr\log\LoggerInterface
     */
    protected $logger;

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param \Yosto\Slider\Model\SlideFactory $slideFactory
     * @param \Yosto\Slider\Model\ImageFactory $imageFactory
     * @param \Yosto\Slider\Model\TypeFactory $typeFactory
     * @param \Yosto\Slider\Model\SlideImageFactory $slideImageFactory
     * @param \Psr\log\LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        \Yosto\Slider\Model\SlideFactory $slideFactory,
        \Yosto\Slider\Model\ImageFactory $imageFactory,
        SlideImageCollectionFactory $slideImageCollectionFactory,
        \Yosto\Slider\Model\TypeFactory $typeFactory,
        \Yosto\Slider\Model\SlideImageFactory $slideImageFactory,
        \Psr\log\LoggerInterface $logger
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->slideFactory = $slideFactory;
        $this->imageFactory = $imageFactory;
        $this->_slideImageCollectionFactory = $slideImageCollectionFactory;
        $this->typeFactory = $typeFactory;
        $this->slideImageFactory = $slideImageFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Yosto_Slider::manage_slide');
    }

}