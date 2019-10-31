<?php
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Mail;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    protected $_status;

    protected $_mailFactory;


    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magenest\UltimateFollowupEmail\Model\MailFactory $mailFactory
     * @param \Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magenest\UltimateFollowupEmail\Model\MailFactory $mailFactory,
        \Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_mailFactory = $mailFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mail_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('mail_filter');
    }

    public function _prepareFilterButtons()
    {
        parent::_prepareFilterButtons();
        $this->setChild(
            'emulate_sending',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Send Mails'),
                    'onclick' => 'location.href="' . $this->getUrl('ultimatefollowupemail/emulate/send') . '"',
                    'class' => 'action-primary',
                ]
            )->setDataAttribute(['action' => 'emulate_sending_redirect'])
        );
        $this->setChild(
            'emulate_collecting',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Collect Abandoned Cart Mails'),
                    'onclick' => 'location.href="' . $this->getUrl('ultimatefollowupemail/emulate/collect') . '"',
                    'class' => 'action-default action-reset action-tertiary',
                ]
            )->setDataAttribute(['action' => 'emulate_collecting_redirect'])
        );
    }

    public function getMainButtonsHtml()
    {
        $html = $this->getEmulateButtonsHtml();
        $html .= parent::getMainButtonsHtml();
        return $html;
    }

    protected function getEmulateButtonsHtml()
    {
        return $this->getChildHtml('emulate_sending') . $this->getChildHtml('emulate_collecting');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_mailFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'renderer' => 'Magenest\UltimateFollowupEmail\Block\Adminhtml\Mail\Grid\Renderer\Status',
                'options' => $this->_status->getOptionArray(),
            ]
        );
        $this->addColumn(
            'rule_id',
            [
                'header' => __('Rule ID'),
                'index' => 'rule_id',
                'type' => 'text',
            ]
        );
        $this->addColumn(
            'recipient_name',
            [
                'header' => __('Recipient Name'),
                'index' => 'recipient_name',
                'type' => 'text',
            ]
        );
        $this->addColumn(
            'recipient_email',
            [
                'header' => __('Recipient Email'),
                'index' => 'recipient_email',
                'type' => 'text',
            ]
        );
//        $this->addColumn(
//            'opened',
//            [
//                'header' => __('Opened'),
//                'index' => 'opened',
//                'sortable' => false,
//                'filter' => false,
//                'renderer' => 'Magenest\UltimateFollowupEmail\Block\Adminhtml\Mail\Grid\Renderer\Opened'
//            ]
//        );
//        $this->addColumn(
//            'clicks',
//            [
//                'header' => __('Clicks'),
//                'index' => 'clicks',
//                'type' => 'number'
//            ]
//        );
        $this->addColumn(
            'send_date',
            [
                'header' => __('Send At'),
                'index' => 'send_date',
                'type' => 'datetime',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created at'),
                'index' => 'created_at',
                'type' => 'datetime',
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'index' => 'id',
                'filter' => false,
                'sortable' => false,
                'actions' => [

                    [
                        'caption' => __('Send now'),
                        'url' => ['base' => '*/mail/send'],
                        'field' => 'id',
                    ],
                    [
                        'caption' => __('Cancel'),
                        'url' => ['base' => '*/mail/cancel'],
                        'field' => 'id',
                    ],
                ],

            ]
        );
        $this->addColumn(
            'preview',
            [
                'header' => __('Preview'),

                'index' => 'id',
                'sortable' => false,
                'filter' => false,
                'no_link' => true,
                'renderer' => 'Magenest\UltimateFollowupEmail\Block\Adminhtml\Mail\Grid\Renderer\Preview',
            ]
        );
        $this->addColumn(
            'Log',
            [
                'header' => __('Log'),
                'index' => 'log',
            ]
        );


        $block = $this->getLayout()->getBlock('grid.bottom.links');

        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }


    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('mail');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('ultimatefollowupemail/*/massDelete'),
                'confirm' => __('Are you sure?'),
            ]
        );
        $statuses = $this->_status->getOptionArray();
        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('ultimatefollowupemail/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses,
                    ],
                ],
            ]
        );

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('ultimatefollowupemail/*/grid', ['_current' => true]);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            'ultimatefollowupemail/*/preview',
            ['id' => $row->getId()]
        );
    }
}
