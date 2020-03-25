<?php





require_once(dirname(__FILE__) . '/../config.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();
$systemcontext = context_system::instance();
require_capability('moodle/question:config', $systemcontext);

admin_externalpage_setup('manageqbehaviours');
$thispageurl = new moodle_url('/admin/qbehaviours.php');

$behaviours = core_component::get_plugin_list('qbehaviour');
$pluginmanager = core_plugin_manager::instance();

$counts = $DB->get_records_sql_menu("
        SELECT behaviour, COUNT(1)
        FROM {question_attempts} GROUP BY behaviour");
$needed = array();
$archetypal = array();
foreach ($behaviours as $behaviour => $notused) {
    if (!array_key_exists($behaviour, $counts)) {
        $counts[$behaviour] = 0;
    }
    $needed[$behaviour] = ($counts[$behaviour] > 0) ||
            $pluginmanager->other_plugins_that_require('qbehaviour_' . $behaviour);
    $archetypal[$behaviour] = question_engine::is_behaviour_archetypal($behaviour);
}
foreach ($counts as $behaviour => $count) {
    if (!array_key_exists($behaviour, $behaviours)) {
        $counts['missing'] += $count;
    }
}
$needed['missing'] = true;

$config = get_config('question');
$sortedbehaviours = array();
foreach ($behaviours as $behaviour => $notused) {
    $sortedbehaviours[$behaviour] = question_engine::get_behaviour_name($behaviour);
}
if (!empty($config->behavioursortorder)) {
    $sortedbehaviours = question_engine::sort_behaviours($sortedbehaviours,
            $config->behavioursortorder, '');
}

if (!empty($config->disabledbehaviours)) {
    $disabledbehaviours = explode(',', $config->disabledbehaviours);
} else {
    $disabledbehaviours = array();
}


if (($disable = optional_param('disable', '', PARAM_PLUGIN)) && confirm_sesskey()) {
    if (!isset($behaviours[$disable])) {
        print_error('unknownbehaviour', 'question', $thispageurl, $disable);
    }

    if (array_search($disable, $disabledbehaviours) === false) {
        $disabledbehaviours[] = $disable;
        set_config('disabledbehaviours', implode(',', $disabledbehaviours), 'question');
    }
    core_plugin_manager::reset_caches();
    redirect($thispageurl);
}

if (($enable = optional_param('enable', '', PARAM_PLUGIN)) && confirm_sesskey()) {
    if (!isset($behaviours[$enable])) {
        print_error('unknownbehaviour', 'question', $thispageurl, $enable);
    }

    if (!$archetypal[$enable]) {
        print_error('cannotenablebehaviour', 'question', $thispageurl, $enable);
    }

    if (($key = array_search($enable, $disabledbehaviours)) !== false) {
        unset($disabledbehaviours[$key]);
        set_config('disabledbehaviours', implode(',', $disabledbehaviours), 'question');
    }
    core_plugin_manager::reset_caches();
    redirect($thispageurl);
}

if (($up = optional_param('up', '', PARAM_PLUGIN)) && confirm_sesskey()) {
    if (!isset($behaviours[$up])) {
        print_error('unknownbehaviour', 'question', $thispageurl, $up);
    }

        $neworder = question_reorder_qtypes($sortedbehaviours, $up, -1);
    set_config('behavioursortorder', implode(',', $neworder), 'question');
    redirect($thispageurl);
}

if (($down = optional_param('down', '', PARAM_PLUGIN)) && confirm_sesskey()) {
    if (!isset($behaviours[$down])) {
        print_error('unknownbehaviour', 'question', $thispageurl, $down);
    }

        $neworder = question_reorder_qtypes($sortedbehaviours, $down, +1);
    set_config('behavioursortorder', implode(',', $neworder), 'question');
    redirect($thispageurl);
}


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageqbehaviours', 'admin'));

$table = new flexible_table('qbehaviouradmintable');
$table->define_baseurl($thispageurl);
$table->define_columns(array('behaviour', 'numqas', 'version', 'requires',
        'available', 'uninstall'));
$table->define_headers(array(get_string('behaviour', 'question'), get_string('numqas', 'question'),
        get_string('version'), get_string('requires', 'admin'),
        get_string('availableq', 'question'), get_string('uninstallplugin', 'core_admin')));
$table->set_attribute('id', 'qbehaviours');
$table->set_attribute('class', 'generaltable admintable');
$table->setup();

foreach ($sortedbehaviours as $behaviour => $behaviourname) {
    $row = array();

        $row[] = $behaviourname;

        $row[] = $counts[$behaviour];

        $version = get_config('qbehaviour_' . $behaviour, 'version');
    if ($version) {
        $row[] = $version;
    } else {
        $row[] = html_writer::tag('span', get_string('nodatabase', 'admin'), array('class' => 'disabled'));
    }

        $plugin = $pluginmanager->get_plugin_info('qbehaviour_' . $behaviour);
    $required = $plugin->get_other_required_plugins();
    if (!empty($required)) {
        $strrequired = array();
        foreach ($required as $component => $notused) {
            $strrequired[] = $pluginmanager->plugin_name($component);
        }
        $row[] = implode(', ', $strrequired);
    } else {
        $row[] = '';
    }

        $rowclass = '';
    if ($archetypal[$behaviour]) {
        $enabled = array_search($behaviour, $disabledbehaviours) === false;
        $icons = question_behaviour_enable_disable_icons($behaviour, $enabled);
        if (!$enabled) {
            $rowclass = 'dimmed_text';
        }
    } else {
        $icons = $OUTPUT->spacer(array('class' => 'iconsmall'));
    }

        $icons .= question_behaviour_icon_html('up', $behaviour, 't/up', get_string('up'), null);
    $icons .= question_behaviour_icon_html('down', $behaviour, 't/down', get_string('down'), null);
    $row[] = $icons;

        if ($needed[$behaviour]) {
        $row[] = '';
    } else {
        $uninstallurl = core_plugin_manager::instance()->get_uninstall_url('qbehaviour_'.$behaviour, 'manage');
        if ($uninstallurl) {
            $row[] = html_writer::link($uninstallurl, get_string('uninstallplugin', 'core_admin'),
                array('title' => get_string('uninstallbehaviour', 'question')));
        }
    }

    $table->add_data($row, $rowclass);
}

$table->finish_output();

echo $OUTPUT->footer();

function question_behaviour_enable_disable_icons($behaviour, $enabled) {
    if ($enabled) {
        return question_behaviour_icon_html('disable', $behaviour, 't/hide',
                get_string('enabled', 'question'), get_string('disable'));
    } else {
        return question_behaviour_icon_html('enable', $behaviour, 't/show',
                get_string('disabled', 'question'), get_string('enable'));
    }
}

function question_behaviour_icon_html($action, $behaviour, $icon, $alt, $tip) {
    global $OUTPUT;
    return $OUTPUT->action_icon(new moodle_url('/admin/qbehaviours.php',
            array($action => $behaviour, 'sesskey' => sesskey())),
            new pix_icon($icon, $alt, 'moodle', array('title' => '', 'class' => 'iconsmall')),
            null, array('title' => $tip));
}

