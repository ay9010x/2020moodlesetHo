<?php




function quiz_statistics_attempts_sql($quizid, $groupstudents, $whichattempts = QUIZ_GRADEAVERAGE, $includeungraded = false) {
    global $DB;

    $fromqa = '{quiz_attempts} quiza ';

    $whereqa = 'quiza.quiz = :quizid AND quiza.preview = 0 AND quiza.state = :quizstatefinished';
    $qaparams = array('quizid' => (int)$quizid, 'quizstatefinished' => quiz_attempt::FINISHED);

    if ($groupstudents) {
        ksort($groupstudents);
        list($grpsql, $grpparams) = $DB->get_in_or_equal(array_keys($groupstudents),
                SQL_PARAMS_NAMED, 'statsuser');
        list($grpsql, $grpparams) = quiz_statistics_renumber_placeholders(
                $grpsql, $grpparams, 'statsuser');
        $whereqa .= " AND quiza.userid $grpsql";
        $qaparams += $grpparams;
    }

    $whichattemptsql = quiz_report_grade_method_sql($whichattempts);
    if ($whichattemptsql) {
        $whereqa .= ' AND '.$whichattemptsql;
    }

    if (!$includeungraded) {
        $whereqa .= ' AND quiza.sumgrades IS NOT NULL';
    }

    return array($fromqa, $whereqa, $qaparams);
}


function quiz_statistics_renumber_placeholders($sql, $params, $paramprefix) {
    $basenumber = null;
    $newparams = array();
    $newsql = preg_replace_callback('~:' . preg_quote($paramprefix, '~') . '(\d+)\b~',
            function($match) use ($paramprefix, $params, &$newparams, &$basenumber) {
                if ($basenumber === null) {
                    $basenumber = $match[1] - 1;
                }
                $oldname = $paramprefix . $match[1];
                $newname = $paramprefix . ($match[1] - $basenumber);
                $newparams[$newname] = $params[$oldname];
                return ':' . $newname;
            }, $sql);

    return array($newsql, $newparams);
}


function quiz_statistics_qubaids_condition($quizid, $groupstudents, $whichattempts = QUIZ_GRADEAVERAGE, $includeungraded = false) {
    list($fromqa, $whereqa, $qaparams) = quiz_statistics_attempts_sql($quizid, $groupstudents, $whichattempts, $includeungraded);
    return new qubaid_join($fromqa, 'quiza.uniqueid', $whereqa, $qaparams);
}


function quiz_statistics_graph_get_new_colour() {
    static $colourindex = -1;
    $colours = array('red', 'green', 'yellow', 'orange', 'purple', 'black',
        'maroon', 'blue', 'ltgreen', 'navy', 'ltred', 'ltltgreen', 'ltltorange',
        'olive', 'gray', 'ltltred', 'ltorange', 'lime', 'ltblue', 'ltltblue');

    $colourindex = ($colourindex + 1) % count($colours);

    return $colours[$colourindex];
}
