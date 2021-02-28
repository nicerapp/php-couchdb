<?php
require_once (dirname(__FILE__).'/boot.php');

$codeLocation = dirname(__FILE__).'/unitTest.php:';

$calledFromApache = array_key_exists ('HTTP_HOST', $_SERVER);

/* 
-- CONNECT TO A COUCHDB SERVER
*/
$serverSettings = array (
    'http' => 'http://',
    'domain' => 'localhost',
    'port' => 5984,
    'adminUsername' => 'admin',
    'adminPassword' => 'texas.t33'
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
    'cmd' => '_all_dbs'
);
$data = array (
    'cmd' => '_all_dbs'
);
$callResults = $server->makeCall ($call, null, $codeLocation);
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
    'dbName' => 'test_phpcouchdb',
    'createIfNotExists' => true
);
$db = $server->connectToDB ($dbSettings, $codeLocation); // this call will succeed regardless whether or not the database already exists.
if (cdb_processResults ($db, $codeLocation, is_object($db) && $db instanceof couchdb_document)) {
    if ($calledFromApache) {
        echo PHP_EOL.'<h2>Created database "test_phpcouchdb"</h2>'.PHP_EOL;
    } else {
        echo PHP_EOL.'Created database "test_phpcouchdb"'.PHP_EOL;
    }
}

/* 
-- CREATE A DOCUMENT IN DATABASE 'test'
*/
$date = new DateTime('2020-02-28 13:30 +0100');
$docSettings = array (
    'server' => $server,
    'dbName' => $dbSettings['dbName'],
    'id' => "id1",
    'data' => array (
        'field_date' => $date->getTimestamp(),
        'field_string' => 'abc',
        'field_int' => 2,
        'field_float' => 3.5,
        'field_bool' => true
    )
);
$doc = $db->createDoc ($docSettings);
//echo '<pre style="color:darkgreen;">$db->createDoc('; var_dump ($docSettings); echo ');</pre>';    
if (cdb_processResults ($doc, $codeLocation, is_array($doc) && strpos($doc['curl output'][0],'"error":')===false)) {
    if ($calledFromApache) {
        echo PHP_EOL.'<h2>Created document :</h2>'.PHP_EOL;
        echo PHP_EOL.PHP_EOL.'<pre style="color:green;">'.PHP_EOL; var_dump ($doc); echo PHP_EOL.'</pre>'.PHP_EOL.PHP_EOL;
    } else {
        echo PHP_EOL.'Created document'.PHP_EOL; var_dump ($doc); echo PHP_EOL;
    }
} else {
    $doc = $db->getDoc ($docSettings);
    if (!cdb_processResults ($doc, $codeLocation, is_array($doc) && strpos($doc['curl output'][0],'"error":')===false)) {
        if ($calledFromApache) { 
            echo PHP_EOL.'<h2>Could not create or get document :</h2>'.PHP_EOL;
            echo PHP_EOL.PHP_EOL.'<pre style="color:orange">'.PHP_EOL; var_dump ($doc); echo PHP_EOL.'</pre>'.PHP_EOL.PHP_EOL;
        } else {
            echo PHP_EOL.'Could not create or get document :'.PHP_EOL; var_dump ($doc); echo PHP_EOL;
        }
    }   
    //echo '<pre style="color:green;">'; var_dump ($doc); echo '</pre>';    
    $docSettings2 = array (
        'server' => $server,
        'dbName' => $dbSettings['dbName'],
        'id' => "id1",
        '_rev' => $doc['_rev'],
        'data' => array (
            '_id' => "1",
            '_rev' => $doc['_rev'],
            'field_string' => cdb_randomString(10),
            'field_int' => $doc['field_int'] * 2,
            'field_float' => $doc['field_float'] * 2.1,
            'field_bool' => !$doc['field_bool']
        )
    );
    $docUpdated = $db->updateDoc ($docSettings2);
    //echo '<pre style="color:darkgreen;">$db->updateDoc('; var_dump ($docSettings2); echo ');</pre>';    
    if (!cdb_processResults ($docUpdated, $codeLocation, $docUpdated['ok']===true)) {
        if ($calledFromApache) { 
            echo PHP_EOL.'<h2>Could not update document :</h2>'.PHP_EOL;
            echo PHP_EOL.PHP_EOL.'<pre style="color:red">'.PHP_EOL; var_dump ($docUpdated); echo PHP_EOL.'</pre>'.PHP_EOL.PHP_EOL;
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
            echo PHP_EOL.PHP_EOL.'<pre style="color:darkgreen;">'.PHP_EOL; var_dump ($docUpdated2); echo PHP_EOL.'</pre>'.PHP_EOL.PHP_EOL;
        } else {
            echo PHP_EOL.'Updated Document'.PHP_EOL; var_dump ($docUpdated2); echo PHP_EOL;
        }
    }
}

