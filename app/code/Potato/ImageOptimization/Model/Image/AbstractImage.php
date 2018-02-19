<?php

namespace Potato\ImageOptimization\Model\Image;

use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Model\App;
use Potato\ImageOptimization\Api\UtilityInterface;
use Potato\ImageOptimization\Model\File;
use Potato\ImageOptimization\Api\Data\ImageInterface;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;

/**
 * Class AbstractImage
 */
abstract class AbstractImage implements UtilityInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var App
     */
    protected $app;
    
    /**
     * @var File
     */
    protected $file;

    /**
     * @var ImageRepositoryInterface
     */
    protected $imageRepository;

    /**
     * AbstractImage constructor.
     * @param ImageRepositoryInterface $imageRepository
     * @param Config $config
     * @param App $app
     * @param File $file
     */
    public function __construct(
        ImageRepositoryInterface $imageRepository,
        Config $config,
        App $app,
        File $file
    ) {
        $this->imageRepository = $imageRepository;
        $this->config = $config;
        $this->app = $app;
        $this->file = $file;
    }

    /**
     * @param string $imagePath
     * @return string
     */
    protected function getStaticImagePath($imagePath)
    {
        $staticImagePath = $this->file->getStaticImagePath($imagePath);
        if (!$staticImagePath) {
            return false;
        }
        return $staticImagePath;
    }

    /**
     * @param ImageInterface $image
     * @return $this
     * @throws \Exception
     */
    protected function updateStaticContent(ImageInterface &$image)
    {
        if (!is_readable($image->getPath())) {
            $image->setErrorType(ErrorSource::IS_NOT_READABLE);
            throw new \Exception(
                __("Can't read the file. Please check the file permissions.
                    Possible solution: Run command 'chmod -R 777 path_to_magento_store/pub' 
                    or 'chmod -R 777 path_to_magento_store/var' if the file is found in this folders. 
                    Otherwise, run command 'chmod 755 %1'.", $image->getPath())
            );
        }
        $optimizedImage = file_get_contents($image->getPath());
        $staticContentImages = $this->file->getAllStaticImages($image->getPath());
        $staticImagesWithError = [];
        foreach ($staticContentImages as $staticImage) {
            $result = file_put_contents($staticImage, $optimizedImage);
            if (false !== $result) {
                continue;
            }
            $staticImagesWithError[] = $staticImage;
        }
        if (count($staticImagesWithError)) {
            $image->setErrorType(ErrorSource::STATIC_CANT_UPDATE);
            throw new \Exception(
                __("The image has been successfully optimized, but some static content has not updated. 
                Please check the folder permissions and allow write access.
                Possible solution: Run command 'chmod -R 777 path_to_magento_store/pub'")
            );
        }
        return $this;
    }

    /**
     * @param ImageInterface $image
     * @return ImageInterface
     */
    protected function sendToService(ImageInterface $image)
    {
        $image
            ->setStatus(StatusSource::STATUS_SERVICE)
            ->setResult(__('The image has been sent to the service.'));
        return $image;
    }
}
