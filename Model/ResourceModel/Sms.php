<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 15/06/2016
 * Time: 10:17
 */

namespace Magenest\UltimateFollowupEmail\Model\ResourceModel;

/*
    * represent class
 */
class Sms extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{


    /**
     * bind class to table in database
     */
    protected function _construct()
    {
        $this->_init('magenest_ultimatefollowupemail_sms_log', 'id');
    }
}
