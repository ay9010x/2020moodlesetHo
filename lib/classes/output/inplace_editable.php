<?php



namespace core\output;

use templatable;
use renderable;
use lang_string;


class inplace_editable implements templatable, renderable {

    
    protected $component = null;

    
    protected $itemtype = null;

    
    protected $itemid = null;

    
    protected $value = null;

    
    protected $displayvalue = null;

    
    protected $editlabel = null;

    
    protected $edithint = null;

    
    protected $editable = false;

    
    protected $type = 'text';

    
    protected $options = '';

    
    public function __construct($component, $itemtype, $itemid, $editable,
            $displayvalue, $value = null, $edithint = null, $editlabel = null) {
        $this->component = $component;
        $this->itemtype = $itemtype;
        $this->itemid = $itemid;
        $this->editable = $editable;
        $this->displayvalue = $displayvalue;
        $this->value = $value;
        $this->edithint = $edithint;
        $this->editlabel = $editlabel;
    }

    
    public function set_type_toggle($options = null) {
        if ($options === null) {
            $options = array(0, 1);
        }
        $options = array_values($options);
        $idx = array_search($this->value, $options, true);
        if ($idx === false) {
            throw new \coding_exception('Specified value must be one of the toggle options');
        }
        $nextvalue = ($idx < count($options) - 1) ? $idx + 1 : 0;

        $this->type = 'toggle';
        $this->options = (string)$nextvalue;
        return $this;
    }

    
    public function set_type_select($options) {
        if (!array_key_exists($this->value, $options)) {
            throw new \coding_exception('Options for select element must contain an option for the specified value');
        }
        if (count($options) < 2) {
            $this->editable = false;
        }
        $this->type = 'select';

        $pairedoptions = [];
        foreach ($options as $key => $value) {
            $pairedoptions[] = [
                'key' => $key,
                'value' => $value,
            ];
        }
        $this->options = json_encode($pairedoptions);
        if ($this->displayvalue === null) {
            $this->displayvalue = $options[$this->value];
        }
        return $this;
    }

    
    protected function get_linkeverything() {
        if ($this->type === 'toggle') {
            return true;
        }

        if (preg_match('#<a .*>.*</a>#', $this->displayvalue) === 1) {
            return false;
        }

        return true;
    }

    
    public function export_for_template(\renderer_base $output) {
        if (!$this->editable) {
            return array(
                'displayvalue' => (string)$this->displayvalue
            );
        }

        return array(
            'component' => $this->component,
            'itemtype' => $this->itemtype,
            'itemid' => $this->itemid,
            'displayvalue' => (string)$this->displayvalue,
            'value' => (string)$this->value,
            'edithint' => (string)$this->edithint,
            'editlabel' => (string)$this->editlabel,
            'type' => $this->type,
            'options' => $this->options,
            'linkeverything' => $this->get_linkeverything() ? 1 : 0,
        );
    }

    
    public function render(\renderer_base $output) {
        return $output->render_from_template('core/inplace_editable', $this->export_for_template($output));
    }
}
