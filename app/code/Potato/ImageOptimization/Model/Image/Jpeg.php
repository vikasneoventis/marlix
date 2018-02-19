<?php

namespace Potato\ImageOptimization\Model\Image;

use Potato\ImageOptimization\Api\Data\ImageInterface;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;

/**
 * Class Jpeg
 * @package Potato\ImageOptimization\Model\Image
 */
class Jpeg extends AbstractImage
{
    const IMAGE_TYPE = IMAGETYPE_JPEG;

    /**
     * @param ImageInterface $image
     * @return ImageInterface
     * @throws \Exception
     */
    public function optimize(ImageInterface &$image)
    {
        if ($this->config->canUseService()) {
            return $this->sendToService($image);
        }
        $libPath = $this->config->jpgPath();
        exec(
            $libPath . ' ' 
            . $this->config->jpgOptions() . ' ' 
            . $this->config->getCompressionLevel() . ' "' . $image->getPath() . '" 2>&1',
            $result,
            $error
        );
        $stringResult = implode(' ', $result);
        if (empty($result) || $error != 0) {
            $image->setErrorType(ErrorSource::APPLICATION);
            throw new \Exception(__('Application for JP(E)G files optimization returns the error. Error code: %1 %2',
                $error, $stringResult));
        }
        $this->updateStaticContent($image);
        $image
            ->setStatus(StatusSource::STATUS_OPTIMIZED)
            ->setResult($stringResult);
        return $image;
    }
}
