<?php
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Cart;

class Cart extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'Magenest_UltimateFollowupEmail';
        $this->_controller = 'adminhtml_ultimatefollowupemail';
        parent::_construct();
        $this->removeButton('add');
    }
}
