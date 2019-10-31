<?php
namespace Magenest\UltimateFollowupEmail\Controller\Adminhtml\Unsubscriber;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magenest\UltimateFollowupEmail\Model\ResourceModel\Unsubscriber\Grid\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * MassStatus constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        try {
            $status = (int)$this->getRequest()->getParam('status');
            if ($status != 0 && $status != 1) {
                $this->messageManager->addError(__('Please choose a status for subscriber(s).'));
            }
            /** @var \Magenest\UltimateFollowupEmail\Model\Unsubscriber $subscriberModel */
            $subscriberModel = $this->_objectManager->create('Magenest\UltimateFollowupEmail\Model\Unsubscriber');
            $resource = $subscriberModel->getResource();
            $ids = [];
            $count = 0;
            $affectedRows = 0;
            $updateData = ['unsubscriber_status' => $status];
            foreach ($collection as $subscriber) {
                $ids[] = $subscriber->getId();
                $count++;
                if ($count >= 5000) {
                    $affectedRows += $resource
                        ->getConnection()
                        ->update(
                            $resource->getMainTable(),
                            $updateData,
                            'unsubscriber_id IN (' . implode(',', $ids) . ')'
                        );
                    $count = 0;
                    $ids = [];
                }
            }
            if (count($ids)) {
                $affectedRows += $resource
                    ->getConnection()
                    ->update(
                        $resource->getMainTable(),
                        $updateData,
                        'unsubscriber_id IN (' . implode(',', $ids) . ')'
                    );
            }

            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been updated.', $affectedRows)
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_UltimateFollowupEmail::ultimatefollowupemail_unsubscriber');
    }
}
