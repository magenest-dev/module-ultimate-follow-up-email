<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 15/10/2015
 * Time: 10:25
 */
namespace Magenest\UltimateFollowupEmail\Model\ResourceModel\Mail;

use Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_resource;


    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magenest\UltimateFollowupEmail\Model\Mail', 'Magenest\UltimateFollowupEmail\Model\ResourceModel\Mail');
    }


    /**
     * Get the emails which have sent time greater than current time
     *
     * @return $this
     */
    public function getMailsNeedToBeSent()
    {
        $current_date_time = new \DateTime();

        $currentTime = $current_date_time->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $cond        = 'send_date < '."'$currentTime'";
        $this->getSelect()->where($cond);
        $this->addFieldToFilter('status', Status::STATUS_QUEUED);
        $this->setOrder('created_at', self::SORT_ORDER_ASC);

        return $this;
    }
}
