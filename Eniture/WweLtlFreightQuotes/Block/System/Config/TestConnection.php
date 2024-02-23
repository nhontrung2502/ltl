<?php

namespace Eniture\WweLtlFreightQuotes\Block\System\Config;

use Eniture\WweLtlFreightQuotes\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TestConnection extends Field
{
    const BUTTON_TEMPLATE = 'system/config/testconnection.phtml';

    private $dataHelper;

    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(Context $context, Data $dataHelper, array $data = [])
    {
        $this->context = $context;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return $this->getbaseUrl() . '/wweltlfreightquotes/Test/TestConnection/';
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->addData(
            [
                'id' => 'WweLtTestConnBtn',
                'button_label' => 'Test Connection',
            ]
        );
        return $this->_toHtml();
    }

    /**
     * Show WWE LTL Plan Notice
     * @return string
     */
    public function planNotice()
    {
        $planRefreshUrl = $this->getPlanRefreshUrl();
        return $this->dataHelper->setPlanNotice($planRefreshUrl);
    }
    /**
     * @return url
     */
    public function getPlanRefreshUrl()
    {
        return $this->getbaseUrl().'/wweltlfreightquotes/Test/PlanRefresh/';
    }
}
