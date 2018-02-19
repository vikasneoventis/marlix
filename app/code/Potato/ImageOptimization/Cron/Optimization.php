<?php

namespace Potato\ImageOptimization\Cron;

use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Logger\Logger;
use Potato\ImageOptimization\Model\Manager\Image as ImageManager;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;

class Optimization
{
    /**
     * @var ImageRepositoryInterface
     */
    protected $imageRepository;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * Optimization constructor.
     * @param ImageRepositoryInterface $imageRepository
     * @param Config $config
     * @param Logger $logger
     * @param ImageManager $imageManager
     */
    public function __construct(
        ImageRepositoryInterface $imageRepository,
        Config $config,
        Logger $logger,
        ImageManager $imageManager
    ) {
        $this->imageRepository = $imageRepository;
        $this->config = $config;
        $this->logger = $logger;
        $this->imageManager = $imageManager;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return $this;
        }
        $imageCollection = $this->imageRepository->getNeedToOptimizationList()->getItems();
        foreach ($imageCollection as $image) {
            try {
                $this->imageManager->optimizeImage($image);
            } catch (\Exception $e) {
                $image->setStatus(StatusSource::STATUS_ERROR);
                $this->imageRepository->save($image);
                $this->logger->error($e->getMessage());
            }
        }
        return $this;
    }
}
