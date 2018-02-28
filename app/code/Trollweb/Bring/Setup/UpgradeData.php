<?php

namespace Trollweb\Bring\Setup;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    private $fieldDataConverterFactory;
    private $queryModifierFactory;

    public function __construct(
        \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory,
        \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
    ) {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
    }

    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {

        // This will run if bring was previously installed with a version lower than 1.5.0
        if (version_compare($context->getVersion(), '1.5.0', '<')) {
            $this->convertSerializedDataToJson($setup);
        }
    }

    // Convert config serialization format from php serialization to json, more details here:
    // Convert values in core_config_data with path carriers/bringpickup/active_methods and carriers/bringdelivered/active_methods
    // http://devdocs.magento.com/guides/v2.2/release-notes/backward-incompatible-changes.html#database-data-format-changes
    private function convertSerializedDataToJson(\Magento\Framework\Setup\ModuleDataSetupInterface $setup)
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(
            \Magento\Framework\DB\DataConverter\SerializedToJson::class
        );

        $queryModifier = $this->queryModifierFactory->create(
            'in',
            [
                'values' => [
                    'path' => [
                        'carriers/bringpickup/active_methods',
                        'carriers/bringdelivered/active_methods',
                    ]
                ]
            ]
        );

        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('core_config_data'),
            'config_id',
            'value',
            $queryModifier
        );
    }
}
