<?php


namespace core\task;


class messaging_cleanup_task extends scheduled_task {

    
    public function get_name() {
        return get_string('taskmessagingcleanup', 'admin');
    }

    
    public function execute() {
        global $CFG, $DB;

        $timenow = time();

                if (!empty($CFG->messagingdeletereadnotificationsdelay)) {
            $notificationdeletetime = $timenow - $CFG->messagingdeletereadnotificationsdelay;
            $params = array('notificationdeletetime' => $notificationdeletetime);
            $DB->delete_records_select('message_read', 'notification=1 AND timeread<:notificationdeletetime', $params);
        }

    }

}
