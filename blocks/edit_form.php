<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/blocklib.php');


class block_edit_form extends moodleform {
    
    public $block;
    
    public $page;

    function __construct($actionurl, $block, $page) {
        global $CFG;
        $this->block = $block;
        $this->page = $page;
        parent::__construct($actionurl);
    }

    function definition() {
        $mform =& $this->_form;

                $this->specific_definition($mform);

                $mform->addElement('header', 'whereheader', get_string('wherethisblockappears', 'block'));

                $blockweight = $this->block->instance->weight;
        $weightoptions = array();
        if ($blockweight < -block_manager::MAX_WEIGHT) {
            $weightoptions[$blockweight] = $blockweight;
        }
        for ($i = -block_manager::MAX_WEIGHT; $i <= block_manager::MAX_WEIGHT; $i++) {
            $weightoptions[$i] = $i;
        }
        if ($blockweight > block_manager::MAX_WEIGHT) {
            $weightoptions[$blockweight] = $blockweight;
        }
        $first = reset($weightoptions);
        $weightoptions[$first] = get_string('bracketfirst', 'block', $first);
        $last = end($weightoptions);
        $weightoptions[$last] = get_string('bracketlast', 'block', $last);

        $regionoptions = $this->page->theme->get_all_block_regions();
        foreach ($this->page->blocks->get_regions() as $region) {
                        if (!isset($regionoptions[$region])) {
                $regionoptions[$region] = $region;
            }
        }

        $parentcontext = context::instance_by_id($this->block->instance->parentcontextid);
        $mform->addElement('hidden', 'bui_parentcontextid', $parentcontext->id);
        $mform->setType('bui_parentcontextid', PARAM_INT);

        $mform->addElement('static', 'bui_homecontext', get_string('createdat', 'block'), $parentcontext->get_context_name());
        $mform->addHelpButton('bui_homecontext', 'createdat', 'block');

                $pagetypelist = array();

                $bits = explode('-', $this->page->pagetype);

                                
                $ctxconditions = $this->page->context->contextlevel == CONTEXT_COURSE &&
                         $this->page->context->instanceid == get_site()->id;
                $pageconditions = isset($bits[0]) && isset($bits[1]) && $bits[0] == 'site' && $bits[1] == 'index';
                $editingatfrontpage = $ctxconditions && $pageconditions;

                $mform->addElement('hidden', 'bui_editingatfrontpage', (int)$editingatfrontpage);
        $mform->setType('bui_editingatfrontpage', PARAM_INT);

                        if ($editingatfrontpage) {
            $contextoptions = array();
            $contextoptions[BUI_CONTEXTS_FRONTPAGE_ONLY] = get_string('showonfrontpageonly', 'block');
            $contextoptions[BUI_CONTEXTS_FRONTPAGE_SUBS] = get_string('showonfrontpageandsubs', 'block');
            $contextoptions[BUI_CONTEXTS_ENTIRE_SITE]    = get_string('showonentiresite', 'block');
            $mform->addElement('select', 'bui_contexts', get_string('contexts', 'block'), $contextoptions);
            $mform->addHelpButton('bui_contexts', 'contexts', 'block');
            $pagetypelist['*'] = '*'; 
                        } else if ($parentcontext->contextlevel == CONTEXT_SYSTEM) {
            $mform->addElement('hidden', 'bui_contexts', BUI_CONTEXTS_ENTIRE_SITE);

        } else if ($parentcontext->contextlevel == CONTEXT_COURSE) {
                                                $mform->addElement('hidden', 'bui_contexts', BUI_CONTEXTS_CURRENT);
        } else if ($parentcontext->contextlevel == CONTEXT_MODULE or $parentcontext->contextlevel == CONTEXT_USER) {
                        $mform->addElement('hidden', 'bui_contexts', BUI_CONTEXTS_CURRENT);
        } else {
            $parentcontextname = $parentcontext->get_context_name();
            $contextoptions[BUI_CONTEXTS_CURRENT]      = get_string('showoncontextonly', 'block', $parentcontextname);
            $contextoptions[BUI_CONTEXTS_CURRENT_SUBS] = get_string('showoncontextandsubs', 'block', $parentcontextname);
            $mform->addElement('select', 'bui_contexts', get_string('contexts', 'block'), $contextoptions);
        }
        $mform->setType('bui_contexts', PARAM_INT);

                if (empty($pagetypelist)) {
            $pagetypelist = generate_page_type_patterns($this->page->pagetype, $parentcontext, $this->page->context);
            $displaypagetypewarning = false;
            if (!array_key_exists($this->block->instance->pagetypepattern, $pagetypelist)) {
                                $pagetypestringname = 'page-'.str_replace('*', 'x', $this->block->instance->pagetypepattern);
                if (get_string_manager()->string_exists($pagetypestringname, 'pagetype')) {
                    $pagetypelist[$this->block->instance->pagetypepattern] = get_string($pagetypestringname, 'pagetype');
                } else {
                                                                                $displaypagetypewarning = true;
                }
            }
        }

                if (count($pagetypelist) > 1) {
            if ($displaypagetypewarning) {
                $mform->addElement('static', 'pagetypewarning', '', get_string('pagetypewarning','block'));
            }

            $mform->addElement('select', 'bui_pagetypepattern', get_string('restrictpagetypes', 'block'), $pagetypelist);
        } else {
            $values = array_keys($pagetypelist);
            $value = array_pop($values);
            $mform->addElement('hidden', 'bui_pagetypepattern', $value);
            $mform->setType('bui_pagetypepattern', PARAM_RAW);
                                                                                                            if (!$editingatfrontpage) {
                                $strvalue = $value;
                $strkey = 'page-'.str_replace('*', 'x', $strvalue);
                if (get_string_manager()->string_exists($strkey, 'pagetype')) {
                    $strvalue = get_string($strkey, 'pagetype');
                }
                                $mform->addElement('static', 'bui_staticpagetypepattern',
                    get_string('restrictpagetypes','block'), $strvalue);
            }
        }

        if ($this->page->subpage) {
            if ($parentcontext->contextlevel == CONTEXT_USER) {
                $mform->addElement('hidden', 'bui_subpagepattern', '%@NULL@%');
                $mform->setType('bui_subpagepattern', PARAM_RAW);
            } else {
                $subpageoptions = array(
                    '%@NULL@%' => get_string('anypagematchingtheabove', 'block'),
                    $this->page->subpage => get_string('thisspecificpage', 'block', $this->page->subpage),
                );
                $mform->addElement('select', 'bui_subpagepattern', get_string('subpages', 'block'), $subpageoptions);
            }
        }

        $defaultregionoptions = $regionoptions;
        $defaultregion = $this->block->instance->defaultregion;
        if (!array_key_exists($defaultregion, $defaultregionoptions)) {
            $defaultregionoptions[$defaultregion] = $defaultregion;
        }
        $mform->addElement('select', 'bui_defaultregion', get_string('defaultregion', 'block'), $defaultregionoptions);
        $mform->addHelpButton('bui_defaultregion', 'defaultregion', 'block');

        $mform->addElement('select', 'bui_defaultweight', get_string('defaultweight', 'block'), $weightoptions);
        $mform->addHelpButton('bui_defaultweight', 'defaultweight', 'block');

                $mform->addElement('header', 'onthispage', get_string('onthispage', 'block'));

        $mform->addElement('selectyesno', 'bui_visible', get_string('visible', 'block'));

        $blockregion = $this->block->instance->region;
        if (!array_key_exists($blockregion, $regionoptions)) {
            $regionoptions[$blockregion] = $blockregion;
        }
        $mform->addElement('select', 'bui_region', get_string('region', 'block'), $regionoptions);

        $mform->addElement('select', 'bui_weight', get_string('weight', 'block'), $weightoptions);

        $pagefields = array('bui_visible', 'bui_region', 'bui_weight');
        if (!$this->block->user_can_edit()) {
            $mform->hardFreezeAllVisibleExcept($pagefields);
        }
        if (!$this->page->user_can_edit_blocks()) {
            $mform->hardFreeze($pagefields);
        }

        $this->add_action_buttons();
    }

    function set_data($defaults) {
                $blockfields = array('showinsubcontexts', 'pagetypepattern', 'subpagepattern', 'parentcontextid',
                'defaultregion', 'defaultweight', 'visible', 'region', 'weight');
        foreach ($blockfields as $field) {
            $newname = 'bui_' . $field;
            $defaults->$newname = $defaults->$field;
        }

                if (!empty($this->block->config)) {
            foreach ($this->block->config as $field => $value) {
                $configfield = 'config_' . $field;
                $defaults->$configfield = $value;
            }
        }

                if (empty($defaults->bui_subpagepattern)) {
            $defaults->bui_subpagepattern = '%@NULL@%';
        }

        $systemcontext = context_system::instance();
        if ($defaults->parentcontextid == $systemcontext->id) {
            $defaults->bui_contexts = BUI_CONTEXTS_ENTIRE_SITE;         } else {
            $defaults->bui_contexts = $defaults->bui_showinsubcontexts;
        }

        parent::set_data($defaults);
    }

    
    protected function specific_definition($mform) {
            }
}
