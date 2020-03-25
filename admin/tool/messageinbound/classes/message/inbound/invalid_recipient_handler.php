<?php



namespace tool_messageinbound\message\inbound;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/repository/lib.php');


class invalid_recipient_handler extends \core\message\inbound\handler {

    
    public function can_change_validateaddress() {
        return false;
    }

    
    public function get_description() {
        return get_string('invalid_recipient_handler', 'tool_messageinbound');
    }

    
    public function get_name() {
        return get_string('invalid_recipient_handler_name', 'tool_messageinbound');
    }

    
    public function process_message(\stdClass $record, \stdClass $data) {
        global $DB;

        if (!$maildata = $DB->get_record('messageinbound_messagelist', array('id' => $record->datavalue))) {
                        throw new \core\message\inbound\processing_failed_exception('oldmessagenotfound', 'tool_messageinbound');
        }

        mtrace("=== Request to re-process message {$record->datavalue} from server.");
        mtrace("=== Message-Id:\t{$maildata->messageid}");
        mtrace("=== Recipient:\t{$maildata->address}");

        $manager = new \tool_messageinbound\manager();
        return $manager->process_existing_message($maildata);
    }

}
