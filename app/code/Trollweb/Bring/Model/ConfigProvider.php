<?php

namespace Trollweb\Bring\Model;


class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    private $assetRepo;
    private $request;
    private $urlBuilder;
    private $config;
    private $logger;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Trollweb\Bring\Helper\Config $config,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function getConfig()
    {
        return [
            "bring" => [
                "logoUrl" => $this->getViewFileUrl("Trollweb_Bring::images/bring-logo.png"),
                "postcodeLookup" => [
                    "isEnabled" => $this->config->postcodeLookupEnabled(),
                    "url" => $this->urlBuilder->getUrl("bring/api/postcodeLookup", ['_secure' => $this->request->isSecure()]),
                ],
            ],
        ];
    }

    public function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->request->isSecure()], $params);
            return $this->assetRepo->getUrlWithParams($fileId, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
            return $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
    }
}
