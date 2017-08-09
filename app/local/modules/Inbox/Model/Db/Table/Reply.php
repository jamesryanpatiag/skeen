<?php

/**
 * Class Inbox_Model_Db_Table_Reply
 */
class Inbox_Model_Db_Table_Reply extends Core_Model_Db_Table {

    protected $_name = "inbox_reply";
    protected $_primary = "reply_id";


    /**
     * @param $message_id
     * @param $customer_id
     * @param bool $all
     * @param int $offset
     * @param bool $limit
     * @return mixed
     */
    public function findByMessageId($message_id, $customer_id, $all = true, $offset = 0, $limit = false) {
        $select = $this->_db->select()
            ->from(array("reply" => $this->_name), array("*", "from_app" => new Zend_Db_Expr("IF(reply.admin_id IS NULL, 0, 1)")))
            ->joinLeft("customer", "customer.customer_id = reply.customer_id", array(
                "customer_firstname" => "firstname",
                "customer_lastname" => "lastname",
                "customer_email" => "email",
            ))
            ->joinLeft("admin", "admin.admin_id = reply.admin_id", array(
                "admin_firstname" => "firstname",
                "admin_lastname" => "lastname",
                "admin_email" => "email",
            ))
            ->where("reply.parent_id = ?", $message_id)
            ->where("reply.customer_id = ?", $customer_id)
            ->order("reply.created_at DESC")
        ;

        if($limit) {
            $select->limit(Inbox_Model_Application_Reply::DISPLAYED_PER_PAGE, $offset);
        }

        if(!$all) {
            $select->where("reply.is_visible_in_editor = ?", true);
        }

        return $this->toModelClass($this->_db->fetchAll($select));
    }
}
