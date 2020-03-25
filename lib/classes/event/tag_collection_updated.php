<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class tag_collection_updated extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'tag_coll';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function create_from_record($tagcoll) {
        $event = self::create(array(
            'objectid' => $tagcoll->id,
            'context' => \context_system::instance(),
        ));
        $event->add_record_snapshot('tag_coll', $tagcoll);
        return $event;
    }

    
    public static function get_name() {
        return get_string('eventtagcollupdated', 'core_tag');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated the tag collection with id '$this->objectid'";
    }
}
