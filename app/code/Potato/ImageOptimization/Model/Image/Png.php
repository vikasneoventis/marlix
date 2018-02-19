<?php

namespace Potato\ImageOptimization\Model\Image;

use Potato\ImageOptimization\Api\Data\ImageInterface;
use Potato\ImageOptimization\Model\App;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;

/**
 * Class Png
 */
class Png extends AbstractImage
{
    const IMAGE_TYPE = IMAGETYPE_PNG;

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
        $beforeFilesize = filesize($image->getPath());
        $libPath = $this->config->pngPath();
        exec(
            $libPath . ' ' . $this->config->pngOptions() . ' "' . $image->getPath() . '" 2>&1',
            $result,
            $error
        );
        $stringResult = implode(' ', $result);
        
        if (empty($result) || $error != 0) {
            $image->setErrorType(ErrorSource::APPLICATION);
            throw new \Exception(__('Application for PNG files optimization returns the error. Error code: %1 %2',
                $error, $stringResult));
        }
        $this->updateStaticContent($image);
        clearstatcache($image->getPath());
        $afterFilesize = filesize($image->getPath());
        $image
            ->setStatus(StatusSource::STATUS_OPTIMIZED)
            ->setResult(__("%1 bytes -> %2 bytes optimized", $beforeFilesize, $afterFilesize));
        return $image;
    }
}
