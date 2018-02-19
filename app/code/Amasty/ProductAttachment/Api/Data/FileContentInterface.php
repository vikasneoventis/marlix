<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\ProductAttachment\Api\Data;


interface FileContentInterface
{
    const BASE64_ENCODED_DATA = 'base64_encoded_data';
    const TYPE = 'type';
    const NAME = 'name';

    /**
     * @return string
     */
    public function getBase64EncodedData();

    /**
     * @param string $base64EncodedData
     *
     * @return FileContentInterface
     */
    public function setBase64EncodedData($base64EncodedData);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     *
     * @return FileContentInterface
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return FileContentInterface
     */
    public function setName($name);

}
