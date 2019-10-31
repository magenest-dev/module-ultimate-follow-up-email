<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 29/09/2015
 * Time: 20:07
 */
namespace Magenest\UltimateFollowupEmail\Model\ResourceModel\Rule;

use Magento\Framework\DB\Select;
use Magenest\UltimateFollowupEmail\Model\System\Config\Source\Mail\Status;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_resource;
    protected $isEmailsReportPrepared = false;


    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magenest\UltimateFollowupEmail\Model\Rule', 'Magenest\UltimateFollowupEmail\Model\ResourceModel\Rule');
    }


    public function getRulesByType($type)
    {
        $this->addFieldToFilter('type', $type)->addFieldToFilter(
            'main_table.status',
            '1'
        );

        $now = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

        $this->getSelect()->where('from_date <=? or from_date is null', $now)->where('to_date>=? or to_date is null', $now);
        return $this;
    }

    public function prepareEmailsReport()
    {
        if (!$this->isEmailsReportPrepared) {
            $this->getSelect()
                ->joinLeft(
                    ['log_emails' => $this->getEmailCountSelect()],
                    'log_emails.id = main_table.id',
                    'log_emails.emails'
                )->joinLeft(
                    ['log_sent' => $this->getEmailSentSelect()],
                    'log_sent.id = main_table.id',
                    'log_sent.sent'
                )->joinLeft(
                    ['log_opened' => $this->getEmailOpenedSelect()],
                    'log_opened.id = main_table.id',
                    'log_opened.opened'
                )->joinLeft(
                    ['log_clicks' => $this->getClickCountSelect()],
                    'log_clicks.id = main_table.id',
                    'log_clicks.clicks'
                );
            $this->isEmailsReportPrepared = true;
        }
    }

    private function getClickCountSelect()
    {
        $logClicks = clone $this;
        $logClicks->getSelect()
            ->reset(Select::COLUMNS)
            ->joinLeft(
                ['log_clicks' => $this->getTable('magenest_ultimatefollowupemail_mail_log')],
                'log_clicks.rule_id = main_table.id',
                'SUM(log_clicks.clicks) as clicks'
            )->columns('main_table.id')
            ->group('main_table.id');
        return $logClicks->getSelect();
    }

    private function getEmailCountSelect()
    {
        $logEmails = clone $this;
        $logEmails->getSelect()
            ->reset(Select::COLUMNS)
            ->joinLeft(
                ['log_emails' => $this->getTable('magenest_ultimatefollowupemail_mail_log')],
                'log_emails.rule_id = main_table.id',
                'COUNT(log_emails.id) as emails'
            )->columns('main_table.id')
            ->group('main_table.id');
        return $logEmails->getSelect();
    }

    private function getEmailSentSelect()
    {
        $logSent = clone $this;
        $logSent->getSelect()
            ->reset(Select::COLUMNS)
            ->joinLeft(
                ['log_sent' => $this->getTable('magenest_ultimatefollowupemail_mail_log')],
                'log_sent.rule_id = main_table.id AND log_sent.status='.Status::STATUS_SENT,
                'COUNT(log_sent.id) as sent'
            )->columns('main_table.id')
            ->group('main_table.id');
        return $logSent->getSelect();
    }

    private function getEmailOpenedSelect()
    {
        $logOpened = clone $this;
        $logOpened->getSelect()
            ->reset(Select::COLUMNS)
            ->joinLeft(
                ['log_opened' => $this->getTable('magenest_ultimatefollowupemail_mail_log')],
                'log_opened.rule_id = main_table.id AND log_opened.opened > 0',
                'COUNT(log_opened.id) as opened'
            )->columns('main_table.id')
            ->group('main_table.id');
        return $logOpened->getSelect();
    }
}
