<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 29/09/2015
 * Time: 20:07
 */

namespace Magenest\UltimateFollowupEmail\Model\ResourceModel;

class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{


    protected function _construct()
    {
        $this->_init('magenest_ultimatefollowupemail_rule', 'id');
    }
}
