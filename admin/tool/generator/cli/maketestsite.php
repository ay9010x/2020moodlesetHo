<?php



define('CLI_SCRIPT', true);
define('NO_OUTPUT_BUFFERING', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir. '/clilib.php');

list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false,
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

$sitesizes = '* ' . implode(PHP_EOL . '* ', tool_generator_site_backend::get_size_choices());

if (!empty($options['help']) || empty($options['size'])) {
    echo "
Utility to generate a standard test site data set.

Not for use on live sites; only normally works if debugging is set to DEVELOPER
level.

Consider that, depending on the size you select, this CLI tool can really generate a lot of data, aproximated sizes:

$sitesizes

Options:
--size           Size of the generated site, this value affects the number of courses and their size. Accepted values: XS, S, M, L, XL, or XXL (required)
--fixeddataset   Use a fixed data set instead of randomly generated data
--filesizelimit  Limits the size of the generated files to the specified bytes
--bypasscheck    Bypasses the developer-mode check (be careful!)
--quiet          Do not show any output

-h, --help     Print out this help

Example from Moodle root directory:
\$ php admin/tool/generator/cli/maketestsite.php --size=S
";
        exit(empty($options['help']) ? 1 : 0);
}

if (empty($options['bypasscheck']) && !$CFG->debugdeveloper) {
    cli_error(get_string('error_notdebugging', 'tool_generator'));
}

$sizename = $options['size'];
$fixeddataset = $options['fixeddataset'];
$filesizelimit = $options['filesizelimit'];

try {
    $size = tool_generator_site_backend::size_for_name($sizename);
} catch (coding_exception $e) {
    cli_error("Invalid size ($sizename). Use --help for help.");
}

\core\session\manager::set_user(get_admin());

$backend = new tool_generator_site_backend($size, $options['bypasscheck'], $fixeddataset, $filesizelimit, empty($options['quiet']));
$backend->make();
