<?php


include_once("nwiki.php");

class html_parser extends nwiki_parser {
    protected $blockrules = array();

    protected $section_editing = true;

    
    protected $minheaderlevel = null;

    public function __construct() {
        parent::__construct();
                $this->tagrules = array(
                        'header' => array(
                'expression' => "/<\s*h([1-6])\s*>(.+?)<\/h[1-6]>/is"
            ),
            'link' => $this->tagrules['link'],
            'url' => $this->tagrules['url']
        );
    }

    
    protected function find_min_header_level($text) {
        preg_match_all($this->tagrules['header']['expression'], $text, $matches);
        return !empty($matches[1]) ? min($matches[1]) : 1;
    }

    protected function before_parsing() {
        parent::before_parsing();

        $this->minheaderlevel = $this->find_min_header_level($this->string);
        $this->rules($this->string);
    }

    
    protected function header_tag_rule($match) {
        return $this->generate_header($match[2], (int)$match[1] - $this->minheaderlevel + 1);
    }

    

    public function get_section($header, $text, $clean = false) {
        if ($clean) {
            $text = preg_replace('/\r\n/', "\n", $text);
            $text = preg_replace('/\r/', "\n", $text);
            $text .= "\n\n";
        }

        $minheaderlevel = $this->find_min_header_level($text);

        $h1 = array("<\s*h{$minheaderlevel}\s*>", "<\/h{$minheaderlevel}>");

        $regex = "/(.*?)({$h1[0]}\s*".preg_quote($header, '/')."\s*{$h1[1]}.*?)((?:{$h1[0]}.*)|$)/is";
        preg_match($regex, $text, $match);

        if (!empty($match)) {
            return array($match[1], $match[2], $match[3]);
        } else {
            return false;
        }
    }

    protected function get_repeated_sections(&$text, $repeated = array()) {
        $this->repeated_sections = $repeated;
        return preg_replace_callback($this->tagrules['header'], array($this, 'get_repeated_sections_callback'), $text);
    }

    protected function get_repeated_sections_callback($match) {
        $text = trim($match[2]);

        if (in_array($text, $this->repeated_sections)) {
            $this->returnvalues['repeated_sections'][] = $text;
            return parser_utils::h('p', $text);
        } else {
            $this->repeated_sections[] = $text;
        }

        return $match[0];
    }
}
