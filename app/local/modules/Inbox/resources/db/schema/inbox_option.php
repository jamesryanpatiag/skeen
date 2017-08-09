<?php
/**
 *
 * Schema definition for 'inbox_reply'
 *
 * Last update: 2016-05-11
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['inbox_option'] = array(
    'inbox_option_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'FK_INBOX_OPTION_VALUE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'inbox_option_value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'recipient_email' => array(
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'send_email_to' => array(
        'type' => 'enum(\'admin\',\'recipient\',\'all\',\'disabled\')',
    ),
    'message_limit' => array(
        'type' => 'int(11) unsigned',
        'default' => 5000,
    ),
    'display_type' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'default',
    ),
    'answer_from' => array(
        'type' => 'varchar(100)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'default' => 'appname',
    ),
);