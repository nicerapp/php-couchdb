<?php 
require_once (dirname(__FILE__).'/boot.php');

class couchdb_database {
    
    public $settings = null;
    
    public function __construct ($dbSettings=null, $fromCodeLocation='couchdb_database->__construct') {
        $this->settings = $dbSettings; // checked by couchdb_server->checkDBsettings() after a call to couchdb_server->connectToDB($dbSettings)
        
    }
    
    public function createDoc ($docSettings=null, $fromCodeLocation='couchdb_database->createDoc') {
        $codeLocation = 'couchdb_database->createDoc';
        if ($fromCodeLocation!==$codeLocation) {
            $actualCodeLocation = $fromCodeLocation.'(...)--->'.$codeLocation;
        } else {
            $actualCodeLocation = $codeLocation;
        };
        
        $cmd = 'curl -s -X PUT -m 5 '
            .$docSettings['server']->address
            .$docSettings['dbName'].'/'
            .$docSettings['id']
            .' -d \''.json_encode($docSettings['data']).'\'';
        $ca = cdb_exec ($cmd, $actualCodeLocation); // $ca = $connectionAttempt
        if (
            $ca['result']!==0
            || strpos($ca['output'][0],'"error":')!==false
        ) {
            $r = array (
                'fromCodeLocation' => $actualCodeLocation,
                'status' => 'FAILED',
                'errorMessage' => 'invalid $callSettings',
                'curl result' => $ca['result'],
                'curl output' => $ca['output']
            ); $r1 = cdb_debug ($r, $actualCodeLocation);
            return $r1;
        } else {
            return json_decode($ca['output'][0], true);
        }
        
        $doc = new couchdb_document ($docSettings);
        return $doc;    
    }
    
    public function getDoc ($docSettings=null, $fromCodeLocation='couchdb_database->createDoc') {
        $codeLocation = 'couchdb_database->getDoc';
        if ($fromCodeLocation!==$codeLocation) {
            $actualCodeLocation = $fromCodeLocation.'(...)--->'.$codeLocation;
        } else {
            $actualCodeLocation = $codeLocation;
        };
        
        $cmd = 'curl -s -X GET -m 5 '
            .$docSettings['server']->address
            .$docSettings['dbName'].'/'
            .$docSettings['id'].' -d \'{"id":'.$docSettings['id'].'}\'';
        $ca = cdb_exec ($cmd, $actualCodeLocation); // $ca = $connectionAttempt
        if (
            $ca['result']!==0
            || strpos($ca['output'][0],'"error":')!==false
        ) {
            $r = array (
                'fromCodeLocation' => $actualCodeLocation,
                'status' => 'FAILED',
                'errorMessage' => 'invalid $callSettings',
                'curl result' => $ca['result'],
                'curl output' => $ca['output']
            ); $r1 = cdb_debug ($r, $actualCodeLocation);
            return $r1;
        } else {
            return json_decode($ca['output'][0], true);
        }
        
        $doc = new couchdb_document ($docSettings);
        return $doc;    
    }

    
    
    public function updateDoc ($docSettings=null, $fromCodeLocation='couchdb_database->createDoc') {
        $codeLocation = 'couchdb_database->updateDoc';
        if ($fromCodeLocation!==$codeLocation) {
            $actualCodeLocation = $fromCodeLocation.'(...)--->'.$codeLocation;
        } else {
            $actualCodeLocation = $codeLocation;
        };
        
        $cmd = 'curl -s -X PUT -m 5 '
            .$docSettings['server']->address
            .$docSettings['dbName'].'/'
            .$docSettings['id']
            .' -d \''.json_encode($docSettings['data']).'\'';
        $ca = cdb_exec ($cmd, $actualCodeLocation); // $ca = $connectionAttempt
        if (
            $ca['result']!==0
            || strpos($ca['output'][0],'"error":')!==false
        ) {
            $r = array (
                'fromCodeLocation' => $actualCodeLocation,
                'status' => 'FAILED',
                'errorMessage' => 'invalid $callSettings',
                'curl result' => $ca['result'],
                'curl output' => $ca['output']
            ); $r1 = cdb_debug ($r, $actualCodeLocation);
            return $r1;
        } else {
            return json_decode($ca['output'][0], true);
        }
        
        $doc = new couchdb_document ($docSettings);
        return $doc;    
    }
    
}
?>
