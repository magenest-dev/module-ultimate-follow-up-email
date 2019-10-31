<?php
namespace Magenest\UltimateFollowupEmail\Helper;

use Magenest\UltimateFollowupEmail\Model\Mail;
use Magenest\UltimateFollowupEmail\Model\Processor\UltimateFollowupEmail;
use Magenest\UltimateFollowupEmail\Model\ResourceModel\Mail\Collection;
use Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filter\Template;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as Logger;

class MandrillConnector extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLE = 'ultimatefollowupemail/mandrill/enable';
    const XML_PATH_APIKEY = 'ultimatefollowupemail/mandrill/api_key';

    protected $scopeConfig;
    protected $_mailData = [];
    protected $templateFactory;
    protected $storeManager;
    protected $serializer;

    public function __construct(
        \Magenest\UltimateFollowupEmail\Model\Serializer $serializer,
        \Magento\Framework\App\Helper\Context $context,
        TemplateFactory $templateFactory,
        StoreManagerInterface $storeManager
    )
    {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->templateFactory = $templateFactory;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
    }


    /**
     * get information that mandrill is enable or not
     *
     * @return boolean
     */
    public function getEnableMandrill()
    {
        $enable = $this->scopeConfig->getValue(self::XML_PATH_ENABLE);

        if ($enable === '1') {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    public function getUserInformation()
    {
        $userInfo = null;
        $apiKey = $this->scopeConfig->getValue(self::XML_PATH_APIKEY);
        if (!$apiKey) {
            return "No API key.";
        }
        if (!class_exists("Mandrill")) {
            return "Mandrill is not installed, please run \"composer require mandrill/mandrill\" on your server.";
        }
        $mandrill = new \Mandrill($apiKey);
        try {
            $userInfo = $mandrill->users->info();
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
        if ($userInfo == null) {
            $userInfo = "Your API key is incorrect.";
        }
        return $userInfo;
    }

    /**
     * @param Collection $mailCollection
     * @return $this
     */
    public function sendEmails($mailCollection)
    {
        $apiKey = $this->scopeConfig->getValue(self::XML_PATH_APIKEY);
        try {
            if (!$apiKey) {
                throw new \Exception('Mandrill is enabled but the API key was not found.');
            }
            $mandrill = new \Mandrill($apiKey);
        } catch (\Exception $e) {
            foreach ($mailCollection as $mail) {
                $mail->setStatus(Status::STATUS_QUEUED);
                $mail->setLog($e->getMessage());
                $mail->save();
            }
            return $this;
        }

        /** @var Mail $mail */
        foreach ($mailCollection as $mail) {
            $mailDataKey = $mail->getRuleId() . ':' . $mail->getTemplateId();
            if (!isset($this->_mailData[$mailDataKey])) {
                $this->_mailData[$mailDataKey] = [];
                $this->prepareTemplate($mail, $mailDataKey)
                    ->prepareSender($mail, $mailDataKey)
                    ->prepareSubject($mail, $mailDataKey)
                    ->prepareAttachments($mail, $mailDataKey)
                    ->prepareBcc($mail, $mailDataKey);
            }
            $this->_mailData[$mailDataKey] = array_merge_recursive($this->_mailData[$mailDataKey], $this->getEmailData($mail, $mailDataKey));
        }

        try {
            if (!empty($this->_mailData)) {
                foreach ($this->_mailData as $message) {
                    $result = $mandrill->messages->send($message);
                }
                if (isset($result[0]['status']) && ($result[0]['status'] == 'sent' || $result[0]['status'] == 'queued')) {
                    foreach ($mailCollection as $mail) {
                        $mail->setStatus(Status::STATUS_SENT);
                        $mail->setLog('Ok');
                        $mail->save();
                    }
                } else {
                    $errorCode = isset($result[0]['reject_reason']) ? $result[0]['reject_reason'] : 'unable to retrieve error code';
                    throw new \Exception("Mandrill email rejected, error code: " . $errorCode);
                }
            }
        } catch (\Exception $e) {
            foreach ($mailCollection as $mail) {
                $mail->setStatus(Status::STATUS_FAILED);
                $mail->setLog($e->getMessage());
                $mail->save();
            }
            return $this;
        }
        return $this;
    }

    public function getEmailData(\Magenest\UltimateFollowupEmail\Model\Mail $mail, $mailDataKey)
    {
        $data = [];
        try {
            $data['to'][] = ['email' => $mail->getData('recipient_email')];
            $templateVars = [];
            $templateVars[] = [
                'name' => 'trackingCode',
                'content' => UltimateFollowupEmail::getTrackingCode($mail->getId())
            ];
            if (!empty($mail->getContextVars())) {
                $contextVars = json_decode($mail->getContextVars(), true);
                foreach ($contextVars as $key => $value) {
                    if (is_string($value)) {
                        $value = UltimateFollowupEmail::applyClickTracking($value, $mail->getId());
                        $templateVars[] = [
                            'name' => $key,
                            'content' => $value
                        ];
                    }
                }
            }

            if (!empty($templateVars)) {
                $data['merge_vars'][] = [
                    'rcpt' => $mail->getData('recipient_email'),
                    'vars' => $templateVars
                ];
            }
        } catch (\Exception $e) {
            $mail->setStatus(Status::STATUS_FAILED);
            $mail->setLog($e->getMessage());
            $mail->save();
        }
        return $data;
    }

    protected function prepareTemplate($mail, $mailDataKey)
    {
        $templateVars = [];
        $template = $this->templateFactory->create()->load($mail->getTemplateId());
        if (preg_match_all('/{{var(.*?)}}/si', $template->getTemplateText(), $vars, PREG_SET_ORDER)) {
            foreach ($vars as $var) {
                $key = trim($var[1]);
                $templateVars[$key] = '*|' . $key . '|*';
            }
        }
        $content = $template->setVars($templateVars)
            ->setDesignConfig([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()
            ])->getProcessedTemplate($templateVars);
        $this->_mailData[$mailDataKey]['html'] = html_entity_decode($content." *|trackingCode|*");
        return $this;
    }

    protected function prepareSender($mail, $mailDataKey)
    {
        /** @var  \Magento\Email\Model\Template\SenderResolver $_senderResolver */
        $_senderResolver = \Magento\Framework\App\ObjectManager::getInstance()->create(\Magento\Email\Model\Template\SenderResolver::class);
        $result = $_senderResolver->resolve($this->scopeConfig->getValue(Mail::XML_PATH_EMAIL_SENDER));
        $this->_mailData[$mailDataKey]['from_name'] = $result['name'];
        $this->_mailData[$mailDataKey]['from_email'] = $result['email'];
        return $this;
    }

    protected function prepareSubject($mail, $mailDataKey)
    {
        $this->_mailData[$mailDataKey]['subject'] = $mail->getData('subject');
        return $this;
    }

    protected function prepareBcc($mail, $mailDataKey)
    {
        if ($mail->getData('bcc_email')) {
            $this->_mailData[$mailDataKey]['to'][] = [
                'email' => $mail->getData('bcc_email'),
                'type' => 'bcc',
            ];
        }
        return $this;
    }

    protected function prepareAttachments($mail, $mailDataKey)
    {
        $attachedFiles = $this->serializer->unserialize($mail->getData('attachments'));

        if (is_array($attachedFiles) && !empty($attachedFiles)) {
            $objectManager = ObjectManager::getInstance();
            /** @var \Magento\Framework\App\Filesystem\DirectoryList $dir */
            $dir = $objectManager->get('\Magento\Framework\App\Filesystem\DirectoryList');
            /** @var \Magento\Catalog\Model\Product\Media\Config $mediaConfig */
            $mediaConfig = $objectManager->get('Magento\Catalog\Model\Product\Media\Config');
            /** @var \Magento\Framework\Filesystem\Io\File $fileReader */
            $fileReader = $objectManager->get('\Magento\Framework\Filesystem\Io\File');
            $mediaPath = $dir->getPath('media');
            $attachments = [];
            $images = [];
            foreach ($attachedFiles as $attachFileTypes) {
                if (!is_array($attachFileTypes)) {
                    break;
                }
                foreach ($attachFileTypes as $file) {
                    if (!isset($file['file'])) {
                        continue;
                    }
                    $filepath = $mediaPath . '/' . $mediaConfig->getTmpMediaPath($file['file']);
                    $body = $fileReader->read($filepath);
                    if (!$body) {
                        \Magento\Framework\App\ObjectManager::getInstance()->create('Psr\Log\LoggerInterface')
                            ->critical('Could not read attachment file for mail ' . $mail->getId());
                        continue;
                    }
                    $info = pathinfo($file['file']);
                    if (!isset($info['extension'])) {
                        continue;
                    }
                    switch ($info['extension']) {
                        case 'gif':
                            $type = 'image/gif';
                            break;
                        case 'jpg':
                        case 'jpeg':
                            $type = 'image/jpg';
                            break;
                        case 'png':
                            $type = 'image/png';
                            break;
                        case 'pdf':
                            $type = 'application/pdf';
                            break;
                        case 'txt':
                            $type = 'text/plain';
                            break;
                        default:
                            $type = 'application/octet-stream';
                    }
                    switch ($info['extension']) {
                        case 'gif':
                        case 'jpg':
                        case 'jpeg':
                        case 'png':
                            $images[] = [
                                'type' => $type,
                                'name' => $file['label'],
                                'content' => base64_encode($body)
                            ];
                            break;
                        default:
                            $attachments[] = [
                                'type' => $type,
                                'name' => $file['label'],
                                'content' => base64_encode($body)
                            ];
                    }
                }
            }
        }
        if (isset($images)) {
            $this->_mailData[$mailDataKey]['images'] = $images;
        }
        if (isset($attachments)) {
            $this->_mailData[$mailDataKey]['attachments'] = $attachments;
        }
        return $this;
    }
}
