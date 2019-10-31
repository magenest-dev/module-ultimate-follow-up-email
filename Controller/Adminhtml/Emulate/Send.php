<?php
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Emulate;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

use Magenest\UltimateFollowupEmail\Model\Cron;

class Send extends Action
{
    protected $_cron;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Cron $cron
    ) {
        $this->_context     = $context;
        $this->coreRegistry = $coreRegistry;
        $this->_cron        = $cron;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $this->_cron->sendScheduledMail();
        $this->_cron->scheduleSendSMS();

        return $this->resultRedirectFactory->create()->setPath('ultimatefollowupemail/mail/index');
    }
}
