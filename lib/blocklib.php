<?php




defined('MOODLE_INTERNAL') || die();


define('BLOCK_POS_LEFT',  'side-pre');
define('BLOCK_POS_RIGHT', 'side-post');


define('BUI_CONTEXTS_FRONTPAGE_ONLY', 0);
define('BUI_CONTEXTS_FRONTPAGE_SUBS', 1);
define('BUI_CONTEXTS_ENTIRE_SITE', 2);

define('BUI_CONTEXTS_CURRENT', 0);
define('BUI_CONTEXTS_CURRENT_SUBS', 1);


class block_not_on_page_exception extends moodle_exception {
    
    public function __construct($instanceid, $page) {
        $a = new stdClass;
        $a->instanceid = $instanceid;
        $a->url = $page->url->out();
        parent::__construct('blockdoesnotexistonpage', '', $page->url->out(), $a);
    }
}


class block_manager {
    
    const MAX_WEIGHT = 10;


    
    protected $page;

    
    protected $regions = array();

    
    protected $defaultregion = null;

    
    protected $allblocks = null;

    
    protected $addableblocks = null;

    
    protected $birecordsbyregion = null;

    
    protected $blockinstances = array();

    
    protected $visibleblockcontent = array();

    
    protected $extracontent = array();

    
    protected $movingblock = null;

    
    protected $fakeblocksonly = false;


    
    public function __construct($page) {
        $this->page = $page;
    }


    
    public function get_regions() {
        if (is_null($this->defaultregion)) {
            $this->page->initialise_theme_and_output();
        }
        return array_keys($this->regions);
    }

    
    public function get_default_region() {
        $this->page->initialise_theme_and_output();
        return $this->defaultregion;
    }

    
    public function get_addable_blocks() {
        $this->check_is_loaded();

        if (!is_null($this->addableblocks)) {
            return $this->addableblocks;
        }

                $this->addableblocks = array();

        $allblocks = blocks_get_record();
        if (empty($allblocks)) {
            return $this->addableblocks;
        }

        $unaddableblocks = self::get_undeletable_block_types();
        $pageformat = $this->page->pagetype;
        foreach($allblocks as $block) {
            if (!$bi = block_instance($block->name)) {
                continue;
            }
            if ($block->visible && !in_array($block->name, $unaddableblocks) &&
                    ($bi->instance_allow_multiple() || !$this->is_block_present($block->name)) &&
                    blocks_name_allowed_in_format($block->name, $pageformat) &&
                    $bi->user_can_addto($this->page)) {
                $this->addableblocks[$block->name] = $block;
            }
        }

        return $this->addableblocks;
    }

    
    public function is_block_present($blockname) {
        if (empty($this->blockinstances)) {
            return false;
        }

        foreach ($this->blockinstances as $region) {
            foreach ($region as $instance) {
                if (empty($instance->instance->blockname)) {
                    continue;
                }
                if ($instance->instance->blockname == $blockname) {
                    return true;
                }
            }
        }
        return false;
    }

    
    public function is_known_block_type($blockname, $includeinvisible = false) {
        $blocks = $this->get_installed_blocks();
        foreach ($blocks as $block) {
            if ($block->name == $blockname && ($includeinvisible || $block->visible)) {
                return true;
            }
        }
        return false;
    }

    
    public function is_known_region($region) {
        if (empty($region)) {
            return false;
        }
        return array_key_exists($region, $this->regions);
    }

    
    public function get_blocks_for_region($region) {
        $this->check_is_loaded();
        $this->ensure_instances_exist($region);
        return $this->blockinstances[$region];
    }

    
    public function get_content_for_region($region, $output) {
        $this->check_is_loaded();
        $this->ensure_content_created($region, $output);
        return $this->visibleblockcontent[$region];
    }

    
    protected function get_move_target_url($region, $weight) {
        return new moodle_url($this->page->url, array('bui_moveid' => $this->movingblock,
                'bui_newregion' => $region, 'bui_newweight' => $weight, 'sesskey' => sesskey()));
    }

    
    public function region_has_content($region, $output) {

        if (!$this->is_known_region($region)) {
            return false;
        }
        $this->check_is_loaded();
        $this->ensure_content_created($region, $output);
                        if ($this->page->user_is_editing() && $this->page->user_can_edit_blocks() && $this->movingblock) {
                                    return true;
        }
        return !empty($this->visibleblockcontent[$region]) || !empty($this->extracontent[$region]);
    }

    
    public function get_installed_blocks() {
        global $DB;
        if (is_null($this->allblocks)) {
            $this->allblocks = $DB->get_records('block');
        }
        return $this->allblocks;
    }

    
    public static function get_undeletable_block_types() {
        global $CFG;

        if (!isset($CFG->undeletableblocktypes) || (!is_array($CFG->undeletableblocktypes) && !is_string($CFG->undeletableblocktypes))) {
            return array('navigation','settings');
        } else if (is_string($CFG->undeletableblocktypes)) {
            return explode(',', $CFG->undeletableblocktypes);
        } else {
            return $CFG->undeletableblocktypes;
        }
    }


    
    public function add_region($region, $custom = true) {
        global $SESSION;
        $this->check_not_yet_loaded();
        if ($custom) {
            if (array_key_exists($region, $this->regions)) {
                                                debugging('A custom region conflicts with a block region in the theme.', DEBUG_DEVELOPER);
            }
                                    $type = $this->page->pagetype;
            if (!isset($SESSION->custom_block_regions)) {
                $SESSION->custom_block_regions = array($type => array($region));
            } else if (!isset($SESSION->custom_block_regions[$type])) {
                $SESSION->custom_block_regions[$type] = array($region);
            } else if (!in_array($region, $SESSION->custom_block_regions[$type])) {
                $SESSION->custom_block_regions[$type][] = $region;
            }
        }
        $this->regions[$region] = 1;
    }

    
    public function add_regions($regions, $custom = true) {
        foreach ($regions as $region) {
            $this->add_region($region, $custom);
        }
    }

    
    public function add_custom_regions_for_pagetype($pagetype) {
        global $SESSION;
        if (isset($SESSION->custom_block_regions[$pagetype])) {
            foreach ($SESSION->custom_block_regions[$pagetype] as $customregion) {
                $this->add_region($customregion, false);
            }
        }
    }

    
    public function set_default_region($defaultregion) {
        $this->check_not_yet_loaded();
        if ($defaultregion) {
            $this->check_region_is_known($defaultregion);
        }
        $this->defaultregion = $defaultregion;
    }

    
    public function add_fake_block($bc, $region) {
        $this->page->initialise_theme_and_output();
        if (!$this->is_known_region($region)) {
            $region = $this->get_default_region();
        }
        if (array_key_exists($region, $this->visibleblockcontent)) {
            throw new coding_exception('block_manager has already prepared the blocks in region ' .
                    $region . 'for output. It is too late to add a fake block.');
        }
        if (!isset($bc->attributes['data-block'])) {
            $bc->attributes['data-block'] = '_fake';
        }
        $bc->attributes['class'] .= ' block_fake';
        $this->extracontent[$region][] = $bc;
    }

    
    public function region_completely_docked($region, $output) {
        global $CFG;
                if (!$this->page->theme->enable_dock || empty($CFG->allowblockstodock)) {
            return false;
        }

                if ($this->movingblock) {
            return false;
        }

                if ($this->page->user_is_editing() && $this->page->user_can_edit_blocks()) {
            return false;
        }

        $this->check_is_loaded();
        $this->ensure_content_created($region, $output);
        if (!$this->region_has_content($region, $output)) {
                        return false;
        }
        foreach ($this->visibleblockcontent[$region] as $instance) {
            if (!get_user_preferences('docked_block_instance_'.$instance->blockinstanceid, 0)) {
                return false;
            }
        }
        return true;
    }

    
    public function region_uses_dock($regions, $output) {
        if (!$this->page->theme->enable_dock) {
            return false;
        }
        $this->check_is_loaded();
        foreach((array)$regions as $region) {
            $this->ensure_content_created($region, $output);
            foreach($this->visibleblockcontent[$region] as $instance) {
                if(!empty($instance->content) && get_user_preferences('docked_block_instance_'.$instance->blockinstanceid, 0)) {
                    return true;
                }
            }
        }
        return false;
    }


    
    public function load_blocks($includeinvisible = null) {
        global $DB, $CFG;

        if (!is_null($this->birecordsbyregion)) {
                        return;
        }

        if ($CFG->version < 2009050619) {
                        $this->birecordsbyregion = array();
            return;
        }

                if (is_null($this->defaultregion)) {
            $this->page->initialise_theme_and_output();
                        if (empty($this->regions)) {
                $this->birecordsbyregion = array();
                return;
            }
        }

                if ($this->fakeblocksonly) {
            $this->birecordsbyregion = $this->prepare_per_region_arrays();
            return;
        }

        if (is_null($includeinvisible)) {
            $includeinvisible = $this->page->user_is_editing();
        }
        if ($includeinvisible) {
            $visiblecheck = '';
        } else {
            $visiblecheck = 'AND (bp.visible = 1 OR bp.visible IS NULL)';
        }

        $context = $this->page->context;
        $contexttest = 'bi.parentcontextid IN (:contextid2, :contextid3)';
        $parentcontextparams = array();
        $parentcontextids = $context->get_parent_context_ids();
        if ($parentcontextids) {
            list($parentcontexttest, $parentcontextparams) =
                    $DB->get_in_or_equal($parentcontextids, SQL_PARAMS_NAMED, 'parentcontext');
            $contexttest = "($contexttest OR (bi.showinsubcontexts = 1 AND bi.parentcontextid $parentcontexttest))";
        }

        $pagetypepatterns = matching_page_type_patterns($this->page->pagetype);
        list($pagetypepatterntest, $pagetypepatternparams) =
                $DB->get_in_or_equal($pagetypepatterns, SQL_PARAMS_NAMED, 'pagetypepatterntest');

        $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
        $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = bi.id AND ctx.contextlevel = :contextlevel)";

