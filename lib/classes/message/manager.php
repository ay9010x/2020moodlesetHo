<?php



namespace core\message;

defined('MOODLE_INTERNAL') || die();


class manager {
    
    protected static $buffer = array();

    
    public static function send_message($eventdata, \stdClass $savemessage, array $processorlist) {
        global $CFG;

        if (!($eventdata instanceof \stdClass) && !($eventdata instanceof message)) {
                        throw new \coding_exception('Message should be of type stdClass or \core\message\message');
        }

        require_once($CFG->dirroot.'/message/lib.php'); 
        if (empty($processorlist)) {
                        \core\event\message_sent::create_from_ids($eventdata->userfrom->id, $eventdata->userto->id, $savemessage->id)->trigger();

            if ($savemessage->notification or empty($CFG->messaging)) {
                                                                $messageid = message_mark_message_read($savemessage, time(), true);

            } else {
                                                $messageid = $savemessage->id;
            }

            return $messageid;
        }

                return self::send_message_to_processors($eventdata, $savemessage, $processorlist);
    }

    
    protected static function send_message_to_processors($eventdata, \stdClass $savemessage, array
    $processorlist) {
        global $CFG, $DB;

                
        if ($DB->is_transaction_started()) {
                        $eventdata = clone($eventdata);
            $eventdata->userto = clone($eventdata->userto);
            $eventdata->userfrom = clone($eventdata->userfrom);

                        unset($eventdata->userto->description);
            unset($eventdata->userfrom->description);

            self::$buffer[] = array($eventdata, $savemessage, $processorlist);
            return $savemessage->id;
        }

        $processors = get_message_processors(true);

        $failed = false;
        foreach ($processorlist as $procname) {
                        $proceventdata = ($eventdata instanceof message) ? $eventdata->get_eventobject_for_processor($procname) : $eventdata;
            if (!$processors[$procname]->object->send_message($proceventdata)) {
                debugging('Error calling message processor ' . $procname);
                $failed = true;
                                            }
        }

                \core\event\message_sent::create_from_ids($eventdata->userfrom->id, $eventdata->userto->id, $savemessage->id)->trigger();

        if (empty($CFG->messaging)) {
                                                $messageid = message_mark_message_read($savemessage, time());

        } else if ($failed) {
                        $messageid = $savemessage->id;

        } else if ($DB->count_records('message_working', array('unreadmessageid' => $savemessage->id)) == 0) {
                        $messageid = message_mark_message_read($savemessage, time(), true);

        } else {
                        $messageid = $savemessage->id;
        }

        return $messageid;
    }

    
    public static function database_transaction_commited() {
        if (!self::$buffer) {
            return;
        }
        self::process_buffer();
    }

    
    public static function database_transaction_rolledback() {
        self::$buffer = array();
    }

    
    protected static function process_buffer() {
                $messages = self::$buffer;
        self::$buffer = array();

        foreach ($messages as $message) {
            list($eventdata, $savemessage, $processorlist) = $message;
            self::send_message_to_processors($eventdata, $savemessage, $processorlist);
        }
    }
}
