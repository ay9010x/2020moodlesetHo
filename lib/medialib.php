<?php



defined('MOODLE_INTERNAL') || die();

if (!defined('CORE_MEDIA_VIDEO_WIDTH')) {
                define('CORE_MEDIA_VIDEO_WIDTH', 400);
}
if (!defined('CORE_MEDIA_VIDEO_HEIGHT')) {
        define('CORE_MEDIA_VIDEO_HEIGHT', 300);
}
if (!defined('CORE_MEDIA_AUDIO_WIDTH')) {
            define('CORE_MEDIA_AUDIO_WIDTH', 300);
}



abstract class core_media {
    
    const OPTION_NO_LINK = 'nolink';

    
    const OPTION_FALLBACK_TO_BLANK = 'embedorblank';

    
    const OPTION_TRUSTED = 'trusted';

    
    const OPTION_BLOCK = 'block';

    
    public static function split_alternatives($combinedurl, &$width, &$height) {
        $urls = explode('#', $combinedurl);
        $width = 0;
        $height = 0;
        $returnurls = array();

        foreach ($urls as $url) {
            $matches = null;

                                    if (preg_match('/^d=([\d]{1,4})x([\d]{1,4})$/i', $url, $matches)) {
                $width  = $matches[1];
                $height = $matches[2];
                continue;
            }

                                    if (preg_match('/\?d=([\d]{1,4})x([\d]{1,4})$/i', $url, $matches)) {
                $width  = $matches[1];
                $height = $matches[2];

                                $url = str_replace($matches[0], '', $url);
            }

                        $url = clean_param($url, PARAM_URL);
            if (empty($url)) {
                continue;
            }

                        $returnurls[] = new moodle_url($url);
        }

        return $returnurls;
    }

    
    public static function get_extension(moodle_url $url) {
                $filename = self::get_filename($url);
        $dot = strrpos($filename, '.');
        if ($dot === false) {
            return '';
        } else {
            return strtolower(substr($filename, $dot + 1));
        }
    }

    
    public static function get_filename(moodle_url $url) {
        global $CFG;

                        $path = $url->get_param('file');
        if (!$path) {
            $path = $url->get_path();
        }

                $slash = strrpos($path, '/');
        if ($slash !== false) {
            $path = substr($path, $slash + 1);
        }
        return $path;
    }

    
    public static function get_mimetype(moodle_url $url) {
        return mimeinfo('type', self::get_filename($url));
    }
}



abstract class core_media_player {
    
    const PLACEHOLDER = '<!--FALLBACK-->';

    
    public abstract function embed($urls, $name, $width, $height, $options);

    
    public function get_supported_extensions() {
        return array();
    }

    
    public function get_embeddable_markers() {
        $markers = array();
        foreach ($this->get_supported_extensions() as $extension) {
            $markers[] = '.' . $extension;
        }
        return $markers;
    }

    
    public abstract function get_rank();

    
    public function is_enabled() {
        global $CFG;

                        $setting = str_replace('_player_', '_enable_', get_class($this));
        return !empty($CFG->{$setting});
    }

    
    public function list_supported_urls(array $urls, array $options = array()) {
        $extensions = $this->get_supported_extensions();
        $result = array();
        foreach ($urls as $url) {
            if (in_array(core_media::get_extension($url), $extensions)) {
                $result[] = $url;
            }
        }
        return $result;
    }

    
    protected function get_name($name, $urls) {
                if ($name) {
            return $name;
        }

                $url = reset($urls);
        $name = core_media::get_filename($url);

                        if (count($urls) > 1) {
            $name = preg_replace('~\.[^.]*$~', '', $name);
        }

        return $name;
    }

    
    public static function compare_by_rank(core_media_player $a, core_media_player $b) {
        return $b->get_rank() - $a->get_rank();
    }

    
    protected static function pick_video_size(&$width, &$height) {
        if (!$width) {
            $width = CORE_MEDIA_VIDEO_WIDTH;
            $height = CORE_MEDIA_VIDEO_HEIGHT;
        }
    }
}



abstract class core_media_player_external extends core_media_player {
    
    protected $matches;

    
    const END_LINK_REGEX_PART = '[^#]*(#d=([\d]{1,4})x([\d]{1,4}))?~si';

    public function embed($urls, $name, $width, $height, $options) {
        return $this->embed_external(reset($urls), $name, $width, $height, $options);
    }

    
    protected abstract function embed_external(moodle_url $url, $name, $width, $height, $options);

