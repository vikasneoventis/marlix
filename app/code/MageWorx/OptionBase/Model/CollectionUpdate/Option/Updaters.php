<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model\CollectionUpdate\Option;

class Updaters
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param array $data
     */
    public function __construct(
        $data = []
    ) {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @param null $key
     * @return mixed|null
     */
    public function getData($key = null)
    {
        if (!$key) {
            return $this->data;
        }

        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
}
