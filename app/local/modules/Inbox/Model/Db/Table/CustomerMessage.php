<?php

/**
 * Class Inbox_Model_Db_Table_CustomerMessage
 */
class Inbox_Model_Db_Table_CustomerMessage extends Core_Model_Db_Table {

    protected $_name = "inbox_customer_message";
    protected $_primary = "customer_message_id";

    /**
     * @param $value_id
     * @param int $limit
     * @return array
     */
    public function findByValueId($value_id, $limit = 5000) {
        $select = "
SELECT 
  *,
  inbox_message.message_id AS id,
  customer.firstname AS customer_firstname,
  customer.lastname AS customer_lastname,
  customer.email AS customer_email,
  IF(inbox_message.send_at >= NOW(), TRUE, FALSE) AS scheduled,
  (SELECT COUNT(*) FROM inbox_reply
    WHERE inbox_reply.parent_id = inbox_message.message_id
    AND inbox_reply.customer_id = inbox_customer_message.customer_id
    AND inbox_reply.is_visible_in_editor = 1)
  AS message_count,
  (SELECT MAX(updated_at) FROM inbox_reply
    WHERE inbox_reply.parent_id = inbox_message.message_id
    AND inbox_reply.customer_id = inbox_customer_message.customer_id
    AND inbox_reply.is_visible_in_editor = 1
    AND inbox_reply.admin_id IS NULL)
  AS latest_update
  FROM inbox_customer_message
  INNER JOIN inbox_message ON inbox_message.message_id = inbox_customer_message.message_id
  INNER JOIN customer ON customer.customer_id = inbox_customer_message.customer_id
  WHERE inbox_message.value_id = {$value_id}
  AND inbox_customer_message.is_visible_in_editor = 1
  AND inbox_customer_message.is_visible_in_mobile = 1
  ORDER BY latest_update DESC, inbox_customer_message.customer_message_id DESC
  LIMIT {$limit}";

        return $this->toModelClass($this->_db->fetchAll($select));
    }

    /**
     * @param $value_id
     * @param $customer_id
     * @param $offset
     * @param string $visibility
     * @return mixed
     */
    public function findByCustomerId($value_id, $customer_id, $offset, $visibility = "editor") {

        switch($visibility) {
            case "editor":default:
                $sql_visibility = "AND inbox_customer_message.is_visible_in_editor = 1 ";
                break;
            case "mobile":
                $sql_visibility = "AND inbox_customer_message.is_visible_in_mobile = 1 ";
                break;
        }

        $select = "
SELECT 
  *,
  customer.firstname AS customer_firstname,
  customer.lastname AS customer_lastname,
  customer.email AS customer_email,
  (SELECT COUNT(*) FROM inbox_reply
    WHERE inbox_reply.parent_id = inbox_message.message_id
    AND inbox_reply.customer_id = inbox_customer_message.customer_id
    AND inbox_reply.is_visible_in_editor = 1)
  AS message_count,
  IFNULL(
    (SELECT MAX(updated_at) FROM inbox_reply
      WHERE inbox_reply.parent_id = inbox_message.message_id
      AND inbox_reply.customer_id = inbox_customer_message.customer_id
      AND inbox_reply.is_visible_in_editor = 1
      AND inbox_reply.admin_id IS NOT NULL)
    , inbox_message.created_at)
  AS latest_update
  FROM inbox_customer_message
  LEFT JOIN inbox_message ON inbox_message.message_id = inbox_customer_message.message_id
  INNER JOIN customer ON customer.customer_id = inbox_customer_message.customer_id
  WHERE inbox_message.value_id = {$value_id}
  AND inbox_customer_message.customer_id = {$customer_id}
  {$sql_visibility}
  AND (inbox_message.send_at <= NOW() OR inbox_message.send_at IS NULL)
  ORDER BY latest_update DESC, inbox_customer_message.customer_message_id DESC
  LIMIT " . Inbox_Model_Application_Message::DISPLAYED_PER_PAGE . "
  OFFSET {$offset}";

        return $this->toModelClass($this->_db->fetchAll($select));
    }
}