    public function list_supported_urls(array $urls, array $options = array()) {
                if (count($urls) != 1) {
            return array();
        }
        $url = reset($urls);

                if (preg_match($this->get_regex(), $url->out(false), $this->matches)) {
            return array($url);
        }

        return array();
    }

    
    protected function get_regex() {
        return '~^unsupported~';
    }

    
    protected static function fix_match_count(&$matches, $count) {
        for ($i = count($matches); $i <= $count; $i++) {
            $matches[$i] = false;
        }
    }
}



class core_media_player_vimeo extends core_media_player_external {
    protected function embed_external(moodle_url $url, $name, $width, $height, $options) {
        $videoid = $this->matches[1];
        $info = s($name);

                                self::pick_video_size($width, $height);

        $output = <<<OET
<span class="mediaplugin mediaplugin_vimeo">
<iframe title="$info" src="https://player.vimeo.com/video/$videoid"
  width="$width" height="$height" frameborder="0"
  webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
</span>
OET;

        return $output;
    }

    protected function get_regex() {
                $start = '~^https?://vimeo\.com/';
                $middle = '([0-9]+)';
        return $start . $middle . core_media_player_external::END_LINK_REGEX_PART;
    }

    public function get_rank() {
        return 1010;
    }

    public function get_embeddable_markers() {
        return array('vimeo.com/');
    }
}


class core_media_player_youtube extends core_media_player_external {
    protected function embed_external(moodle_url $url, $name, $width, $height, $options) {
        $videoid = end($this->matches);

        $info = trim($name);
        if (empty($info) or strpos($info, 'http') === 0) {
            $info = get_string('siteyoutube', 'core_media');
        }
        $info = s($info);

        self::pick_video_size($width, $height);

        $params = '';
        $start = self::get_start_time($url);
        if ($start > 0) {
            $params .= "start=$start&amp;";
        }

        $listid = $url->param('list');
                if (!empty($listid) && !preg_match('/[^a-zA-Z0-9\-_]/', $listid)) {
                        $params .= "list=$listid&amp;";
        }

        return <<<OET
<span class="mediaplugin mediaplugin_youtube">
<iframe title="$info" width="$width" height="$height"
  src="https://www.youtube.com/embed/$videoid?{$params}rel=0&amp;wmode=transparent" frameborder="0" allowfullscreen="1"></iframe>
</span>
OET;

    }

    
    protected static function get_start_time($url) {
        $matches = array();
        $seconds = 0;

        $rawtime = $url->param('t');
        if (empty($rawtime)) {
            $rawtime = $url->param('start');
        }

        if (is_numeric($rawtime)) {
                        $seconds = $rawtime;
        } else if (preg_match('/(\d+?h)?(\d+?m)?(\d+?s)?/i', $rawtime, $matches)) {
                        for ($i = 1; $i < count($matches); $i++) {
                if (empty($matches[$i])) {
                    continue;
                }
                $part = str_split($matches[$i], strlen($matches[$i]) - 1);
                switch ($part[1]) {
                    case 'h':
                        $seconds += 3600 * $part[0];
                        break;
                    case 'm':
                        $seconds += 60 * $part[0];
                        break;
                    default:
                        $seconds += $part[0];
                }
            }
        }

        return intval($seconds);
    }

    protected function get_regex() {
                 $link = '(youtube(-nocookie)?\.com/(?:watch\?v=|v/))';
                $shortlink = '((youtu|y2u)\.be/)';

                 $start = '~^https?://(www\.)?(' . $link . '|' . $shortlink . ')';
                $middle = '([a-z0-9\-_]+)';
        return $start . $middle . core_media_player_external::END_LINK_REGEX_PART;
    }

    public function get_rank() {
                        return 1001;
    }

    public function get_embeddable_markers() {
        return array('youtube.com', 'youtube-nocookie.com', 'youtu.be', 'y2u.be');
    }
}



class core_media_player_youtube_playlist extends core_media_player_external {
    public function is_enabled() {
        global $CFG;
                return $CFG->core_media_enable_youtube;
    }

    protected function embed_external(moodle_url $url, $name, $width, $height, $options) {
        $site = $this->matches[1];
        $playlist = $this->matches[3];

        $info = trim($name);
        if (empty($info) or strpos($info, 'http') === 0) {
            $info = get_string('siteyoutube', 'core_media');
        }
        $info = s($info);

        self::pick_video_size($width, $height);

        return <<<OET
<span class="mediaplugin mediaplugin_youtube">
<iframe width="$width" height="$height" src="https://$site/embed/videoseries?list=$playlist" frameborder="0" allowfullscreen="1"></iframe>
</span>
OET;
    }

