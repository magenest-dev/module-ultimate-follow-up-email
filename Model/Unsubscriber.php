<?php
namespace Magenest\UltimateFollowupEmail\Model;

class Unsubscriber extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Init
     */
    protected function _construct()
    {
        $this->_init('Magenest\UltimateFollowupEmail\Model\ResourceModel\Unsubscriber');
    }
}
