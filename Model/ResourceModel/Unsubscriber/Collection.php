<?php
namespace Magenest\UltimateFollowupEmail\Model\ResourceModel\Unsubscriber;

/**
 * Class Collection
 * @package Magenest\Xero\Model\ResourceModel\Queue
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magenest\UltimateFollowupEmail\Model\Unsubscriber', 'Magenest\UltimateFollowupEmail\Model\ResourceModel\Unsubscriber');
    }
}