    protected function get_regex() {
                $start = '~^https?://(www\.youtube(-nocookie)?\.com)/';
                $middle = '(?:view_play_list\?p=|p/|playlist\?list=)([a-z0-9\-_]+)';
        return $start . $middle . core_media_player_external::END_LINK_REGEX_PART;
    }

    public function get_rank() {
                        return 1000;
    }

    public function get_embeddable_markers() {
        return array('youtube');
    }
}



class core_media_player_mp3 extends core_media_player {
    public function embed($urls, $name, $width, $height, $options) {
                        $url = reset($urls);

                        $id = 'core_media_mp3_' . md5(time() . '_' . rand());

                        $spanparams = array('id' => $id, 'class' => 'mediaplugin mediaplugin_mp3');
        if ($width) {
            $spanparams['style'] = 'width: ' . $width . 'px';
        }
        $output = html_writer::tag('span', core_media_player::PLACEHOLDER, $spanparams);
                        $output .= html_writer::script(js_writer::function_call(
                'M.util.add_audio_player', array($id, $url->out(false),
                empty($options[core_media::OPTION_BLOCK]))));

        return $output;
    }

    public function get_supported_extensions() {
        return array('mp3');
    }

    public function get_rank() {
        return 80;
    }
}



class core_media_player_flv extends core_media_player {
    public function embed($urls, $name, $width, $height, $options) {
                        $url = reset($urls);

                        $id = 'core_media_flv_' . md5(time() . '_' . rand());

                $autosize = false;
        if (!$width && !$height) {
            $width = CORE_MEDIA_VIDEO_WIDTH;
            $height = CORE_MEDIA_VIDEO_HEIGHT;
            $autosize = true;
        }

                $output = html_writer::tag('span', core_media_player::PLACEHOLDER,
                array('id'=>$id, 'class'=>'mediaplugin mediaplugin_flv'));
                $output .= html_writer::script(js_writer::function_call(
                'M.util.add_video_player', array($id, addslashes_js($url->out(false)),
                $width, $height, $autosize)));
        return $output;
    }

    public function get_supported_extensions() {
        return array('flv', 'f4v');
    }

    public function get_rank() {
        return 70;
    }
}



class core_media_player_wmp extends core_media_player {
    public function embed($urls, $name, $width, $height, $options) {
                $firsturl = reset($urls);
        $url = $firsturl->out(false);

                if (!$width || !$height) {
                        $mpsize = '';
            $size = 'width="' . CORE_MEDIA_VIDEO_WIDTH .
                    '" height="' . (CORE_MEDIA_VIDEO_HEIGHT+64) . '"';
            $autosize = 'true';
        } else {
            $size = 'width="' . $width . '" height="' . ($height + 15) . '"';
            $mpsize = 'width="' . $width . '" height="' . ($height + 64) . '"';
            $autosize = 'false';
        }

                $mimetype = core_media::get_mimetype($firsturl);

        $fallback = core_media_player::PLACEHOLDER;

                return <<<OET
<span class="mediaplugin mediaplugin_wmp">
    <object classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" $mpsize
            standby="Loading Microsoft(R) Windows(R) Media Player components..."
            type="application/x-oleobject">
        <param name="Filename" value="$url" />
        <param name="src" value="$url" />
        <param name="url" value="$url" />
        <param name="ShowControls" value="true" />
        <param name="AutoRewind" value="true" />
        <param name="AutoStart" value="false" />
        <param name="Autosize" value="$autosize" />
        <param name="EnableContextMenu" value="true" />
        <param name="TransparentAtStart" value="false" />
        <param name="AnimationAtStart" value="false" />
        <param name="ShowGotoBar" value="false" />
        <param name="EnableFullScreenControls" value="true" />
        <param name="uimode" value="full" />
        <!--[if !IE]><!-->
        <object data="$url" type="$mimetype" $size>
            <param name="src" value="$url" />
            <param name="controller" value="true" />
            <param name="autoplay" value="false" />
            <param name="autostart" value="false" />
            <param name="resize" value="scale" />
        <!--<![endif]-->
            $fallback
        <!--[if !IE]><!-->
        </object>
        <!--<![endif]-->
    </object>
</span>
OET;
    }

    public function get_supported_extensions() {
        return array('wmv', 'avi');
    }

    public function get_rank() {
        return 60;
    }
}



