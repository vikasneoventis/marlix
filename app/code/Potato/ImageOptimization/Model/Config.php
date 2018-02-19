<?php
namespace Potato\ImageOptimization\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Module\Dir\Reader as DirReader;
use Potato\ImageOptimization\Model\Source\System\OptimizationMethod;

/**
 * Class Config
 */
class Config
{
    const PNG_DEFAULT_LIB_PATH      = 'optipng';
    const JPEG_DEFAULT_LIB_PATH     = 'jpegoptim';
    const GIF_DEFAULT_LIB_PATH      = 'gifsicle';

    const PNG_DEFAULT_OPTIONS       = '-o7 -clobber -strip all';
    const JPEG_DEFAULT_OPTIONS      = '-f -o --strip-all --strip-icc --strip-iptc';
    const GIF_DEFAULT_OPTIONS       = '-b -O3';

    const GENERAL_ENABLED           = 'potato_image_optimization/general/is_enabled';
    const GENERAL_IMAGE_BACKUP      = 'potato_image_optimization/general/image_backup';
    const OPTIMIZATION_METHOD       = 'potato_image_optimization/general/optimization_method';

    const PNG_PATH                  = 'potato_image_optimization/png/path';
    const PNG_OPTIONS               = 'potato_image_optimization/png/options';

    const JPG_PATH                  = 'potato_image_optimization/jpg/path';
    const JPG_OPTIONS               = 'potato_image_optimization/jpg/options';
    const JPEG_COMPRESSION_LEVEL    = 'potato_image_optimization/jpg/compression_level';

    const GIF_PATH                  = 'potato_image_optimization/gif/path';
    const GIF_OPTIONS               = 'potato_image_optimization/gif/options';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /** 
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleDirReader;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DirReader $moduleDirReader
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->moduleDirReader = $moduleDirReader;
    }
    
    /**
     * @param int|null $storeId
     * @return bool
     */
    public function canUseService($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $result = $this->scopeConfig->getValue(
            self::OPTIMIZATION_METHOD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $result === OptimizationMethod::USE_SERVICE;
    }
    
    /**
     * @return string
     */
    public function gifPath()
    {
        $xmlSetting = $this->getCustomSettingXml('gif/path');
        if ($xmlSetting) {
            return $xmlSetting;
        }
        return self::GIF_DEFAULT_LIB_PATH;
    }

    /**
     * @return string
     */
    public function gifOptions()
    {
        $xmlSetting = $this->getCustomSettingXml('gif/options');
        if ($xmlSetting) {
            return $xmlSetting;
        }
        return self::GIF_DEFAULT_OPTIONS;
    }
    
    /**
     * @return string
     */
    public function jpgPath()
    {
        $xmlSetting = $this->getCustomSettingXml('jpeg/path');
        if ($xmlSetting) {
            return $xmlSetting;
        }
        return self::JPEG_DEFAULT_LIB_PATH;
    }

    /**
     * @return string
     */
    public function jpgOptions()
    {
        $xmlSetting = $this->getCustomSettingXml('jpeg/options');
        if ($xmlSetting) {
            return $xmlSetting;
        }
        return self::JPEG_DEFAULT_OPTIONS;
    }
    
    /**
     * @return string
     */
    public function pngPath()
    {
        $xmlSetting = $this->getCustomSettingXml('png/path');
        if ($xmlSetting) {
            return $xmlSetting;
        }
        return self::PNG_DEFAULT_LIB_PATH;
    }

    /**
     * @return string
     */
    public function pngOptions()
    {
        $xmlSetting = $this->getCustomSettingXml('png/options');
        if ($xmlSetting) {
            return $xmlSetting;
        }
        return self::PNG_DEFAULT_OPTIONS;
    }
    
    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isAllowImageBackup()
    {
        return (bool)$this->scopeConfig->getValue(
            self::GENERAL_IMAGE_BACKUP
        );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            self::GENERAL_ENABLED
        );
    }

    /**
     * @return string
     */
    public function getCompressionLevel()
    {
        return $this->scopeConfig->getValue(
            self::JPEG_COMPRESSION_LEVEL
        );
    }
    /**
     * @param string $xpath
     * @return null|\SimpleXMLElement[]
     */
    private function getCustomSettingXml($xpath)
    {
        $customFilePath = $this->moduleDirReader->getModuleDir('etc', 'Potato_ImageOptimization')
            . DIRECTORY_SEPARATOR . 'custom_setting.xml';
        if (false === is_readable($customFilePath)) {
            return false;
        }
        $xmlSettings = simplexml_load_file($customFilePath);
        $result = [];
        if (false !== $xmlSettings) {
            $result = $xmlSettings->xpath($xpath);
        }
        return array_shift($result);
    }
}
