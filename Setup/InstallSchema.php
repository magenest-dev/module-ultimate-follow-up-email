<?php
namespace Magenest\UltimateFollowupEmail\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{


    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /** Create table 'magenest_ultimatefollowupemail_rule' */
        $table = $installer->getConnection()->newTable($installer->getTable('magenest_ultimatefollowupemail_rule'))->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
            ],
            'Rule id'
        )->addColumn(
            'type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Follow up email type'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name of rule'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Description'
        )->addColumn(
            'from_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            null,
            [
                'nullable' => true,
                'default' => null,
            ],
            'From'
        )->addColumn(
            'to_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            null,
            [
                'nullable' => true,
                'default' => null,
            ],
            'To'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'magento side field'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Website Ids'
        )->addColumn(
            'customer_group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Customer group Ids'
        )->addColumn(
            'conditions_serialized',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Conditions Serialized'
        )->addColumn(
            'cancel_serialized',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Conditions to cancel queue mail Serialized'
        )->addColumn(
            'email_chain',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Email Chain'
        )->addColumn(
            'sms_chain',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'SMS Chain'
        )->addColumn(
            'duplicated_key',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Prevent duplidated follow up email'
        )->addColumn(
            'enable_coupon',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [
                'nullable' => false,
                'default' => 0,
            ],
            'Allow insert coupon in email or not'
        )->addColumn(
            'promotion_rule_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'nullable' => true,
                'default' => 0,
            ],
            'Prevent duplidated follow up email'
        )->addColumn(
            'customer_group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Customer group Ids'
        )->addColumn(
            'ga_source',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Google analytics source'
        )->addColumn(
            'ga_medium',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Google analytics medium'
        )->addColumn(
            'ga_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Google analytics name'
        )->addColumn(
            'ga_term',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Google analytics term'
        )->addColumn(
            'ga_content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Google analytics content'
        )->addColumn(
            'attached_files',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'Attach files which is serialized'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [
                'nullable' => false,
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
            ],
            'Created At'
        )->setComment('Follow up email rule');
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable($installer->getTable('magenest_ultimatefollowupemail_guest_abandoned_cart'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'Field Mapping id'
            )->addColumn(
                'quote_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Quote id'
            )->addColumn(
                'email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Billing email guest enters in checkout'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'default' => 0,
                ],
                'Status Id'
            )->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'whether anonymous'
            )
            /*
                ->addForeignKey(
                   $installer->getFkName('magenest_ultimatefollowupemail_guest_abandoned_cart', 'quote_id', 'quote', 'entity_id'),
                   'quote_id',
                   $installer->getTable('quote'),
                   'entity_id',
                   \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
               )*/
            ->setComment('Guest email of abandoned cart');

        $installer->getConnection()->createTable($table);

        // create table mail_log
        $table = $installer->getConnection()->newTable($installer->getTable('magenest_ultimatefollowupemail_mail_log'))->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
            ],
            'Mail id'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Status Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'nullable' => false,
                'unsigned' => true,
            ],
            'Store Id'
        )->addColumn(
            'rule_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            255,
            [
                'nullable' => false,
                'unsigned' => true,
            ],
            'The mail  is associated with a rule'
        )->addColumn(
            'rule_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Rule type'
        )->addColumn(
            'duplicated_key',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Information to prevent duplicating email'
        )->addColumn(
            'recipient_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Recipient name'
        )->addColumn(
            'recipient_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Recipient name'
        )->addColumn(
            'bcc_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Bcc name'
        )->addColumn(
            'bcc_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Bcc name'
        )->addColumn(
            'send_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'From'
        )->addColumn(
            'subject',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Subject of email'
        )->addColumn(
            'styles',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Email Styles'
        )->addColumn(
            'content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Email Content'
        )->addColumn(
            'attachments',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'File path of attachments'
        )->addColumn(
            'cancel_serialized',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Cancel email automatically base on this trigger'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [
                'nullable' => false,
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
            ],
            'Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [
                'nullable' => false,
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
            ],
            'Updated At'
        )->addForeignKey(
            $installer->getFkName('magenest_ultimatefollowupemail_mail_log', 'rule_id', 'magenest_ultimatefollowupemail_rule', 'id'),
            'rule_id',
            $installer->getTable('magenest_ultimatefollowupemail_rule'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment('Mail Log');
        $installer->getConnection()->createTable($table);

        // sms log
        $table = $installer->getConnection()->newTable($installer->getTable('magenest_ultimatefollowupemail_mail_sms'))
            ->addColumn(
                'sms_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'Mail id'
            )->addColumn(
                'content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '100k',
                ['nullable' => false],
                'Content'
            )->addColumn(
                'before',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Before or After'
            )->addColumn(
                'day',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'day'
            )->addColumn(
                'hour',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Hour'
            )->addColumn(
                'min',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Minutes'
            )->setComment('SMS Table');
        $installer->getConnection()->createTable($table);

        // create sms log table
        $table = $installer->getConnection()->newTable($installer->getTable('magenest_ultimatefollowupemail_sms_log'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'sms log id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Store Id'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Status'
            )->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                255,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'The mail  is associated with a rule'
            )->addColumn(
                'duplicated_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Information to prevent duplicating email'
            )->addColumn(
                'recipient_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Recipient name'
            )->addColumn(
                'recipient_mobile',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Recipient mobile'
            )->addColumn(
                'scheduled_send_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'From'
            )->addColumn(
                'content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'SMS Content'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'Created At'
            )->setComment('SMS Table');
        $installer->getConnection()->createTable($table);
        $setup->endSetup();
    }
}
