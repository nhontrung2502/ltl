<?php

namespace Eniture\WweLtlFreightQuotes\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    protected $eavSetupFactory;
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create();
        // Uninstall schema and/or data...
        $eavSetup->removeAttribute(
            Product::ENTITY,
            'en_freight_class'
        );
        $setup->endSetup();
    }
}
