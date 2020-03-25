<?php



defined('MOODLE_INTERNAL') || die;


$modltifolder = new admin_category('modltifolder', new lang_string('pluginname', 'mod_lti'), $module->is_enabled() === false);
$ADMIN->add('modsettings', $modltifolder);
$settings->visiblename = new lang_string('manage_tools', 'mod_lti');
$settings->hidden = true;
$ADMIN->add('modltifolder', $settings);
$proxieslink = new admin_externalpage('ltitoolproxies',
        get_string('manage_tool_proxies', 'lti'),
        new moodle_url('/mod/lti/toolproxies.php'));
$proxieslink->hidden = true;
$ADMIN->add('modltifolder', $proxieslink);
$ADMIN->add('modltifolder', new admin_externalpage('ltitoolconfigure',
        get_string('manage_external_tools', 'lti'),
        new moodle_url('/mod/lti/toolconfigure.php')));

foreach (core_plugin_manager::instance()->get_plugins_of_type('ltisource') as $plugin) {
    
    $plugin->load_settings($ADMIN, 'modltifolder', $hassiteconfig);
}

$toolproxiesurl = new moodle_url('/mod/lti/toolproxies.php');
$toolproxiesurl = $toolproxiesurl->out();

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/lti/locallib.php');

    $configuredtoolshtml = '';
    $pendingtoolshtml = '';
    $rejectedtoolshtml = '';

    $active = get_string('active', 'lti');
    $pending = get_string('pending', 'lti');
    $rejected = get_string('rejected', 'lti');

        $PAGE->requires->strings_for_js(
        array(
            'typename',
            'baseurl',
            'action',
            'createdon'
        ),
        'mod_lti'
    );

    $types = lti_filter_get_types(get_site()->id);

    $configuredtools = lti_filter_tool_types($types, LTI_TOOL_STATE_CONFIGURED);

    $configuredtoolshtml = lti_get_tool_table($configuredtools, 'lti_configured');

    $pendingtools = lti_filter_tool_types($types, LTI_TOOL_STATE_PENDING);

    $pendingtoolshtml = lti_get_tool_table($pendingtools, 'lti_pending');

    $rejectedtools = lti_filter_tool_types($types, LTI_TOOL_STATE_REJECTED);

    $rejectedtoolshtml = lti_get_tool_table($rejectedtools, 'lti_rejected');

    $tab = optional_param('tab', '', PARAM_ALPHAEXT);
    $activeselected = '';
    $pendingselected = '';
    $rejectedselected = '';
    switch ($tab) {
        case 'lti_pending':
            $pendingselected = 'class="selected"';
            break;
        case 'lti_rejected':
            $rejectedselected = 'class="selected"';
            break;
        default:
            $activeselected = 'class="selected"';
            break;
    }
    $addtype = get_string('addtype', 'lti');
    $config = get_string('manage_tool_proxies', 'lti');

    $addtypeurl = "{$CFG->wwwroot}/mod/lti/typessettings.php?action=add&amp;sesskey={$USER->sesskey}";

    $template = <<< EOD
<div id="lti_tabs" class="yui-navset">
    <ul id="lti_tab_heading" class="yui-nav" style="display:none">
        <li {$activeselected}>
            <a href="#tab1">
                <em>$active</em>
            </a>
        </li>
        <li {$pendingselected}>
            <a href="#tab2">
                <em>$pending</em>
            </a>
        </li>
        <li {$rejectedselected}>
            <a href="#tab3">
                <em>$rejected</em>
            </a>
        </li>
    </ul>
    <div class="yui-content">
        <div>
            <div><a style="margin-top:.25em" href="{$addtypeurl}">{$addtype}</a></div>
            $configuredtoolshtml
        </div>
        <div>
            $pendingtoolshtml
        </div>
        <div>
            $rejectedtoolshtml
        </div>
    </div>
</div>

<script type="text/javascript">
//<![CDATA[
    YUI().use('yui2-tabview', 'yui2-datatable', function(Y) {
        //If javascript is disabled, they will just see the three tabs one after another
        var lti_tab_heading = document.getElementById('lti_tab_heading');
        lti_tab_heading.style.display = '';

        new Y.YUI2.widget.TabView('lti_tabs');

        var setupTools = function(id, sort){
            var lti_tools = Y.YUI2.util.Dom.get(id);

            if(lti_tools){
                var dataSource = new Y.YUI2.util.DataSource(lti_tools);

                var configuredColumns = [
                    {key:'name', label: M.util.get_string('typename', 'mod_lti'), sortable: true},
                    {key:'baseURL', label: M.util.get_string('baseurl', 'mod_lti'), sortable: true},
                    {key:'timecreated', label: M.util.get_string('createdon', 'mod_lti'), sortable: true},
                    {key:'action', label: M.util.get_string('action', 'mod_lti')}
                ];

                dataSource.responseType = Y.YUI2.util.DataSource.TYPE_HTMLTABLE;
                dataSource.responseSchema = {
                    fields: [
                        {key:'name'},
                        {key:'baseURL'},
                        {key:'timecreated'},
                        {key:'action'}
                    ]
                };

                new Y.YUI2.widget.DataTable(id + '_container', configuredColumns, dataSource,
                    {
                        sortedBy: sort
                    }
                );
            }
        };

        setupTools('lti_configured_tools', {key:'name', dir:'asc'});
        setupTools('lti_pending_tools', {key:'timecreated', dir:'desc'});
        setupTools('lti_rejected_tools', {key:'timecreated', dir:'desc'});
    });
//]]
</script>
EOD;
    $settings->add(new admin_setting_heading('lti_types', new lang_string('external_tool_types', 'lti') .
        $OUTPUT->help_icon('main_admin', 'lti'), $template));
}

$settings = null;

