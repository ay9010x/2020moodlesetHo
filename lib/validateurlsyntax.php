<?php





function validateUrlSyntax( $urladdr, $options="" ){

            
        if (!preg_match( '/^([sHSEFuPaIpfqr][+?-])*$/', $options ))
    {
        trigger_error("Options attribute malformed", E_USER_ERROR);
    }

            if (strpos( $options, 's') === false) $aOptions['s'] = '?';
    else $aOptions['s'] = substr( $options, strpos( $options, 's') + 1, 1);
        if (strpos( $options, 'H') === false) $aOptions['H'] = '?';
    else $aOptions['H'] = substr( $options, strpos( $options, 'H') + 1, 1);
        if (strpos( $options, 'S') === false) $aOptions['S'] = '?';
    else $aOptions['S'] = substr( $options, strpos( $options, 'S') + 1, 1);
        if (strpos( $options, 'E') === false) $aOptions['E'] = '-';
    else $aOptions['E'] = substr( $options, strpos( $options, 'E') + 1, 1);
        if (strpos( $options, 'F') === false) $aOptions['F'] = '-';
    else $aOptions['F'] = substr( $options, strpos( $options, 'F') + 1, 1);
        if (strpos( $options, 'u') === false) $aOptions['u'] = '?';
    else $aOptions['u'] = substr( $options, strpos( $options, 'u') + 1, 1);
        if (strpos( $options, 'P') === false) $aOptions['P'] = '?';
    else $aOptions['P'] = substr( $options, strpos( $options, 'P') + 1, 1);
        if (strpos( $options, 'a') === false) $aOptions['a'] = '+';
    else $aOptions['a'] = substr( $options, strpos( $options, 'a') + 1, 1);
        if (strpos( $options, 'I') === false) $aOptions['I'] = '?';
    else $aOptions['I'] = substr( $options, strpos( $options, 'I') + 1, 1);
        if (strpos( $options, 'p') === false) $aOptions['p'] = '?';
    else $aOptions['p'] = substr( $options, strpos( $options, 'p') + 1, 1);
        if (strpos( $options, 'f') === false) $aOptions['f'] = '?';
    else $aOptions['f'] = substr( $options, strpos( $options, 'f') + 1, 1);
        if (strpos( $options, 'q') === false) $aOptions['q'] = '?';
    else $aOptions['q'] = substr( $options, strpos( $options, 'q') + 1, 1);
        if (strpos( $options, 'r') === false) $aOptions['r'] = '?';
    else $aOptions['r'] = substr( $options, strpos( $options, 'r') + 1, 1);


        foreach($aOptions as $key => $value)
    {
        if ($value == '-')
        {
            $aOptions[$key] = '{0}';
        }
        if ($value == '+')
        {
            $aOptions[$key] = '';
        }
    }

        

        $alphanum    = '[a-zA-Z0-9]';      $unreserved  = '[a-zA-Z0-9_.!~*' . '\'' . '()-]';
    $escaped     = '(%[0-9a-fA-F]{2})';     $reserved    = '[;/?:@&=+$,]'; 
                               $scheme            = '(';
    if     ($aOptions['H'] === '') { $scheme .= 'http://'; }
    elseif ($aOptions['S'] === '') { $scheme .= 'https://'; }
    elseif ($aOptions['E'] === '') { $scheme .= 'mailto:'; }
    elseif ($aOptions['F'] === '') { $scheme .= 'ftp://'; }
    else
    {
        if ($aOptions['H'] === '?') { $scheme .= '|(http://)'; }
        if ($aOptions['S'] === '?') { $scheme .= '|(https://)'; }
        if ($aOptions['E'] === '?') { $scheme .= '|(mailto:)'; }
        if ($aOptions['F'] === '?') { $scheme .= '|(ftp://)'; }
        $scheme = str_replace('(|', '(', $scheme);     }
    $scheme            .= ')' . $aOptions['s'];
    
                                                  $userinfo          = '((' . $unreserved . '|' . $escaped . '|[;&=+$,]' . ')+(:(' . $unreserved . '|' . $escaped . '|[;:&=+$,]' . ')+)' . $aOptions['P'] . '@)' . $aOptions['u'];

                           $ipaddress         = '((((2(([0-4][0-9])|(5[0-5])))|([01]?[0-9]?[0-9]))\.){3}((2(([0-4][0-9])|(5[0-5])))|([01]?[0-9]?[0-9])))';

                           $domain_tertiary   = '(' . $alphanum . '(([a-zA-Z0-9-]{0,62})' . $alphanum . ')?\.)*';



                           $domain_toplevel   = '([a-zA-Z](([a-zA-Z0-9-]*)[a-zA-Z0-9])?)';


                           if ($aOptions['I'] === '{0}') {               $address       = '(' . $domain_tertiary .  $domain_toplevel . ')';
    } elseif ($aOptions['I'] === '') {          $address       = '(' . $ipaddress . ')';
    } else {                                    $address       = '((' . $ipaddress . ')|(' . $domain_tertiary .  $domain_toplevel . '))';
    }
    $address = $address . $aOptions['a'];

                                                  $port_number       = '(:(([0-5]?[0-9]{1,4})|(6[0-4][0-9]{3})|(65[0-4][0-9]{2})|(655[0-2][0-9])|(6553[0-5])))' . $aOptions['p'];

                           $path              = '(/((;)?(' . $unreserved . '|' . $escaped . '|' . '[:@&=+$,]' . ')+(/)?)*)' . $aOptions['f'];

                           $querystring       = '(\?(' . $reserved . '|' . $unreserved . '|' . $escaped . ')*)' . $aOptions['q'];

                           $fragment          = '(\#(' . $reserved . '|' . $unreserved . '|' . $escaped . ')*)' . $aOptions['r'];


        $regexp = '#^' . $scheme . $userinfo . $address . $port_number . $path . $querystring . $fragment . '$#i';

        
        if (preg_match( $regexp, $urladdr ))
    {
        return true;     }
    else
    {
        return false;     }

} 




