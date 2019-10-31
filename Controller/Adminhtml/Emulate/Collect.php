<?php
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Emulate;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

use Magenest\UltimateFollowupEmail\Model\Cron;

class Collect extends Action
{
    protected $_cron;

    public function __construct(
        Context $context,
        Cron $cron
    ) {
        $this->_cron = $cron;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->_cron->collectWishlistOnSaleMails();
        $this->_cron->collectWishlistReminderMails();
        $this->_cron->collectAbandonedCarts();
        $this->_cron->collectBirthDayMails();
        $this->_cron->updateItem();
        return $this->resultRedirectFactory->create()->setPath('ultimatefollowupemail/mail/index');
    }
}
