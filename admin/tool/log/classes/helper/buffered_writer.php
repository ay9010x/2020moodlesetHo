<?php



namespace tool_log\helper;
defined('MOODLE_INTERNAL') || die();


trait buffered_writer {

    
    protected $buffer = array();

    
    protected $buffersize;

    
    protected $count = 0;

    
    abstract protected function is_event_ignored(\core\event\base $event);

    
    public function write(\core\event\base $event) {
        global $PAGE;

        if ($this->is_event_ignored($event)) {
            return;
        }

                                $entry = $event->get_data();
        $entry['other'] = serialize($entry['other']);
        $entry['origin'] = $PAGE->requestorigin;
        $entry['ip'] = $PAGE->requestip;
        $entry['realuserid'] = \core\session\manager::is_loggedinas() ? $GLOBALS['USER']->realuser : null;

        $this->buffer[] = $entry;
        $this->count++;

        if (!isset($this->buffersize)) {
            $this->buffersize = $this->get_config('buffersize', 50);
        }

        if ($this->count >= $this->buffersize) {
            $this->flush();
        }
    }

    
    public function flush() {
        if ($this->count == 0) {
            return;
        }
        $events = $this->buffer;
        $this->count = 0;
        $this->buffer = array();
        $this->insert_event_entries($events);
    }

    
    abstract protected function insert_event_entries($evententries);

    
    abstract protected function get_config($name, $default = null);

    
    public function dispose() {
        $this->flush();
    }
}
