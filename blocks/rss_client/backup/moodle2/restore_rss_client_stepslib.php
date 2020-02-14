<?php






class restore_rss_client_block_structure_step extends restore_structure_step {

    protected function define_structure() {

        $paths = array();

        $paths[] = new restore_path_element('block', '/block', true);
        $paths[] = new restore_path_element('rss_client', '/block/rss_client');
        $paths[] = new restore_path_element('feed', '/block/rss_client/feeds/feed');

        return $paths;
    }

    public function process_block($data) {
        global $DB;

        $data = (object)$data;
        $feedsarr = array(); 
                if (!$this->task->get_blockid()) {
            return;
        }

                if (isset($data->rss_client['feeds']['feed'])) {
            foreach ($data->rss_client['feeds']['feed'] as $feed) {
                $feed = (object)$feed;
                                $select = 'url = :url AND (shared = 1 OR userid = :userid)';
                $params = array('url' => $feed->url, 'userid' => $this->task->get_userid());
                                if ($feedid = $DB->get_field_select('block_rss_client', 'id', $select, $params, IGNORE_MULTIPLE)) {
                    $feedsarr[] = $feedid;

                                } else {
                    $feed->userid = $this->task->get_userid();
                    $feedid = $DB->insert_record('block_rss_client', $feed);
                    $feedsarr[] = $feedid;
                }
            }
        }

                        $configdata = $DB->get_field('block_instances', 'configdata', array('id' => $this->task->get_blockid()));
                $config = unserialize(base64_decode($configdata));
        if (empty($config)) {
            $config = new stdClass();
        }
                $config->rssid = $feedsarr;
                $configdata = base64_encode(serialize($config));
                $DB->set_field('block_instances', 'configdata', $configdata, array('id' => $this->task->get_blockid()));
    }
}
