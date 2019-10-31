<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 15/10/2015
 * Time: 10:17
 */

namespace Magenest\UltimateFollowupEmail\Model\ResourceModel;

class AbandonedCart extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $currentDbTime = null;
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $connectionName = null)
    {
        parent::__construct($context, $connectionName);
        $this->scopeConfig = $scopeConfig;
    }

    protected function _construct()
    {
        $this->_init('magenest_ultimatefollowupemail_guest_abandoned_cart', 'id');
    }

    public function getUpperLimit($modify)
    {
        $modify = '-' . $modify . ' minutes';
        $now = new \DateTime($this->getCurrentTime());

        $now->modify($modify);

        $configTimeZone = $this->scopeConfig->getValue('general/locale/timezone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $now->setTimezone(new \DateTimeZone($configTimeZone));

        $upperLimit = $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

        return $upperLimit;
    }

    public function getLowerLimit($modify)
    {
        $now = new \DateTime($this->getCurrentTime());
        $modify = $modify <= 240 ? 480 : $modify * 2;
        $modify = '-' . $modify . ' minutes';
        $now->modify($modify);

        $lowerLimit = $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

        return $lowerLimit;
    }


    public function getAbandonedCartForInsertOperation($minuteLimit)
    {
        $upperLimit = $this->getUpperLimit($minuteLimit);
        $lowerLimit = $this->getLowerLimit($minuteLimit);

        $mainTable = $this->getTable('quote');

        $followUpAbandonedCartTable = $this->getTable('magenest_ultimatefollowupemail_guest_abandoned_cart');

        $select = $this->getConnection()->select()->from(
            ['m' => $mainTable]
        )->joinLeft(
            ['a' => $followUpAbandonedCartTable],
            'm.entity_id = a.quote_id'
        )->where(
            'a.quote_id is null AND m.is_active = 1 AND m.customer_id  is not null  AND m.items_count != 0'
        )->where(
            '(m.updated_at < "' . $upperLimit . '" AND m.updated_at > "'. $lowerLimit .'")'
            .' OR (m.created_at > "'. $lowerLimit .'" AND m.created_at < "' . $upperLimit . '"  AND m.updated_at ="0000-00-00 00:00:00")'
        )->__toString();
        $row = $this->getConnection()->fetchAll($select);
        return $row;
    }


    public function getAbandonedCartOfGuest($minuteLimit)
    {
        $upperLimit = $this->getUpperLimit($minuteLimit);
        $lowerLimit = $this->getLowerLimit($minuteLimit);

        $mainTable = $this->getTable('quote');

        $guestTable = $this->getTable('magenest_ultimatefollowupemail_guest_capture');
        $followUpAbandonedCartTable = $this->getTable('magenest_ultimatefollowupemail_guest_abandoned_cart');

        $select = $this->getConnection()->select()
            ->from(['m' => $mainTable])
            ->join(['a' => $guestTable], 'm.entity_id = a.quote_id')
            ->joinLeft(['b' => $followUpAbandonedCartTable], 'm.entity_id = b.quote_id')
            ->where('a.quote_id is not null AND b.quote_id is null AND m.is_active = 1 AND m.customer_id  is  null  AND m.items_count != 0')
            ->where(
                '(m.updated_at < "' . $upperLimit . '" AND m.updated_at > "'. $lowerLimit .'")'.
                ' OR (m.created_at > "'. $lowerLimit .'" AND m.created_at < "' . $upperLimit . '"  AND m.updated_at ="0000-00-00 00:00:00")'
            )
            ->columns(['entity_id', 'email' => 'a.email'])->__toString();
        $row = $this->getConnection()->fetchAll($select);
        return $row;
    }

    public function getCurrentTime()
    {
        if (is_null($this->currentDbTime)) {
            $row = $this->getConnection()->fetchRow('select now()');
            $this->currentDbTime = array_pop($row);
        }

        return $this->currentDbTime;
    }
}
