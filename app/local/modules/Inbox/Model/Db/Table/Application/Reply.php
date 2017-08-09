<?php

class Inbox_Model_Db_Table_Application_Reply extends Core_Model_Db_Table {

    protected $_name = "inbox_reply";
    protected $_primary = "reply_id";

    public function findAll($parent_id, $customer_id, $all, $offset) {
        $select = $this->select()
            ->from(array('ir' => $this->_name))
            ->joinLeft(array('c' => 'customer'), 'ir.customer_id = c.customer_id', array('firstname', 'lastname'))
            ->where('ir.parent_id = ?', $parent_id)
            ->limit(Inbox_Model_Application_Reply::DISPLAYED_PER_PAGE, $offset)
            ->order('ir.created_at DESC')
            ->setIntegrityCheck(false)
        ;


        if($customer_id) {
            $select->where('ir.customer_id = ? OR ir.customer_id IS NULL', $customer_id);
        }

        if(!$all) {
            $select->where('ir.is_visible_in_editor = 1');
        }

        return $this->fetchAll($select);
    }

    public function getNumberOfComments($message_id) {
        $select = $this->select()
            ->from(array('ir' => $this->_name), array('nb_comments' => 'COUNT(ir.reply_id)'))
            ->where('ir.parent_id = ?', $message_id)
            ->where('ir.is_visible_in_editor = 1')
        ;

        return $this->_db->fetchOne($select);
    }
}