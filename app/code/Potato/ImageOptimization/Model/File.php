<?php
namespace Potato\ImageOptimization\Model;

use Potato\ImageOptimization\Model\ResourceModel\Image\CollectionFactory as ImageCollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Potato\ImageOptimization\Model\Manager\Image as ImageManager;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\View\Design\Theme\ThemePackageList;

/**
 * Class File
 */
class File extends Files
{
    const GALLERY_CACHE_ID = 'po_image_optimization_GALLERY_CACHE_ID';
    const GALLERY_CACHE_LIFETIME = 1800;
    
    const BASE_AREA = 'base';
    const FRONTEND_AREA = 'frontend';

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var ImageCollectionFactory
     */
    protected $imageCollectionFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $asset;

    /**
     * @var ThemePackageList
     */
    protected $themePackageList;
    
    /**
     * File constructor.
     * @param ComponentRegistrar $componentRegistrar
     * @param DirSearch $dirSearch
     * @param ThemePackageList $themePackageList
     * @param ImageCollectionFactory $imageCollectionFactory
     * @param CacheInterface $cache
     * @param Filesystem $filesystem
     * @param AssetRepository $asset
     */
    public function __construct(
        ComponentRegistrar $componentRegistrar,
        DirSearch $dirSearch,
        ThemePackageList $themePackageList,
        ImageCollectionFactory $imageCollectionFactory,
        CacheInterface $cache,
        Filesystem $filesystem,
        AssetRepository $asset
    ) {
        parent::__construct($componentRegistrar, $dirSearch, $themePackageList);
        $this->imageCollectionFactory = $imageCollectionFactory;
        $this->cache = $cache;
        $this->filesystem = $filesystem;
        $this->asset = $asset;
        $this->themePackageList = $themePackageList;
    }
    
    /**
     * @return array
     */
    public function getImageGalleryFiles()
    {
        $images = $this->loadImageGalleryCache();
        if (!is_array($images) || empty($images)) {
            //image only from extensions, not design (need logic for detect static design files)
            $appCodePath = $this->filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath() . 'code';
            $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            $images = array_merge(
                $this->getImagesFromDir($appCodePath),
                $this->getImagesFromDir($mediaPath)
            );
            /** @var \Potato\ImageOptimization\Model\ResourceModel\Image\Collection $imageCollection */
            $imageCollection = $this->imageCollectionFactory->create();
            $optimizedImages = $imageCollection->toOptionHash();
            $images = array_diff($images, $optimizedImages);
            $this->saveImageGalleryCache($images);
        }
        return $images;
    }

    /**
     * @param array $images
     * @return bool
     */
    protected function saveImageGalleryCache(array $images)
    {
        $this->cache->save(serialize($images), self::GALLERY_CACHE_ID, array(), self::GALLERY_CACHE_LIFETIME);
        return true;
    }

    /**
     * @return bool|mixed
     */
    protected function loadImageGalleryCache()
    {
        $data = $this->cache->load(self::GALLERY_CACHE_ID);
        if ($data) {
            return unserialize($data);
        }
        return false;
    }

    /**
     * @return $this
     */
    public function removeImageGalleryCache()
    {
        $this->cache->remove(self::GALLERY_CACHE_ID);
        return $this;
    }
    
    /**
     * @param string $dirPath
     * @return array
     */
    protected function getImagesFromDir($dirPath)
    {
        $files = $this->fastSearch($dirPath);
        if ($files) {
            return $files;
        }
        $files = $this->recursiveSearch($dirPath);
        $result = [];
        foreach ($files as $index => $object){
            $filePath = $index;
            if (
                strpos($filePath, ImageManager::DEFAULT_BACKUP_FOLDER_NAME) !== false ||
                strpos($filePath, '/tmp/') !== false ||
                strpos($filePath, '/Test/Unit/') !== false ||
                strpos($filePath, '/.thumbs/') !== false
            ){
                continue;
            }
            $result[] = $filePath;
        }
        return $result;
    }

    /**
     * @param string $dirPath
     * @return \RegexIterator
     */
    protected function recursiveSearch($dirPath)
    {
        $dirIterator = new \RecursiveDirectoryIterator($dirPath);
        $iterator = new \RecursiveIteratorIterator($dirIterator);
        $regex = new \RegexIterator($iterator, '/^.+(.jpe?g|.png|.gif)$/i', \RecursiveRegexIterator::GET_MATCH);
        return $regex;
    }

    /**
     * @param string $dirPath
     * @return array|bool
     */
    protected function fastSearch($dirPath)
    {
        if (!function_exists('exec')) {
            return false;
        }
        $result = [];
        $status = [];
        exec(
            'find ' . $dirPath
            . ' -type f \( -iname \*.jpg -o -iname \*.png -o -iname \*.gif -o -iname \*.jpeg \) | egrep -v "'
            . ImageManager::DEFAULT_BACKUP_FOLDER_NAME . '|'
            . '|tmp|.thumbs" 2>&1', $result, $status
        );
        if ($status === 0) {
            return $result;
        }
        return false;
    }

    /**
     * Get image path in pub/static directory
     * @param string $imagePath
     * @return bool|string
     */
    public function getStaticImagePath($imagePath)
    {
        //todo get image path from used theme
        $staticImagePathList = $this->getAllStaticImages($imagePath);
        return array_shift($staticImagePathList);
    }

    /**
     * Get all pub/static images (all theme, all area, all locations) by original image path 
     * @param string $imagePath
     * @return array
     */
    public function getAllStaticImages($imagePath)
    {
        $staticImagePathList = [];
        $fileInfo = $this->_parseModuleStatic($imagePath);
        if (!$fileInfo) {
            return $staticImagePathList;
        }
        list($area, $themePath, $locale, $module, $filePath) = $fileInfo;
        if ($area === self::BASE_AREA) {
            $area = self::FRONTEND_AREA;
        }
        foreach ($this->themePackageList->getThemes() as $theme) {
            $asset = $this->asset->createAsset(
                $filePath,
                [
                    'area' => $area,
                    'theme' => $theme->getVendor() . DIRECTORY_SEPARATOR . $theme->getName(),
                    'locale' => $locale,
                    'module' => $module
                ]
            );
            $staticFilePath = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW)->getAbsolutePath()
                . $asset->getPath();

            if (!is_readable($staticFilePath)) {
                continue;
            }
            $staticImagePathList[] = $staticFilePath;
        }
        return $staticImagePathList;
    }
}
