<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 15/02/2017
 * Time: 18:35
 */

namespace Magenest\UltimateFollowupEmail\Model\ResourceModel;

class Guest extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('magenest_ultimatefollowupemail_guest_capture', 'id');
    }
}
