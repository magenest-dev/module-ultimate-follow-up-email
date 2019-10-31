<?php
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Mail\Grid\Renderer;

class Opened extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $data = $row->getData($this->getColumn()->getIndex());
        if ($data) {
            return "Yes";
        }
        return "No";
    }
}
