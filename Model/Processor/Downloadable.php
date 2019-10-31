<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 09/12/2016
 * Time: 15:29
 */
namespace Magenest\UltimateFollowupEmail\Model\Processor;

class Downloadable
{
    protected $orderProcessorFactory;

    public function __construct(
        OrderProcessorFactory $orderProcessorFactory
    ) {
        $this->orderProcessorFactory = $orderProcessorFactory;
    }

    public function run()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        /** @var  \Magenest\UltimateFollowupEmail\Model\Aggregator\DownloadableLink  $aggregator */
        $aggregator = $objectManager->create('Magenest\UltimateFollowupEmail\Model\Aggregator\DownloadableLink');
        $orderRows = $aggregator->collect();
        /** @var \Magenest\UltimateFollowupEmail\Model\Link $linkModel */
        $linkModel = $objectManager->create('Magenest\UltimateFollowupEmail\Model\Link');

        foreach ($orderRows as $orderRow) {
            /** @var  $order \Magento\Sales\Model\Order */
            $order = $objectManager->create('Magento\Sales\Model\Order')->load(isset($orderRow['entity_id'])?$orderRow['entity_id']:"");

            if ($order->getId()) {
                $linkId = isset($orderRow['link_id'])?$orderRow['link_id']:"";
                $linkModel->load($linkId);
                /** @var OrderProcessor $orderProcessor */
                $orderProcessor = $this->orderProcessorFactory->create();
                $orderProcessor->setType('order_updated_item');
                $orderProcessor->setAdditionalDuplicateKey($linkId);
                $orderProcessor->setEmailTarget($order);
                $orderHelper = $objectManager->create('Magenest\UltimateFollowupEmail\Helper\Order');
                $downloadLink = $orderHelper->getDownloadableLink($linkModel);
                $orderProcessor->setVars(['downloadLink' => $downloadLink,])->run();
                $linkModel->unsetData();
            }
        }
    }

}
