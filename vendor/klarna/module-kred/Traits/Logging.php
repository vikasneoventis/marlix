<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Traits;

use Klarna\Kco\Model\Payment\Kco;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @property  LoggerInterface log
 */
trait Logging
{
    /**
     * Log debug messages
     *
     * @param mixed  $message
     * @param string $level
     */
    protected function _debug($message, $level = LogLevel::DEBUG)
    {
        if (empty($message)) {
            return;
        }

        $this->log->log($level, $this->_rawDebugMessage($message));
    }

    /**
     * Raw debug message for logging
     *
     * @param $mixed
     *
     * @return string
     */
    protected function _rawDebugMessage($mixed)
    {
        $message = $mixed;
        if ($mixed instanceof \Klarna_Checkout_ApiErrorException) {
            if ($payload = $mixed->getPayload()) {
                if (isset($payload['http_status_code'])) {
                    $status_code = $payload['http_status_code'];
                } else {
                    $status_code = $mixed->getCode();
                }
                if (isset($payload['internal_message'])) {
                    $error_message = $payload['internal_message'];
                } else {
                    $error_message = print_r($payload, true);
                }
                $message = "{$error_message} - {$status_code}\n";
                $message .= print_r($payload, true) . "\n";
                $message .= print_r($mixed, true);
            }
        } elseif ($mixed instanceof \Klarna_Checkout_HTTP_Response) {
            // build request
            $request = $mixed->getRequest();

            $message = 'Request:' . "\n";
            $message .= $request->getMethod() . ' ' . $request->getURL() . "\n";
            foreach ($request->getHeaders() as $header => $headerValue) {
                $message .= $header . ': ' . $headerValue . "\n";
            }

            $data = $request->getData();
            if (!empty($data)) {
                $json = json_decode($data);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data = json_encode($json, JSON_PRETTY_PRINT);
                }

                $message .= "\n" . $data . "\n\n";
            }

            // build response
            $message .= "Response ({$mixed->getStatus()}):\n";
            foreach ($mixed->getHeaders() as $header => $headerValue) {
                $message .= $header . ': ' . $headerValue . "\n";
            }

            $data = $mixed->getData();
            if (!empty($data)) {
                $json = json_decode($data);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data = json_encode($json, JSON_PRETTY_PRINT);
                }

                $message .= "\n" . $data . "\n\n";
            }
        } elseif (is_object($mixed)) {
            $message = print_r($mixed, true);
        } elseif (is_array($mixed)) {
            $message = print_r($mixed, true);
        }

        return $message;
    }
}
