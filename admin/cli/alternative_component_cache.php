<?php




define('CLI_SCRIPT', true);
define('ABORT_AFTER_CONFIG', true); define('CACHE_DISABLE_ALL', true); define('IGNORE_COMPONENT_CACHE', true);

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/clilib.php');

list($options, $unrecognized) = cli_get_params(
    array(
        'file'    => false,
        'rebuild' => false,
        'print'   => false,
        'help'    => false
    ),
    array(
        'h' => 'help'
    )
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

if (!$options['rebuild'] and !$options['file'] and !$options['print']) {
    $help =
"Create alternative component cache file

Options:
-h, --help            Print out this help
--rebuild             Rebuild \$CFG->alternative_component_cache file
--file=filepath       Save component cache to file
--print               Print component cache file content

Example:
\$ php admin/cli/rebuild_alternative_component_cache.php --rebuild
";

    echo $help;
    exit(0);
}

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

$content = core_component::get_cache_content();

if ($options['print']) {
    echo $content;
    exit(0);
}

if ($options['rebuild']) {
    if (empty($CFG->alternative_component_cache)) {
        fwrite(STDERR, 'config.php does not contain $CFG->alternative_component_cache setting');
        fwrite(STDERR, "\n");
        exit(2);
    }
    $target = $CFG->alternative_component_cache;
} else {
    $target = $options['file'];
}

if (!$target) {
    fwrite(STDERR, "Invalid target file $target");
    fwrite(STDERR, "\n");
    exit(1);
}

$bytes = file_put_contents($target, $content);

if (!$bytes) {
    fwrite(STDERR, "Error writing to $target");
    fwrite(STDERR, "\n");
    exit(1);
}

echo "File $target was updated\n";
exit(0);
