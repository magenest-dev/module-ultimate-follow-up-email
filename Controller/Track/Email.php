<?php
/**
 * Author: Eric Quach
 * Date: 2/20/18
 */
namespace Magenest\UltimateFollowupEmail\Controller\Track;

use Magenest\UltimateFollowupEmail\Model\MailFactory;
use Magenest\UltimateFollowupEmail\Model\Processor\UltimateFollowupEmail;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Email extends \Magento\Framework\App\Action\Action
{
    protected $_encryptor;
    protected $mailFactory;

    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        MailFactory $mailFactory
    )
    {
        parent::__construct($context);
        $this->_encryptor = $encryptor;
        $this->mailFactory = $mailFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $id = $this->_encryptor->decrypt(UltimateFollowupEmail::base64UrlDecode($id));
            $mail = $this->mailFactory->create()->load($id);
            if ($mail->getId()) {
                $mail->setOpened($mail->getOpened()+1)->save();
            }
        }
        $this->getResponse()->setHeader('Content-Type', 'image/jpg', true);
    }

}