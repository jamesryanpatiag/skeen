<?php

class Inbox_ApplicationController extends Application_Controller_Default {

    /**
     * New Inbox edit Action, used in v2
     */
    public function editAction() {
        $value_id = $this->getCurrentOptionValue()->getId();

        # Options
        $option_model = new Inbox_Model_Option();

        $this->view->option = $option_model->find($value_id, "value_id");
        if(!$this->view->option->getId()) {
            $this->view->option
                ->setValueId($value_id)
                ->save();
        }

        $limit = 5000;
        if($this->view->option->getLimit()) {
            $limit = $this->view->option->getLimit();
        }

        $message_model = new Inbox_Model_CustomerMessage();
        $this->view->messages = $message_model->findByValueId($value_id, $limit);


        $this->view->form_option = new Inbox_Form_Option();
        $this->view->form_option->setValueId($value_id);
        $this->view->form_option->populate($this->view->option->getData());

        # Customers
        $customer_model = new Customer_Model_Customer();
        $this->view->customers = $customer_model->findByAppId($this->getApplication()->getId());

        parent::editAction();
    }

    public function indexAction() {
        $this->loadPartials();
    }

    /**
     * @deprecated
     */
    public function formAction() {


        $id = $this->getRequest()->getParam("id");

        try {

            $page = new Cms_Model_Application_Page();
            $page->find($id);
            if(!$page->getId()) {
                $page->setId("new");
            }

            $this->getLayout()->setBaseRender('form', 'cms/application/page/edit.phtml', 'admin_view_default')
                ->setCurrentPage($page)
                ->setOptionValue($this->getCurrentOptionValue())
                ->setCurrentFeature("inbox")
            ;

            $html = array(
                'form' => $this->getLayout()->render(),
                'success' => 1
            );

        } catch (Exception $e) {
            $html = array(
                'message' => $e->getMessage()
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    /**
     * @deprecated
     *
     * use > Inbox_MessageController::editpostAction
     */
    public function editpostAction() {
        if ($data = $this->getRequest()->getPost()) {

            try {
                $inbox_message = new Inbox_Model_Application_Message();

                if(empty($data['send_at'])) {
                    $data['send_at'] = null;
                }

                $sendtoAll = ($data['sendToAllCustomer'])? true: false;
                unset($data['sendToAllCustomer']);
                $inbox_message->setData($data)->save();
                if($sendtoAll) {
                    $this->sendToAllCustomers($inbox_message);

                } else {
                    if ($data["customers_receiver"]) {
                        $customers_data = explode(";", $data["customers_receiver"]);
                        foreach ($customers_data as $id_customer) {
                            if ($id_customer != "") {
                                $customer_message = new Inbox_Model_Application_Customer_Message();
                                $customer_message_data = array(
                                    "customer_id" => $id_customer,
                                    "message_id" => $inbox_message->getId()
                                );
                                $customer_message->setData($customer_message_data);
                                $customer_message->save();
                            }
                        }
                    }
                }

                //PUSH
                if($data["send_notif"]) {
                    $message_push = new Push_Model_Message();
                    $message_push->setMessageType(Push_Model_Message::TYPE_PUSH);

                    $data_push = array(
                        "title" => __("New Inbox Message"),
                        "text" => __("You have a new Inbox message."),
                        "send_at" => $data["send_at"],
                        "action_value" => $data["value_id"],
                        "type_id" => $message_push->getMessageType(),
                        "app_id" => $this->getApplication()->getId(),
                        "send_to_all" => 0,
                        "send_to_specific_customer" => 1
                    );

                    $message_push->setData($data_push)->save();

                    //PUSH TO USER ONLY
                    if (Push_Model_Message::hasIndividualPush()) {
                        if ($data["customers_receiver"]) {
                            $customers_data = explode(";", $data["customers_receiver"]);

                            foreach ($customers_data as $id_customer) {
                                if ($id_customer != "") {
                                    $customer_message = new Push_Model_Customer_Message();
                                    $customer_message_data = array(
                                        "customer_id" => $id_customer,
                                        "message_id" => $message_push->getId()
                                    );
                                    $customer_message->setData($customer_message_data);
                                    $customer_message->save();
                                }
                            }
                        }
                    }

                    /** Fallback for SAE, or disabled cron */
                    if(!Cron_Model_Cron::is_active()) {
                        $cron = new Cron_Model_Cron();
                        $task = $cron->find("pushinstant", "command");
                        Siberian_Cache::__clearLocks();
                        $siberian_cron = new Siberian_Cron();
                        $siberian_cron->execute($task);
                    }

                }
                //---END PUSH

                $html = array(
                    'success' => 1,
                    'success_message' => __('Your message has been successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }
    }

    protected function sendToAllCustomers ($inbox_message) {
        $customer_model = new Customer_Model_Customer();
        $customers = $customer_model->findAll(array("app_id" => $this->getApplication()->getId()));

        
            foreach ($customers as $customer) {
                
                $customer_message = new Inbox_Model_Application_Customer_Message();
                $customer_message_data = array(
                    "customer_id" => $customer->getId(),
                    "message_id" => $inbox_message->getId()
                );
                $customer_message->setData($customer_message_data);
                $customer_message->save();
                
            }
                

    }

    public function editcommentAction() {
        if($id = $this->getRequest()->getParam('message_id')) {
            $message = new Inbox_Model_Application_Message();

            $message->find($id);
            if(!$message->getId()) {
                throw new Exception(__('An error occurred during the process. Please try again later.'));
            }

            $html = $this->getLayout()->addPartial('inbox_comments_form', 'admin_view_default', 'inbox/application/edit/comment.phtml')
                ->setOptionValue($this->getCurrentOptionValue())
                ->setCurrentMessage($message)
                ->toHtml();

            $datas = array(
                "form_html" => $html
            );
        }

        $this->_sendJson($html);

    }

    public function replycommentAction() {
        if($id = $this->getRequest()->getParam('reply_id')) {

            $reply = new Inbox_Model_Application_Reply();

            $reply->find($id);
            if(!$reply->getId()) {
                throw new Exception(__('An error occurred during the process. Please try again later.'));
            }

            $html = $this->getLayout()->addPartial('inbox_comments_reply_form', 'admin_view_default', 'inbox/application/edit/reply_comment.phtml')
                ->setOptionValue($this->getCurrentOptionValue())
                ->setCurrentReply($reply)
                ->toHtml();

            $datas = array(
                "form_html" => $html
            );
        }

        $this->_sendJson($datas);

    }

    public function postreplyAction() {

        if($data = $this->getRequest()->getPost()) {

            $reply = new Inbox_Model_Application_Reply();

            $reply->setData($data)->save();

            $html = array(
                'success' => 1,
                'success_message' => __('Your message has been successfully saved'),
                'message_timeout' => 2,
                'message_button' => 0,
                'message_loader' => 0
            );
        }

        $this->_sendHtml($html);

    }

    public function deletemessageAction() {
        try {
            if($id = $this->getRequest()->getParam('delete_message_id')) {


                $message = new Inbox_Model_CustomerMessage();

                $message->find($id);
                if (!$message->getId()) {
                    throw new Siberian_Exception(__('An error occurred during the process. Please try again later.'));
                }

                $message
                    ->setIsVisibleInEditor(false)
                    ->save();

                $datas = array(
                    "success" => true
                );

            } else {
                throw new Exception(__('An error occurred during the process. Please try again later.'));
            }
        } catch(Exception $e) {
            $datas = array(
                "error" => true,
                "message" => $e->getMessage()
            );
        }

        $this->_sendJson($datas);
    }

    public function deletecommentAction() {
        try {
            if($id = $this->getRequest()->getParam("delete_comment_id")) {

                $reply = new Inbox_Model_Application_Reply();
                $reply->find($id);

                if (!$reply->getId()) {
                    throw new Siberian_Exception(__('An error occurred during the process. Please try again later.'));
                }

                $reply
                    ->setIsVisibleInEditor(false)
                    ->save();

                $datas = array(
                    "success" => true
                );

            } else {
                throw new Siberian_Exception(__('An error occurred during the process. Please try again later.'));
            }

        } catch(Exception $e) {
            $datas = array(
                "error" => true,
                "message" => $e->getMessage()
            );
        }

        $this->_sendJson($datas);
    }
}