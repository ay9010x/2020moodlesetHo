<?php






class backup_rss_client_block_structure_step extends backup_block_structure_step {

    protected function define_structure() {
        global $DB;

                $block = $DB->get_record('block_instances', array('id' => $this->task->get_blockid()));
                $config = unserialize(base64_decode($block->configdata));
                if (!empty($config->rssid)) {
            $feedids = $config->rssid;
                        list($in_sql, $in_params) = $DB->get_in_or_equal($feedids);
                        foreach ($in_params as $key => $value) {
                $in_params[$key] = backup_helper::is_sqlparam($value);
            }
        }

        
        $rss_client = new backup_nested_element('rss_client', array('id'), null);

        $feeds = new backup_nested_element('feeds');

        $feed = new backup_nested_element('feed', array('id'), array(
            'title', 'preferredtitle', 'description', 'shared',
            'url'));

        
        $rss_client->add_child($feeds);
        $feeds->add_child($feed);

        
        $rss_client->set_source_array(array((object)array('id' => $this->task->get_blockid())));

                if (!empty($config->rssid)) {
            $feed->set_source_sql("
                SELECT *
                  FROM {block_rss_client}
                 WHERE id $in_sql", $in_params);
        }

        
                return $this->prepare_block_structure($rss_client);
    }
}
