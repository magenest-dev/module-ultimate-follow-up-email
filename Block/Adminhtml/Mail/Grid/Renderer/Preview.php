<?php
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Mail\Grid\Renderer;

class Preview extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * Renderer for "Action" column in Mail Log templates grid
     *
     * @param  \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $actions[] = [
            'url' => $this->getUrl('*/*/preview', ['id' => $row->getId()]),
            'popup' => true,
            'caption' => __('Preview'),
        ];
        $this->getColumn()->setActions($actions);
        $this->getColumn()->setNoLink(false);
        return parent::render($row);
    }
}
