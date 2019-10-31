<?php
namespace Magenest\UltimateFollowupEmail\Model\ResourceModel;

class Mail extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('magenest_ultimatefollowupemail_mail_log', 'id');
    }


    public function getIsRuleProcessed($rule_id, $key)
    {
        $isProcessed = false;
        $adapter     = $this->_getConnection('write');

        $mainTable = $this->getTable('magenest_ultimatefollowupemail_mail_log');
        $select    = $adapter->select()->from(
            ['m' => $mainTable]
        )->where(
            'm.rule_id=:rule_id and m.duplicated_key =:duplicated_key'
        );
        $bind      = [
                      ':rule_id'        => $rule_id,
                      ':duplicated_key' => $key,
                     ];

        $row = $adapter->fetchRow($select, $bind);

        if (!empty($row)) {
            $isProcessed = true;
        }

        return $isProcessed;
    }
}
