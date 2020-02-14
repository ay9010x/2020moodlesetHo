<?php



namespace tool_langimport\event;

defined('MOODLE_INTERNAL') || die();


class langpack_imported extends \core\event\base {
    
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
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public function get_description() {
        return "The language pack '{$this->other['langcode']}' was installed.";
    }

    
    public static function get_name() {
        return get_string('langpackinstalledevent', 'tool_langimport');
    }

    
    public function get_url() {
        return new \moodle_url('/admin/tool/langimport/');
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
