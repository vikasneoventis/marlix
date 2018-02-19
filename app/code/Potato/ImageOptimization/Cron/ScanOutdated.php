<?php

namespace Potato\ImageOptimization\Cron;

use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Logger\Logger;
use Potato\ImageOptimization\Model\Manager\Image as ImageManager;

class ScanOutdated
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
     * Update constructor.
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
        $images = $this->imageRepository->getAllList()->getItems();
        foreach ($images as $image) {
            if (!$this->imageManager->isOutdated($image)) {
                continue;
            }
            try {
                $image->setStatus(StatusSource::STATUS_OUTDATED);
                $this->imageRepository->save($image);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        return $this;
    }
}
