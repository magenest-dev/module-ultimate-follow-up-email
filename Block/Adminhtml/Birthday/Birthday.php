<?php
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Birthday;

class Birthday extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'Magenest_UltimateFollowupEmail';
        $this->_controller = 'adminhtml_birthday';
        parent::_construct();
        $this->removeButton('add');
    }
}
