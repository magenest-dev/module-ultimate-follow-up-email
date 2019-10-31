<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 09/12/2016
 * Time: 09:48
 */

namespace Magenest\UltimateFollowupEmail\Model\ResourceModel;

class Link extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('magenest_ultimatefollowupemail_downloadable_link', 'entity_id');
    }
}
