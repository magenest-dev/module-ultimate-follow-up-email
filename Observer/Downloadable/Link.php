<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 08/12/2016
 * Time: 16:37
 */
namespace Magenest\UltimateFollowupEmail\Observer\Downloadable;

use Magenest\UltimateFollowupEmail\Model\Processor\Downloadable;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Link implements ObserverInterface
{
    protected $linkFactory;
    protected $downloadableProcessor;

    public function __construct(
        \Magenest\UltimateFollowupEmail\Model\LinkFactory $linkFactory,
        Downloadable $downloadableProcessor
    ) {
        $this->linkFactory = $linkFactory;
        $this->downloadableProcessor = $downloadableProcessor;

    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $downloadableLink = $observer->getEvent()->getDataObject();

        if ($downloadableLink instanceof \Magento\Downloadable\Model\Link) {
            $data = $downloadableLink->getData();

            if ($downloadableLink->getId() == null) {
                if (isset($data['link_file'])) {
                    $linkBean = $this->linkFactory->create();
                    $productId = $data['product_id'];
                    $link_file = $data['link_file'];
                    $linkBean->setData([
                        'link_file' => $link_file,
                        'product_id' => $productId,
                        'state' => 'pending'
                    ]);
                    $linkBean->save();
                    $this->downloadableProcessor->run();
                }
            }
        }
    }
}
