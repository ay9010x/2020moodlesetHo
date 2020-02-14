<?php






class restore_chat_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('chat', '/activity/chat');
        if ($userinfo) {
            $paths[] = new restore_path_element('chat_message', '/activity/chat/messages/message');
        }

                return $this->prepare_activity_structure($paths);
    }

    protected function process_chat($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->chattime = $this->apply_date_offset($data->chattime);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

                $newitemid = $DB->insert_record('chat', $data);
                $this->apply_activity_instance($newitemid);
    }

    protected function process_chat_message($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->chatid = $this->get_new_parentid('chat');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        $data->message = $data->message_text;
        $data->timestamp = $this->apply_date_offset($data->timestamp);

        $newitemid = $DB->insert_record('chat_messages', $data);
        $this->set_mapping('chat_message', $oldid, $newitemid);     }

    protected function after_execute() {
                $this->add_related_files('mod_chat', 'intro', null);
    }
}
