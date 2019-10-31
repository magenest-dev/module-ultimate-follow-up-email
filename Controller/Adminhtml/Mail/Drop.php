<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 15/01/2016
 * Time: 11:25
 */

namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Mail;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;

class Drop extends Action
{


    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $this->_view->loadLayout('ultimatefollowupemail_mail_preview_popup');
        $this->_view->renderLayout();
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_mail');
    }
}
