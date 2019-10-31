<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 08/12/2016
 * Time: 17:02
 */

namespace Magenest\UltimateFollowupEmail\Helper;

use Psr\Log\LoggerInterface as Logger;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{

    /** @var \Magento\Catalog\Model\ResourceModel\Product  */
    protected $productResource;

    /** @var \Magento\Sales\Model\ResourceModel\Order  */
    protected $orderResource;

    protected $logger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Sales\Model\ResourceModel\Order $orderResource
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->logger = $context->getLogger();

        $this->productResource = $productResource;
        $this->orderResource = $orderResource;
    }


    public function getOrdersByProduct($productId)
    {
        $connection = $this->orderResource->getConnection();
        $select = $connection->select();
        $orderTable = $this->orderResource->getTable('sales_order');
        $orderItemTable =$this->orderResource->getTable('sales_order_item');
        $select->from(
            ['order_table' => $orderTable],
            ['entity_id'=>'order_table.entity_id', 'customer_email' =>'order_table.customer_email']
        )->joinInner(
            ['order_item' =>$orderItemTable],
            'order_item.order_id = order_table.entity_id and product_id='.$productId,
            []
        )->where(
            'order_table.state = ?',
            \Magento\Sales\Model\Order::STATE_COMPLETE
        )->where(
            'order_item.product_type  IN(?)',
            ['downloadable','configurable']
        );

        $adapter = $this->orderResource->getConnection('read');

        $rows = $adapter->fetchAssoc($select);
        return $rows;
    }

    /**
     * @param $link
     * @return string
     */
    public function getDownloadableLink($link)
    {
        return  $this->_getUrl('ultimatefollowupemail/download/link/', ['id' => $link->getId()]);
    }
}
