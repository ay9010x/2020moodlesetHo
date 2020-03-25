<?php



class block_tags extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_tags');
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function applicable_formats() {
        return array('all' => true);
    }

    public function instance_allow_config() {
        return true;
    }

    public function specialization() {

                if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_tags');
        } else {
            $this->title = $this->config->title;
        }
    }

    public function get_content() {

        global $CFG, $COURSE, $USER, $SCRIPT, $OUTPUT;

        if (empty($CFG->usetags)) {
            $this->content = new stdClass();
            $this->content->text = '';
            if ($this->page->user_is_editing()) {
                $this->content->text = get_string('disabledtags', 'block_tags');
            }
            return $this->content;
        }

        if (!isset($this->config)) {
            $this->config = new stdClass();
        }

        if (empty($this->config->numberoftags)) {
            $this->config->numberoftags = 80;
        }

        if (empty($this->config->showstandard)) {
            $this->config->showstandard = core_tag_tag::BOTH_STANDARD_AND_NOT;
        }

        if (empty($this->config->ctx)) {
            $this->config->ctx = 0;
        }

        if (empty($this->config->rec)) {
            $this->config->rec = 1;
        }

        if (empty($this->config->tagcoll)) {
            $this->config->tagcoll = 0;
        }

        if ($this->content !== NULL) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        
        $tagcloud = core_tag_collection::get_tag_cloud($this->config->tagcoll,
                $this->config->showstandard == core_tag_tag::STANDARD_ONLY,
                $this->config->numberoftags,
                'name', '', $this->page->context->id, $this->config->ctx, $this->config->rec);
        $this->content->text = $OUTPUT->render_from_template('core_tag/tagcloud', $tagcloud->export_for_template($OUTPUT));

        return $this->content;
    }
}
