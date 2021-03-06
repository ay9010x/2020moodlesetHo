<?php



require(dirname(__FILE__) . '/../../../config.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url('/lib/tests/other/todochecker.php');
$PAGE->set_context($context);
$PAGE->set_title('To-do checker');
$PAGE->set_heading('To-do checker');

$thirdparty = load_third_party_lib_list();
$extensionstotest = array('php');
$extensionsregex = '/\.(?:' . implode('|', $extensionstotest) . ')$/';
$patterntofind = 'TO' . 'DO'; $found = array();

echo $OUTPUT->header();
echo $OUTPUT->heading('To-do checker', 2);

echo $OUTPUT->box_start();
echo 'Checking code ...';
flush();
recurseFolders($CFG->dirroot, 'check_to_dos', $extensionsregex, false, array_keys($thirdparty));
echo ' done.';
echo $OUTPUT->box_end();

if (empty($found)) {
    echo '<p>No to-dos found.</p>';
} else {
    $total = 0;
    foreach ($found as $filepath => $matches) {
        $total += count($matches);
    }

    echo '<p>' . $total . ' to-dos found:</p><dl>';
    foreach ($found as $filepath => $matches) {
        echo '<dt>' . $filepath . ' <b>(' . count($matches) . ')</b></dt><dd><ul>';
        foreach ($matches as $lineno => $line) {
            $url = 'http://cvs.moodle.org/moodle/' . $filepath . '?view=annotate#l' . $lineno;
            $error = '';

                        $matches = array();
            if (preg_match('/\bTODO\b.*?\b(MDL-\d+)/', $line, $matches)) {
                $issueid = $matches[1];
                $issueurl = 'http://tracker.moodle.org/browse/' . $issueid;

                                list($issueopen, $issuesummary) = issue_info($issueid);
                if ($issueopen) {
                    $issuename = $issueid;
                } else {
                    $issuename = '<strike>' . $issueid . '</strike>';
                    $error = 'The associated tracker issue is Resolved.';
                }

                $line = str_replace($issueid, '<a href="' . $issueurl . '" title="' . s($issuesummary) .
                        '">' . $issuename . '</a>', htmlspecialchars($line));
            } else {
                $line = htmlspecialchars($line);
                $error = 'No associated tracker issue.';
            }

            if ($error) {
                $error = '<span class="error">' . $error . '</span>';
            }
            echo '<li><a href="' . $url . '">' . $lineno . '</a>: ' . $line . $error . '</li>';
        }
        echo '</ul></dd>';
    }
    echo '</dl>';
}

echo $OUTPUT->footer();

function check_to_dos($filepath) {
    global $CFG, $found, $thirdparty;
    if (isset($thirdparty[$filepath])) {
        return;     }
    $lines = file($filepath);
    $matchesinfile = array();
    foreach ($lines as $lineno => $line) {
        if (preg_match('/(?<!->|\$)\bTODO\b/i', $line)) {
            $matchesinfile[$lineno] = $line;
        }
    }
    if (!empty($matchesinfile)) {
        $shortpath = str_replace($CFG->dirroot . '/', '', $filepath);
        $found[$shortpath] = $matchesinfile;
    }
}

function issue_info($issueid) {
    static $cache = array();
    if (array_key_exists($issueid, $cache)) {
        return $cache[$issueid];
    }

    $xmlurl = 'http://tracker.moodle.org/si/jira.issueviews:issue-xml/' . $issueid . '/' . $issueid . '.xml';
    $content = download_file_content($xmlurl);

        $open = preg_match('/Unresolved<\/resolution>/', $content);

        $matches = array();
    preg_match('/<title>\[' . $issueid . '\]\s+(.*?)<\/title>/', $content, $matches);
    $summary = $matches[1];
    preg_match('/<assignee[^>]*>(.*?)<\/assignee>/', $content, $matches);
    $summary .= ' - Assignee: ' . $matches[1];

    $cache[$issueid] = array($open, $summary);
    return $cache[$issueid];
}

function load_third_party_lib_list() {
    global $CFG;
    $libs = array();
    $xml = simplexml_load_file($CFG->libdir . '/thirdpartylibs.xml');
    foreach ($xml->library as $libobject) {
        $libs[$CFG->libdir . '/' . $libobject->location] = 1;
    }
    return $libs;
}

function recurseFolders($path, $callback, $fileregexp = '/.*/', $exclude = false, $ignorefolders = array()) {
    $files = scandir($path);

    foreach ($files as $file) {
        $filepath = $path .'/'. $file;
        if (strpos($file, '.') === 0) {
                        continue;
        } else if (is_dir($filepath)) {
            if (!in_array($filepath, $ignorefolders)) {
                recurseFolders($filepath, $callback, $fileregexp, $exclude, $ignorefolders);
            }
        } else if ($exclude xor preg_match($fileregexp, $filepath)) {
            call_user_func($callback, $filepath);
        }
    }
}
