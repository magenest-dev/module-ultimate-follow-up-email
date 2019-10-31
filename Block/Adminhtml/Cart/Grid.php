<?php
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Cart;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_abandonedCartCollection;

    protected $_quotesFactory;

    protected $_status;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magenest\UltimateFollowupEmail\Model\ResourceModel\AbandonedCart\CollectionFactory $abandonedCartCollection,
        \Magenest\UltimateFollowupEmail\Model\System\Config\Source\AbandonedCart\Status $status,
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quotesFactory,
        array $data = []
    )
    {
        $this->_abandonedCartCollection = $abandonedCartCollection;
        $this->_quotesFactory = $quotesFactory;
        $this->_status = $status;
        parent::__construct($context, $backendHelper, $data);
    }


    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('cart_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('cart_filter');
    }

    protected function _prepareCollection()
    {
        $collection = $this->_abandonedCartCollection->create();

        $quoteTable = $collection->getResource()->getTable('quote');

        $collection->getSelect()->join(
            ['q' => $quoteTable],
            'q.entity_id = main_table.quote_id',
            ['created_at']
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'quote_id',
            [
                'header' => __('Quote ID'),
                'type' => 'number',
                'index' => 'quote_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'customer_email',
            [
                'header' => __('Customer Email'),
                'type' => 'text',
                'index' => 'email',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'type' => 'text',
                'index' => 'type',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'type' => 'text',
                'index' => 'q.created_at',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'getter' => 'getCreatedAt'
            ]
        );

        $this->addColumn(
            'updated_at',
            [
                'header' => __('Updated At'),
                'type' => 'text',
                'index' => 'main_table.updated_at',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'getter' => 'getUpdatedAt'
            ]
        );
        $this->addColumn(
            'created_fue',
            [
                'header' => __('Processed'),
                'index' => 'is_processed',
                'type' => 'options',
                'options' => [
                    0 => __('No'),
                    1 => __('Yes')
                ]
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('ultimatefollowupemail/*/grid', ['_current' => true]);
    }
}
