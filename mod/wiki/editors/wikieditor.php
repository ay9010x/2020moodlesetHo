<?php



require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/lib/form/textarea.php');

class MoodleQuickForm_wikieditor extends MoodleQuickForm_textarea {

    private $files;

    
    function __construct($elementName = null, $elementLabel = null, $attributes = null) {
        if (isset($attributes['wiki_format'])) {
            $this->wikiformat = $attributes['wiki_format'];
            unset($attributes['wiki_format']);
        }
        if (isset($attributes['files'])) {
            $this->files = $attributes['files'];
            unset($attributes['files']);
        }

        parent::__construct($elementName, $elementLabel, $attributes);
    }

    
    public function MoodleQuickForm_wikieditor($elementName = null, $elementLabel = null, $attributes = null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }

    function setWikiFormat($wikiformat) {
        $this->wikiformat = $wikiformat;
    }

    function toHtml() {
        $textarea = parent::toHtml();

        return $this->{
            $this->wikiformat."Editor"}
            ($textarea);
    }

    function creoleEditor($textarea) {
        return $this->printWikiEditor($textarea);
    }

    function nwikiEditor($textarea) {
        return $this->printWikiEditor($textarea);
    }

    private function printWikiEditor($textarea) {
        global $OUTPUT;

        $textarea = $OUTPUT->container_start().$textarea.$OUTPUT->container_end();

        $buttons = $this->getButtons();

        return $buttons.$textarea;
    }

    private function getButtons() {
        global $PAGE, $OUTPUT, $CFG;

        $editor = $this->wikiformat;

        $tag = $this->getTokens($editor, 'bold');
        $wiki_editor['bold'] = array('ed_bold.gif', get_string('wikiboldtext', 'wiki'), $tag[0], $tag[1], get_string('wikiboldtext', 'wiki'));

        $tag = $this->getTokens($editor, 'italic');
        $wiki_editor['italic'] = array('ed_italic.gif', get_string('wikiitalictext', 'wiki'), $tag[0], $tag[1], get_string('wikiitalictext', 'wiki'));

        $imagetag = $this->getTokens($editor, 'image');
        $wiki_editor['image'] = array('ed_img.gif', get_string('wikiimage', 'wiki'), $imagetag[0], $imagetag[1], get_string('wikiimage', 'wiki'));

        $tag = $this->getTokens($editor, 'link');
        $wiki_editor['internal'] = array('ed_internal.gif', get_string('wikiinternalurl', 'wiki'), $tag[0], $tag[1], get_string('wikiinternalurl', 'wiki'));

        $tag = $this->getTokens($editor, 'url');
        $wiki_editor['external'] = array('ed_external.gif', get_string('wikiexternalurl', 'wiki'), $tag, "", get_string('wikiexternalurl', 'wiki'));

        $tag = $this->getTokens($editor, 'list');
        $wiki_editor['u_list'] = array('ed_ul.gif', get_string('wikiunorderedlist', 'wiki'), '\\n'.$tag[0], '', '');
        $wiki_editor['o_list'] = array('ed_ol.gif', get_string('wikiorderedlist', 'wiki'), '\\n'.$tag[1], '', '');

        $tag = $this->getTokens($editor, 'header');
        $wiki_editor['h1'] = array('ed_h1.gif', get_string('wikiheader', 'wiki', 1), '\\n'.$tag.' ', ' '.$tag.'\\n', get_string('wikiheader', 'wiki', 1));
        $wiki_editor['h2'] = array('ed_h2.gif', get_string('wikiheader', 'wiki', 2), '\\n'.$tag.$tag.' ', ' '.$tag.$tag.'\\n', get_string('wikiheader', 'wiki', 2));
        $wiki_editor['h3'] = array('ed_h3.gif', get_string('wikiheader', 'wiki', 3), '\\n'.$tag.$tag.$tag.' ', ' '.$tag.$tag.$tag.'\\n', get_string('wikiheader', 'wiki', 3));

        $tag = $this->getTokens($editor, 'line_break');
        $wiki_editor['hr'] = array('ed_hr.gif', get_string('wikihr', 'wiki'), '\\n'.$tag.'\\n', '', '');

        $tag = $this->getTokens($editor, 'nowiki');
        $wiki_editor['nowiki'] = array('ed_nowiki.gif', get_string('wikinowikitext', 'wiki'), $tag[0], $tag[1], get_string('wikinowikitext', 'wiki'));

        $PAGE->requires->js('/mod/wiki/editors/wiki/buttons.js');

        $html = '<div class="wikieditor-toolbar">';
        foreach ($wiki_editor as $button) {
            $html .= "<a href=\"javascript:insertTags";
            $html .= "('".$button[2]."','".$button[3]."','".$button[4]."');\">";
            $html .= html_writer::empty_tag('img', array('alt' => $button[1], 'src' => $CFG->wwwroot . '/mod/wiki/editors/wiki/images/' . $button[0]));
            $html .= "</a>";
        }
        $html .= "<label class='accesshide' for='addtags'>" . get_string('insertimage', 'wiki')  . "</label>";
        $html .= "<select id='addtags' onchange=\"insertTags('{$imagetag[0]}', '{$imagetag[1]}', this.value)\">";
        $html .= "<option value='" . s(get_string('wikiimage', 'wiki')) . "'>" . get_string('insertimage', 'wiki') . '</option>';
        foreach ($this->files as $filename) {
            $html .= "<option value='".s($filename)."'>";
            $html .= $filename;
            $html .= '</option>';
        }
        $html .= '</select>';
        $html .= $OUTPUT->help_icon('insertimage', 'wiki');
        $html .= '</div>';

        return $html;
    }

    private function getTokens($format, $token) {
        $tokens = wiki_parser_get_token($format, $token);

        if (is_array($tokens)) {
            foreach ($tokens as & $t) {
                $this->escapeToken($t);
            }
        } else {
            $this->escapeToken($tokens);
        }

        return $tokens;
    }

    private function escapeToken(&$token) {
        $token = urlencode(str_replace("'", "\'", $token));
    }
}

MoodleQuickForm::registerElementType('wikieditor', $CFG->dirroot."/mod/wiki/editors/wikieditor.php", 'MoodleQuickForm_wikieditor');
