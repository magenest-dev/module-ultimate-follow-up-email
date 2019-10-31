<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 14/06/2016
 * Time: 13:27
 */
namespace Magenest\UltimateFollowupEmail\Model\ResourceModel\Message;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $smsDataCollected = false;

    /**
     * set model and resource for this
     */
    protected function _construct()
    {
        $this->_init('Magenest\UltimateFollowupEmail\Model\Message', 'Magenest\UltimateFollowupEmail\Model\ResourceModel\Message');
    }

    /**
     * get the sms collection using in condition
     */
    public function getSMSCollectionByIds($ids)
    {
        if (!$this->smsDataCollected) {
            $this->addFieldToFilter($this->getResource()->getIdFieldName(), ['in' => $ids]);
            $this->smsDataCollected = true;
        }
        return $this;
    }
}
