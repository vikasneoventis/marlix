<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Traits\Import;

use Symfony\Component\Console\Output\OutputInterface;

trait Map
{
    /**
     * @param $data
     * @return $this
     */
    public function setMap($data)
    {
        $this->maps = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMap()
    {
        return $this->maps;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function changeFields($data)
    {
        $maps = $this->getMap();
        if (count($maps)) {
            foreach ($maps as $field) {
                if (isset($data[$field['import']])) {
                    $temp = $data[$field['import']];
                    unset($data[$field['import']]);
                    $data[$field['system']] = $temp;
                }
            }
        }

        return $data;
    }

    /**
     * @param $rowData
     * @return mixed
     */
    protected function replaceValue($rowData)
    {
        if ($this->getPlatform()) {
            $rowData = $this->getPlatform()->prepareRow($rowData);
        }
        $maps = $this->getMap();
        if (count($maps)) {
            foreach ($maps as $field) {
                if ($field['default'] != '') {
                    $default = $field['default'];
                    $rowData[$field['system']] = $default;
                }
            }
        }

        return $rowData;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function replaceColumns($data)
    {
        if ($this->getPlatform()) {
            $data = $this->getPlatform()->prepareColumns($data);
        }
        $maps = $this->getMap();
        if (count($maps)) {
            foreach ($maps as $field) {
                if (in_array($field['import'], $data)) {
                    $key = array_search($field['import'], $data);
                    $data[$key] = $field['system'];
                }
                if (empty($field['import'])) {
                    $data[] =$field['system'];
                }
            }
        }

        return $data;
    }

    /**
     * @param $file
     * @return bool|\Magento\Framework\Phrase
     */
    public function checkMimeType($file)
    {
        $message = true;
        $error = __('The format of this file is not suitable');

        if (function_exists('mime_content_type') && !empty($this->mimeTypes)) {
            $result = mime_content_type($file);
            if (!in_array($result, $this->mimeTypes)) {
                $message = $error;
            }
        } else {
            $result = pathinfo($file, PATHINFO_EXTENSION);
            if ($result != $this->extension) {
                $message = $error;
            }
        }


        return $message;
    }
}