        $systemcontext = context_system::instance();
        $params = array(
            'contextlevel' => CONTEXT_BLOCK,
            'subpage1' => $this->page->subpage,
            'subpage2' => $this->page->subpage,
            'contextid1' => $context->id,
            'contextid2' => $context->id,
            'contextid3' => $systemcontext->id,
            'pagetype' => $this->page->pagetype,
        );
        if ($this->page->subpage === '') {
            $params['subpage1'] = '';
            $params['subpage2'] = '';
        }
        $sql = "SELECT
                    bi.id,
                    bp.id AS blockpositionid,
                    bi.blockname,
                    bi.parentcontextid,
                    bi.showinsubcontexts,
                    bi.pagetypepattern,
                    bi.subpagepattern,
                    bi.defaultregion,
                    bi.defaultweight,
                    COALESCE(bp.visible, 1) AS visible,
                    COALESCE(bp.region, bi.defaultregion) AS region,
                    COALESCE(bp.weight, bi.defaultweight) AS weight,
                    bi.configdata
                    $ccselect

                FROM {block_instances} bi
                JOIN {block} b ON bi.blockname = b.name
                LEFT JOIN {block_positions} bp ON bp.blockinstanceid = bi.id
                                                  AND bp.contextid = :contextid1
                                                  AND bp.pagetype = :pagetype
                                                  AND bp.subpage = :subpage1
                $ccjoin

                WHERE
                $contexttest
                AND bi.pagetypepattern $pagetypepatterntest
                AND (bi.subpagepattern IS NULL OR bi.subpagepattern = :subpage2)
                $visiblecheck
                AND b.visible = 1

                ORDER BY
                    COALESCE(bp.region, bi.defaultregion),
                    COALESCE(bp.weight, bi.defaultweight),
                    bi.id";
        $blockinstances = $DB->get_recordset_sql($sql, $params + $parentcontextparams + $pagetypepatternparams);

        $this->birecordsbyregion = $this->prepare_per_region_arrays();
        $unknown = array();
        foreach ($blockinstances as $bi) {
            context_helper::preload_from_record($bi);
            if ($this->is_known_region($bi->region)) {
                $this->birecordsbyregion[$bi->region][] = $bi;
            } else {
                $unknown[] = $bi;
            }
        }

