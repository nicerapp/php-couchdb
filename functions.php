<?php 
function cdb_debug ($dbg, $fromCodeLocation='cdb_debug') {
    /*
    if (is_array($dbg) && array_key_exists('result', $dbg) && $dbg['result']===0) {
        return $dbg;    
    } else {
        if (array_key_exists('HTTP_HOST', $_SERVER)) {
            echo PHP_EOL.'<br/>'.$fromCodeLocation.'(...) :<br/>'.PHP_EOL;
            echo '<pre>'; 
            var_dump ($dbg); 
            echo '</pre>';
        }
        return $dbg;
    }*/
    return $dbg;
}

function cdb_processResults ($callResults, $fromCodeLocation='cdb_processResults', $scriptAllowedToContinue, $printErrors = false) {
    $codeLocation = 'cdb_processResults';
    if ($fromCodeLocation!==$codeLocation) {
        $actualCodeLocation = $fromCodeLocation.'(...)--->'.$codeLocation;
    } else {
        $actualCodeLocation = $codeLocation;
    };
    
    $calledFromApache = array_key_exists ('HTTP_HOST', $_SERVER);

    if ($printErrors) {
        if (!$scriptAllowedToContinue) { 
            if ($calledFromApache) {
                echo PHP_EOL.PHP_EOL.'<pre>'; var_dump ($callResults); echo '</pre>'.PHP_EOL.PHP_EOL; 
                trigger_error (json_encode($callResults, JSON_PRETTY_PRINT), E_USER_WARNING);
            } else {
                echo PHP_EOL.PHP_EOL; var_dump ($callResults); echo PHP_EOL.PHP_EOL;
            }
        }
    };
    
    return $scriptAllowedToContinue;
}



function cdb_exec ($cmd, $fromCodeLocation='cdb_exec') {
    exec ($cmd, $output, $result);
    $r = array (
        'cmd' => $cmd,
        'output' => $output,
        'result' => $result,
        'fromCodeLocation' => $fromCodeLocation
    );
    //$r1 = cdb_debug ($r, $fromCodeLocation);
    return $r;
}

function randomString ($length) {
    $seed = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $r = '';
    for ($i=0; $i<$length; $i++) {
        $r .= substr ($seed, rand(0,strlen($seed)), 1);
    };
    return $r;
}
?>
