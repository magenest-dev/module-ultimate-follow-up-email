<?php
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Birthday;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_status;

    protected $customerFactory;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magenest\UltimateFollowupEmail\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magenest\UltimateFollowupEmail\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->customerFactory = $customerFactory;
        $this->_status         = $status;
        parent::__construct($context, $backendHelper, $data);
    }


    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('birthday_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('birthday_filter');
    }


    protected function _prepareCollection()
    {
        $birthDay = $this->_request->getParam('birthday-search');

        if (!$birthDay) {
            $collection = $this->customerFactory->create()
                ->getCollection()
                ->addFieldToFilter('dob', ['notnull' => true]);
        } else {
            $collection = $this->customerFactory->create()->getCollection();

            $collection->getSelect()->where(
                'DATE_FORMAT(dob, "%m-%d")=?',
                $birthDay
            );
        }

        $this->setCollection($collection);

        parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
             'header'           => __('ID'),
             'type'             => 'number',
             'index'            => 'entity_id',
             'header_css_class' => 'col-id',
             'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'email',
            [
             'header' => __('Email'),
             'index'  => 'email',
             'class'  => 'text',
            ]
        );
        $this->addColumn(
            'firstname',
            [
                'header' => __('First Name'),
                'index'  => 'firstname',
                'class'  => 'text',
            ]
        );
        $this->addColumn(
            'lastname',
            [
             'header' => __('Last Name'),
             'index'  => 'lastname',
             'class'  => 'text',
            ]
        );
        $this->addColumn(
            'dob',
            [
             'header' => __('Dob'),
             'index'  => 'dob',
             'type'   => 'date',
            ]
        );
        $this->addColumn(
            'website_id',
            [
             'header' => __('Website'),
             'index'  => 'website_id',
             'type'   => 'text',
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

        return parent::_prepareColumns();
    }


    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('ultimatefollowupemail/*/*', ['_current' => true]);
    }


    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'customer/index/edit',
            ['id' => $row->getId()]
        );
    }
}