                                if (!empty($this->defaultregion)) {
            $this->birecordsbyregion[$this->defaultregion] =
                    array_merge($this->birecordsbyregion[$this->defaultregion], $unknown);
        }
    }

    
    public function add_block($blockname, $region, $weight, $showinsubcontexts, $pagetypepattern = NULL, $subpagepattern = NULL) {
        global $DB;
                        $this->check_known_block_type($blockname, true);
        $this->check_region_is_known($region);

        if (empty($pagetypepattern)) {
            $pagetypepattern = $this->page->pagetype;
        }

        $blockinstance = new stdClass;
        $blockinstance->blockname = $blockname;
        $blockinstance->parentcontextid = $this->page->context->id;
        $blockinstance->showinsubcontexts = !empty($showinsubcontexts);
        $blockinstance->pagetypepattern = $pagetypepattern;
        $blockinstance->subpagepattern = $subpagepattern;
        $blockinstance->defaultregion = $region;
        $blockinstance->defaultweight = $weight;
        $blockinstance->configdata = '';
        $blockinstance->id = $DB->insert_record('block_instances', $blockinstance);

                context_block::instance($blockinstance->id);

                if ($block = block_instance($blockname, $blockinstance)) {
            $block->instance_create();
        }
    }

    public function add_block_at_end_of_default_region($blockname) {
        $defaulregion = $this->get_default_region();

        $lastcurrentblock = end($this->birecordsbyregion[$defaulregion]);
        if ($lastcurrentblock) {
            $weight = $lastcurrentblock->weight + 1;
        } else {
            $weight = 0;
        }

        if ($this->page->subpage) {
            $subpage = $this->page->subpage;
        } else {
            $subpage = null;
        }

                        $pagetypepattern = $this->page->pagetype;
        if (strpos($pagetypepattern, 'course-view') === 0) {
            $pagetypepattern = 'course-view-*';
        }

                                        
                        if (preg_match('/^mod-.*-/', $pagetypepattern)) {
            $pagetypelist = generate_page_type_patterns($this->page->pagetype, null, $this->page->context);
                        if (is_array($pagetypelist) && !array_key_exists($pagetypepattern, $pagetypelist)) {
                $pagetypepattern = key($pagetypelist);
            }
        }
                
        $this->add_block($blockname, $defaulregion, $weight, false, $pagetypepattern, $subpage);
    }

    
    public function add_blocks($blocks, $pagetypepattern = NULL, $subpagepattern = NULL, $showinsubcontexts=false, $weight=0) {
        $initialweight = $weight;
        $this->add_regions(array_keys($blocks), false);
        foreach ($blocks as $region => $regionblocks) {
            foreach ($regionblocks as $offset => $blockname) {
                $weight = $initialweight + $offset;
                $this->add_block($blockname, $region, $weight, $showinsubcontexts, $pagetypepattern, $subpagepattern);
            }
        }
    }

    
    public function reposition_block($blockinstanceid, $newregion, $newweight) {
        global $DB;

        $this->check_region_is_known($newregion);
        $inst = $this->find_instance($blockinstanceid);

        $bi = $inst->instance;
        if ($bi->weight == $bi->defaultweight && $bi->region == $bi->defaultregion &&
                !$bi->showinsubcontexts && strpos($bi->pagetypepattern, '*') === false &&
                (!$this->page->subpage || $bi->subpagepattern)) {

                        $newbi = new stdClass;
            $newbi->id = $bi->id;
            $newbi->defaultregion = $newregion;
            $newbi->defaultweight = $newweight;
            $DB->update_record('block_instances', $newbi);

            if ($bi->blockpositionid) {
                $bp = new stdClass;
                $bp->id = $bi->blockpositionid;
                $bp->region = $newregion;
                $bp->weight = $newweight;
                $DB->update_record('block_positions', $bp);
            }

        } else {
                        $bp = new stdClass;
            $bp->region = $newregion;
            $bp->weight = $newweight;

            if ($bi->blockpositionid) {
                $bp->id = $bi->blockpositionid;
                $DB->update_record('block_positions', $bp);

            } else {
                $bp->blockinstanceid = $bi->id;
                $bp->contextid = $this->page->context->id;
                $bp->pagetype = $this->page->pagetype;
                if ($this->page->subpage) {
                    $bp->subpage = $this->page->subpage;
                } else {
                    $bp->subpage = '';
                }
                $bp->visible = $bi->visible;
                $DB->insert_record('block_positions', $bp);
            }
        }
    }

    
    public function find_instance($instanceid) {
        foreach ($this->regions as $region => $notused) {
            $this->ensure_instances_exist($region);
            foreach($this->blockinstances[$region] as $instance) {
                if ($instance->instance->id == $instanceid) {
                    return $instance;
                }
            }
        }
        throw new block_not_on_page_exception($instanceid, $this->page);
    }


    
    protected function check_not_yet_loaded() {
        if (!is_null($this->birecordsbyregion)) {
            throw new coding_exception('block_manager has already loaded the blocks, to it is too late to change things that might affect which blocks are visible.');
        }
    }

    
    protected function check_is_loaded() {
        if (is_null($this->birecordsbyregion)) {
            throw new coding_exception('block_manager has not yet loaded the blocks, to it is too soon to request the information you asked for.');
        }
    }

    
    protected function check_known_block_type($blockname, $includeinvisible = false) {
        if (!$this->is_known_block_type($blockname, $includeinvisible)) {
            if ($this->is_known_block_type($blockname, true)) {
                throw new coding_exception('Unknown block type ' . $blockname);
            } else {
                throw new coding_exception('Block type ' . $blockname . ' has been disabled by the administrator.');
            }
        }
    }

    
    protected function check_region_is_known($region) {
        if (!$this->is_known_region($region)) {
            throw new coding_exception('Trying to reference an unknown block region ' . $region);
        }
    }

    
    protected function prepare_per_region_arrays() {
        $result = array();
        foreach ($this->regions as $region => $notused) {
            $result[$region] = array();
        }
        return $result;
    }

    
    protected function create_block_instances($birecords) {
        $results = array();
        foreach ($birecords as $record) {
            if ($blockobject = block_instance($record->blockname, $record, $this->page)) {
                $results[] = $blockobject;
            }
        }
        return $results;
    }

    
    public function create_all_block_instances() {
        foreach ($this->get_regions() as $region) {
            $this->ensure_instances_exist($region);
        }
    }

    
    protected function create_block_contents($instances, $output, $region) {
        $results = array();

        $lastweight = 0;
        $lastblock = 0;
        if ($this->movingblock) {
            $first = reset($instances);
            if ($first) {
                $lastweight = $first->instance->weight - 2;
            }
        }

        foreach ($instances as $instance) {
            $content = $instance->get_content_for_output($output);
            if (empty($content)) {
                continue;
            }

            if ($this->movingblock && $lastweight != $instance->instance->weight &&
                    $content->blockinstanceid != $this->movingblock && $lastblock != $this->movingblock) {
                $results[] = new block_move_target($this->get_move_target_url($region, ($lastweight + $instance->instance->weight)/2));
            }

            if ($content->blockinstanceid == $this->movingblock) {
                $content->add_class('beingmoved');
                $content->annotation .= get_string('movingthisblockcancel', 'block',
                        html_writer::link($this->page->url, get_string('cancel')));
            }

            $results[] = $content;
            $lastweight = $instance->instance->weight;
            $lastblock = $instance->instance->id;
        }

        if ($this->movingblock && $lastblock != $this->movingblock) {
            $results[] = new block_move_target($this->get_move_target_url($region, $lastweight + 1));
        }
        return $results;
    }

    
    protected function ensure_instances_exist($region) {
        $this->check_region_is_known($region);
        if (!array_key_exists($region, $this->blockinstances)) {
            $this->blockinstances[$region] =
                    $this->create_block_instances($this->birecordsbyregion[$region]);
        }
    }

    
    public function ensure_content_created($region, $output) {
        $this->ensure_instances_exist($region);
        if (!array_key_exists($region, $this->visibleblockcontent)) {
            $contents = array();
            if (array_key_exists($region, $this->extracontent)) {
                $contents = $this->extracontent[$region];
            }
            $contents = array_merge($contents, $this->create_block_contents($this->blockinstances[$region], $output, $region));
            if ($region == $this->defaultregion) {
                $addblockui = block_add_block_ui($this->page, $output);
                if ($addblockui) {
                    $contents[] = $addblockui;
                }
            }
            $this->visibleblockcontent[$region] = $contents;
        }
    }


    
    public function edit_controls($block) {
        global $CFG;

        $controls = array();
        $actionurl = $this->page->url->out(false, array('sesskey'=> sesskey()));
        $blocktitle = $block->title;
        if (empty($blocktitle)) {
            $blocktitle = $block->arialabel;
        }

        if ($this->page->user_can_edit_blocks()) {
                        $str = new lang_string('moveblock', 'block', $blocktitle);
            $controls[] = new action_menu_link_primary(
                new moodle_url($actionurl, array('bui_moveid' => $block->instance->id)),
                new pix_icon('t/move', $str, 'moodle', array('class' => 'iconsmall', 'title' => '')),
                $str,
                array('class' => 'editing_move')
            );

        }

        if ($this->page->user_can_edit_blocks() || $block->user_can_edit()) {
                        $str = new lang_string('configureblock', 'block', $blocktitle);
            $controls[] = new action_menu_link_secondary(
                new moodle_url($actionurl, array('bui_editid' => $block->instance->id)),
                new pix_icon('t/edit', $str, 'moodle', array('class' => 'iconsmall', 'title' => '')),
                $str,
                array('class' => 'editing_edit')
            );

        }

        if ($this->page->user_can_edit_blocks() && $block->instance_can_be_hidden()) {
                        if ($block->instance->visible) {
                $str = new lang_string('hideblock', 'block', $blocktitle);
                $url = new moodle_url($actionurl, array('bui_hideid' => $block->instance->id));
                $icon = new pix_icon('t/hide', $str, 'moodle', array('class' => 'iconsmall', 'title' => ''));
                $attributes = array('class' => 'editing_hide');
            } else {
                $str = new lang_string('showblock', 'block', $blocktitle);
                $url = new moodle_url($actionurl, array('bui_showid' => $block->instance->id));
                $icon = new pix_icon('t/show', $str, 'moodle', array('class' => 'iconsmall', 'title' => ''));
                $attributes = array('class' => 'editing_show');
            }
            $controls[] = new action_menu_link_secondary($url, $icon, $str, $attributes);
        }

                $rolesurl = null;

        if (get_assignable_roles($block->context, ROLENAME_SHORT)) {
            $rolesurl = new moodle_url('/admin/roles/assign.php', array('contextid' => $block->context->id));
            $str = new lang_string('assignrolesinblock', 'block', $blocktitle);
            $icon = 'i/assignroles';
        } else if (has_capability('moodle/role:review', $block->context) or get_overridable_roles($block->context)) {
            $rolesurl = new moodle_url('/admin/roles/permissions.php', array('contextid' => $block->context->id));
            $str = get_string('permissions', 'role');
            $icon = 'i/permissions';
        } else if (has_any_capability(array('moodle/role:safeoverride', 'moodle/role:override', 'moodle/role:assign'), $block->context)) {
            $rolesurl = new moodle_url('/admin/roles/check.php', array('contextid' => $block->context->id));
            $str = get_string('checkpermissions', 'role');
            $icon = 'i/checkpermissions';
        }

        if ($rolesurl) {
                                                $return = $this->page->url->out(false);
            $return = str_replace($CFG->wwwroot . '/', '', $return);
            $rolesurl->param('returnurl', $return);

            $controls[] = new action_menu_link_secondary(
                $rolesurl,
                new pix_icon($icon, $str, 'moodle', array('class' => 'iconsmall', 'title' => '')),
                $str,
                array('class' => 'editing_roles')
            );
        }

        if ($this->user_can_delete_block($block)) {
                        $str = new lang_string('deleteblock', 'block', $blocktitle);
            $controls[] = new action_menu_link_secondary(
                new moodle_url($actionurl, array('bui_deleteid' => $block->instance->id)),
                new pix_icon('t/delete', $str, 'moodle', array('class' => 'iconsmall', 'title' => '')),
                $str,
                array('class' => 'editing_delete')
            );
        }

        return $controls;
    }

    
    protected function user_can_delete_block($block) {
        return $this->page->user_can_edit_blocks() && $block->user_can_edit() &&
                $block->user_can_addto($this->page) &&
                !in_array($block->instance->blockname, self::get_undeletable_block_types());
    }

    
    public function process_url_actions() {
        if (!$this->page->user_is_editing()) {
            return false;
        }
        return $this->process_url_add() || $this->process_url_delete() ||
            $this->process_url_show_hide() || $this->process_url_edit() ||
            $this->process_url_move();
    }

    
    public function process_url_add() {
        $blocktype = optional_param('bui_addblock', null, PARAM_PLUGIN);
        if (!$blocktype) {
            return false;
        }

        require_sesskey();

        if (!$this->page->user_can_edit_blocks()) {
            throw new moodle_exception('nopermissions', '', $this->page->url->out(), get_string('addblock'));
        }

        if (!array_key_exists($blocktype, $this->get_addable_blocks())) {
            throw new moodle_exception('cannotaddthisblocktype', '', $this->page->url->out(), $blocktype);
        }

        $this->add_block_at_end_of_default_region($blocktype);

                $this->page->ensure_param_not_in_url('bui_addblock');

        return true;
    }

    
    public function process_url_delete() {
        global $CFG, $PAGE, $OUTPUT;

        $blockid = optional_param('bui_deleteid', null, PARAM_INT);
        $confirmdelete = optional_param('bui_confirm', null, PARAM_INT);

        if (!$blockid) {
            return false;
        }

        require_sesskey();
        $block = $this->page->blocks->find_instance($blockid);
        if (!$this->user_can_delete_block($block)) {
            throw new moodle_exception('nopermissions', '', $this->page->url->out(), get_string('deleteablock'));
        }

        if (!$confirmdelete) {
            $deletepage = new moodle_page();
            $deletepage->set_pagelayout('admin');
            $deletepage->set_course($this->page->course);
            $deletepage->set_context($this->page->context);
            if ($this->page->cm) {
                $deletepage->set_cm($this->page->cm);
            }

            $deleteurlbase = str_replace($CFG->wwwroot . '/', '/', $this->page->url->out_omit_querystring());
            $deleteurlparams = $this->page->url->params();
            $deletepage->set_url($deleteurlbase, $deleteurlparams);
            $deletepage->set_block_actions_done();
                                    $PAGE = $deletepage;
                        $output = $deletepage->get_renderer('core');
            $OUTPUT = $output;

            $site = get_site();
            $blocktitle = $block->get_title();
            $strdeletecheck = get_string('deletecheck', 'block', $blocktitle);
            $message = get_string('deleteblockcheck', 'block', $blocktitle);

                        if ($block->instance->showinsubcontexts == 1) {
                $parentcontext = context::instance_by_id($block->instance->parentcontextid);
                $systemcontext = context_system::instance();
                $messagestring = new stdClass();
                $messagestring->location = $parentcontext->get_context_name();

                                if ($parentcontext->id != $systemcontext->id && is_inside_frontpage($parentcontext)) {
                    $messagestring->pagetype = get_string('showonfrontpageandsubs', 'block');
                } else {
                    $pagetypes = generate_page_type_patterns($this->page->pagetype, $parentcontext);
                    $messagestring->pagetype = $block->instance->pagetypepattern;
                    if (isset($pagetypes[$block->instance->pagetypepattern])) {
                        $messagestring->pagetype = $pagetypes[$block->instance->pagetypepattern];
                    }
                }

                $message = get_string('deleteblockwarning', 'block', $messagestring);
            }

            $PAGE->navbar->add($strdeletecheck);
            $PAGE->set_title($blocktitle . ': ' . $strdeletecheck);
            $PAGE->set_heading($site->fullname);
            echo $OUTPUT->header();
            $confirmurl = new moodle_url($deletepage->url, array('sesskey' => sesskey(), 'bui_deleteid' => $block->instance->id, 'bui_confirm' => 1));
            $cancelurl = new moodle_url($deletepage->url);
            $yesbutton = new single_button($confirmurl, get_string('yes'));
            $nobutton = new single_button($cancelurl, get_string('no'));
            echo $OUTPUT->confirm($message, $yesbutton, $nobutton);
            echo $OUTPUT->footer();
                        exit;
        } else {
            blocks_delete_instance($block->instance);
                        $this->page->ensure_param_not_in_url('bui_deleteid');
            $this->page->ensure_param_not_in_url('bui_confirm');
            return true;
        }
    }

    
    public function process_url_show_hide() {
        if ($blockid = optional_param('bui_hideid', null, PARAM_INT)) {
            $newvisibility = 0;
        } else if ($blockid = optional_param('bui_showid', null, PARAM_INT)) {
            $newvisibility = 1;
        } else {
            return false;
        }

        require_sesskey();

        $block = $this->page->blocks->find_instance($blockid);

        if (!$this->page->user_can_edit_blocks()) {
            throw new moodle_exception('nopermissions', '', $this->page->url->out(), get_string('hideshowblocks'));
        } else if (!$block->instance_can_be_hidden()) {
            return false;
        }

        blocks_set_visibility($block->instance, $this->page, $newvisibility);

                $this->page->ensure_param_not_in_url('bui_hideid');
        $this->page->ensure_param_not_in_url('bui_showid');

        return true;
    }

    
    public function process_url_edit() {
        global $CFG, $DB, $PAGE, $OUTPUT;

        $blockid = optional_param('bui_editid', null, PARAM_INT);
        if (!$blockid) {
            return false;
        }

        require_sesskey();
        require_once($CFG->dirroot . '/blocks/edit_form.php');

        $block = $this->find_instance($blockid);

        if (!$block->user_can_edit() && !$this->page->user_can_edit_blocks()) {
            throw new moodle_exception('nopermissions', '', $this->page->url->out(), get_string('editblock'));
        }

        $editpage = new moodle_page();
        $editpage->set_pagelayout('admin');
        $editpage->set_course($this->page->course);
                $editpage->set_context($this->page->context);
        if ($this->page->cm) {
            $editpage->set_cm($this->page->cm);
        }
        $editurlbase = str_replace($CFG->wwwroot . '/', '/', $this->page->url->out_omit_querystring());
        $editurlparams = $this->page->url->params();
        $editurlparams['bui_editid'] = $blockid;
        $editpage->set_url($editurlbase, $editurlparams);
        $editpage->set_block_actions_done();
                        $PAGE = $editpage;
                $output = $editpage->get_renderer('core');
        $OUTPUT = $output;

        $formfile = $CFG->dirroot . '/blocks/' . $block->name() . '/edit_form.php';
        if (is_readable($formfile)) {
            require_once($formfile);
            $classname = 'block_' . $block->name() . '_edit_form';
            if (!class_exists($classname)) {
                $classname = 'block_edit_form';
            }
        } else {
            $classname = 'block_edit_form';
        }

        $mform = new $classname($editpage->url, $block, $this->page);
        $mform->set_data($block->instance);

        if ($mform->is_cancelled()) {
            redirect($this->page->url);

        } else if ($data = $mform->get_data()) {
            $bi = new stdClass;
            $bi->id = $block->instance->id;

                        $bi->pagetypepattern = $data->bui_pagetypepattern;
            $bi->showinsubcontexts = (bool) $data->bui_contexts;
            if (empty($data->bui_subpagepattern) || $data->bui_subpagepattern == '%@NULL@%') {
                $bi->subpagepattern = null;
            } else {
                $bi->subpagepattern = $data->bui_subpagepattern;
            }

            $systemcontext = context_system::instance();
            $frontpagecontext = context_course::instance(SITEID);
            $parentcontext = context::instance_by_id($data->bui_parentcontextid);

                        if (has_capability('moodle/site:manageblocks', $parentcontext)) { 
                                $bi->parentcontextid = $parentcontext->id;

                if ($data->bui_editingatfrontpage) {   
                                        
                    switch ($data->bui_contexts) {
                        case BUI_CONTEXTS_ENTIRE_SITE:
                                                        $bi->parentcontextid = $systemcontext->id;
                            $bi->showinsubcontexts = true;
                            $bi->pagetypepattern  = '*';
                            break;
                        case BUI_CONTEXTS_FRONTPAGE_SUBS:
                                                        $bi->parentcontextid = $frontpagecontext->id;
                            $bi->showinsubcontexts = true;
                            $bi->pagetypepattern  = '*';
                            break;
                        case BUI_CONTEXTS_FRONTPAGE_ONLY:
                                                        $bi->parentcontextid = $frontpagecontext->id;
                            $bi->showinsubcontexts = false;
                            $bi->pagetypepattern  = 'site-index';
                                                                                    break;
                    }
                }
            }

            $bits = explode('-', $bi->pagetypepattern);
                        if (($parentcontext->contextlevel == CONTEXT_COURSE) && ($parentcontext->instanceid != SITEID)) {
                                                if ($bits[0] == 'mod' || $bi->pagetypepattern == '*') {
                    $bi->showinsubcontexts = 1;
                } else {
                    $bi->showinsubcontexts = 0;
                }
            } else  if ($parentcontext->contextlevel == CONTEXT_USER) {
                                                if ($bits[0] == 'user' or $bits[0] == 'my') {
                                        $bi->subpagepattern = null;
                }
            }

            $bi->defaultregion = $data->bui_defaultregion;
            $bi->defaultweight = $data->bui_defaultweight;
            $DB->update_record('block_instances', $bi);

            if (!empty($block->config)) {
                $config = clone($block->config);
            } else {
                $config = new stdClass;
            }
            foreach ($data as $configfield => $value) {
                if (strpos($configfield, 'config_') !== 0) {
                    continue;
                }
                $field = substr($configfield, 7);
                $config->$field = $value;
            }
            $block->instance_config_save($config);

            $bp = new stdClass;
            $bp->visible = $data->bui_visible;
            $bp->region = $data->bui_region;
            $bp->weight = $data->bui_weight;
            $needbprecord = !$data->bui_visible || $data->bui_region != $data->bui_defaultregion ||
                    $data->bui_weight != $data->bui_defaultweight;

            if ($block->instance->blockpositionid && !$needbprecord) {
                $DB->delete_records('block_positions', array('id' => $block->instance->blockpositionid));

            } else if ($block->instance->blockpositionid && $needbprecord) {
                $bp->id = $block->instance->blockpositionid;
                $DB->update_record('block_positions', $bp);

            } else if ($needbprecord) {
                $bp->blockinstanceid = $block->instance->id;
                $bp->contextid = $this->page->context->id;
                $bp->pagetype = $this->page->pagetype;
                if ($this->page->subpage) {
                    $bp->subpage = $this->page->subpage;
                } else {
                    $bp->subpage = '';
                }
                $DB->insert_record('block_positions', $bp);
            }

            redirect($this->page->url);

        } else {
            $strheading = get_string('blockconfiga', 'moodle', $block->get_title());
            $editpage->set_title($strheading);
            $editpage->set_heading($strheading);
            $bits = explode('-', $this->page->pagetype);
            if ($bits[0] == 'tag' && !empty($this->page->subpage)) {
                                $editpage->navbar->add(get_string('tags'), new moodle_url('/tag/'));
                $tag = core_tag_tag::get($this->page->subpage);
                                if ($tag) {
                    $editpage->navbar->add($tag->get_display_name(), $tag->get_view_url());
                }
            }
            $editpage->navbar->add($block->get_title());
            $editpage->navbar->add(get_string('configuration'));
            echo $output->header();
            echo $output->heading($strheading, 2);
            $mform->display();
            echo $output->footer();
            exit;
        }
    }

    
    public function process_url_move() {
        global $CFG, $DB, $PAGE;

        $blockid = optional_param('bui_moveid', null, PARAM_INT);
        if (!$blockid) {
            return false;
        }

        require_sesskey();

        $block = $this->find_instance($blockid);

        if (!$this->page->user_can_edit_blocks()) {
            throw new moodle_exception('nopermissions', '', $this->page->url->out(), get_string('editblock'));
        }

        $newregion = optional_param('bui_newregion', '', PARAM_ALPHANUMEXT);
        $newweight = optional_param('bui_newweight', null, PARAM_FLOAT);
        if (!$newregion || is_null($newweight)) {
                        $this->movingblock = $blockid;
            $this->page->ensure_param_not_in_url('bui_moveid');
            return false;
        }

        if (!$this->is_known_region($newregion)) {
            throw new moodle_exception('unknownblockregion', '', $this->page->url, $newregion);
        }

                $blocks = $this->birecordsbyregion[$newregion];

        $maxweight = self::MAX_WEIGHT;
        $minweight = -self::MAX_WEIGHT;

                $spareweights = array();
        $usedweights = array();
        for ($i = $minweight; $i <= $maxweight; $i++) {
            $spareweights[$i] = $i;
            $usedweights[$i] = array();
        }

                foreach ($blocks as $bi) {
            if ($bi->weight > $maxweight) {
                                                                                                $parseweight = $bi->weight;
                while (!array_key_exists($parseweight, $usedweights)) {
                    $usedweights[$parseweight] = array();
                    $spareweights[$parseweight] = $parseweight;
                    $parseweight--;
                }
                $maxweight = $bi->weight;
            } else if ($bi->weight < $minweight) {
                                                                $parseweight = $bi->weight;
                while (!array_key_exists($parseweight, $usedweights)) {
                    $usedweights[$parseweight] = array();
                    $spareweights[$parseweight] = $parseweight;
                    $parseweight++;
                }
                $minweight = $bi->weight;
            }
            if ($bi->id != $block->instance->id) {
                unset($spareweights[$bi->weight]);
                $usedweights[$bi->weight][] = $bi->id;
            }
        }

                $bestdistance = max(abs($newweight - self::MAX_WEIGHT), abs($newweight + self::MAX_WEIGHT)) + 1;
        $bestgap = null;
        foreach ($spareweights as $spareweight) {
            if (abs($newweight - $spareweight) < $bestdistance) {
                $bestdistance = abs($newweight - $spareweight);
                $bestgap = $spareweight;
            }
        }

                if (is_null($bestgap)) {
            $bestgap = self::MAX_WEIGHT + 1;
            while (!empty($usedweights[$bestgap])) {
                $bestgap++;
            }
        }

                if ($bestgap < $newweight) {
            $newweight = floor($newweight);
            for ($weight = $bestgap + 1; $weight <= $newweight; $weight++) {
                if (array_key_exists($weight, $usedweights)) {
                    foreach ($usedweights[$weight] as $biid) {
                        $this->reposition_block($biid, $newregion, $weight - 1);
                    }
                }
            }
            $this->reposition_block($block->instance->id, $newregion, $newweight);
        } else {
            $newweight = ceil($newweight);
            for ($weight = $bestgap - 1; $weight >= $newweight; $weight--) {
                if (array_key_exists($weight, $usedweights)) {
                    foreach ($usedweights[$weight] as $biid) {
                        $this->reposition_block($biid, $newregion, $weight + 1);
                    }
                }
            }
            $this->reposition_block($block->instance->id, $newregion, $newweight);
        }

        $this->page->ensure_param_not_in_url('bui_moveid');
        $this->page->ensure_param_not_in_url('bui_newregion');
        $this->page->ensure_param_not_in_url('bui_newweight');
        return true;
    }

    
    public function show_only_fake_blocks($setting = true) {
        $this->fakeblocksonly = $setting;
    }
}



