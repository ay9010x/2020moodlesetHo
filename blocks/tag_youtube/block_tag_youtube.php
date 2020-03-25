<?php



define('DEFAULT_NUMBER_OF_VIDEOS', 5);

class block_tag_youtube extends block_base {

    
    protected $service = null;

    function init() {
        $this->title = get_string('pluginname','block_tag_youtube');
        $this->config = new stdClass();
    }

    function applicable_formats() {
        return array('tag' => true);
    }

    
    public function has_config() {
        return true;
    }

    function specialization() {
        $this->title = !empty($this->config->title) ? $this->config->title : get_string('pluginname', 'block_tag_youtube');
                        $this->config->category = !empty($this->config->category) ? $this->category_map_old2new($this->config->category) : '0';
    }

    function instance_allow_multiple() {
        return true;
    }

    function get_content() {
        global $CFG;

                require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        if (!$this->get_service()) {
            $this->content->text = $this->get_error_message();
            return $this->content;
        }

        $text = '';
        if(!empty($this->config->playlist)){
                        $text = $this->get_videos_by_playlist();
        }
        else{
            if(!empty($this->config->category)){
                                $text = $this->get_videos_by_tag_and_category();
            }
            else {
                                $text = $this->get_videos_by_tag();
            }
        }

        $this->content->text = $text;

        return $this->content;
    }

