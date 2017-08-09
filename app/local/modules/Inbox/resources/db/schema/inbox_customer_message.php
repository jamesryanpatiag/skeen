<?php
/**
 *
 * Schema definition for 'inbox_customer_message'
 *
 * Last update: 2016-05-11
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['inbox_customer_message'] = array(
    'customer_message_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'message_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'inbox_message',
            'column' => 'message_id',
            'name' => 'FK_INBOX_CUSTOMER_MESSAGE_MESSAGE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'message_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'customer_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'customer',
            'column' => 'customer_id',
            'name' => 'FK_INBOX_CUSTOMER_MESSAGE_CUSTOMER_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'customer_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'is_visible_in_editor' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'is_visible_in_mobile' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'is_new' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'has_new_reply' => array(
        'type' => 'tinyint(1)',
        'default' => '0',
    ),
);