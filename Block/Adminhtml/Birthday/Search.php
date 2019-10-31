<?php
/**
 * Created by PhpStorm.
 * User: duccanh
 * Date: 23/10/2015
 * Time: 10:20
 */
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Birthday;

use Magento\Backend\Block\Template;

class Search extends Template
{


    public function birthday()
    {
        $type = $this->_coreRegistry->registry('birthday');
    }
}
