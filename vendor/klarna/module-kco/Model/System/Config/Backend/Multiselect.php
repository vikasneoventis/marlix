<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Model\System\Config\Backend;

class Multiselect extends \Magento\Framework\App\Config\Value
{
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value == '-1') {
            $this->setValue(null);
            return parent::beforeSave();
        }
        if (!is_array($value)) {
            $value = [$value];
        }
        if (in_array('-1', $value, false)) {
            $this->setValue(null);
            return parent::beforeSave();
        }
        return parent::beforeSave();
    }
}
