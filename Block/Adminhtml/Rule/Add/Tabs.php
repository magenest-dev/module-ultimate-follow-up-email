<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 29/09/2015
 * Time: 23:46
 */

namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Rule\Add;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{


    protected function _construct()
    {
        parent::_construct();
        $this->setId('ultimatefollowupemail_rule_new_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Follow up email Rule'));
    }
}
