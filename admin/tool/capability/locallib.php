<?php




function tool_capability_calculate_role_data($capability, array $roles) {
    global $DB;

    $systemcontext = context_system::instance();
    $roleids = array_keys($roles);

        $params = array($capability);
    list($sqlroletest, $roleparams) = $DB->get_in_or_equal($roleids);
    $params = array_merge($params, $roleparams);
    $sqlroletest = 'AND roleid ' . $sqlroletest;

            $sql = 'SELECT id, roleid, contextid, permission
              FROM {role_capabilities}
             WHERE capability = ? '.$sqlroletest;
    $rolecaps = $DB->get_records_sql($sql, $params);

            $sql = 'SELECT DISTINCT con.path, 1
              FROM {context} con
              JOIN {role_capabilities} rc ON rc.contextid = con.id
             WHERE capability = ? '.$sqlroletest;
    $relevantpaths = $DB->get_records_sql_menu($sql, $params);
    $requiredcontexts = array($systemcontext->id);
    foreach ($relevantpaths as $path => $notused) {
        $requiredcontexts = array_merge($requiredcontexts, explode('/', trim($path, '/')));
    }
    $requiredcontexts = array_unique($requiredcontexts);

        list($sqlcontexttest, $contextparams) = $DB->get_in_or_equal($requiredcontexts);
    $contexts = get_sorted_contexts('ctx.id ' . $sqlcontexttest, $contextparams);

        foreach ($contexts as $conid => $con) {
        $contexts[$conid]->children = array();
        $contexts[$conid]->rolecapabilities = array();
    }

        foreach ($contexts as $conid => $con) {
        $context = context::instance_by_id($conid);
        try {
            $parentcontext = $context->get_parent_context();
            if ($parentcontext) {                 $contexts[$parentcontext->id]->children[] = $conid;
            }
        } catch (dml_missing_record_exception $e) {
                                    continue;
        }
    }

        foreach ($rolecaps as $rolecap) {
        $contexts[$rolecap->contextid]->rolecapabilities[$rolecap->roleid] = $rolecap->permission;
    }

        foreach ($roleids as $roleid) {
        if (!isset($contexts[$systemcontext->id]->rolecapabilities[$roleid])) {
            $contexts[$systemcontext->id]->rolecapabilities[$roleid] = CAP_INHERIT;
        }
    }

    return $contexts;
}