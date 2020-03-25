<?php





 class block_rss_client extends block_base {
    
    const CLIENT_MAX_SKIPTIME = 43200; 
    function init() {
        $this->title = get_string('pluginname', 'block_rss_client');
    }

    function applicable_formats() {
        return array('all' => true, 'tag' => false);       }

    function specialization() {
                if (!empty($this->config) && !empty($this->config->title)) {
                        $this->title = $this->config->title;
        } else {
                        $this->title = get_string('remotenewsfeed', 'block_rss_client');
        }
    }

    
    protected function get_footer($feedrecords) {
        $footer = null;

        if ($this->config->block_rss_client_show_channel_link) {
            global $CFG;
            require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

            $feedrecord     = array_pop($feedrecords);
            $feed           = new moodle_simplepie($feedrecord->url);
            $channellink    = new moodle_url($feed->get_link());

            if (!empty($channellink)) {
                $footer = new block_rss_client\output\footer($channellink);
            }
        }

        return $footer;
    }

    function get_content() {
        global $CFG, $DB;

        if ($this->content !== NULL) {
            return $this->content;
        }

                $this->content = new stdClass;
        $this->content->text   = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        if (!isset($this->config)) {
                        
            if (has_capability('block/rss_client:manageanyfeeds', $this->context)) {
                $this->content->text = get_string('feedsconfigurenewinstance2', 'block_rss_client');
            }

            return $this->content;
        }

                $maxentries = 5;
        if ( !empty($this->config->shownumentries) ) {
            $maxentries = intval($this->config->shownumentries);
        }elseif( isset($CFG->block_rss_client_num_entries) ) {
            $maxentries = intval($CFG->block_rss_client_num_entries);
        }

        

        $renderer = $this->page->get_renderer('block_rss_client');
        $block = new \block_rss_client\output\block();

        if (!empty($this->config->rssid)) {
            list($rssidssql, $params) = $DB->get_in_or_equal($this->config->rssid);
            $rssfeeds = $DB->get_records_select('block_rss_client', "id $rssidssql", $params);

            if (!empty($rssfeeds)) {
                $showtitle = false;
                if (count($rssfeeds) > 1) {
                                        $showtitle = true;
                }

                foreach ($rssfeeds as $feed) {
                    if ($renderablefeed = $this->get_feed($feed, $maxentries, $showtitle)) {
                        $block->add_feed($renderablefeed);
                    }
                }

                $footer = $this->get_footer($rssfeeds);
            }
        }

        $this->content->text = $renderer->render_block($block);
        if (isset($footer)) {
            $this->content->footer = $renderer->render_footer($footer);
        }

        return $this->content;
    }


    function instance_allow_multiple() {
        return true;
    }

    function has_config() {
        return true;
    }

    function instance_allow_config() {
        return true;
    }

    
    public function get_feed($feedrecord, $maxentries, $showtitle) {
        global $CFG;
        require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

        $simplepiefeed = new moodle_simplepie($feedrecord->url);

        if(isset($CFG->block_rss_client_timeout)){
            $simplepiefeed->set_cache_duration($CFG->block_rss_client_timeout * 60);
        }

        if ($simplepiefeed->error()) {
            debugging($feedrecord->url .' Failed with code: '.$simplepiefeed->error());
            return null;
        }

        if(empty($feedrecord->preferredtitle)){
            $feedtitle = $this->format_title($simplepiefeed->get_title());
        }else{
            $feedtitle = $this->format_title($feedrecord->preferredtitle);
        }

        if (empty($this->config->title)){
                                    $this->title = strip_tags($feedtitle);
        }

        $feed = new \block_rss_client\output\feed($feedtitle, $showtitle, $this->config->block_rss_client_show_channel_image);

        if ($simplepieitems = $simplepiefeed->get_items(0, $maxentries)) {
            foreach ($simplepieitems as $simplepieitem) {
                try {
                    $item = new \block_rss_client\output\item(
                        $simplepieitem->get_id(),
                        new moodle_url($simplepieitem->get_link()),
                        $simplepieitem->get_title(),
                        $simplepieitem->get_description(),
                        new moodle_url($simplepieitem->get_permalink()),
                        $simplepieitem->get_date('U'),
                        $this->config->display_description
                    );

                    $feed->add_item($item);
                } catch (moodle_exception $e) {
                                                                                                    debugging($e->getMessage());
                }
            }
        }

                if ($imageurl = $simplepiefeed->get_image_url()) {
            try {
                $image = new \block_rss_client\output\channel_image(
                    new moodle_url($imageurl),
                    $simplepiefeed->get_image_title(),
                    new moodle_url($simplepiefeed->get_image_link())
                );

                $feed->set_image($image);
            } catch (moodle_exception $e) {
                                                                debugging($e->getMessage());
            }
        }

        return $feed;
    }

    
    function format_title($title,$max=64) {

        if (core_text::strlen($title) <= $max) {
            return s($title);
        } else {
            return s(core_text::substr($title,0,$max-3).'...');
        }
    }

    
    function cron() {
        global $CFG, $DB;
        require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

                        $this->cron = $DB->get_field('block', 'cron', array('name' => 'rss_client'));

                $starttime =  microtime();
        $starttimesec = time();

                $rs = $DB->get_recordset('block_rss_client');
        $counter = 0;
        mtrace('');
        foreach ($rs as $rec) {
            mtrace('    ' . $rec->url . ' ', '');

                        if ($starttimesec < $rec->skipuntil) {
                mtrace('skipping until ' . userdate($rec->skipuntil));
                continue;
            }

                                    core_php_time_limit::raise(60);

            $feed =  new moodle_simplepie();
                                    $feed->set_timeout(40);
            $feed->set_cache_duration(0);
            $feed->set_feed_url($rec->url);
            $feed->init();

            if ($feed->error()) {
                                $rec->skiptime = $this->calculate_skiptime($rec->skiptime);
                $rec->skipuntil = time() + $rec->skiptime;
                $DB->update_record('block_rss_client', $rec);
                mtrace("Error: could not load/find the RSS feed - skipping for {$rec->skiptime} seconds.");
            } else {
                mtrace ('ok');
                                if ($rec->skiptime > 0) {
                    $rec->skiptime = 0;
                    $rec->skipuntil = 0;
                    $DB->update_record('block_rss_client', $rec);
                }
                                $counter ++;
            }
        }
        $rs->close();

                mtrace($counter . ' feeds refreshed (took ' . microtime_diff($starttime, microtime()) . ' seconds)');

        return true;
    }

    
    protected function calculate_skiptime($currentskip) {
                $newskiptime = $this->cron * 1.1;
        if ($currentskip > 0) {
                        $newskiptime = $currentskip * 2;
        }
        if ($newskiptime > self::CLIENT_MAX_SKIPTIME) {
                        $newskiptime = self::CLIENT_MAX_SKIPTIME;
        }
        return $newskiptime;
    }
}