/*
-- ADD SEVERAL DOCUMENTS TO THE DATABASE, FOR USE WITH ->find()
*/
if ($calledFromApache) {
    echo PHP_EOL.'<h2>Adding 3 documents for use with ->find()</h2>'.PHP_EOL.PHP_EOL;
} else {
    echo PHP_EOL.'Adding 3 documents for use with ->find()'.PHP_EOL.PHP_EOL;
}
$date = new DateTime('2018-04-10 14:00 +0100');
$docSettings = array (
    'server' => $server,
    'dbName' => $dbSettings['dbName'],
    'id' => "id3",
    'data' => array (
        'field_date' => $date->getTimestamp(),
        'field_string' => 'abc',
        'field_int' => 100,
        'field_float' => 18.04,
        'field_bool' => true
    )
);
$doc = $db->createDoc ($docSettings);
if (!cdb_processResults ($doc, $codeLocation, is_array($doc) && array_key_exists('status',$doc) && $doc['status']!=='FAILED')) {
    if ($calledFromApache) { 
        echo PHP_EOL.'<h2>Could not create document :</h2>'.PHP_EOL;
        echo PHP_EOL.PHP_EOL.'<pre style="color:darkred">'.PHP_EOL; var_dump ($doc); echo PHP_EOL.'</pre>'.PHP_EOL.PHP_EOL;
        if ($doc['curl output'][0]==='{"error":"conflict","reason":"Document update conflict."}') {
            echo PHP_EOL.PHP_EOL.'<h3 style="color:red">This error means that the document already exists.</h3>'.PHP_EOL.PHP_EOL;
        }
    } else {
        echo PHP_EOL.'Could not create or get document :'.PHP_EOL; var_dump ($doc); echo PHP_EOL;
        if ($doc['curl output'][0]==='{"error":"conflict","reason":"Document update conflict."}') {
            echo PHP_EOL.PHP_EOL.'This error means that the document already exists.>'.PHP_EOL.PHP_EOL;
        }
    }
} 


$date = new DateTime('2019-05-20 12:00 +0100');
$docSettings = array (
    'server' => $server,
    'dbName' => $dbSettings['dbName'],
    'id' => "id2",
    'data' => array (
        'field_date' => $date->getTimestamp(),
        'field_string' => 'defg',
        'field_int' => 150,
        'field_float' => 19.05,
        'field_bool' => false
    )
);
$doc = $db->createDoc ($docSettings);
//echo '<pre style="color:darkgreen;">$db->createDoc('; var_dump ($docSettings); echo ');</pre>';    
if (!cdb_processResults ($doc, $codeLocation, is_array($doc) && array_key_exists('status',$doc) && $doc['status']!=='FAILED')) {
    if ($calledFromApache) { 
        echo PHP_EOL.'<h2>Could not create document :</h2>'.PHP_EOL;
        echo PHP_EOL.PHP_EOL.'<pre style="color:darkred">'.PHP_EOL; var_dump ($doc); echo PHP_EOL.'</pre>'.PHP_EOL.PHP_EOL;
        if ($doc['curl output'][0]==='{"error":"conflict","reason":"Document update conflict."}') {
            echo PHP_EOL.PHP_EOL.'<h3 style="color:red">This error means that the document already exists.</h3>'.PHP_EOL.PHP_EOL;
        }
    } else {
        echo PHP_EOL.'Could not create or get document :'.PHP_EOL; var_dump ($doc); echo PHP_EOL;
        if ($doc['curl output'][0]==='{"error":"conflict","reason":"Document update conflict."}') {
            echo PHP_EOL.PHP_EOL.'This error means that the document already exists.>'.PHP_EOL.PHP_EOL;
        }
    }
} 