    function get_videos_by_playlist(){

        if (!$service = $this->get_service()) {
            return $this->get_error_message();
        }

        $numberofvideos = DEFAULT_NUMBER_OF_VIDEOS;
        if( !empty($this->config->numberofvideos)) {
            $numberofvideos = $this->config->numberofvideos;
        }

        try {
            $response = $service->playlistItems->listPlaylistItems('id,snippet', array(
                'playlistId' => $this->config->playlist,
                'maxResults' => $numberofvideos
            ));
        } catch (Google_Service_Exception $e) {
            debugging('Google service exception: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return $this->get_error_message(get_string('requesterror', 'block_tag_youtube'));
        }

        return $this->render_items($response);
    }

    function get_videos_by_tag(){

        if (!$service = $this->get_service()) {
            return $this->get_error_message();
        }

        $tagid = optional_param('id', 0, PARAM_INT);           $tag = optional_param('tag', '', PARAM_TAG);         $tc = optional_param('tc', 0, PARAM_INT); 
        if ($tagid) {
            $tagobject = core_tag_tag::get($tagid);
        } else if ($tag) {
            $tagobject = core_tag_tag::get_by_name($tc, $tag);
        }

        if (empty($tagobject)) {
            return '';
        }

        $querytag = urlencode($tagobject->name);

        $numberofvideos = DEFAULT_NUMBER_OF_VIDEOS;
        if ( !empty($this->config->numberofvideos) ) {
            $numberofvideos = $this->config->numberofvideos;
        }

        try {
            $response = $service->search->listSearch('id,snippet', array(
                'q' => $querytag,
                'type' => 'video',
                'maxResults' => $numberofvideos
            ));
        } catch (Google_Service_Exception $e) {
            debugging('Google service exception: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return $this->get_error_message(get_string('requesterror', 'block_tag_youtube'));
        }

        return $this->render_items($response);
    }

    function get_videos_by_tag_and_category(){

        if (!$service = $this->get_service()) {
            return $this->get_error_message();
        }

        $tagid = optional_param('id', 0, PARAM_INT);           $tag = optional_param('tag', '', PARAM_TAG);         $tc = optional_param('tc', 0, PARAM_INT); 
        if ($tagid) {
            $tagobject = core_tag_tag::get($tagid);
        } else if ($tag) {
            $tagobject = core_tag_tag::get_by_name($tc, $tag);
        }

        if (empty($tagobject)) {
            return '';
        }

        $querytag = urlencode($tagobject->name);

        $numberofvideos = DEFAULT_NUMBER_OF_VIDEOS;
        if( !empty($this->config->numberofvideos)) {
            $numberofvideos = $this->config->numberofvideos;
        }

        try {
            $response = $service->search->listSearch('id,snippet', array(
                'q' => $querytag,
                'type' => 'video',
                'maxResults' => $numberofvideos,
                'videoCategoryId' => $this->config->category
            ));
        } catch (Google_Service_Exception $e) {
            debugging('Google service exception: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return $this->get_error_message(get_string('requesterror', 'block_tag_youtube'));
        }

        return $this->render_items($response);
    }

    
    public function fetch_request($request) {
        throw new coding_exception('Sorry, this function has been deprecated in Moodle 2.8.8, 2.9.2 and 3.0. Use block_tag_youtube::get_service instead.');

        $c = new curl(array('cache' => true, 'module_cache'=>'tag_youtube'));
        $c->setopt(array('CURLOPT_TIMEOUT' => 3, 'CURLOPT_CONNECTTIMEOUT' => 3));

        $response = $c->get($request);

        $xml = new SimpleXMLElement($response);
        return $this->render_video_list($xml);
    }

    
    function render_video_list(SimpleXMLElement $xml){
        throw new coding_exception('Sorry, this function has been deprecated in Moodle 2.8.8, 2.9.2 and 3.0. Use block_tag_youtube::render_items instead.');
    }

    
    protected function get_error_message($message = null) {
        global $OUTPUT;

        if (empty($message)) {
            $message = get_string('apierror', 'block_tag_youtube');
        }
        return $OUTPUT->notification($message);
    }

    
    protected function get_service() {
        global $CFG;

        if (!$apikey = get_config('block_tag_youtube', 'apikey')) {
            return false;
        }

                if (!isset($this->service)) {
            require_once($CFG->libdir . '/google/lib.php');
            $client = get_google_client();
            $client->setDeveloperKey($apikey);
            $client->setScopes(array(Google_Service_YouTube::YOUTUBE_READONLY));
            $this->service = new Google_Service_YouTube($client);
        }

        return $this->service;
    }

    
    protected function render_items($videosdata) {

        if (!$videosdata || empty($videosdata->items)) {
            if (!empty($videosdata->error)) {
                debugging('Error fetching data from youtube: ' . $videosdata->error->message, DEBUG_DEVELOPER);
            }
            return '';
        }

                $service = $this->get_service();

        $text = html_writer::start_tag('ul', array('class' => 'yt-video-entry unlist img-text'));
        foreach ($videosdata->items as $video) {

                        if (!empty($video->snippet->resourceId)) {
                $id = $video->snippet->resourceId->videoId;
                $playlist = '&list=' . $video->snippet->playlistId;
            } else {
                $id = $video->id->videoId;
                $playlist = '';
            }

            $thumbnail = $video->snippet->getThumbnails()->getDefault();
            $url = 'http://www.youtube.com/watch?v=' . $id . $playlist;

            $videodetails = $service->videos->listVideos('id,contentDetails', array('id' => $id));
            if ($videodetails && !empty($videodetails->items)) {

                                $details = $videodetails->items[0];
                $start = new DateTime('@0');
                $start->add(new DateInterval($details->contentDetails->duration));
                $seconds = $start->format('U');
            }

            $text .= html_writer::start_tag('li');

            $imgattrs = array('class' => 'youtube-thumb', 'src' => $thumbnail->url, 'alt' => $video->snippet->title);
            $thumbhtml = html_writer::empty_tag('img', $imgattrs);
            $link = html_writer::tag('a', $thumbhtml, array('href' => $url));
            $text .= html_writer::tag('div', $link, array('class' => 'clearfix'));

            $text .= html_writer::tag('span', html_writer::tag('a', $video->snippet->title, array('href' => $url)));

            if (!empty($seconds)) {
                $text .= html_writer::tag('div', format_time($seconds));
            }
            $text .= html_writer::end_tag('li');
        }
        $text .= html_writer::end_tag('ul');

        return $text;
    }

    function get_categories() {
                                        return array (
            '0' => get_string('anycategory', 'block_tag_youtube'),
            'Film'  => get_string('filmsanimation', 'block_tag_youtube'),
            'Autos' => get_string('autosvehicles', 'block_tag_youtube'),
            'Music' => get_string('music', 'block_tag_youtube'),
            'Animals'=> get_string('petsanimals', 'block_tag_youtube'),
            'Sports' => get_string('sports', 'block_tag_youtube'),
            'Travel' => get_string('travel', 'block_tag_youtube'),
            'Games'  => get_string('gadgetsgames', 'block_tag_youtube'),
            'Comedy' => get_string('comedy', 'block_tag_youtube'),
            'People' => get_string('peopleblogs', 'block_tag_youtube'),
            'News'   => get_string('newspolitics', 'block_tag_youtube'),
            'Entertainment' => get_string('entertainment', 'block_tag_youtube'),
            'Education' => get_string('education', 'block_tag_youtube'),
            'Howto'  => get_string('howtodiy', 'block_tag_youtube'),
            'Tech'   => get_string('scienceandtech', 'block_tag_youtube')
        );
    }

    
    function category_map_old2new($oldcat) {
        $oldoptions = array (
            0  => '0',
            1  => 'Film',
            2  => 'Autos',
            23 => 'Comedy',
            24 => 'Entertainment',
            10 => 'Music',
            25 => 'News',
            22 => 'People',
            15 => 'Animals',
            26 => 'Howto',
            17 => 'Sports',
            19 => 'Travel',
            20 => 'Games'
        );
        if (array_key_exists($oldcat, $oldoptions)) {
            return $oldoptions[$oldcat];
        } else {
            return $oldcat;
        }
    }
}

