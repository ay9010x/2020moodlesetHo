<?php






function cli_write($text, $stream=STDOUT) {
    fwrite($stream, $text);
}


function cli_writeln($text, $stream=STDOUT) {
    cli_write($text.PHP_EOL, $stream);
}


function cli_input($prompt, $default='', array $options=null, $casesensitiveoptions=false) {
    cli_writeln($prompt);
    cli_write(': ');
    $input = fread(STDIN, 2048);
    $input = trim($input);
    if ($input === '') {
        $input = $default;
    }
    if ($options) {
        if (!$casesensitiveoptions) {
            $input = strtolower($input);
        }
        if (!in_array($input, $options)) {
            cli_writeln(get_string('cliincorrectvalueretry', 'admin'));
            return cli_input($prompt, $default, $options, $casesensitiveoptions);
        }
    }
    return $input;
}


function cli_get_params(array $longoptions, array $shortmapping=null) {
    $shortmapping = (array)$shortmapping;
    $options      = array();
    $unrecognized = array();

    if (empty($_SERVER['argv'])) {
                return array($options, $unrecognized);
    }
    $rawoptions = $_SERVER['argv'];

        if (($key = array_search('--', $rawoptions)) !== false) {
        $rawoptions = array_slice($rawoptions, 0, $key);
    }

        unset($rawoptions[0]);
    foreach ($rawoptions as $raw) {
        if (substr($raw, 0, 2) === '--') {
            $value = substr($raw, 2);
            $parts = explode('=', $value);
            if (count($parts) == 1) {
                $key   = reset($parts);
                $value = true;
            } else {
                $key = array_shift($parts);
                $value = implode('=', $parts);
            }
            if (array_key_exists($key, $longoptions)) {
                $options[$key] = $value;
            } else {
                $unrecognized[] = $raw;
            }

        } else if (substr($raw, 0, 1) === '-') {
            $value = substr($raw, 1);
            $parts = explode('=', $value);
            if (count($parts) == 1) {
                $key   = reset($parts);
                $value = true;
            } else {
                $key = array_shift($parts);
                $value = implode('=', $parts);
            }
            if (array_key_exists($key, $shortmapping)) {
                $options[$shortmapping[$key]] = $value;
            } else {
                $unrecognized[] = $raw;
            }
        } else {
            $unrecognized[] = $raw;
            continue;
        }
    }
        foreach ($longoptions as $key=>$default) {
        if (!array_key_exists($key, $options)) {
            $options[$key] = $default;
        }
    }
        return array($options, $unrecognized);
}


function cli_separator($return=false) {
    $separator = str_repeat('-', 79).PHP_EOL;
    if ($return) {
        return $separator;
    } else {
        cli_write($separator);
    }
}


function cli_heading($string, $return=false) {
    $string = "== $string ==".PHP_EOL;
    if ($return) {
        return $string;
    } else {
        cli_write($string);
    }
}


function cli_problem($text) {
    cli_writeln($text, STDERR);
}


function cli_error($text, $errorcode=1) {
    cli_writeln($text.PHP_EOL, STDERR);
    die($errorcode);
}


function cli_logo($padding=2, $return=false) {

    $lines = array(
        '                               .-..-.       ',
        ' _____                         | || |       ',
        '/____/-.---_  .---.  .---.  .-.| || | .---. ',
        '| |  _   _  |/  _  \\/  _  \\/  _  || |/  __ \\',
        '* | | | | | || |_| || |_| || |_| || || |___/',
        '  |_| |_| |_|\\_____/\\_____/\\_____||_|\\_____)',
    );

    $logo = '';

    foreach ($lines as $line) {
        $logo .= str_repeat(' ', $padding);
        $logo .= $line;
        $logo .= PHP_EOL;
    }

    if ($return) {
        return $logo;
    } else {
        cli_write($logo);
    }
}
