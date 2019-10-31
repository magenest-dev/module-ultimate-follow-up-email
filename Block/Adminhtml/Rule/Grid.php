<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 29/09/2015
 * Time: 23:19
 */

namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Rule;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_status;

    protected $_ruleFactory;


    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magenest\UltimateFollowupEmail\Model\RuleFactory $ruleFactory
     * @param \Magenest\UltimateFollowupEmail\Model\Status $status
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magenest\UltimateFollowupEmail\Model\RuleFactory $ruleFactory,
        \Magenest\UltimateFollowupEmail\Model\Status $status,
        array $data = []
    ) {
        $this->_ruleFactory  = $ruleFactory;
        $this->_status       = $status;
        parent::__construct($context, $backendHelper, $data);
    }


    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rule_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('rule_id');
        $this->setFilterVisibility(false);
    }


    protected function _prepareCollection()
    {
        $collection = $this->_ruleFactory->create()->getCollection();
        $collection->prepareEmailsReport();
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
            'type',
            [
             'header' => __('Type'),
             'index'  => 'type',
            ]
        );
        $this->addColumn(
            'name',
            [
             'header' => __('Name'),
             'index'  => 'name',
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
            'emails',
            [
                'header' => __('Emails Generated'),
                'index'  => 'emails',
                'type' => 'number',
            ]
        );
        $this->addColumn(
            'sent',
            [
                'header' => __('Successfully Sent'),
                'index'  => 'sent',
                'type' => 'number',
            ]
        );
//        $this->addColumn(
//            'opened',
//            [
//                'header' => __('Opened'),
//                'index'  => 'opened',
//                'type' => 'number',
//            ]
//        );
//        $this->addColumn(
//            'clicks',
//            [
//                'header' => __('Clicks'),
//                'index'  => 'clicks',
//                'type' => 'number',
//            ]
//        );
        $this->addColumn(
            'description',
            [
                'header'           => __('Description'),
                'type'             => 'text',
                'index'            => 'description',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'from_date',
            [
             'header' => __('From'),
             'index'  => 'from_date',
             'type'   => 'datetime',

            ]
        );
        $this->addColumn(
            'to_date',
            [
             'header' => __('To'),
             'index'  => 'to_date',
             'type'   => 'datetime'
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
        $this->getMassactionBlock()->setFormFieldName('rule');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
             'label'   => __('Delete'),
             'url'     => $this->getUrl('ultimatefollowupemail/*/massDelete'),
             'confirm' => __('Are you sure?'),
            ]
        );
        $statuses = $this->_status->getOptionArray();
        $this->getMassactionBlock()->addItem(
            'status',
            [
             'label'      => __('Change status'),
             'url'        => $this->getUrl('ultimatefollowupemail/*/massStatus', ['_current' => true]),
             'additional' => [
                              'visibility' => [
                                               'name'   => 'status',
                                               'type'   => 'select',
                                               'class'  => 'required-entry',
                                               'label'  => __('Status'),
                                               'values' => $statuses,
                                              ],
                             ],
            ]
        );

        return $this;
    }


    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('ultimatefollowupemail/*/grid', ['_current' => true]);
    }


    /**
     * @param \Magenest\UltimateFollowupEmail\Model\Rule|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'ultimatefollowupemail/*/edit',
            [
             'id'   => $row->getId(),
             'type' => $row->getType(),
            ]
        );
    }
}
