<?php




require_once('HTML/QuickForm/input.php');


class MoodleQuickForm_recaptcha extends HTML_QuickForm_input {

    
    var $_helpbutton='';

    
    var $_https=false;

    
    public function __construct($elementName = null, $elementLabel = null, $attributes = null) {
        global $CFG;
        parent::__construct($elementName, $elementLabel, $attributes);
        $this->_type = 'recaptcha';
        if (is_https()) {
            $this->_https = true;
        } else {
            $this->_https = false;
        }
    }

    
    public function MoodleQuickForm_recaptcha($elementName = null, $elementLabel = null, $attributes = null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }

    
    function toHtml() {
        global $CFG, $PAGE;
        require_once $CFG->libdir . '/recaptchalib.php';

        $recaptureoptions = Array('theme'=>'custom', 'custom_theme_widget'=>'recaptcha_widget');
        $html = html_writer::script(js_writer::set_variable('RecaptchaOptions', $recaptureoptions));

        $attributes = $this->getAttributes();
        if (empty($attributes['error_message'])) {
            $attributes['error_message'] = null;
            $this->setAttributes($attributes);
        }
        $error = $attributes['error_message'];
        unset($attributes['error_message']);

        $strincorrectpleasetryagain = get_string('incorrectpleasetryagain', 'auth');
        $strenterthewordsabove = get_string('enterthewordsabove', 'auth');
        $strenterthenumbersyouhear = get_string('enterthenumbersyouhear', 'auth');
        $strgetanothercaptcha = get_string('getanothercaptcha', 'auth');
        $strgetanaudiocaptcha = get_string('getanaudiocaptcha', 'auth');
        $strgetanimagecaptcha = get_string('getanimagecaptcha', 'auth');

        $html .= '
<div id="recaptcha_widget" style="display:none">

<div id="recaptcha_image"></div>
<div class="recaptcha_only_if_incorrect_sol" style="color:red">' . $strincorrectpleasetryagain . '</div>

<span class="recaptcha_only_if_image"><label for="recaptcha_response_field">' . $strenterthewordsabove . '</label></span>
<span class="recaptcha_only_if_audio"><label for="recaptcha_response_field">' . $strenterthenumbersyouhear . '</label></span>

<input type="text" id="recaptcha_response_field" name="recaptcha_response_field" />
<input type="hidden" name="recaptcha_element" value="dummyvalue" /> <!-- Dummy value to fool formslib -->
<div><a href="javascript:Recaptcha.reload()">' . $strgetanothercaptcha . '</a></div>
<div class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type(\'audio\')">' . $strgetanaudiocaptcha . '</a></div>
<div class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type(\'image\')">' . $strgetanimagecaptcha . '</a></div>
</div>';

        return $html . recaptcha_get_html($CFG->recaptchapublickey, $error, $this->_https);
    }

    
    function getHelpButton(){
        return $this->_helpbutton;
    }

    
    function verify($challenge_field, $response_field) {
        global $CFG;
        require_once $CFG->libdir . '/recaptchalib.php';
        $response = recaptcha_check_answer($CFG->recaptchaprivatekey,
                                           getremoteaddr(),
                                           $challenge_field,
                                           $response_field,
                                           $this->_https);
        if (!$response->is_valid) {
            $attributes = $this->getAttributes();
            $attributes['error_message'] = $response->error;
            $this->setAttributes($attributes);
            return $response->error;
        }
        return true;
    }
}