class core_media_player_qt extends core_media_player {
    public function embed($urls, $name, $width, $height, $options) {
                $firsturl = reset($urls);
        $url = $firsturl->out(true);

                if (!$width || !$height) {
            $size = 'width="' . CORE_MEDIA_VIDEO_WIDTH .
                    '" height="' . (CORE_MEDIA_VIDEO_HEIGHT + 15) . '"';
        } else {
            $size = 'width="' . $width . '" height="' . ($height + 15) . '"';
        }

                $mimetype = core_media::get_mimetype($firsturl);

        $fallback = core_media_player::PLACEHOLDER;

                return <<<OET
<span class="mediaplugin mediaplugin_qt">
    <object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"
            codebase="http://www.apple.com/qtactivex/qtplugin.cab" $size>
        <param name="pluginspage" value="http://www.apple.com/quicktime/download/" />
        <param name="src" value="$url" />
        <param name="controller" value="true" />
        <param name="loop" value="false" />
        <param name="autoplay" value="false" />
        <param name="autostart" value="false" />
        <param name="scale" value="aspect" />
        <!--[if !IE]><!-->
        <object data="$url" type="$mimetype" $size>
            <param name="src" value="$url" />
            <param name="pluginurl" value="http://www.apple.com/quicktime/download/" />
            <param name="controller" value="true" />
            <param name="loop" value="false" />
            <param name="autoplay" value="false" />
            <param name="autostart" value="false" />
            <param name="scale" value="aspect" />
        <!--<![endif]-->
            $fallback
        <!--[if !IE]><!-->
        </object>
        <!--<![endif]-->
    </object>
</span>
OET;
    }

    public function get_supported_extensions() {
        return array('mpg', 'mpeg', 'mov', 'mp4', 'm4v', 'm4a');
    }

    public function get_rank() {
        return 10;
    }
}



class core_media_player_rm extends core_media_player {
    public function embed($urls, $name, $width, $height, $options) {
                $firsturl = reset($urls);
        $url = $firsturl->out(true);

                $info = s($this->get_name($name, $urls));

                                $width = CORE_MEDIA_VIDEO_WIDTH;
        $height = CORE_MEDIA_VIDEO_HEIGHT;

        $fallback = core_media_player::PLACEHOLDER;
        return <<<OET
<span class="mediaplugin mediaplugin_real">
    <object title="$info" classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA"
            data="$url" width="$width" height="$height"">
        <param name="src" value="$url" />
        <param name="controls" value="All" />
        <!--[if !IE]><!-->
        <object title="$info" type="audio/x-pn-realaudio-plugin"
                data="$url" width="$width" height="$height">
            <param name="src" value="$url" />
            <param name="controls" value="All" />
        <!--<![endif]-->
            $fallback
        <!--[if !IE]><!-->
        </object>
        <!--<![endif]-->
  </object>
</span>
OET;
    }

    public function get_supported_extensions() {
        return array('ra', 'ram', 'rm', 'rv');
    }

    public function get_rank() {
        return 40;
    }
}



class core_media_player_swf extends core_media_player {
    public function embed($urls, $name, $width, $height, $options) {
        self::pick_video_size($width, $height);

        $firsturl = reset($urls);
        $url = $firsturl->out(true);

        $fallback = core_media_player::PLACEHOLDER;
        $output = <<<OET
<span class="mediaplugin mediaplugin_swf">
  <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="$width" height="$height">
    <param name="movie" value="$url" />
    <param name="autoplay" value="true" />
    <param name="loop" value="false" />
    <param name="controller" value="true" />
    <param name="scale" value="aspect" />
    <param name="base" value="." />
    <param name="allowscriptaccess" value="never" />
    <param name="allowfullscreen" value="true" />
<!--[if !IE]><!-->
    <object type="application/x-shockwave-flash" data="$url" width="$width" height="$height">
      <param name="controller" value="true" />
      <param name="autoplay" value="true" />
      <param name="loop" value="false" />
      <param name="scale" value="aspect" />
      <param name="base" value="." />
      <param name="allowscriptaccess" value="never" />
      <param name="allowfullscreen" value="true" />
<!--<![endif]-->
$fallback
<!--[if !IE]><!-->
    </object>
<!--<![endif]-->
  </object>
</span>
OET;

        return $output;
    }

    public function get_supported_extensions() {
        return array('swf');
    }

    public function list_supported_urls(array $urls, array $options = array()) {
                if (empty($options[core_media::OPTION_TRUSTED])) {
            return array();
        }
        return parent::list_supported_urls($urls, $options);
    }

    public function get_rank() {
        return 30;
    }
}



