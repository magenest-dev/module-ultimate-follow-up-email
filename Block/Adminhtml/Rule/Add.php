<?php
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Rule;

use Magento\Backend\Block\Widget\Context;

class Add extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $registry;

    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Add button save
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_blockGroup = 'Magenest_UltimateFollowupEmail';
        $this->_controller = 'adminhtml_rule';
        $this->_mode       = 'add';

        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->update('save', 'id', 'save_button');

        $this->buttonList->update('reset', 'id', 'reset_button');
        if (!$this->registry->registry('type')) {
            $this->buttonList->remove('save');
        }
    }


    /**
     * Get add new review header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('New Rule');
    }
}
