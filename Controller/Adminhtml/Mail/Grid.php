<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 28/10/2015
 * Time: 15:49
 */

namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Mail;

class Grid extends \Magenest\UltimateFollowupEmail\Controller\Adminhtml\Mail
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
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_mail');
    }
}
