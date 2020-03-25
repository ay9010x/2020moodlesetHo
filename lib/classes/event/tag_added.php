<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class tag_added extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'tag_instance';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventtagadded', 'tag');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' added the tag with id '{$this->other['tagid']}' to the item type '" .
            s($this->other['itemtype']) . "' with id '{$this->other['itemid']}'.";
    }

    
    public static function create_from_tag_instance($taginstance, $tagname, $tagrawname, $addsnapshot = false) {
        $event = self::create(array(
            'objectid' => $taginstance->id,
            'contextid' => $taginstance->contextid,
            'other' => array(
                'tagid' => $taginstance->tagid,
                'tagname' => $tagname,
                'tagrawname' => $tagrawname,
                'itemid' => $taginstance->itemid,
                'itemtype' => $taginstance->itemtype
            )
        ));
        if ($addsnapshot) {
            $event->add_record_snapshot('tag_instance', $taginstance);
        }
        return $event;
    }

    
    protected function get_legacy_logdata() {
        if ($this->other['itemtype'] === 'course') {
            $url = 'tag/search.php?query=' . urlencode($this->other['tagrawname']);
            return array($this->courseid, 'coursetags', 'add', $url, 'Course tagged');
        }

        return null;
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['tagid'])) {
            throw new \coding_exception('The \'tagid\' value must be set in other.');
        }

        if (!isset($this->other['itemid'])) {
            throw new \coding_exception('The \'itemid\' value must be set in other.');
        }

        if (!isset($this->other['itemtype'])) {
            throw new \coding_exception('The \'itemtype\' value must be set in other.');
        }

        if (!isset($this->other['tagname'])) {
            throw new \coding_exception('The \'tagname\' value must be set in other.');
        }

        if (!isset($this->other['tagrawname'])) {
            throw new \coding_exception('The \'tagrawname\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'tag_instance', 'restore' => base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['tagid'] = array('db' => 'tag', 'restore' => base::NOT_MAPPED);
        $othermapped['itemid'] = base::NOT_MAPPED;

        return $othermapped;
    }
}
