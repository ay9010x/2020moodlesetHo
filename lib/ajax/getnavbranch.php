<?php




define('AJAX_SCRIPT', true);


require_once(dirname(__FILE__) . '/../../config.php');

require_once($CFG->dirroot.'/course/lib.php');

if (!empty($CFG->forcelogin)) {
    require_login();
}

try {
        ob_start();
                $branchid = required_param('id', PARAM_ALPHANUM);
        $branchtype = required_param('type', PARAM_INT);
        $instanceid = optional_param('instance', null, PARAM_INT);

    $PAGE->set_context(context_system::instance());

        $navigation = new global_navigation_for_ajax($PAGE, $branchtype, $branchid);

    $linkcategories = false;

    if ($instanceid!==null) {
                $blockrecord = $DB->get_record('block_instances', array('id'=>$instanceid,'blockname'=>'navigation'));
        if ($blockrecord!=false) {

                        $block = block_instance('navigation', $blockrecord);

            $trimmode = block_navigation::TRIM_RIGHT;
            $trimlength = 50;

                        if (!empty($block->config->trimmode)) {
                $trimmode = (int)$block->config->trimmode;
            }
                        if (!empty($block->config->trimlength)) {
                $trimlength = (int)$block->config->trimlength;
            }
            if (!empty($block->config->linkcategories) && $block->config->linkcategories == 'yes') {
                $linkcategories = true;
            }
        }
    }

        if (!isloggedin()) {
        $navigation->set_expansion_limit(navigation_node::TYPE_COURSE);
    } else {
        if (isset($block) && !empty($block->config->expansionlimit)) {
            $navigation->set_expansion_limit($block->config->expansionlimit);
        }
    }
    if (isset($block)) {
        $block->trim($navigation, $trimmode, $trimlength, ceil($trimlength/2));
    }
    $converter = new navigation_json();

        if ($branchtype != 0) {
        $branch = $navigation->find($branchid, $branchtype);
    } else if ($branchid === 'mycourses' || $branchid === 'courses') {
        $branch = $navigation->find($branchid, navigation_node::TYPE_ROOTNODE);
    } else {
        throw new coding_exception('Invalid branch type/id passed to AJAX call to load branches.');
    }

        if (!$linkcategories) {
        foreach ($branch->find_all_of_type(navigation_node::TYPE_CATEGORY) as $category) {
            $category->action = null;
        }
        foreach ($branch->find_all_of_type(navigation_node::TYPE_MY_CATEGORY) as $category) {
            $category->action = null;
        }
    }

        $html = ob_get_contents();
    ob_end_clean();
} catch (Exception $e) {
    throw new coding_exception('Error: '.$e->getMessage());
}

if (trim($html) !== '') {
    throw new coding_exception('Errors were encountered while producing the navigation branch'."\n\n\n".$html);
}
if (empty($branch) || ($branch->nodetype !== navigation_node::NODETYPE_BRANCH && !$branch->isexpandable)) {
    throw new coding_exception('No further information available for this branch');
}

$converter->set_expandable($navigation->get_expandable());
header('Content-type: text/plain; charset=utf-8');
echo $converter->convert($branch);
