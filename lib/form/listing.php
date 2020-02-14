<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once("HTML/QuickForm/input.php");


class MoodleQuickForm_listing extends HTML_QuickForm_input {

    
    protected $items = array();

    
    protected $showall;

    
    protected $hideall;

    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $options=array()) {

       $this->_type = 'listing';
        if (!empty($options['items'])) {
            $this->items = $options['items'];
        }
        if (!empty($options['showall'])) {
            $this->showall = $options['showall'];
        } else {
            $this->showall = get_string('showall');
        }
        if (!empty($options['hideall'])) {
            $this->hideall = $options['hideall'];
        } else {
            $this->hideall = get_string('hide');
        }
        parent::__construct($elementName, $elementLabel, $attributes);
    }

    
    public function MoodleQuickForm_listing($elementName=null, $elementLabel=null, $attributes=null, $options=array()) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes, $options);
    }

    
    function toHtml() {
        global $CFG, $PAGE;

        $mainhtml = html_writer::tag('div', $this->items[$this->getValue()]->mainhtml,
                array('id' => $this->getName().'_items_main', 'class' => 'formlistingmain'));

                $html = html_writer::tag('div', $mainhtml .
                    html_writer::tag('div', $this->showall,
                        array('id' => $this->getName().'_items_caption', 'class' => 'formlistingmore')),
                    array('id'=>$this->getName().'_items', 'class' => 'formlisting hide'));

                $itemrows = '';
        $html .= html_writer::tag('div', $itemrows,
                array('id' => $this->getName().'_items_all', 'class' => 'formlistingall'));

                $radiobuttons = '';
        foreach ($this->items as $itemid => $item) {
            $radioparams = array('name' => $this->getName(), 'value' => $itemid,
                    'id' => 'id_'.$itemid, 'class' => 'formlistinginputradio', 'type' => 'radio');
            if ($itemid == $this->getValue()) {
                $radioparams['checked'] = 'checked';
            }
            $radiobuttons .= html_writer::tag('div', html_writer::tag('input',
                html_writer::tag('div', $item->rowhtml, array('class' => 'formlistingradiocontent')), $radioparams),
                array('class' => 'formlistingradio'));
        }

                $html .= html_writer::tag('div', $radiobuttons,
                array('id' => 'formlistinginputcontainer_' . $this->getName(), 'class' => 'formlistinginputcontainer'));

        $module = array('name'=>'form_listing', 'fullpath'=>'/lib/form/yui/listing/listing.js',
            'requires'=>array('node', 'event', 'transition', 'escape'));

        $PAGE->requires->js_init_call('M.form_listing.init',
                 array(array(
                'elementid' => $this->getName().'_items',
                'hideall' => $this->hideall,
                'showall' => $this->showall,
                'hiddeninputid' => $this->getAttribute('id'),
                'items' => $this->items,
                'inputname' => $this->getName(),
                'currentvalue' => $this->getValue())), true, $module);

        return $html;
    }
}
