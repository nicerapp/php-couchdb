<?php
require_once (dirname(__FILE__).'/boot.php');

$codeLocation = dirname(__FILE__).'/unitTest.php:';

$calledFromApache = array_key_exists ('HTTP_HOST', $_SERVER);

/* 
-- CONNECT TO A COUCHDB SERVER
*/
$serverSettings = array (
    'protocol' => 'http',
    'server' => 'localhost',
    'port' => 5984,
    'user' => 'admin',
    'password' => 'texas.t33'
);
$server = new couchdb_server ($serverSettings, $codeLocation);
if (!cdb_processResults ($server, $codeLocation, is_object($server) && !is_null($server->address))) {
    if ($calledFromApache) {
        echo PHP_EOL.'<br/><h2>Cannot connect to server, invalid $serverSettings</h2><br/><pre>'.PHP_EOL; var_dump ($serverSettings); echo '</pre>';
    } else {
        echo PHP_EOL.'Cannot connect to server, invalid $serverSettings'.PHP_EOL;
        var_dump ($serverSettings);
    }
    die();
}


/* 
-- LIST ALL DATABASES
*/
$call = array (
    'server' => $server,
    'command' => '_all_dbs'
);
$callResults = $server->makeCall ($call, $codeLocation);
if (is_string($callResults)) {
    echo PHP_EOL.PHP_EOL.'<pre>'.PHP_EOL; var_dump ($callResults); echo PHP_EOL.'</pre>'.PHP_EOL.PHP_EOL;
} else if (cdb_processResults ($callResults, $codeLocation,
    !array_key_exists('status', $callResults)
    || $callResults['status']!=='FAILED'
)) {
    // call succeeded
    if ($calledFromApache) { 
        echo PHP_EOL.'<h2>Listing all databases:</h2>'.PHP_EOL;
        echo PHP_EOL.PHP_EOL.'<pre>'.PHP_EOL; var_dump ($callResults); echo PHP_EOL.'</pre>'.PHP_EOL.PHP_EOL;
    } else {
        echo PHP_EOL.'Listing all databases:'.PHP_EOL; var_dump ($callResults); echo PHP_EOL;
    }
};

/* 
-- CREATE A DATABASE NAMED 'test'
*/
$dbSettings = array (
    'server' => $server,
    'dbName' => 'test',
    'createIfNotExists' => true
);
$db = $server->connectToDB ($dbSettings, $codeLocation); // this call will succeed regardless whether or not the database already exists.
cdb_processResults ($db, $codeLocation, is_object($db) && $db instanceof couchdb_document);

/* 
-- CREATE A DOCUMENT IN DATABASE 'test'
*/
$docSettings = array (
    'server' => $server,
    'dbName' => $dbSettings['dbName'],
    'id' => "1",
    'data' => array (
        'field_string' => 'abc',
        'field_int' => 2,
        'field_float' => 3.5,
        'field_bool' => true
    )
);
$doc = $db->createDoc ($docSettings);
if (!cdb_processResults ($doc, $codeLocation, is_array($doc) && strpos($doc['curl output'][0],'"error":')===false)) {
    $doc = $db->getDoc ($docSettings);
    if (!cdb_processResults ($doc, $codeLocation, is_array($doc) && strpos($doc['curl output'][0],'"error":')===false)) {
        if ($calledFromApache) { 
            echo PHP_EOL.'<h2>Could not create or get document :</h2>'.PHP_EOL;
            echo PHP_EOL.PHP_EOL.'<pre>'.PHP_EOL; var_dump ($doc); echo PHP_EOL.'</pre>'.PHP_EOL.PHP_EOL;
        } else {
            echo PHP_EOL.'Could not create or get document :'.PHP_EOL; var_dump ($doc); echo PHP_EOL;
        }
    }   
}
echo '<pre style="color:green;">'; var_dump ($doc); echo '</pre>';    
$docSettings2 = array (
    'server' => $server,
    'dbName' => $dbSettings['dbName'],
    'id' => "1",
    '_rev' => $doc['_rev'],
    'data' => array (
        '_id' => "1",
        '_rev' => $doc['_rev'],
        'field_string' => randomString(10),
        'field_int' => $doc['field_int'] * 2,
        'field_float' => $doc['field_float'] * 2.1,
        'field_bool' => !$doc['field_bool']
    )
);
$docUpdated = $db->updateDoc ($docSettings2);
echo '<pre style="color:red;">'; var_dump ($docUpdated); echo '</pre>';    
if (!cdb_processResults ($docUpdated, $codeLocation, $docUpdated['ok']===true)) {
    if ($calledFromApache) { 
        echo PHP_EOL.'<h2>Could not update document :</h2>'.PHP_EOL;
        echo PHP_EOL.PHP_EOL.'<pre>'.PHP_EOL; var_dump ($docUpdated); echo PHP_EOL.'</pre>'.PHP_EOL.PHP_EOL;
    } else {
        echo PHP_EOL.'Could not update document :'.PHP_EOL; var_dump ($docUpdated); echo PHP_EOL;
    }
} else {
    $docSettings3 = $docSettings2;
    $docSettings3['_rev'] = $docUpdated['_rev'];
    $docSettings3['data']['_rev'] = $docUpdated['_rev'];
    $docUpdated2 = $db->getDoc ($docSettings3);
    if ($calledFromApache) {
        echo PHP_EOL.'<h2>Updated document :</h2>'.PHP_EOL;
        echo PHP_EOL.PHP_EOL.'<pre>'.PHP_EOL; var_dump ($docUpdated2); echo PHP_EOL.'</pre>'.PHP_EOL.PHP_EOL;
    } else {
        echo PHP_EOL.'Updated Document'.PHP_EOL; var_dump ($docUpdated2); echo PHP_EOL;
    }
}

?>
