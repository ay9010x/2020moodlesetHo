<?php



namespace tool_langimport\event;

defined('MOODLE_INTERNAL') || die();


class langpack_removed extends \core\event\base {
    
    public static function event_with_langcode($langcode) {
        $data = array(
            'context' => \context_system::instance(),
            'other' => array(
                'langcode' => $langcode,
            )
        );

        return self::create($data);
    }

    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public function get_description() {
        return "The language pack '{$this->other['langcode']}' was removed.";
    }

    
    public function get_url() {
        return new \moodle_url('/admin/tool/langimport/');
    }

    
    public static function get_name() {
        return get_string('langpackremovedevent', 'tool_langimport');
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['langcode'])) {
            throw new \coding_exception('The \'langcode\' value must be set');
        }

                $cleanedlang = clean_param($this->other['langcode'], PARAM_SAFEDIR);
        if ($cleanedlang !== $this->other['langcode']) {
            throw new \coding_exception('The \'langcode\' value must be set to a valid language code');
        }
    }

    public static function get_other_mapping() {
                return false;
    }
}
