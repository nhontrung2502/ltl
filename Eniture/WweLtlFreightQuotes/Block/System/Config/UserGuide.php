<?php
namespace Eniture\WweLtlFreightQuotes\Block\System\Config;

use Eniture\WweLtlFreightQuotes\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class UserGuide extends Field
{
    const GUIDE_TEMPLATE = 'system/config/userguide.phtml';

    private $dataHelper;
    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        array $data = []
    ) {
        $this->dataHelper      = $dataHelper;
        parent::__construct($context, $data);
    }
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::GUIDE_TEMPLATE);
        }
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
    /**
     * Show WWE LTL Plan Notice
     * @return string
     */
    public function planNotice()
    {
        return $this->dataHelper->setPlanNotice();
    }
}
