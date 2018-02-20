<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Core\Model\System\Config\Source;

/**
 * Class Version
 */
class Version extends Base
{
    protected $optionName = 'api_versions';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        sort($options);
        return $options;
    }

}
