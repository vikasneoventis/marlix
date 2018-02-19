<?php

namespace Potato\ImageOptimization\Model\Image;

use Potato\ImageOptimization\Api\Data\ImageInterface;
use Potato\ImageOptimization\Api\ImageRepositoryInterface;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Model\App;
use Potato\ImageOptimization\Model\File;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;

/**
 * Class Gif
 */
class Gif extends AbstractImage
{
    const IMAGE_TYPE = IMAGETYPE_GIF;

    /**
     * @var Png
     */
    protected $pngManager;

    /**
     * Gif constructor.
     * @param ImageRepositoryInterface $imageRepository
     * @param Config $config
     * @param App $app
     * @param File $file
     * @param Png $pngManager
     */
    public function __construct(
        ImageRepositoryInterface $imageRepository,
        Config $config,
        App $app,
        File $file,
        Png $pngManager
    ) {
        parent::__construct($imageRepository, $config, $app, $file);
        $this->pngManager = $pngManager;
    }

    /**
     * @param ImageInterface $image
     * @return ImageInterface
     * @throws \Exception
     */
    public function optimize(ImageInterface &$image)
    {
        if (!$this->isAnimatedGif($image)) {
            $pngFileName = dirname($image->getPath())
                . DIRECTORY_SEPARATOR . basename($image->getPath(), ".gif") . '.png';
            if (file_exists($pngFileName)) {
                //after optimization img may be renamed to .png -> need do backup if same file already exists
                rename($pngFileName, $pngFileName . '_tmp');
            }
            $image = $this->pngManager->optimize($image);
            if (file_exists($pngFileName)) {
                rename($pngFileName, $image->getPath());
            }
            if (file_exists($pngFileName . '_tmp')) {
                //restore previously renamed image
                rename($pngFileName . '_tmp', $pngFileName);
            }
            return $image;
        }
        if ($this->config->canUseService()) {
            return $this->sendToService($image);
        }
        $libPath = $this->config->gifPath();
        exec(
            $libPath . ' ' . $this->config->gifOptions() . ' "' . $image->getPath() . '" 2>&1',
            $result,
            $error
        );
        $stringResult = implode(' ', $result);

        if ($error != 0 && strpos($stringResult, 'gifsicle:   trailing garbage after GIF ignored') === false) {
            $image->setErrorType(ErrorSource::APPLICATION);
            throw new \Exception(__('Application for GIF files optimization returns the error. Error code: %1 %2',
                $error, $stringResult));
        }
        $this->updateStaticContent($image);
        $image
            ->setStatus(StatusSource::STATUS_OPTIMIZED)
            ->setResult($stringResult);
        return $image;
    }

    /**
     * @param ImageInterface $image
     * @return bool
     */
    protected function isAnimatedGif(ImageInterface &$image)
    {
        $imagePath = $image->getPath();
        if (!is_readable($imagePath)) {
            $image->setErrorType(ErrorSource::APPLICATION);
            throw new \Exception(__('The file is not readable'));
        }
        $content = file_get_contents($imagePath);
        $strLoc = 0;
        $count = 0;

        // There is no point in continuing after we find a 2nd frame
        while ($count < 2) {
            $where1 = strpos($content, "\x00\x21\xF9\x04", $strLoc);
            if ($where1 === false) {
                break;
            }
            $str_loc = $where1 + 1;
            $where2  = strpos($content, "\x00\x2C", $str_loc);
            if ($where2 === false) {
                break;
            } else {
                if ($where1 + 8 == $where2) {
                    $count++;
                }
                $strLoc = $where2 + 1;
            }
        }
        // gif is animated when it has two or more frames
        return ($count >= 2);
    }
}
