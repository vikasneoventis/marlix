<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Klarna\Kred\Lib;

class MagentoOrder extends \Klarna_Checkout_Order
{
    public function create(array $data)
    {
        $options = [
            'url' => $this->connector->getDomain() . $this->relativePath,
            'data' => $data
        ];

        return $this->connector->apply('POST', $this, $options);
    }

    public function fetch()
    {
        $options = [
            'url' => $this->location
        ];
        return $this->connector->apply('GET', $this, $options);
    }

    public function update(
        array $data
    ) {
        $options = [
            'url' => $this->location,
            'data' => $data
        ];
        return $this->connector->apply('POST', $this, $options);
    }
}
