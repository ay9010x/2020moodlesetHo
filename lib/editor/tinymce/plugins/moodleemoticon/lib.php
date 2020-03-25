<?php

defined('MOODLE_INTERNAL') || die();


class tinymce_moodleemoticon extends editor_tinymce_plugin {
    
    protected $buttons = array('moodleemoticon');

    protected function update_init_params(array &$params, context $context,
            array $options = null) {
        global $OUTPUT;

        if ($this->get_config('requireemoticon', 1)) {
                        $filters = filter_get_active_in_context($context);
            if (!array_key_exists('emoticon', $filters)) {
                return;
            }
        }

        if ($row = $this->find_button($params, 'image')) {
                        $this->add_button_after($params, $row, 'moodleemoticon', 'image');
        } else {
                        $this->add_button_after($params, $this->count_button_rows($params), 'moodleemoticon');
        }

                $this->add_js_plugin($params);

                $manager = get_emoticon_manager();
        $emoticons = $manager->get_emoticons();
        $imgs = array();
                $index = 0;
        foreach ($emoticons as $emoticon) {
            $imgs[$emoticon->text] = $OUTPUT->render($manager->prepare_renderable_emoticon(
                    $emoticon, array('class' => 'emoticon emoticon-index-'.$index++)));
        }
        $params['moodleemoticon_emoticons'] = json_encode($imgs);
    }
}
