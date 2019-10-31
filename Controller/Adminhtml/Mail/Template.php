<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 22/01/2016
 * Time: 14:06
 */

namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Mail;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface as Logger;

class Template extends \Magento\Framework\App\Action\Action
{

    /**
     * @var   $_emailTemplateFactory \Magento\Email\Model\ResourceModel\Template\Collection
     */
    protected $_emailTemplateCollection;

    protected $_jsonData = [];


    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if (!$this->_emailTemplateCollection) {
            $this->_emailTemplateCollection = $this->_objectManager->create('\Magento\Email\Model\ResourceModel\Template\Collection');
        }

        $this->_emailTemplateCollection->load();

        if ($this->_emailTemplateCollection->getSize() > 0) {
            foreach ($this->_emailTemplateCollection->getItems() as $email) {
                $this->_jsonData[] = [
                                      'id'    => $email->getId(),
                                      'label' => $email->getTemplateCode(),
                                     ];
            }
        }

        $resultJson->setData($this->_jsonData);
        return $resultJson;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_mail');
    }
}