function block_method_result($blockname, $method, $param = NULL) {
    if(!block_load_class($blockname)) {
        return NULL;
    }
    return call_user_func(array('block_'.$blockname, $method), $param);
}


function block_instance($blockname, $instance = NULL, $page = NULL) {
    if(!block_load_class($blockname)) {
        return false;
    }
    $classname = 'block_'.$blockname;
    $retval = new $classname;
    if($instance !== NULL) {
        if (is_null($page)) {
            global $PAGE;
            $page = $PAGE;
        }
        $retval->_load_instance($instance, $page);
    }
    return $retval;
}


function block_load_class($blockname) {
    global $CFG;

    if(empty($blockname)) {
        return false;
    }

    $classname = 'block_'.$blockname;

    if(class_exists($classname)) {
        return true;
    }

    $blockpath = $CFG->dirroot.'/blocks/'.$blockname.'/block_'.$blockname.'.php';

    if (file_exists($blockpath)) {
        require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
        include_once($blockpath);
    }else{
                return false;
    }

    return class_exists($classname);
}


function matching_page_type_patterns($pagetype) {
    $patterns = array($pagetype);
    $bits = explode('-', $pagetype);
    if (count($bits) == 3 && $bits[0] == 'mod') {
        if ($bits[2] == 'view') {
            $patterns[] = 'mod-*-view';
        } else if ($bits[2] == 'index') {
            $patterns[] = 'mod-*-index';
        }
    }
    while (count($bits) > 0) {
        $patterns[] = implode('-', $bits) . '-*';
        array_pop($bits);
    }
    $patterns[] = '*';
    return $patterns;
}


