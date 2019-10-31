<?php
namespace Magenest\UltimateFollowupEmail\Model\ResourceModel\Sms;

use Magenest\UltimateFollowupEmail\Model\System\Config\Source\Sms\Status;

/**
 * Class Collection
 *
 * @package Magenest\UltimateFollowupEmail\Model\ResourceModel\Mail
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magenest\UltimateFollowupEmail\Model\Sms', 'Magenest\UltimateFollowupEmail\Model\ResourceModel\Sms');
    }

    /**
     * @return $this
     */
    public function getSmsNeedToBeSent()
    {
        $current_date_time = new \DateTime();

        $currentTime = $current_date_time->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $cond        = 'scheduled_send_date < '."'$currentTime'";
        $select      = $this->getSelect()->where($cond);
        $this->addFieldToFilter('status', Status::STATUS_QUEUED);
        $this->setOrder('created_at', self::SORT_ORDER_ASC);

        return $this;
    }
}
