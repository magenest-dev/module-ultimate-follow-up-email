<?php
/**
 * Author: Eric Quach
 * Date: 4/17/18
 */
namespace Magenest\UltimateFollowupEmail\Block\Adminhtml\Chart;

use Magenest\UltimateFollowupEmail\Model\Mail;
use Magenest\UltimateFollowupEmail\Model\MailFactory;
use Magento\Backend\Block\Template;
use Magento\Framework\DB\Select;

class EmailCampaign extends AbstractChart
{
    protected $mailFactory;
    protected $emailTotalCounts;
    protected $openedEmailTotalCounts;
    protected $clickedEmailTotalCounts;

    public function __construct(
        Template\Context $context,
        MailFactory $mailFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->mailFactory = $mailFactory;
    }

    public function getOpeningRatesData()
    {
        return [
            'Opened' => $this->getOpenedEmailCount(),
            'Unopened' => $this->getUnopenedEmailCount()
        ];
    }

    public function getClickingRatesData()
    {
        return [
            'Clicked' => $this->getClickedEmailCount(),
            'Un-clicked' => $this->getUnclickedEmailCount()
        ];
    }

    public function getEmailCount()
    {
        if (is_null($this->emailTotalCounts)) {
            $mailCollection = $this->mailFactory->create()->getCollection();
            $this->applyPeriodToCollection($mailCollection, 'send_date');
            $this->emailTotalCounts = $mailCollection->getSize();
        }
        return $this->emailTotalCounts;
    }

    public function getEmailsLineData()
    {
        $mail = $this->mailFactory->create();
        $mailCollection = $mail->getCollection();
        $this->applyPeriodToCollection($mailCollection, 'send_date');
        $select = $mailCollection->getSelect()->reset(Select::COLUMNS)
            ->group(
                'send_at'
            )->order(
                'send_at ASC'
            )->columns([
                'COUNT(main_table.id) as count',
                'SUM(IF(opened>0,1,0)) as opened_count',
                'SUM(IF(clicks>0,1,0)) as click_count',
                'send_at' => new \Zend_Db_Expr('CAST(main_table.send_date AS DATE)')
            ]);
        $rows = $mail->getResource()->getConnection()->fetchAll($select);
        return $rows;
    }

    public function getOpenedEmailCount()
    {
        if (is_null($this->openedEmailTotalCounts)) {
            $mailCollection = $this->mailFactory->create()->getCollection();
            $mailCollection->getSelect()
                ->where('opened is not null AND opened > 0');
            $this->applyPeriodToCollection($mailCollection, 'send_date');
            $this->openedEmailTotalCounts = $mailCollection->getSize();
        }
        return $this->openedEmailTotalCounts;
    }

    public function getUnopenedEmailCount()
    {
        return $this->getEmailCount() - $this->getOpenedEmailCount();
    }

    public function getClickedEmailCount()
    {
        if (is_null($this->clickedEmailTotalCounts)) {
            $mailCollection = $this->mailFactory->create()->getCollection();
            $mailCollection->getSelect()
                ->where('clicks is not null AND clicks > 0');
            $this->applyPeriodToCollection($mailCollection, 'send_date');
            $this->clickedEmailTotalCounts = $mailCollection->getSize();
        }
        return $this->clickedEmailTotalCounts;
    }

    public function getUnclickedEmailCount()
    {
        return $this->getEmailCount() - $this->getClickedEmailCount();
    }
}