function matching_page_type_patterns_from_pattern($pattern) {
    $patterns = array($pattern);
    if ($pattern === '*') {
        return $patterns;
    }

        $star = strpos($pattern, '-*');
    if ($star !== false) {
        $pattern = substr($pattern, 0, $star);
    }

    $patterns = array_merge($patterns, matching_page_type_patterns($pattern));
    $patterns = array_unique($patterns);

    return $patterns;
}


function generate_page_type_patterns($pagetype, $parentcontext = null, $currentcontext = null) {
    global $CFG; 
    $bits = explode('-', $pagetype);

    $core = core_component::get_core_subsystems();
    $plugins = core_component::get_plugin_types();

        $componentarray = null;
    for ($i = count($bits); $i > 0; $i--) {
        $possiblecomponentarray = array_slice($bits, 0, $i);
        $possiblecomponent = implode('', $possiblecomponentarray);

                if (array_key_exists($possiblecomponent, $core) && !empty($core[$possiblecomponent])) {
            $libfile = $core[$possiblecomponent].'/lib.php';
            if (file_exists($libfile)) {
                require_once($libfile);
                $function = $possiblecomponent.'_page_type_list';
                if (function_exists($function)) {
                    if ($patterns = $function($pagetype, $parentcontext, $currentcontext)) {
                        break;
                    }
                }
            }
        }

                if (array_key_exists($possiblecomponent, $plugins) && !empty($plugins[$possiblecomponent])) {

                        if (count($bits) > $i) {
                $pluginname = $bits[$i];
                $directory = core_component::get_plugin_directory($possiblecomponent, $pluginname);
                if (!empty($directory)){
                    $libfile = $directory.'/lib.php';
                    if (file_exists($libfile)) {
                        require_once($libfile);
                        $function = $possiblecomponent.'_'.$pluginname.'_page_type_list';
                        if (!function_exists($function)) {
                            $function = $pluginname.'_page_type_list';
                        }
                        if (function_exists($function)) {
                            if ($patterns = $function($pagetype, $parentcontext, $currentcontext)) {
                                break;
                            }
                        }
                    }
                }
            }

                                    $directory = $plugins[$possiblecomponent];
            $libfile = $directory.'/lib.php';
            if (file_exists($libfile)) {
                require_once($libfile);
                $function = $possiblecomponent.'_page_type_list';
                if (function_exists($function)) {
                    if ($patterns = $function($pagetype, $parentcontext, $currentcontext)) {
                        break;
                    }
                }
            }
        }
    }

    if (empty($patterns)) {
        $patterns = default_page_type_list($pagetype, $parentcontext, $currentcontext);
    }

            if ((!isset($currentcontext) or !isset($parentcontext) or $currentcontext->id != $parentcontext->id) && !isset($patterns['*'])) {
                $patterns['*'] = get_string('page-x', 'pagetype');
    }

    return $patterns;
}


