<?php

namespace Potato\ImageOptimization\Controller\App;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\Error;
use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Potato\ImageOptimization\Model\App\ImageOptimization;
use Magento\Framework\Controller\ResultFactory;
use Potato\ImageOptimization\Model\File;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Logger\Logger;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;

/**
 * Class Save
 */
class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ImageRepositoryInterface
     */
    protected $imageRepository;

    /**
     * @var ImageOptimization
     */
    protected $appImageOptimization;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Save constructor.
     * @param Context $context
     * @param ImageRepositoryInterface $imageRepository
     * @param ImageOptimization $appImageOptimization
     * @param File $file
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        ImageRepositoryInterface $imageRepository,
        ImageOptimization $appImageOptimization,
        File $file,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->imageRepository = $imageRepository;
        $this->appImageOptimization = $appImageOptimization;
        $this->file = $file;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultForward->forward('noroute');
        try {
            $optimizationResult = $this->getRequest()->getParam('optimization_result');
            $images = $this->appImageOptimization->getOptimizedImages($optimizationResult);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return $resultForward;
        }
        
        /** @var  $image \Potato\ImageOptimization\Model\App\Image\Result;*/
        foreach ($images as $image) {
            $imagePath = $this->createImagePathFromUrl($image->getOriginalUrl());
            if ($image->getAlternativeUrl()) {
                $imagePath = $this->createImagePathFromUrl($image->getAlternativeUrl());
            }
            
            try {
                $imageEntity = $this->imageRepository->getByPath($imagePath);
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e->getMessage());
                continue;
            }    
            
            $optimizedImage = @file_get_contents($image->getOptimizedUrl());
            if (false === $optimizedImage) {
                $imageEntity
                    ->setStatus(StatusSource::STATUS_ERROR)
                    ->setErrorType(ErrorSource::IS_NOT_READABLE)
                    ->setResult(
                        __("The optimized image can't be retrieved from the service. Path to file: %1
                            Possible solution: Submit a support ticket 
                            <a href='https://potatocommerce.com/contacts/'>here</a>",
                        $image->getOptimizedUrl())
                    );
                $this->imageRepository->save($imageEntity);
                continue;
            }

            $result = file_put_contents($imagePath, $optimizedImage);
            if (false === $result) {
                $imageEntity
                    ->setStatus(StatusSource::STATUS_ERROR)
                    ->setErrorType(ErrorSource::CANT_UPDATE)
                    ->setResult(__("Can't update the file. Please check the file permissions.
                                    Possible solution: Run command 'chmod 755 %1'", $imagePath));
                $this->imageRepository->save($imageEntity);
                continue;
            }
            //update static content images
            $staticContentImages = $this->file->getAllStaticImages($imagePath);
            $staticImagesWithError = [];
            foreach ($staticContentImages as $staticImage) {
                $result = file_put_contents($staticImage, $optimizedImage);
                if (false !== $result) {
                    continue;
                }
                $staticImagesWithError[] = $staticImage;
            }
            if (count($staticImagesWithError)) {
                $result = __(
                    "The image has been successfully optimized, but some static content has not updated. 
                    Please check the folder permissions and set write access.
                    Possible solution: Run command 'chmod -R 777 path_to_magento_store/pub'"
                );
                $imageEntity
                    ->setStatus(StatusSource::STATUS_ERROR)
                    ->setErrorType(ErrorSource::STATIC_CANT_UPDATE)
                    ->setResult($result);
                $this->imageRepository->save($imageEntity);
                continue;
            }

            $imageEntity->setStatus(StatusSource::STATUS_OPTIMIZED);
            if (!$image->isOptimized()) {
                $imageEntity
                    ->setErrorType(ErrorSource::APPLICATION)
                    ->setStatus(StatusSource::STATUS_ERROR);
            }
            $imageEntity
                ->setPath($imagePath)
                ->setResult($image->getResult())
                ->setTime(filemtime($imagePath))
            ;
            $this->imageRepository->save($imageEntity);
        }
        $this->file->removeImageGalleryCache();
        
        return $this;
    }

    /**
     * @param string $imageUrl
     * @return string
     */
    private function createImagePathFromUrl($imageUrl)
    {
        $secure = false;
        if (preg_match('/^https:\/\//', $imageUrl)) {
            $secure = true;
        }
        return str_replace(
            trim($this->_url->getBaseUrl(['_type' => UrlInterface::URL_TYPE_WEB, '_secure' => $secure]), '/'),
            BP,
            $imageUrl
        );
    }
}