$date = new DateTime('2020-02-05 16:00 +0100');
$docSettings = array (
    'server' => $server,
    'dbName' => $dbSettings['dbName'],
    'id' => "id3",
    'data' => array (
        'field_date' => $date->getTimestamp(),
        'field_string' => cdb_randomString(10),
        'field_int' => 200,
        'field_float' => 20.02,
        'field_bool' => true
    )
);
$doc = $db->createDoc ($docSettings);
//echo '<pre style="color:darkgreen;">$db->createDoc('; var_dump ($docSettings); echo ');</pre>';    
if (!cdb_processResults ($doc, $codeLocation, is_array($doc) && array_key_exists('status',$doc) && $doc['status']!=='FAILED')) {
    if ($calledFromApache) { 
        echo PHP_EOL.'<h2>Could not create document :</h2>'.PHP_EOL;
        echo PHP_EOL.PHP_EOL.'<pre style="color:darkred">'.PHP_EOL; var_dump ($doc); echo PHP_EOL.'</pre>'.PHP_EOL.PHP_EOL;
        if ($doc['curl output'][0]==='{"error":"conflict","reason":"Document update conflict."}') {
            echo PHP_EOL.PHP_EOL.'<h3 style="color:red">This error means that the document already exists.</h3>'.PHP_EOL.PHP_EOL;
        }
    } else {
        echo PHP_EOL.'Could not create or get document :'.PHP_EOL; var_dump ($doc); echo PHP_EOL;
        if ($doc['curl output'][0]==='{"error":"conflict","reason":"Document update conflict."}') {
            echo PHP_EOL.PHP_EOL.'This error means that the document already exists.>'.PHP_EOL.PHP_EOL;
        }
    }
} 




/*
-- FIND A DOCUMENT IN DATABASE 'test' WITH field_string='abc'
*/
$findCommand = array (
    'server' => $server,
    'dbName' => $dbSettings['dbName'],
    '_find' => array(
        'selector' => array(
            'field_string' => 'abc'
        ),
        'fields' => array(
            'field_date', 'field_string', 'field_int', 'field_float', 'field_bool'
        )
    )
);
$doc = $db->find ($findCommand);
if (cdb_processResults ($doc, $codeLocation, is_array($doc))) {
    foreach ($doc['docs'] as $idx => $d) {
        $date = new DateTime();
        $date->setTimestamp($d['field_date']);
        $d['field_date'] = $date->format('Y-m-d\TH:i:s\Z');
    }
    if ($calledFromApache) {
        echo PHP_EOL.'<h2>Find document with field_string==="abc"</h2>'.PHP_EOL;
        echo PHP_EOL.PHP_EOL.'<pre style="color:orange">'.PHP_EOL; var_dump ($doc); echo PHP_EOL.'</pre>'.PHP_EOL.PHP_EOL;
    } else {
        echo PHP_EOL.'Find document with field_string==="abc"'.PHP_EOL;
        echo PHP_EOL.PHP_EOL; var_dump ($doc); echo PHP_EOL.PHP_EOL;
    }
}

/*
-- FIND ALL DOCUMENTS IN DATABASE 'test' WITH field_date < '2020-01-01 12:00'
*/
$date = new DateTime('2020-01-01 12:00 +0100');
$findCommand = array (
    'server' => $server,
    'dbName' => $dbSettings['dbName'],
    '_find' => array(
        'selector' => array(
            'field_date' => array(
                '$lt' => $date->getTimestamp()
            )
        ),
        'fields' => array(
            'field_date', 'field_string', 'field_int', 'field_float', 'field_bool'
        )
    )
);
$doc = $db->find ($findCommand);
if (cdb_processResults ($doc, $codeLocation, is_array($doc))) {
    foreach ($doc['docs'] as $idx => $d) {
        $date = new DateTime();
        $date->setTimestamp($d['field_date']);
        $doc['docs'][$idx]['field_date'] = $date->format('Y-m-d\TH:i:s\Z');
    }
    if ($calledFromApache) {
        echo PHP_EOL.'<h2>Find all documents with field_date<"2020-01-01 12:00 +0100"</h2>'.PHP_EOL;
        echo PHP_EOL.PHP_EOL.'<pre style="color:orange">'.PHP_EOL; var_dump ($doc); echo PHP_EOL.'</pre>'.PHP_EOL.PHP_EOL;
    } else {
        echo PHP_EOL.'Find all documents with field_date<"2020-01-01 12:00 +0100"'.PHP_EOL;
        echo PHP_EOL.PHP_EOL; var_dump ($doc); echo PHP_EOL.PHP_EOL;
    }
}
?>
