<?php






class webservice_rest_client {

    
    private $serverurl;

    
    private $token;

    
    private $format;

    
    public function __construct($serverurl, $token, $format = 'xml') {
        $this->serverurl = new moodle_url($serverurl);
        $this->token = $token;
        $this->format = $format;
    }

    
    public function set_token($token) {
        $this->token = $token;
    }

    
    public function call($functionname, $params) {
        global $DB, $CFG;

         if ($this->format == 'json') {
             $formatparam = '&moodlewsrestformat=json';
             $this->serverurl->param('moodlewsrestformat','json');
         } else {
             $formatparam = '';          }

        $this->serverurl->param('wstoken',$this->token);
        $this->serverurl->param('wsfunction',$functionname); 
        $result = download_file_content($this->serverurl->out(false), null, $params);

                if ($this->format == 'json') {
            $result = json_decode($result);
        }

        return $result;
    }

}