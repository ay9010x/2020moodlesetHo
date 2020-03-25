<?php



require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');

$id = required_param('id', PARAM_INT);
$tab = optional_param('tab', '', PARAM_ALPHAEXT);

require_login(0, false);

$redirect = new moodle_url('/mod/lti/toolproxies.php', array('tab' => $tab));
$redirect = $redirect->out();

require_sesskey();

$toolproxies = $DB->get_records('lti_tool_proxies');

$duplicate = false;
foreach ($toolproxies as $key => $toolproxy) {
    if (($toolproxy->state == LTI_TOOL_PROXY_STATE_PENDING) || ($toolproxy->state == LTI_TOOL_PROXY_STATE_ACCEPTED)) {
        if ($toolproxy->regurl == $toolproxies[$id]->regurl) {
            $duplicate = true;
            break;
        }
    }
}

$redirect = new moodle_url('/mod/lti/toolproxies.php');
if ($duplicate) {
    redirect($redirect,  get_string('duplicateregurl', 'lti'));
}


$profileservice = lti_get_service_by_name('profile');
if (empty($profileservice)) {
    redirect($redirect,  get_string('noprofileservice', 'lti'));
}

$url = new moodle_url('/mod/lti/register.php', array('id' => $id));
$PAGE->set_url($url);

admin_externalpage_setup('ltitoolproxies');


$PAGE->set_heading(get_string('toolproxyregistration', 'lti'));
$PAGE->set_title("{$SITE->shortname}: " . get_string('toolproxyregistration', 'lti'));

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('toolproxyregistration', 'lti'));

echo $OUTPUT->box_start('generalbox');

$registration = new moodle_url('/mod/lti/registration.php',
    array('id' => $id, 'sesskey' => sesskey()));

echo "<p id=\"id_warning\" style=\"display: none; color: red; font-weight: bold; margin-top: 1em; padding-top: 1em;\">\n";
echo get_string('register_warning', 'lti');
echo "\n</p>\n";

echo '<iframe id="contentframe" height="600px" width="100%" src="' . $registration->out() . '" onload="doOnload()"></iframe>';

$resize = '
        <script type="text/javascript">
        //<![CDATA[
            function doReveal() {
              var el = document.getElementById(\'id_warning\');
              el.style.display = \'block\';
            }
            function doOnload() {
                window.clearTimeout(mod_lti_timer);
            }
            var mod_lti_timer = window.setTimeout(doReveal, 20000);
            YUI().use("node", "event", function(Y) {
                //Take scrollbars off the outer document to prevent double scroll bar effect
                var doc = Y.one("body");
                doc.setStyle("overflow", "hidden");

                var frame = Y.one("#contentframe");
                var padding = 15; //The bottom of the iframe wasn\'t visible on some themes. Probably because of border widths, etc.
                var lastHeight;
                var resize = function(e) {
                    var viewportHeight = doc.get("winHeight");
                    if(lastHeight !== Math.min(doc.get("docHeight"), viewportHeight)){
                        frame.setStyle("height", viewportHeight - frame.getY() - padding + "px");
                        lastHeight = Math.min(doc.get("docHeight"), doc.get("winHeight"));
                    }
                };

                resize();

                Y.on("windowresize", resize);
            });
        //]]
        </script>
';

echo $resize;

echo $OUTPUT->box_end();
echo $OUTPUT->footer();
