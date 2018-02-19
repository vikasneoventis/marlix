<?php

namespace Potato\ImageOptimization\Cron;

use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Model\File;
use Potato\ImageOptimization\Logger\Logger;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ScanImage
{
    const PROCESS_STEP = 25;
    const CACHE_KEY_IMAGE_GALLERY_FILES = 'po_image_optimization_gallery_files';

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
     * @var File
     */
    protected $file;
    
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * ScanImage constructor.
     * @param ImageRepositoryInterface $imageRepository
     * @param Config $config
     * @param Logger $logger
     * @param File $file
     * @param CacheInterface $cache
     */
    public function __construct(
        ImageRepositoryInterface $imageRepository,
        Config $config,
        Logger $logger,
        File $file,
        CacheInterface $cache
    ) {
        $this->imageRepository = $imageRepository;
        $this->config = $config;
        $this->logger = $logger;
        $this->file = $file;
        $this->cache = $cache;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return $this;
        }
        $cacheImages = $this->cache->load(self::CACHE_KEY_IMAGE_GALLERY_FILES);
        $imageCollection = [];
        if ($cacheImages) {
            $imageCollection = unserialize($cacheImages);
        }
        if (!$imageCollection || count($imageCollection) === 0) {
            $imageCollection = $this->file->getImageGalleryFiles();
        }
        $counter = 0;
        foreach ($imageCollection as $key => $path) {
            unset($imageCollection[$key]);
            try {
                $image = $this->imageRepository->getByPath($path);
            } catch (NoSuchEntityException $e) {
                $image = $this->imageRepository->create();
                $image
                    ->setPath($path)
                    ->setStatus(StatusSource::STATUS_PENDING)
                ;
            }
            try {
                $this->imageRepository->save($image);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
            $counter++;
            $this->cache->save(serialize($imageCollection), self::CACHE_KEY_IMAGE_GALLERY_FILES);
            if ($counter == self::PROCESS_STEP) {
                break;
            }
        }
        return $this;
    }
}
