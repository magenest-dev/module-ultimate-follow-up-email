<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 09/12/2016
 * Time: 09:49
 */
namespace Magenest\UltimateFollowupEmail\Model\ResourceModel\Link;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magenest\UltimateFollowupEmail\Model\Link', 'Magenest\UltimateFollowupEmail\Model\ResourceModel\Link');
    }
}
