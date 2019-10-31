<?php
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Mail\Grid\Renderer;

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $options = \Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status::getOptionArray();
        $class = 'critical';
        switch($data) {
            case \Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status::STATUS_QUEUED:
                $class = 'notice';
                break;
            case \Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status::STATUS_SENT:
                $class = 'minor';
                break;
            case \Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status::STATUS_FAILED:
                $class = 'critical';
                break;
            case \Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status::STATUS_CANCELLED:
                $class = 'critical';
                break;

        }
        return '<span class="grid-severity-' . $class .'">'. $options[$data] .'</span>';
    }
}