function validateEmailSyntax( $emailaddr, $options="" ){

        if (!preg_match( '/^([sHSEFuPaIpfqr][+?-])*$/', $options ))
    {
        trigger_error("Options attribute malformed", E_USER_ERROR);
    }

            if (strpos( $options, 's') === false) $aOptions['s'] = '-';
    else $aOptions['s'] = substr( $options, strpos( $options, 's') + 1, 1);
        if (strpos( $options, 'H') === false) $aOptions['H'] = '-';
    else $aOptions['H'] = substr( $options, strpos( $options, 'H') + 1, 1);
        if (strpos( $options, 'S') === false) $aOptions['S'] = '-';
    else $aOptions['S'] = substr( $options, strpos( $options, 'S') + 1, 1);
        if (strpos( $options, 'E') === false) $aOptions['E'] = '?';
    else $aOptions['E'] = substr( $options, strpos( $options, 'E') + 1, 1);
        if (strpos( $options, 'F') === false) $aOptions['F'] = '-';
    else $aOptions['F'] = substr( $options, strpos( $options, 'F') + 1, 1);
        if (strpos( $options, 'u') === false) $aOptions['u'] = '+';
    else $aOptions['u'] = substr( $options, strpos( $options, 'u') + 1, 1);
        if (strpos( $options, 'P') === false) $aOptions['P'] = '-';
    else $aOptions['P'] = substr( $options, strpos( $options, 'P') + 1, 1);
        if (strpos( $options, 'a') === false) $aOptions['a'] = '+';
    else $aOptions['a'] = substr( $options, strpos( $options, 'a') + 1, 1);
        if (strpos( $options, 'I') === false) $aOptions['I'] = '-';
    else $aOptions['I'] = substr( $options, strpos( $options, 'I') + 1, 1);
        if (strpos( $options, 'p') === false) $aOptions['p'] = '-';
    else $aOptions['p'] = substr( $options, strpos( $options, 'p') + 1, 1);
        if (strpos( $options, 'f') === false) $aOptions['f'] = '-';
    else $aOptions['f'] = substr( $options, strpos( $options, 'f') + 1, 1);
        if (strpos( $options, 'q') === false) $aOptions['q'] = '-';
    else $aOptions['q'] = substr( $options, strpos( $options, 'q') + 1, 1);
        if (strpos( $options, 'r') === false) $aOptions['r'] = '-';
    else $aOptions['r'] = substr( $options, strpos( $options, 'r') + 1, 1);

        $newoptions = '';
    foreach($aOptions as $key => $value)
    {
        $newoptions .= $key . $value;
    }

        
        return validateUrlSyntax( $emailaddr, $newoptions);

} 




function validateFtpSyntax( $ftpaddr, $options="" ){

        if (!preg_match( '/^([sHSEFuPaIpfqr][+?-])*$/', $options ))
    {
        trigger_error("Options attribute malformed", E_USER_ERROR);
    }

            if (strpos( $options, 's') === false) $aOptions['s'] = '?';
    else $aOptions['s'] = substr( $options, strpos( $options, 's') + 1, 1);
        if (strpos( $options, 'H') === false) $aOptions['H'] = '-';
    else $aOptions['H'] = substr( $options, strpos( $options, 'H') + 1, 1);
        if (strpos( $options, 'S') === false) $aOptions['S'] = '-';
    else $aOptions['S'] = substr( $options, strpos( $options, 'S') + 1, 1);
        if (strpos( $options, 'E') === false) $aOptions['E'] = '-';
    else $aOptions['E'] = substr( $options, strpos( $options, 'E') + 1, 1);
        if (strpos( $options, 'F') === false) $aOptions['F'] = '+';
    else $aOptions['F'] = substr( $options, strpos( $options, 'F') + 1, 1);
        if (strpos( $options, 'u') === false) $aOptions['u'] = '?';
    else $aOptions['u'] = substr( $options, strpos( $options, 'u') + 1, 1);
        if (strpos( $options, 'P') === false) $aOptions['P'] = '?';
    else $aOptions['P'] = substr( $options, strpos( $options, 'P') + 1, 1);
        if (strpos( $options, 'a') === false) $aOptions['a'] = '+';
    else $aOptions['a'] = substr( $options, strpos( $options, 'a') + 1, 1);
        if (strpos( $options, 'I') === false) $aOptions['I'] = '?';
    else $aOptions['I'] = substr( $options, strpos( $options, 'I') + 1, 1);
        if (strpos( $options, 'p') === false) $aOptions['p'] = '?';
    else $aOptions['p'] = substr( $options, strpos( $options, 'p') + 1, 1);
        if (strpos( $options, 'f') === false) $aOptions['f'] = '?';
    else $aOptions['f'] = substr( $options, strpos( $options, 'f') + 1, 1);
        if (strpos( $options, 'q') === false) $aOptions['q'] = '-';
    else $aOptions['q'] = substr( $options, strpos( $options, 'q') + 1, 1);
        if (strpos( $options, 'r') === false) $aOptions['r'] = '-';
    else $aOptions['r'] = substr( $options, strpos( $options, 'r') + 1, 1);

        $newoptions = '';
    foreach($aOptions as $key => $value)
    {
        $newoptions .= $key . $value;
    }

        
        return validateUrlSyntax( $ftpaddr, $newoptions);

} 