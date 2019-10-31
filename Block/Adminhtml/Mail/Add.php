<?php

namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Mail;

class Add extends \Magento\Backend\Block\Widget\Form\Container
{


    /**
     * add the button Save
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_blockGroup = 'Magenest_UltimateFollowupEmail';
        $this->_controller = 'adminhtml_mail';
        $this->_mode       = 'add';

        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->update('save', 'id', 'save_button');

        $this->buttonList->update('reset', 'id', 'reset_button');
    }


    /**
     * Get add new review header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('New Mail');
    }
}
