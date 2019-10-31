<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 14/06/2016
 * Time: 13:25
 */

namespace Magenest\UltimateFollowupEmail\Model\ResourceModel;

class Message extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{


    /**
     * Bind this class to table in database
     */
    protected function _construct()
    {
        $this->_init('magenest_ultimatefollowupemail_mail_sms', 'sms_id');
    }
}