function default_page_type_list($pagetype, $parentcontext = null, $currentcontext = null) {
            $patterns = array($pagetype => $pagetype);
    $bits = explode('-', $pagetype);
    while (count($bits) > 0) {
        $pattern = implode('-', $bits) . '-*';
        $pagetypestringname = 'page-'.str_replace('*', 'x', $pattern);
                if (get_string_manager()->string_exists($pagetypestringname, 'pagetype')) {
            $patterns[$pattern] = get_string($pagetypestringname, 'pagetype');
        } else {
            $patterns[$pattern] = $pattern;
        }
        array_pop($bits);
    }
    $patterns['*'] = get_string('page-x', 'pagetype');
    return $patterns;
}


function my_page_type_list($pagetype, $parentcontext = null, $currentcontext = null) {
    return array('my-index' => get_string('page-my-index', 'pagetype'));
}


function mod_page_type_list($pagetype, $parentcontext = null, $currentcontext = null) {
    $patterns = plugin_page_type_list($pagetype, $parentcontext, $currentcontext);
    if (empty($patterns)) {
                        $bits = explode('-', $pagetype);
        $patterns = array($pagetype => $pagetype);
        if ($bits[2] == 'view') {
            $patterns['mod-*-view'] = get_string('page-mod-x-view', 'pagetype');
        } else if ($bits[2] == 'index') {
            $patterns['mod-*-index'] = get_string('page-mod-x-index', 'pagetype');
        }
    }
    return $patterns;
}


