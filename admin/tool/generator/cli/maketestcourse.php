<?php



define('CLI_SCRIPT', true);
define('NO_OUTPUT_BUFFERING', true);

require(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->libdir. '/clilib.php');

list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false,
        'shortname' => false,
        'fullname' => false,
        'summary' => false,
        'size' => false,
        'fixeddataset' => false,
        'filesizelimit' => false,
        'bypasscheck' => false,
        'quiet' => false
    ),
    array(
        'h' => 'help'
    )
);

if (!empty($options['help']) || empty($options['shortname']) || empty($options['size'])) {
    echo "
Utility to create standard test course. (Also available in GUI interface.)

Not for use on live sites; only normally works if debugging is set to DEVELOPER
level.

Options:
--shortname      Shortname of course to create (required)
--fullname       Fullname of course to create (optional)
--summary        Course summary, in double quotes (optional)
--size           Size of course to create XS, S, M, L, XL, or XXL (required)
--fixeddataset   Use a fixed data set instead of randomly generated data
--filesizelimit  Limits the size of the generated files to the specified bytes
--bypasscheck    Bypasses the developer-mode check (be careful!)
--quiet          Do not show any output

-h, --help     Print out this help

Example from Moodle root directory:
\$ php admin/tool/generator/cli/maketestcourse.php --shortname=SIZE_S --size=S
";
        exit(empty($options['help']) ? 1 : 0);
}

if (empty($options['bypasscheck']) && !debugging('', DEBUG_DEVELOPER)) {
    cli_error(get_string('error_notdebugging', 'tool_generator'));
}

$shortname = $options['shortname'];
$fullname = $options['fullname'];
$summary = $options['summary'];
$sizename = $options['size'];
$fixeddataset = $options['fixeddataset'];
$filesizelimit = $options['filesizelimit'];

try {
    $size = tool_generator_course_backend::size_for_name($sizename);
} catch (coding_exception $e) {
    cli_error("Invalid size ($sizename). Use --help for help.");
}

if ($error = tool_generator_course_backend::check_shortname_available($shortname)) {
    cli_error($error);
}

\core\session\manager::set_user(get_admin());

$backend = new tool_generator_course_backend(
    $shortname,
    $size,
    $fixeddataset,
    $filesizelimit,
    empty($options['quiet']),
    $fullname,
    $summary,
    FORMAT_HTML
);
$id = $backend->make();

if (empty($options['quiet'])) {
    echo PHP_EOL.'Generated course: '.course_get_url($id).PHP_EOL;
}
