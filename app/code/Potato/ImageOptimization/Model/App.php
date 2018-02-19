<?php
namespace Potato\ImageOptimization\Model;

use Magento\Framework\UrlInterface;
use Potato\ImageOptimization\Model\App\ImageOptimization as AppImageOptimization;
use Potato\ImageOptimization\Logger\Logger;
use Potato\ImageOptimization\Model\ResourceModel\Image\CollectionFactory as ImageCollectionFactory;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;

/**
 * Class App
 */
class App
{
    const SERVICE_IMAGES_DATA_NAME = 'potato_service_images';
    const SERVICE_IMAGES_TRANSFER_LIMIT = 20;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var ImageCollectionFactory
     */
    protected $imageCollectionFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var AppImageOptimization
     */
    protected $appImageOptimization;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * App constructor.
     * @param ImageCollectionFactory $imageCollectionFactory
     * @param UrlInterface $urlBuilder
     * @param AppImageOptimization $appImageOptimization
     * @param Logger $logger
     * @param File $file
     */
    public function __construct(
        ImageCollectionFactory $imageCollectionFactory,
        UrlInterface $urlBuilder,
        AppImageOptimization $appImageOptimization,
        Logger $logger,
        File $file
    ) {
        $this->imageCollectionFactory = $imageCollectionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->appImageOptimization = $appImageOptimization;
        $this->logger = $logger;
        $this->file = $file;
    }

    /**
     * @return bool
     */
    public function sendServiceImages()
    {
        /** @var \Potato\ImageOptimization\Model\ResourceModel\Image\Collection $imageCollection */
        $imageCollection = $this->imageCollectionFactory->create();
        $imageCollection->addFilterByStatus(StatusSource::STATUS_SERVICE);
        $imageCollection->setPageSize(self::SERVICE_IMAGES_TRANSFER_LIMIT);
        $images = $imageCollection->toOptionHash();
        $imagesForService = [];
        foreach ($images as $imagePath) {
            $staticImagePath = $this->file->getStaticImagePath($imagePath);
            $imagePath = $this->createImageUrlFromPath($imagePath);
            if (false === $staticImagePath) {
                $imagesForService[] = [
                    'url' => $imagePath
                ];
            } else {
                $staticImagePath = $this->createImageUrlFromPath($staticImagePath);
                $imagesForService[] = [
                    'url' => $staticImagePath,
                    'alternative' => $imagePath
                ];
            }
        }
        if (count($imagesForService)) {
            try {
                $this->appImageOptimization->process($this->urlBuilder->getUrl('po_image/app/save'), $imagesForService);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        return true;
    }

    /**
     * @param string $imagePath
     * @return string
     */
    private function createImageUrlFromPath($imagePath)
    {
        return str_replace(
            BP,
            trim($this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_WEB]), '/'),
            $imagePath
        );
    }
}
