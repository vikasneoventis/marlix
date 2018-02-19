<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Yosto\Slider\Model\ImageFactory;
use Yosto\Slider\Model\SlideFactory;
use Yosto\Slider\Model\SlideImageFactory;
use \Magento\Cms\Model\Template\FilterProvider;

/**
 * Class Slide
 * @package Yosto\Slider\Block
 */
class Slide extends Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var SlideFactory
     */
    protected $slideFactory;

    /**
     * @var ImageFactory
     */
    protected $imageFactory;

    /**
     * @var SlideImageFactory
     */
    protected $slideImageFactory;

    /**
     * @var FilterProvider
     */
    protected $_filterProvider;

    /**
     * @param SlideFactory $slideFactory
     * @param ImageFactory $imageFactory
     * @param SlideImageFactory $slideImageFactory
     * @param FilterProvider $filterProvider
     * @param Context $context
     */
    function __construct(
        SlideFactory $slideFactory,
        ImageFactory $imageFactory,
        SlideImageFactory $slideImageFactory,
        FilterProvider $filterProvider,
        Context $context
    ) {
        $this->imageFactory = $imageFactory;
        $this->slideImageFactory = $slideImageFactory;
        $this->slideFactory = $slideFactory;
       // $this->logger = $logger;
        $this->_filterProvider = $filterProvider;
        parent::__construct($context);
    }

    /**
     * @param $slideId
     * @return $this
     */
    public function getSlide($slideId)
    {
        return $this->slideFactory->create()->load($slideId);
    }

    /**
     * @param $slideId
     * @return $this
     */
    public function getImages($slideId)
    {
        $imageIds = $this->slideImageFactory->create()->getCollection()
            ->addFieldToFilter('slide_id', $slideId)
            ->addFieldToSelect('image_id');
        if($imageIds!=null && count($imageIds)>0) {
            $images = $this->imageFactory->create()->getCollection()
                ->addFieldToFilter('image_id', ['in' => $imageIds->toArray(['image_id'])['items']])
                ->setOrder('sort_order', 'asc');
            return $images;
        }else{
            return [];
        }

    }

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate("Yosto_Slider::widgets/slide.phtml");
    }

    /**
     * @param $content
     * @return string
     * @throws \Exception
     */
    public function getContent($content)
    {
        return $this->_filterProvider->getPageFilter()->filter($content);
    }
}