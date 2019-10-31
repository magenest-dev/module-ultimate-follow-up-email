<?php
/**
 * Created by Magenest
 * User: Eric Quach
 * Date: 09/12/2016
 * Time: 10:14
 */

namespace Magenest\UltimateFollowupEmail\Setup;

use Magento\Framework\DB\Select;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\Setup\SetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    const DOWNLOADABLE_LINK_TABLE = "magenest_ultimatefollowupemail_downloadable_link";
    const CAPTURED_GUEST_TABLE = "magenest_ultimatefollowupemail_guest_capture";
    const ABANDONED_CART_TABLE = "magenest_ultimatefollowupemail_guest_abandoned_cart";
    const MAIL_LOG_TABLE = "magenest_ultimatefollowupemail_mail_log";
    const UNSUBSCRIBE_TABLE = "magenest_ultimatefollowupemail_unsubscribe";

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->addDownloadableLinkTable($setup);
        }
        if (version_compare($context->getVersion(), '100.2.2', '<')) {
            $this->addCapturedGuestTable($setup);
            $this->addIsProcessedColumn($setup);
        }
        if (version_compare($context->getVersion(), '100.3.10', '<')) {
            $this->addCouponTimeColumn($setup);
            $this->addLogColumnToMailLogTable($setup);
            $this->addUnsubscribeTable($setup);
        }
        if (version_compare($context->getVersion(), '100.2.4', '<')) {
            $this->addOpenedEmailColumn($setup);
            $this->addClicksEmailColumn($setup);
            $this->addPreviewContentColumn($setup);
            $this->addContextVarsColumn($setup);
            $this->addTemplateIdColumn($setup);
            $this->addAdditionalSettingsColumn($setup);
            $this->addRuleIdColumn($setup);
        }
        if (version_compare($context->getVersion(), '100.3.10', '<')) {
            $this->addUpdatedAtColumn($setup);
            $this->addIndexForDuplicateKeyColumn($setup);
            $this->readdForeignKeyForMailTable($setup);
            $this->addForeignKeyForSmsTable($setup);
        }
        $setup->endSetup();
    }

    private function addForeignKeyForSmsTable(SetupInterface $setup)
    {
        $smsTable =  $setup->getTable('magenest_ultimatefollowupemail_sms_log');
        $ruleTable = $setup->getTable('magenest_ultimatefollowupemail_rule');
        $setup->getConnection()
            ->dropForeignKey(
                $smsTable,
                $setup->getConnection()->getForeignKeyName(
                    'magenest_ultimatefollowupemail_sms_log',
                    'rule_id',
                    'magenest_ultimatefollowupemail_rule',
                    'id'
                )
            );
        $setup->getConnection()
            ->modifyColumn(
                $smsTable,
                'rule_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'unsigned' => true,
                    'length' => null
                ]
            );
        $setup->getConnection()
            ->addForeignKey(
                $setup->getFkName('magenest_ultimatefollowupemail_sms_log', 'rule_id', 'magenest_ultimatefollowupemail_rule', 'id'),
                $setup->getTable('magenest_ultimatefollowupemail_sms_log'),
                'rule_id',
                $setup->getTable('magenest_ultimatefollowupemail_rule'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            );
    }

    private function readdForeignKeyForMailTable(SetupInterface $setup)
    {
        $mailTable =  $setup->getTable(self::MAIL_LOG_TABLE);
        $setup->getConnection()
            ->dropForeignKey(
                $mailTable,
                $setup->getConnection()->getForeignKeyName(
                    self::MAIL_LOG_TABLE,
                    'rule_id',
                    'magenest_ultimatefollowupemail_rule',
                    'id'
                )
            );
        $setup->getConnection()
            ->modifyColumn(
                $setup->getTable('magenest_ultimatefollowupemail_rule'),
                'id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ]
            );
        $setup->getConnection()
            ->modifyColumn(
                $mailTable,
                'rule_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'unsigned' => true,
                    'length' => null
                ]
            );
        $setup->getConnection()
            ->addForeignKey(
                $setup->getFkName('magenest_ultimatefollowupemail_mail_log', 'rule_id', 'magenest_ultimatefollowupemail_rule', 'id'),
                $setup->getTable('magenest_ultimatefollowupemail_mail_log'),
                'rule_id',
                $setup->getTable('magenest_ultimatefollowupemail_rule'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            );
    }

    private function addIndexForDuplicateKeyColumn(SetupInterface $setup)
    {
        $mailTable =  $setup->getTable(self::MAIL_LOG_TABLE);
        $setup->getConnection()
            ->addIndex(
                $mailTable,
                $setup->getConnection()->getIndexName(
                    $mailTable,
                    'duplicated_key'
                ),
                'duplicated_key'
            );
    }

    private function addUpdatedAtColumn(SetupInterface $setup)
    {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable(self::ABANDONED_CART_TABLE),
                'updated_at',
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'default' => Table::TIMESTAMP_UPDATE,
                    'nullable' => false,
                    'comment' => 'Updated At'
                ]
            );
        $oldData = $setup->getConnection()->select()
            ->joinInner(
                ['q' => $setup->getTable('quote')],
                'q.entity_id = abc.quote_id'
            )
            ->reset(Select::COLUMNS)
            ->columns('updated_at');
        $updateQuery = $setup->getConnection()->updateFromSelect($oldData, ['abc' => $setup->getTable(self::ABANDONED_CART_TABLE)]);
        $setup->getConnection()->query($updateQuery);
    }

    /**
     * @param $setup
     */
    private function addDownloadableLinkTable(SetupInterface $setup)
    {
        $tableName = $setup->getTable(self::DOWNLOADABLE_LINK_TABLE);
        if (!$setup->tableExists($tableName)) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable($tableName)
            )->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Entity ID'
            )->addColumn(
                'link_file',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Link File'
            )->addColumn(
                'state',
                Table::TYPE_TEXT,
                100,
                ['nullable' => true],
                'State of the Link'
            )->addColumn(
                'product_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Product Name'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                11,
                ['nullable' => true],
                'Product Id'
            )->setComment('Downloadable Link');

            $setup->getConnection()->createTable($table);
        }
    }
    /**
     * @param $setup
     */
    private function addCapturedGuestTable(SetupInterface $setup)
    {
        /**
         * Table to capture the email of guest customer immediately
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable(self::CAPTURED_GUEST_TABLE))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Field Mapping id'
            )
            ->addColumn(
                'quote_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false,'unsigned' => true],
                'Quote id'
            )
            ->addColumn(
                'email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Billing email guest enters in checkout'
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Type is guest or customer'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false , 'default'=>0],
                'Status Id'
            )
            ->setComment('Guest email');
        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addRuleIdColumn($setup)
    {
        if (!$setup->tableExists(self::UNSUBSCRIBE_TABLE)) {
            return;
        };

        $setup->getConnection()->addColumn(
            $setup->getTable(self::UNSUBSCRIBE_TABLE),
            'rule_id',
            [
                'type' => Table::TYPE_INTEGER,
                null,
                'nullable' => false ,
                'default'=> 0,
                'comment' =>'Rule Id'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addIsProcessedColumn($setup)
    {
        if (!$setup->tableExists(self::ABANDONED_CART_TABLE)) {
            return;
        };

        $setup->getConnection()->addColumn(
            $setup->getTable(self::ABANDONED_CART_TABLE),
            'is_processed',
            [
                'type' => Table::TYPE_INTEGER,
                null,
                ['nullable' => false , 'default'=>0],
                'comment' =>'Is processed'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addCouponTimeColumn($setup)
    {
        if (!$setup->tableExists('magenest_ultimatefollowupemail_rule')) {
            return;
        };

        $setup->getConnection()->addColumn(
            $setup->getTable('magenest_ultimatefollowupemail_rule'),
            'coupon_time',
            [
                'type' => Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'comment' =>'Coupon Available Time'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addLogColumnToMailLogTable($setup)
    {
        $columnName = 'log';
        $columnExisted = $setup->getConnection()->tableColumnExists(
            $setup->getTable(self::MAIL_LOG_TABLE),
            $columnName
        );
        if (!$columnExisted) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::MAIL_LOG_TABLE),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'comment' => 'Mail Status Log'
                ]
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addClicksEmailColumn($setup)
    {
        $columnName = 'clicks';
        $columnExisted = $setup->getConnection()->tableColumnExists(
            $setup->getTable(self::MAIL_LOG_TABLE),
            $columnName
        );
        if (!$columnExisted) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::MAIL_LOG_TABLE),
                $columnName,
                [
                    'type' => Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'comment' => 'Recipient Clicks Mail'
                ]
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addOpenedEmailColumn($setup)
    {
        $columnName = 'opened';
        $columnExisted = $setup->getConnection()->tableColumnExists(
            $setup->getTable(self::MAIL_LOG_TABLE),
            $columnName
        );
        if (!$columnExisted) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::MAIL_LOG_TABLE),
                $columnName,
                [
                    'type' => Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'comment' => 'Recipient Opened Mail'
                ]
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addPreviewContentColumn($setup)
    {
        $columnName = 'preview_content';
        $columnExisted = $setup->getConnection()->tableColumnExists(
            $setup->getTable(self::MAIL_LOG_TABLE),
            $columnName
        );
        if (!$columnExisted) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::MAIL_LOG_TABLE),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'comment' => 'Preview Content'
                ]
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addContextVarsColumn($setup)
    {
        $columnName = 'context_vars';
        $columnExisted = $setup->getConnection()->tableColumnExists(
            $setup->getTable(self::MAIL_LOG_TABLE),
            $columnName
        );
        if (!$columnExisted) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::MAIL_LOG_TABLE),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'comment' => 'Context Variables'
                ]
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addTemplateIdColumn($setup)
    {
        $columnName = 'template_id';
        $columnExisted = $setup->getConnection()->tableColumnExists(
            $setup->getTable(self::MAIL_LOG_TABLE),
            $columnName
        );
        if (!$columnExisted) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::MAIL_LOG_TABLE),
                $columnName,
                [
                    'type' => Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'comment' => 'Email Template Id'
                ]
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addAdditionalSettingsColumn($setup)
    {
        $columnName = 'additional_settings';
        $columnExisted = $setup->getConnection()->tableColumnExists(
            $setup->getTable('magenest_ultimatefollowupemail_rule'),
            $columnName
        );
        if (!$columnExisted) {
            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_ultimatefollowupemail_rule'),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'comment' => 'Additional Settings for individual Rule'
                ]
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addUnsubscribeTable($setup)
    {
        /**
         * Table to capture the email of guest customer immediately
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable(self::UNSUBSCRIBE_TABLE))
            ->addColumn(
                'unsubscriber_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'unsubscriber_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false,'unsigned' => true]
            )
            ->addColumn(
                'unsubscriber_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false , 'default'=>0],
                'Status Id'
            )
            ->setComment('Guest email');
        $setup->getConnection()->createTable($table);
    }
}