function block_add_block_ui($page, $output) {
    global $CFG, $OUTPUT;
    if (!$page->user_is_editing() || !$page->user_can_edit_blocks()) {
        return null;
    }

    $bc = new block_contents();
    $bc->title = get_string('addblock');
    $bc->add_class('block_adminblock');
    $bc->attributes['data-block'] = 'adminblock';

    $missingblocks = $page->blocks->get_addable_blocks();
    if (empty($missingblocks)) {
        $bc->content = get_string('noblockstoaddhere');
        return $bc;
    }

    $menu = array();
    foreach ($missingblocks as $block) {
        $blockobject = block_instance($block->name);
        if ($blockobject !== false && $blockobject->user_can_addto($page)) {
            $menu[$block->name] = $blockobject->get_title();
        }
    }
    core_collator::asort($menu);

    $actionurl = new moodle_url($page->url, array('sesskey'=>sesskey()));
    $select = new single_select($actionurl, 'bui_addblock', $menu, null, array(''=>get_string('adddots')), 'add_block');
    $select->set_label(get_string('addblock'), array('class'=>'accesshide'));
    $bc->content = $OUTPUT->render($select);
    return $bc;
}


function blocks_remove_inappropriate($course) {
        return;
    
}


function blocks_name_allowed_in_format($name, $pageformat) {
    $accept = NULL;
    $maxdepth = -1;
    if (!$bi = block_instance($name)) {
        return false;
    }

    $formats = $bi->applicable_formats();
    if (!$formats) {
        $formats = array();
    }
    foreach ($formats as $format => $allowed) {
        $formatregex = '/^'.str_replace('*', '[^-]*', $format).'.*$/';
        $depth = substr_count($format, '-');
        if (preg_match($formatregex, $pageformat) && $depth > $maxdepth) {
            $maxdepth = $depth;
            $accept = $allowed;
        }
    }
    if ($accept === NULL) {
        $accept = !empty($formats['all']);
    }
    return $accept;
}


