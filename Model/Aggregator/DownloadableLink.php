<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 09/12/2016
 * Time: 14:25
 */

namespace Magenest\UltimateFollowupEmail\Model\Aggregator;

class DownloadableLink implements AggregatorInterface
{

    /**
     * @var \Magenest\UltimateFollowupEmail\Helper\Order
     */
    protected $orderHelper;

    /**
     * @var \Magenest\UltimateFollowupEmail\Model\LinkFactory
     */
    protected $linkFactory;

    public function __construct(
        \Magenest\UltimateFollowupEmail\Model\LinkFactory $linkFactory,
        \Magenest\UltimateFollowupEmail\Helper\Order $orderHelper
    ) {
        $this->orderHelper = $orderHelper;
        $this->linkFactory = $linkFactory;
    }
    public function collect()
    {
        $output =[];
        $linkCollection = $this->linkFactory->create()->getCollection()->addFieldToFilter(
            'state',
            'pending'
        );

        if ($linkCollection->getSize() > 0) {
            foreach ($linkCollection as $linkBean) {
                $productId = $linkBean->getProductId();
                $linkId    = $linkBean->getId();

                $targets = $this->orderHelper->getOrdersByProduct($productId);

                if (count($targets) > 0) {
                    foreach ($targets as $target) {
                        $target['link_id'] = $linkId;
                        $output[]=$target;
                    }
                }
                $linkBean->addData(['state' => 'complete'])->save();
            }
        }

        return $output;
    }
}
