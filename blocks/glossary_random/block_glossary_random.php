<?php



define('BGR_RANDOMLY',     '0');
define('BGR_LASTMODIFIED', '1');
define('BGR_NEXTONE',      '2');
define('BGR_NEXTALPHA',    '3');

class block_glossary_random extends block_base {

    
    protected $glossarycm = null;

    function init() {
        $this->title = get_string('pluginname','block_glossary_random');
    }

    function specialization() {
        global $CFG, $DB;

        require_once($CFG->libdir . '/filelib.php');

        $this->course = $this->page->course;

                if (empty($this->config->title)) {
            $this->title = get_string('pluginname','block_glossary_random');
        } else {
            $this->title = $this->config->title;
        }

        if (empty($this->config->glossary)) {
            return false;
        }

        if (!isset($this->config->nexttime)) {
            $this->config->nexttime = 0;
        }

                if (time() > $this->config->nexttime) {

            if (!($cm = $this->get_glossary_cm()) || !$cm->uservisible) {
                                return false;
            }

                        if (!$numberofentries = $DB->count_records('glossary_entries',
                                                       array('glossaryid'=>$this->config->glossary, 'approved'=>1))) {
                $this->config->cache = get_string('noentriesyet','block_glossary_random');
                $this->instance_config_commit();
            }

            $glossaryctx = context_module::instance($cm->id);

            $limitfrom = 0;
            $limitnum = 1;

            $orderby = 'timemodified ASC';

            switch ($this->config->type) {

                case BGR_RANDOMLY:
                    $i = ($numberofentries > 1) ? rand(1, $numberofentries) : 1;
                    $limitfrom = $i-1;
                    break;

                case BGR_NEXTONE:
                    if (isset($this->config->previous)) {
                        $i = $this->config->previous + 1;
                    } else {
                        $i = 1;
                    }
                    if ($i > $numberofentries) {                          $i = 1;
                    }
                    $limitfrom = $i-1;
                    break;

                case BGR_NEXTALPHA:
                    $orderby = 'concept ASC';
                    if (isset($this->config->previous)) {
                        $i = $this->config->previous + 1;
                    } else {
                        $i = 1;
                    }
                    if ($i > $numberofentries) {                          $i = 1;
                    }
                    $limitfrom = $i-1;
                    break;

                default:                      $i = $numberofentries;
                    $limitfrom = 0;
                    $orderby = 'timemodified DESC, id DESC';
                    break;
            }

            if ($entry = $DB->get_records_sql("SELECT id, concept, definition, definitionformat, definitiontrust
                                                 FROM {glossary_entries}
                                                WHERE glossaryid = ? AND approved = 1
                                             ORDER BY $orderby", array($this->config->glossary), $limitfrom, $limitnum)) {

                $entry = reset($entry);

                if (empty($this->config->showconcept)) {
                    $text = '';
                } else {
                    $text = "<h3>".format_string($entry->concept,true)."</h3>";
                }

                $options = new stdClass();
                $options->trusted = $entry->definitiontrust;
                $options->overflowdiv = true;
                $entry->definition = file_rewrite_pluginfile_urls($entry->definition, 'pluginfile.php', $glossaryctx->id, 'mod_glossary', 'entry', $entry->id);
                $text .= format_text($entry->definition, $entry->definitionformat, $options);

                $this->config->nexttime = usergetmidnight(time()) + DAYSECS * $this->config->refresh;
                $this->config->previous = $i;

            } else {
                $text = get_string('noentriesyet','block_glossary_random');
            }
                        $this->config->cache = $text;
            $this->instance_config_commit();
        }
    }

    
    function instance_config_commit($nolongerused = false) {
                unset($this->config->globalglossary);
        unset($this->config->courseid);
        parent::instance_config_commit($nolongerused);
    }

    
    protected function get_glossary_cm() {
        global $DB;
        if (empty($this->config->glossary)) {
                        return null;
        }

        if (!empty($this->glossarycm)) {
            return $this->glossarycm;
        }

        if (!empty($this->page->course->id)) {
                        $modinfo = get_fast_modinfo($this->page->course);
            if (isset($modinfo->instances['glossary'][$this->config->glossary])) {
                $this->glossarycm = $modinfo->instances['glossary'][$this->config->glossary];
                if ($this->glossarycm->uservisible) {
                                                            return $this->glossarycm;
                }
            }
        }

                $cm = $DB->get_record_sql("SELECT cm.id, cm.visible AS uservisible
              FROM {course_modules} cm
                   JOIN {modules} md ON md.id = cm.module
                   JOIN {glossary} g ON g.id = cm.instance
             WHERE g.id = :instance AND md.name = :modulename AND g.globalglossary = 1",
            ['instance' => $this->config->glossary, 'modulename' => 'glossary']);

        if ($cm) {
                                                $this->glossarycm = $cm;
        } else if (empty($this->glossarycm)) {
                        $this->config->glossary = 0;
            $this->instance_config_commit();
        }

        return $this->glossarycm;
    }

    function instance_allow_multiple() {
                return true;
    }

    function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = (object)['text' => '', 'footer' => ''];

        if (!$cm = $this->get_glossary_cm()) {
            if ($this->user_can_edit()) {
                $this->content->text = get_string('notyetconfigured', 'block_glossary_random');
            }
            return $this->content;
        }

        if (empty($this->config->cache)) {
            $this->config->cache = '';
        }

        if ($cm->uservisible) {
                        $this->content->text = $this->config->cache;
            if (has_capability('mod/glossary:write', context_module::instance($cm->id))) {
                $this->content->footer = html_writer::link(new moodle_url('/mod/glossary/edit.php', ['cmid' => $cm->id]),
                    format_string($this->config->addentry)) . '<br/>';
            }

            $this->content->footer .= html_writer::link(new moodle_url('/mod/glossary/view.php', ['id' => $cm->id]),
                format_string($this->config->viewglossary));
        } else {
                        $this->content->footer = format_string($this->config->invisible);
        }

        return $this->content;
    }
}

