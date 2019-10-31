<?php
namespace Magenest\UltimateFollowupEmail\Controller\Unsubscribe;

use Magenest\UltimateFollowupEmail\Model\Config\Source\UnsubscriberStatus;
use Magenest\UltimateFollowupEmail\Model\Processor\UltimateFollowupEmail;
use Magenest\UltimateFollowupEmail\Model\UnsubscriberFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Encryption\Encryptor;

class Resubscribe extends \Magento\Framework\App\Action\Action
{
    protected $unsubscriberFactory;

    protected $_encryptor;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        UnsubscriberFactory $unsubscriberFactory,
        Encryptor $encryptor
    ) {
    
        parent::__construct($context);
        $this->_encryptor = $encryptor;
        $this->unsubscriberFactory = $unsubscriberFactory;
    }


    public function execute()
    {
        $e = UltimateFollowupEmail::base64UrlDecode($this->getRequest()->getParam('e'));
        if ($r = $this->getRequest()->getParam('r')) {
            $r = $this->_encryptor->decrypt(UltimateFollowupEmail::base64UrlDecode($r));
        } else {
            $r = 0;
        }
        if ($e) {
            $email = $this->_encryptor->decrypt($e);
            $unsubscribe = $this->unsubscriberFactory->create();
            $unsubscribe->getResource()->load($unsubscribe, $email, 'unsubscriber_email');
            $unsubscribe->setUnsubscriberEmail($email);
            $unsubscribe->setUnsubscriberStatus(UnsubscriberStatus::SUBSCRIBED);
            $unsubscribe->setRuleId($r);
            $unsubscribe->getResource()->save($unsubscribe);
            $this->messageManager->addSuccess('You have successfully re-subscribed our email letters');
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('/');
        return $resultRedirect;
    }
}
