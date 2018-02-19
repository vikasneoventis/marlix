<?php
/**
 * This file is part of the Klarna DACH module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Dach\Observer;

use GuzzleHttp\Client;
use Klarna\Core\Exception as KlarnaException;
use Klarna\Core\Helper\ConfigHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class DownloadAndStoreInvoice implements ObserverInterface
{
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * DownloadAndStoreInvoice constructor.
     *
     * @param ConfigHelper    $configHelper
     * @param LoggerInterface $log
     */
    public function __construct(ConfigHelper $configHelper, LoggerInterface $log)
    {
        $this->configHelper = $configHelper;
        $this->client = new Client();
        $this->log = $log;
    }

    /**
     * Download and store Kred invoices
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->configHelper->getCheckoutConfigFlag('invoice_download')) {
            return;
        }
        try {
            /** @var \Magento\Sales\Api\Data\OrderPaymentInterface $payment */
            $payment = $observer->getPayment();

            if ($payment->getMethod() !== 'klarna_kco') {
                return;
            }

            $invoiceId = $payment->getTransactionId();
            if (strpos($invoiceId, '-void') !== false) {
                return;
            }
            $invoiceId = str_replace('-refund', '', $invoiceId);

            $store = $payment->getOrder()->getStore();
            $mid          = $this->configHelper->getApiConfig('merchant_id', $store);
            $sharedSecret = $this->configHelper->getApiConfig('shared_secret', $store);
            $authHash     = urlencode(base64_encode(hash('sha512', sprintf('%s:%s:%s', $mid, $invoiceId, $sharedSecret), true)));

            if ($invoiceId && 'kred' === $this->configHelper->getCheckoutType($store)) {
                $invoiceUrl = $this->configHelper->getApiConfigFlag('test_mode', $store)
                    ? sprintf('https://online.testdrive.klarna.com/invoices/%s.pdf?secret=%s', $invoiceId, $authHash)
                    : sprintf('https://online.klarna.com/invoices/%s.pdf?secret=%s', $invoiceId, $authHash);
                $saveDirectory = BP . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'klarnainvoices';

                if (!@mkdir($saveDirectory) && !is_dir($saveDirectory)) {
                    throw new KlarnaException(__('Unable to create Klarna invoice directory "%1"', $saveDirectory));
                }

                $this->client->get($invoiceUrl, [
                    'save_to' => $saveDirectory . DIRECTORY_SEPARATOR . $invoiceId . '.pdf'
                ]);
            }
        } catch (\Exception $e) {
            $this->log->error($e);
        }
    }
}
