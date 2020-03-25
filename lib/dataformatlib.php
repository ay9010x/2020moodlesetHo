<?php




function download_as_dataformat($filename, $dataformat, $columns, $iterator, $callback = null) {

    if (ob_get_length()) {
        throw new coding_exception("Output can not be buffered before calling download_as_dataformat");
    }

    $classname = 'dataformat_' . $dataformat . '\writer';
    if (!class_exists($classname)) {
        throw new coding_exception("Unable to locate dataformat/$type/classes/writer.php");
    }
    $format = new $classname;

        set_time_limit(0);

        \core\session\manager::write_close();

    $format->set_filename($filename);
    $format->send_http_headers();
    $format->write_header($columns);
    $c = 0;
    foreach ($iterator as $row) {
        if ($callback) {
            $row = $callback($row);
        }
        if ($row === null) {
            continue;
        }
        $format->write_record($row, $c++);
    }
    $format->write_footer($columns);
}

