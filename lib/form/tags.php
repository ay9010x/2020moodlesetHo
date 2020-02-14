<?php




global $CFG;
require_once($CFG->libdir . '/form/autocomplete.php');


class MoodleQuickForm_tags extends MoodleQuickForm_autocomplete {
    
    const DEFAULTUI = 'defaultui';

    
    const ONLYOFFICIAL = 'onlyofficial';

    
    const NOOFFICIAL = 'noofficial';

    
    protected $showstandard = false;

    
    protected $tagsoptions = array();

    
    public function __construct($elementName = null, $elementLabel = null, $options = array(), $attributes = null) {
        $validoptions = array();

        if (!empty($options)) {
                                    $showstandard = core_tag_tag::BOTH_STANDARD_AND_NOT;
            if (isset($options['showstandard'])) {
                $showstandard = $options['showstandard'];
            } else if (isset($options['display'])) {
                debugging('Option "display" is deprecated, each tag area can be configured to show standard tags or not ' .
                    'by admin or manager. If it is necessary for the developer to override it, please use "showstandard" option',
                    DEBUG_DEVELOPER);
                if ($options['display'] === self::NOOFFICIAL) {
                    $showstandard = core_tag_tag::HIDE_STANDARD;
                } else if ($options['display'] === self::ONLYOFFICIAL) {
                    $showstandard = core_tag_tag::STANDARD_ONLY;
                }
            } else if (!empty($options['component']) && !empty($options['itemtype'])) {
                $showstandard = core_tag_area::get_showstandard($options['component'], $options['itemtype']);
            }

            $this->tagsoptions = $options;

            $this->showstandard = ($showstandard != core_tag_tag::HIDE_STANDARD);
            if ($this->showstandard) {
                $validoptions = $this->load_standard_tags();
            }
                        $attributes['tags'] = ($showstandard != core_tag_tag::STANDARD_ONLY);
            $attributes['multiple'] = 'multiple';
            $attributes['placeholder'] = get_string('entertags', 'tag');
            $attributes['showsuggestions'] = $this->showstandard;
        }

        parent::__construct($elementName, $elementLabel, $validoptions, $attributes);
    }

    
    public function onQuickFormEvent($event, $arg, &$caller) {
        if ($event === 'createElement') {
            if (!is_array($arg[2])) {
                $arg[2] = [];
            }
            $arg[2] += array('itemtype' => '', 'component' => '');
        }
        return parent::onQuickFormEvent($event, $arg, $caller);
    }

    
    protected function is_tagging_enabled() {
        if (!empty($this->tagsoptions['itemtype']) && !empty($this->tagsoptions['component'])) {
            $enabled = core_tag_tag::is_enabled($this->tagsoptions['component'], $this->tagsoptions['itemtype']);
            if ($enabled === false) {
                return false;
            }
        }
                return true;
    }

    
    public function MoodleQuickForm_tags($elementName = null, $elementLabel = null, $options = array(), $attributes = null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $options, $attributes);
    }

    
    protected function get_tag_collection() {
        if (empty($this->tagsoptions['tagcollid']) && (empty($this->tagsoptions['itemtype']) ||
                empty($this->tagsoptions['component']))) {
            debugging('You need to specify \'itemtype\' and \'component\' of the tagged '
                    . 'area in the tags form element options',
                    DEBUG_DEVELOPER);
        }
        if (!empty($this->tagsoptions['tagcollid'])) {
            return $this->tagsoptions['tagcollid'];
        }
        if ($this->tagsoptions['itemtype']) {
            $this->tagsoptions['tagcollid'] = core_tag_area::get_collection($this->tagsoptions['component'],
                    $this->tagsoptions['itemtype']);
        } else {
            $this->tagsoptions['tagcollid'] = core_tag_collection::get_default();
        }
        return $this->tagsoptions['tagcollid'];
    }

    
    function toHtml(){
        global $OUTPUT;

        $managelink = '';
        if (has_capability('moodle/tag:manage', context_system::instance()) && $this->showstandard) {
            $url = new moodle_url('/tag/manage.php', array('tc' => $this->get_tag_collection()));
            $managelink = ' ' . $OUTPUT->action_link($url, get_string('managestandardtags', 'tag'));
        }

        return parent::toHTML() . $managelink;
    }

    
    public function accept(&$renderer, $required = false, $error = null) {
        if ($this->is_tagging_enabled()) {
            $renderer->renderElement($this, $required, $error);
        } else {
            $renderer->renderHidden($this);
        }
    }

    
    protected function load_standard_tags() {
        global $CFG, $DB;
        if (!$this->is_tagging_enabled()) {
            return array();
        }
        $namefield = empty($CFG->keeptagnamecase) ? 'name' : 'rawname';
        $tags = $DB->get_records_menu('tag',
            array('isstandard' => 1, 'tagcollid' => $this->get_tag_collection()),
            $namefield, 'id,' . $namefield);
        return array_combine($tags, $tags);
    }

    
    public function exportValue(&$submitValues, $assoc = false) {
        if (!$this->is_tagging_enabled()) {
            return $assoc ? array($this->getName() => array()) : array();
        }

        return parent::exportValue($submitValues, $assoc);
    }
}
