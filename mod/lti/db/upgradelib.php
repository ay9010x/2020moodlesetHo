<?php




defined('MOODLE_INTERNAL') || die();


function mod_lti_upgrade_custom_separator() {
    global $DB;

        $params = array('semicolon' => ';', 'likecr' => "%\r%", 'likelf' => "%\n%", 'lf' => "\n");

        $notlikecr = $DB->sql_like('value', ':likecr', true, true, true);
    $notlikelf = $DB->sql_like('value', ':likelf', true, true, true);

        $sql = 'UPDATE {lti_types_config} ' .
           'SET value = REPLACE(value, :semicolon, :lf) ' .
           'WHERE (name = \'customparameters\') AND (' . $notlikecr . ') AND (' . $notlikelf . ')';
    $DB->execute($sql, $params);

        $notlikecr = $DB->sql_like('instructorcustomparameters', ':likecr', true, true, true);
    $notlikelf = $DB->sql_like('instructorcustomparameters', ':likelf', true, true, true);

        $sql = 'UPDATE {lti} ' .
           'SET instructorcustomparameters = REPLACE(instructorcustomparameters, :semicolon, :lf) ' .
           'WHERE (instructorcustomparameters IS NOT NULL) AND (' . $notlikecr . ') AND (' . $notlikelf . ')';
    $DB->execute($sql, $params);
}
