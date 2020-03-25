<?php



$observers = array(

        array(
        'eventname' => '\core\event\user_loggedout',
        'callback' => 'chat_user_logout',
        'includefile' => '/mod/chat/lib.php'
    )
);
