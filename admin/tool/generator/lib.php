<?php



defined('MOODLE_INTERNAL') || die();


function tool_generator_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {

        if (!defined('CLI_SCRIPT') && !is_siteadmin()) {
        die;
    }

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        send_file_not_found();
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'tool_generator', $filearea, $args[0], '/', $args[1]);

        \core\session\manager::write_close();
    send_stored_file($file, 0, 0, true);
}

