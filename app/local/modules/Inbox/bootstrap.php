<?php
class Inbox_Bootstrap {

    public static function init($bootstrap) {

        # Register assets
        Siberian_Assets::registerAssets("Inbox", "/app/local/modules/Inbox/resources/var/apps/");
        Siberian_Assets::addJavascripts(array(
            "js/controllers/inbox.js",
            "js/factory/inbox.js",
        ));
    }

}
