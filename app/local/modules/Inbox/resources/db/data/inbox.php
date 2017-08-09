<?php
$name = "Inbox";
$category = "contact";

# Install icons
$icons = array(
    "/app/local/modules/Inbox/resources/media/library/inbox/inbox1.png",
    "/app/local/modules/Inbox/resources/media/library/inbox/inbox2.png",
    "/app/local/modules/Inbox/resources/media/library/inbox/inbox3.png",
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    "library_id"     => $result["library_id"],
    "icon_id"        => $result["icon_id"],
    "code"           => "inbox",
    "name"           => $name,
    "model"          => "Inbox_Model_Application_Message",
    "desktop_uri"    => "inbox/application/",
    "mobile_uri"     => "inbox/mobile_list/",
    "only_once"      => 0,
    "is_ajax"        => 1,
    "use_my_account" => 1,
    "position"       => 240,
);

$option = Siberian_Feature::install($category, $data, array("code"));

/** Acl */
Siberian_Feature::installAcl($option);

# Icons Flat
$icons = array(
    "/app/local/modules/Inbox/resources/media/library/inbox/inbox1-flat.png",
    "/app/local/modules/Inbox/resources/media/library/inbox/inbox2-flat.png",
    "/app/local/modules/Inbox/resources/media/library/inbox/inbox3-flat.png",
);

Siberian_Feature::installIcons("{$name}-flat", $icons);

Siberian_Assets::copyAssets("/app/local/modules/Ìnbox/resources/var/apps/");


# Guess message dates.
$inbox_patch_1 = System_Model_Config::getValueFor("inbox_patch_4.8.11");
if(empty($inbox_patch_1)) {

    $this->query("UPDATE inbox_message 
    SET created_at = IFNULL((
        SELECT created_at
        FROM inbox_reply 
        WHERE inbox_reply.parent_id = inbox_message.message_id 
        ORDER BY created_at ASC
        LIMIT 1
    ), NOW()), 
    updated_at = IFNULL((
        SELECT updated_at
        FROM inbox_reply 
        WHERE inbox_reply.parent_id = inbox_message.message_id 
        ORDER BY created_at ASC
        LIMIT 1
    ), NOW())
    WHERE created_at = '0000-00-00 00:00:00'");

    System_Model_Config::setValueFor("inbox_patch_4.8.11", time());

}

# Update messages visibility, remove after
$inbox_patch = System_Model_Config::getValueFor("inbox_patch_4.8.12");
if(empty($inbox_patch)) {

    $this->query("UPDATE inbox_customer_message 
    SET is_visible_in_editor = (
        SELECT is_visible_in_editor 
        FROM inbox_message 
        WHERE inbox_message.message_id = inbox_customer_message.message_id
    ), 
    is_visible_in_mobile = (
        SELECT is_visible_in_mobile 
        FROM inbox_message 
        WHERE inbox_message.message_id = inbox_customer_message.message_id
    );");

    System_Model_Config::setValueFor("inbox_patch_4.8.12", time());

}
