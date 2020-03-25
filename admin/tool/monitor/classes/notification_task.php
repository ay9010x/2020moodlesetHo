<?php



namespace tool_monitor;

defined('MOODLE_INTERNAL') || die();


class notification_task extends \core\task\adhoc_task {

    
    public function execute() {
        foreach ($this->get_custom_data() as $data) {
            $eventobj = $data->event;
            $subscriptionids = $data->subscriptionids;
            foreach ($subscriptionids as $id) {
                if ($message = $this->generate_message($id, $eventobj)) {
                    mtrace("Sending message to the user with id " . $message->userto->id . " for the subscription with id $id...");
                    message_send($message);
                    mtrace("Sent.");
                }
            }
        }
    }

    
    protected function generate_message($subscriptionid, \stdClass $eventobj) {

        try {
            $subscription = subscription_manager::get_subscription($subscriptionid);
        } catch (\dml_exception $e) {
                        return false;
        }
        $user = \core_user::get_user($subscription->userid);
        if (empty($user)) {
                        return false;
        }
        $context = \context_user::instance($user->id, IGNORE_MISSING);
        if ($context === false) {
                        return false;
        }

        $template = $subscription->template;
        $template = $this->replace_placeholders($template, $subscription, $eventobj, $context);
        $htmlmessage = format_text($template, $subscription->templateformat, array('context' => $context));
        $msgdata = new \stdClass();
        $msgdata->component         = 'tool_monitor';         $msgdata->name              = 'notification';         $msgdata->userfrom          = \core_user::get_noreply_user();
        $msgdata->userto            = $user;
        $msgdata->subject           = $subscription->get_name($context);
        $msgdata->fullmessage       = html_to_text($htmlmessage);
        $msgdata->fullmessageformat = FORMAT_PLAIN;
        $msgdata->fullmessagehtml   = $htmlmessage;
        $msgdata->smallmessage      = '';
        $msgdata->notification      = 1; 
        return $msgdata;
    }

    
    protected function replace_placeholders($template, subscription $subscription, $eventobj, $context) {
        $template = str_replace('{link}', $eventobj->link, $template);
        if ($eventobj->contextlevel == CONTEXT_MODULE && !empty($eventobj->contextinstanceid)
            && (strpos($template, '{modulelink}') !== false)) {
            $cm = get_fast_modinfo($eventobj->courseid)->get_cm($eventobj->contextinstanceid);
            $modulelink = $cm->url;
            $template = str_replace('{modulelink}', $modulelink, $template);
        }
        $template = str_replace('{rulename}', $subscription->get_name($context), $template);
        $template = str_replace('{description}', $subscription->get_description($context), $template);
        $template = str_replace('{eventname}', $subscription->get_event_name(), $template);

        return $template;
    }
}
