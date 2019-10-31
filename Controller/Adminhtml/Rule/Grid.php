<?php
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Rule;

class Grid extends \Magenest\UltimateFollowupEmail\Controller\Adminhtml\Rule
{
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        return $resultLayout;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_rule');
    }
}
