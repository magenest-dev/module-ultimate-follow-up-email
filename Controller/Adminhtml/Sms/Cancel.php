<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 27/06/2016
 * Time: 15:15
 */

namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Sms;

use Magento\Framework\Controller\ResultFactory;

use Magento\Backend\App\Action;

class Cancel extends Action
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('id');

        if (is_numeric($ids)) {
            $Ids[] = $ids;
        } else {
            $Ids = $ids;
        }

        /*
            * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
        */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!is_array($Ids)) {
            $this->messageManager->addWarning('Please select item(s)');
        } else {
            try {
                foreach ($Ids as $id) {
                    /*
                        * @var  $mailModel \Magenest\UltimateFollowupEmail\Model\Sms
                    */
                    $mailModel = $this->_objectManager->create('Magenest\UltimateFollowupEmail\Model\Sms')->load($id);

                    $mailModel->setStatus(4)->save();
                }

                $this->messageManager->addSuccess(__('Total of %1 record(s) were processed', count($Ids)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }
}
