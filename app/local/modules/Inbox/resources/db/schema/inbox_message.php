<?php
/**
 *
 * Schema definition for 'inbox_message'
 *
 * Last update: 2016-05-11
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['inbox_message'] = array(
    'message_id' => array(
        'type' => 'int(11) unsigned',
        'auto_increment' => true,
        'primary' => true,
    ),
    'value_id' => array(
        'type' => 'int(11) unsigned',
        'foreign_key' => array(
            'table' => 'application_option_value',
            'column' => 'value_id',
            'name' => 'FK_INBOX_MESSAGE_VALUE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'value_id',
            'index_type' => 'BTREE',
            'is_null' => false,
            'is_unique' => false,
        ),
    ),
    'page_id' => array(
        'type' => 'int(11)',
        'is_null' => true,
        'foreign_key' => array(
            'table' => 'cms_application_page',
            'column' => 'page_id',
            'name' => 'FK_INBOX_MESSAGE_PAGE_ID',
            'on_update' => 'CASCADE',
            'on_delete' => 'CASCADE',
        ),
        'index' => array(
            'key_name' => 'page_id',
            'index_type' => 'BTREE',
            'is_null' => true,
            'is_unique' => false,
        ),
    ),
    'admin_id' => array(
        'type' => 'int(11)',
    ),
    'title' => array(
        'type' => 'varchar(150)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'thumbnail' => array(
        'type' => 'varchar(255)',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ),
    'send_at' => array(
        'type' => 'datetime',
        'is_null' => true,
    ),
    'send_notif' => array(
        'type' => 'int(1)',
        'is_null' => true,
    ),
    /** @deprecated field */
    'is_visible_in_editor' => array(
        'type' => 'tinyint(1)',
        'default' => '1',
    ),
    /** @deprecated field */
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