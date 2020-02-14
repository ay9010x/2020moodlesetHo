<?php




class backup_chat_activity_structure_step extends backup_activity_structure_step {
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

                $chat = new backup_nested_element('chat', array('id'), array(
            'name', 'intro', 'introformat', 'keepdays', 'studentlogs',
            'chattime', 'schedule', 'timemodified'));
        $messages = new backup_nested_element('messages');

        $message = new backup_nested_element('message', array('id'), array(
            'userid', 'groupid', 'system', 'message_text', 'timestamp'));

                $message->set_source_alias('message', 'message_text');

                $chat->add_child($messages);
            $messages->add_child($message);

                $chat->set_source_table('chat', array('id' => backup::VAR_ACTIVITYID));

                if ($userinfo) {
            $message->set_source_table('chat_messages', array('chatid' => backup::VAR_PARENTID));
        }

                $message->annotate_ids('user', 'userid');
        $message->annotate_ids('group', 'groupid');

                $chat->annotate_files('mod_chat', 'intro', null); 
                return $this->prepare_activity_structure($chat);
    }
}