function blocks_delete_instance($instance, $nolongerused = false, $skipblockstables = false) {
    global $DB;

        if ($pluginsfunction = get_plugins_with_function('pre_block_delete')) {
        foreach ($pluginsfunction as $plugintype => $plugins) {
            foreach ($plugins as $pluginfunction) {
                $pluginfunction($instance);
            }
        }
    }

    if ($block = block_instance($instance->blockname, $instance)) {
        $block->instance_delete();
    }
    context_helper::delete_instance(CONTEXT_BLOCK, $instance->id);

    if (!$skipblockstables) {
        $DB->delete_records('block_positions', array('blockinstanceid' => $instance->id));
        $DB->delete_records('block_instances', array('id' => $instance->id));
        $DB->delete_records_list('user_preferences', 'name', array('block'.$instance->id.'hidden','docked_block_instance_'.$instance->id));
    }
}


function blocks_delete_instances($instanceids) {
    global $DB;

    $limit = 1000;
    $count = count($instanceids);
    $chunks = [$instanceids];
    if ($count > $limit) {
        $chunks = array_chunk($instanceids, $limit);
    }

        foreach ($chunks as $chunk) {
        $instances = $DB->get_recordset_list('block_instances', 'id', $chunk);
        foreach ($instances as $instance) {
            blocks_delete_instance($instance, false, true);
        }
        $instances->close();

        $DB->delete_records_list('block_positions', 'blockinstanceid', $chunk);
        $DB->delete_records_list('block_instances', 'id', $chunk);

        $preferences = array();
        foreach ($chunk as $instanceid) {
            $preferences[] = 'block' . $instanceid . 'hidden';
            $preferences[] = 'docked_block_instance_' . $instanceid;
        }
        $DB->delete_records_list('user_preferences', 'name', $preferences);
    }
}


function blocks_delete_all_for_context($contextid) {
    global $DB;
    $instances = $DB->get_recordset('block_instances', array('parentcontextid' => $contextid));
    foreach ($instances as $instance) {
        blocks_delete_instance($instance, true);
    }
    $instances->close();
    $DB->delete_records('block_instances', array('parentcontextid' => $contextid));
    $DB->delete_records('block_positions', array('contextid' => $contextid));
}


function blocks_set_visibility($instance, $page, $newvisibility) {
    global $DB;
    if (!empty($instance->blockpositionid)) {
                $DB->set_field('block_positions', 'visible', $newvisibility, array('id' => $instance->blockpositionid));
        return;
    }

        $bp = new stdClass;
    $bp->blockinstanceid = $instance->id;
    $bp->contextid = $page->context->id;
    $bp->pagetype = $page->pagetype;
    if ($page->subpage) {
        $bp->subpage = $page->subpage;
    }
    $bp->visible = $newvisibility;
    $bp->region = $instance->defaultregion;
    $bp->weight = $instance->defaultweight;
    $DB->insert_record('block_positions', $bp);
}


function blocks_get_record($blockid = NULL, $notusedanymore = false) {
    global $PAGE;
    $blocks = $PAGE->blocks->get_installed_blocks();
    if ($blockid === NULL) {
        return $blocks;
    } else if (isset($blocks[$blockid])) {
        return $blocks[$blockid];
    } else {
        return false;
    }
}


function blocks_find_block($blockid, $blocksarray) {
    if (empty($blocksarray)) {
        return false;
    }
    foreach($blocksarray as $blockgroup) {
        if (empty($blockgroup)) {
            continue;
        }
        foreach($blockgroup as $instance) {
            if($instance->blockid == $blockid) {
                return $instance;
            }
        }
    }
    return false;
}


 
function blocks_parse_default_blocks_list($blocksstr) {
    $blocks = array();
    $bits = explode(':', $blocksstr);
    if (!empty($bits)) {
        $leftbits = trim(array_shift($bits));
        if ($leftbits != '') {
            $blocks[BLOCK_POS_LEFT] = explode(',', $leftbits);
        }
    }
    if (!empty($bits)) {
        $rightbits = trim(array_shift($bits));
        if ($rightbits != '') {
            $blocks[BLOCK_POS_RIGHT] = explode(',', $rightbits);
        }
    }
    return $blocks;
}


function blocks_get_default_site_course_blocks() {
    global $CFG;

    if (isset($CFG->defaultblocks_site)) {
        return blocks_parse_default_blocks_list($CFG->defaultblocks_site);
    } else {
        return array(
            BLOCK_POS_LEFT => array('site_main_menu'),
            BLOCK_POS_RIGHT => array('course_summary', 'calendar_month')
        );
    }
}


function blocks_add_default_course_blocks($course) {
    global $CFG;

    if (isset($CFG->defaultblocks_override)) {
        $blocknames = blocks_parse_default_blocks_list($CFG->defaultblocks_override);

    } else if ($course->id == SITEID) {
        $blocknames = blocks_get_default_site_course_blocks();

    } else if (isset($CFG->{'defaultblocks_' . $course->format})) {
        $blocknames = blocks_parse_default_blocks_list($CFG->{'defaultblocks_' . $course->format});

    } else {
        require_once($CFG->dirroot. '/course/lib.php');
        $blocknames = course_get_format($course)->get_default_blocks();

    }

    if ($course->id == SITEID) {
        $pagetypepattern = 'site-index';
    } else {
        $pagetypepattern = 'course-view-*';
    }
    $page = new moodle_page();
    $page->set_course($course);
    $page->blocks->add_blocks($blocknames, $pagetypepattern);
}


function blocks_add_default_system_blocks() {
    global $DB;

    $page = new moodle_page();
    $page->set_context(context_system::instance());
    $page->blocks->add_blocks(array(BLOCK_POS_LEFT => array('navigation')), 'admin-*', null, true);
    $page->blocks->add_blocks(array(BLOCK_POS_LEFT => array('navigation')), 'my-index', null, true);
    $page->blocks->add_blocks(array(BLOCK_POS_LEFT => array('admin_bookmarks')), 'admin-*', null, null, 0);
    $page->blocks->add_blocks(array(BLOCK_POS_LEFT => array('settings')), '*', null, true);

    if ($defaultmypage = $DB->get_record('my_pages', array('userid' => null, 'name' => '__default', 'private' => 1))) {
        $subpagepattern = $defaultmypage->id;
    } else {
        $subpagepattern = null;
    }
    $newblocks = array('private_files', 'calendar_month', 'calendar_upcoming');
    $newcontent = array('course_overview');
    $page->blocks->add_blocks(array(BLOCK_POS_RIGHT => $newblocks, 'content' => $newcontent), 'my-index', $subpagepattern);
}