class core_media_player_html5video extends core_media_player {
    public function embed($urls, $name, $width, $height, $options) {
                        $oldandroid = core_useragent::is_webkit_android() &&
                !core_useragent::check_webkit_android_version('533.1');

                $sources = array();
        foreach ($urls as $url) {
            $mimetype = core_media::get_mimetype($url);
            $source = html_writer::tag('source', '', array('src' => $url, 'type' => $mimetype));
            if ($mimetype === 'video/mp4') {
                if ($oldandroid) {
                                        $source = html_writer::tag('source', '', array('src' => $url));
                }

                                                array_unshift($sources, $source);
            } else {
                $sources[] = $source;
            }
        }

        $sources = implode("\n", $sources);
        $title = s($this->get_name($name, $urls));

        if (!$width) {
                        $width = CORE_MEDIA_VIDEO_WIDTH;
        }

        if (!$height) {
                        $size = "width=\"$width\"";
        } else {
            $size = "width=\"$width\" height=\"$height\"";
        }

        $sillyscript = '';
        $idtag = '';
        if ($oldandroid) {
                        $id = 'core_media_html5v_' . md5(time() . '_' . rand());
            $idtag = 'id="' . $id . '"';
            $sillyscript = <<<OET
<script type="text/javascript">
document.getElementById('$id').addEventListener('click', function() {
    this.play();
}, false);
</script>
OET;
        }

        $fallback = core_media_player::PLACEHOLDER;
        return <<<OET
<span class="mediaplugin mediaplugin_html5video">
<video $idtag controls="true" $size preload="metadata" title="$title">
    $sources
    $fallback
</video>
$sillyscript
</span>
OET;
    }

    public function get_supported_extensions() {
        return array('m4v', 'webm', 'ogv', 'mp4');
    }

    public function list_supported_urls(array $urls, array $options = array()) {
        $extensions = $this->get_supported_extensions();
        $result = array();
        foreach ($urls as $url) {
            $ext = core_media::get_extension($url);
            if (in_array($ext, $extensions)) {
                                                                                                                                                if ($ext === 'ogv' || $ext === 'webm') {
                                        if (core_useragent::is_ie() || core_useragent::is_edge() || core_useragent::is_safari()) {
                        continue;
                    }
                }

                $result[] = $url;
            }
        }
        return $result;
    }

    public function get_rank() {
        return 50;
    }
}



class core_media_player_html5audio extends core_media_player {
    public function embed($urls, $name, $width, $height, $options) {

                $sources = array();
        foreach ($urls as $url) {
            $mimetype = core_media::get_mimetype($url);
            $sources[] = html_writer::tag('source', '', array('src' => $url, 'type' => $mimetype));
        }

        $sources = implode("\n", $sources);
        $title = s($this->get_name($name, $urls));

                $size = '';
        if ($width) {
            $size = 'width="' . $width . '"';
        }

        $fallback = core_media_player::PLACEHOLDER;

        return <<<OET
<audio controls="true" $size class="mediaplugin mediaplugin_html5audio" preload="none" title="$title">
$sources
$fallback
</audio>
OET;
    }

    public function get_supported_extensions() {
        return array('ogg', 'oga', 'aac', 'm4a', 'mp3', 'wav');
    }

    public function list_supported_urls(array $urls, array $options = array()) {
        $extensions = $this->get_supported_extensions();
        $result = array();
        foreach ($urls as $url) {
            $ext = core_media::get_extension($url);
            if (in_array($ext, $extensions)) {
                if ($ext === 'ogg' || $ext === 'oga') {
                                        if (core_useragent::is_ie() || core_useragent::is_edge() || core_useragent::is_safari()) {
                        continue;
                    }
                } else if ($ext === 'wav') {
                                        if (core_useragent::is_ie()) {
                        continue;
                    }
                }
                                if (core_useragent::is_webkit_android() &&
                        !core_useragent::is_webkit_android('533.1')) {
                    continue;
                }

                $result[] = $url;
            }
        }
        return $result;
    }

    public function get_rank() {
        return 20;
    }
}



class core_media_player_link extends core_media_player {
    public function embed($urls, $name, $width, $height, $options) {
                if (!empty($options[core_media::OPTION_NO_LINK])) {
            return '';
        }

                $output = '';
        foreach ($urls as $url) {
            $title = core_media::get_filename($url);
            $printlink = html_writer::link($url, $title, array('class' => 'mediafallbacklink'));
            if ($output) {
                                                $output .= ' / ';
            }
            $output .= $printlink;
        }
        return $output;
    }

    public function list_supported_urls(array $urls, array $options = array()) {
                return $urls;
    }

    public function is_enabled() {
                return true;
    }

    public function get_rank() {
        return 0;
    }
}
