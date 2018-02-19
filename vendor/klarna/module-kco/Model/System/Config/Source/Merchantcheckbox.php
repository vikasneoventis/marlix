<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Model\System\Config\Source;

use Klarna\Core\Model\System\Config\Source\Base;

/**
 * Class Merchantcheckbox
 *
 * @package Klarna\Kco\Model\Config\Source
 */
class Merchantcheckbox extends Base
{
    protected $optionName = 'merchant_checkbox';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift($options, ['value' => -1, 'label' => 'Disabled']);

        return $options;
    }
}
