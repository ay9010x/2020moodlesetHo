<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class tag_created extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'tag';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function create_from_tag($tag) {
        $event = self::create(array(
            'objectid' => $tag->id,
            'relateduserid' => $tag->userid,
            'context' => \context_system::instance(),
            'other' => array(
                'name' => $tag->name,
                'rawname' => $tag->rawname
            )
        ));
        return $event;
    }

    
    public static function get_name() {
        return get_string('eventtagcreated', 'tag');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' created the tag with id '$this->objectid'.";
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['name'])) {
            throw new \coding_exception('The \'name\' value must be set in other.');
        }

        if (!isset($this->other['rawname'])) {
            throw new \coding_exception('The \'rawname\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'tag', 'restore' => base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
        return false;
    }
}
