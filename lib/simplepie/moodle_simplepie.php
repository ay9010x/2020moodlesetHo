<?php



require_once($CFG->libdir.'/filelib.php');

require_once($CFG->libdir.'/simplepie/autoloader.php');


class moodle_simplepie extends SimplePie {
    
    public function __construct($feedurl = null, $timeout = 2) {
        $cachedir = moodle_simplepie::get_cache_directory();
        check_dir_exists($cachedir);

        parent::__construct();

                $this->set_file_class('moodle_simplepie_file');

                $this->set_sanitize_class('moodle_simplepie_sanitize');
        $this->sanitize = new moodle_simplepie_sanitize();

                $this->set_output_encoding('UTF-8');

                $this->set_timeout($timeout);

                $this->set_cache_location($cachedir);
        $this->set_cache_duration(3600);

                if ($feedurl !== null) {
            $this->set_feed_url($feedurl);
            $this->init();
        }
    }

    
    private static function get_cache_directory() {
        global $CFG;

        return $CFG->cachedir.'/simplepie/';
    }

    
    public static function reset_cache() {

        $cachedir = moodle_simplepie::get_cache_directory();

        return remove_dir($cachedir);
    }
}


class moodle_simplepie_file extends SimplePie_File {

    
    public function __construct($url, $timeout = 10, $redirects = 5, $headers = null, $useragent = null, $force_fsockopen = false) {
        $this->url = $url;
        $this->method = SIMPLEPIE_FILE_SOURCE_REMOTE | SIMPLEPIE_FILE_SOURCE_CURL;

        $curl = new curl();
        $curl->setopt( array(
                'CURLOPT_HEADER' => true,
                'CURLOPT_TIMEOUT' => $timeout,
                'CURLOPT_CONNECTTIMEOUT' => $timeout ));


        if ($headers !== null) {
                        foreach($headers as $headername => $headervalue){
                $headerstr = "{$headername}: {$headervalue}";
                $curl->setHeader($headerstr);
            }
        }

        $this->headers = curl::strip_double_headers($curl->get($url));

        if ($curl->error) {
            $this->error = 'cURL Error: '.$curl->error;
            $this->success = false;
            return false;
        }

        $parser = new SimplePie_HTTP_Parser($this->headers);

        if ($parser->parse()) {
            $this->headers = $parser->headers;
            $this->body = trim($parser->body);
            $this->status_code = $parser->status_code;


            if (($this->status_code == 300 || $this->status_code == 301 || $this->status_code == 302 || $this->status_code == 303
                    || $this->status_code == 307 || $this->status_code > 307 && $this->status_code < 400)
                    && isset($this->headers['location']) && $this->redirects < $redirects) {
                $this->redirects++;
                $location = SimplePie_Misc::absolutize_url($this->headers['location'], $url);
                return $this->__construct($location, $timeout, $redirects, $headers);
            }
        }
    }
}



class moodle_simplepie_sanitize extends SimplePie_Sanitize {
    public function sanitize($data, $type, $base = '') {
        $data = trim($data);

        if ($data === '') {
            return '';
        }

        if ($type & SIMPLEPIE_CONSTRUCT_BASE64){
            $data = base64_decode($data);
        }

        if ($type & SIMPLEPIE_CONSTRUCT_MAYBE_HTML) {
            if (preg_match('/(&(#(x[0-9a-fA-F]+|[0-9]+)|[a-zA-Z0-9]+)|<\/[A-Za-z][^\x09\x0A\x0B\x0C\x0D\x20\x2F\x3E]*' . SIMPLEPIE_PCRE_HTML_ATTRIBUTE . '>)/', $data)) {
                $type |= SIMPLEPIE_CONSTRUCT_HTML;
            } else {
                $type |= SIMPLEPIE_CONSTRUCT_TEXT;
            }
        }

        if ($type & SIMPLEPIE_CONSTRUCT_IRI) {
            $absolute = $this->registry->call('Misc', 'absolutize_url', array($data, $base));
            if ($absolute !== false) {
                $data = $absolute;
            }
            $data = clean_param($data, PARAM_URL);
        }

        if ($type & (SIMPLEPIE_CONSTRUCT_TEXT | SIMPLEPIE_CONSTRUCT_IRI)) {
            $data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
        }

        $data = purify_html($data);

        if ($this->remove_div) {
            $data = preg_replace('/^<div' . SIMPLEPIE_PCRE_XML_ATTRIBUTE . '>/', '', $data);
            $data = preg_replace('/<\/div>$/', '', $data);
        } else {
            $data = preg_replace('/^<div' . SIMPLEPIE_PCRE_XML_ATTRIBUTE . '>/', '<div>', $data);
        }

        if ($this->output_encoding !== 'UTF-8') {
            core_text::convert($data, 'UTF-8', $this->output_encoding);
        }

        return $data;
    }
}
