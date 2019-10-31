<?php
namespace Magenest\UltimateFollowupEmail\Model\ResourceModel;

use Magenest\UltimateFollowupEmail\Setup\UpgradeSchema;

/**
 * Class Unsubscriber
 * @package Magenest\UltimateFollowupEmail\Model\ResourceModel
 */
class Unsubscriber extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init(UpgradeSchema::UNSUBSCRIBE_TABLE, 'unsubscriber_id');
    }
}
