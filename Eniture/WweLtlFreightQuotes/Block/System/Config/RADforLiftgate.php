<?php

namespace Eniture\WweLtlFreightQuotes\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\ScopeInterface;

/**
 * Class RADforLiftgate
 * @package Eniture\WweLtlFreightQuotes\Block\System\Config
 */
class RADforLiftgate extends Field
{
    /**
     * CONFIG_PATH
     */
    const CONFIG_PATH = 'WweLtQuoteSetting/third/RADforLiftgate';
    /**
     *
     */
    const RAD_TEMPLATE = 'system/config/radforliftgate.phtml';

    /**
     * @var Manager
     */
    public $moduleManager;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     * @var string
     */
    public $enable = 'no';

    /**
     * RADforLiftgate constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Manager $moduleManager,
        $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->moduleManager = $moduleManager;
        $this->checkRADModule();
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::RAD_TEMPLATE);
        }
        return $this;
    }

    /**
     * checkRADModule
     */
    public function checkRADModule()
    {
        if ($this->moduleManager->isEnabled('Eniture_ResidentialAddressDetection')) {
            $this->enable = 'yes';
        }
    }

    /**
     * @param AbstractElement $element
     * @return mixed
     */
    public function _getElementHtml(AbstractElement $element)
    {
        $this->setNamePrefix($element->getName())
            ->setHtmlId($element->getHtmlId());

        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getIsChecked()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getOriginAddressUrl()
    {
        return $this->getbaseUrl() . 'wweltlfreightquotes/Warehouse/WweLTLOriginAddress/';
    }
}
