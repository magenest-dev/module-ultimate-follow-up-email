<?php
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Sms;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $smsFactory;

    protected $_status;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magenest\UltimateFollowupEmail\Model\SmsFactory $smsFactory
     * @param \Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status $status
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magenest\UltimateFollowupEmail\Model\SmsFactory $smsFactory,
        \Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status $status,
        array $data = []
    ) {
        $this->smsFactory  = $smsFactory;
        $this->_status       = $status;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('sms_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('sms_filter');
    }

    protected function _prepareCollection()
    {
        $collection = $this->smsFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header'           => __('ID'),
                'type'             => 'number',
                'index'            => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'status',
            [
                'header'  => __('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'options' => $this->_status->getOptionArray(),
            ]
        );
        $this->addColumn(
            'rule_id',
            [
                'header' => __('Rule ID'),
                'index'  => 'rule_id',
                'type'   => 'abc',
            ]
        );
        $this->addColumn(
            'recipient_name',
            [
                'header' => __('Recipient Name'),
                'index'  => 'recipient_name',
                'type'   => 'abc',
            ]
        );
        $this->addColumn(
            'recipient_mobile',
            [
                'header' => __('Recipient Mobile'),
                'index'  => 'recipient_mobile',
                'type'   => 'abc',
            ]
        );

        $this->addColumn(
            'scheduled_send_date',
            [
                'header' => __('Send At'),
                'index'  => 'scheduled_send_date',
                'type'   => 'datetime',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created at'),
                'index'  => 'created_at',
                'type'   => 'datetime',
            ]
        );

        $this->addColumn(
            'action',
            [
                'header'  => __('Action'),
                'type'    => 'action',
                'index'   => 'id',

                'actions' => [

                    [
                        'caption' => __('Send now'),
                        'url'     => ['base' => '*/sms/send'],
                        'field'   => 'id',
                    ] ,
                    [
                        'caption' => __('Cancel'),
                        'url'     => ['base' => '*/sms/cancel'],
                        'field'   => 'id',
                    ],
                ],

            ]
        );

        return parent::_prepareColumns();
    }
}
