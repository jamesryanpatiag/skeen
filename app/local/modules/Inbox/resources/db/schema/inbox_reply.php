<?php
/**
 *
 * Schema definition for 'inbox_reply'
 *
 * Last update: 2016-05-11
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['inbox_reply'] = array(
    'reply_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'parent_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'inbox_message',
            'column' => 'message_id',
            'name' => 'FK_INBOX_REPLY_PARENT_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'parent_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'customer_id' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ),
    'admin_id' => array(
        'type' => 'int(11) unsigned',
        'is_null' => true,
    ),
    'message' => array(
        'type' => 'text',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'is_visible_in_editor' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'is_visible_in_mobile' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    'created_at' => array(
        'type' => 'datetime',
    ),
    'updated_at' => array(
        'type' => 'datetime',
    ),
);