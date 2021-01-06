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
//var_dump ($docSettings);// die();

        $data = $docSettings['data'];
        $data = json_encode($data);/*str_replace('!', '\!', $data);
        $data = str_replace('(', '\(', $data);
        $data = str_replace(')', '\)', $data);*/

        if (array_key_exists('data', $docSettings) && is_string($docSettings['data']) && $docSettings['data']!=='') {
            $cmd = 'curl -s -k -X PUT -m 5 '
                .$docSettings['server']->address
                .$docSettings['dbName'].'/'
                .$docSettings['id']
                .' -d \''.$data.'\'';
                echo json_encode(file_put_contents ('/home/rene/data1/htdocs/nicer.app/t.sh', $cmd))    ;
        } elseif (array_key_exists('dataFilepath', $docSettings) && is_string($docSettings['dataFilepath']) && $docSettings['dataFilepath']!=='') {
            $cmd = 'curl -s -k -X PUT -m 5 '
                .$docSettings['server']->address
                .$docSettings['dbName'].'/'
                .$docSettings['id']
                .' -d @"'.$docSettings['dataFilepath'].'"';
        };
        //var_dump ($cmd);
        $ca = cdb_exec ($cmd, $actualCodeLocation); // $ca = $connectionAttempt
        if (
            $ca['result']!==0
            || strpos($ca['output'][0],'"error":')!==false
        ) {
            $r = array (
                'fromCodeLocation' => $actualCodeLocation,
                'status' => 'FAILED',
                'errorMessage' => 'invalid $callSettings',
                'cmd' => $cmd,
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
    
    public function getDoc ($docSettings=null, $fromCodeLocation='couchdb_database->getDoc') {
        $codeLocation = 'couchdb_database->getDoc';
        if ($fromCodeLocation!==$codeLocation) {
            $actualCodeLocation = $fromCodeLocation.'(...)--->'.$codeLocation;
        } else {
            $actualCodeLocation = $codeLocation;
        };
        
        $cmd = 'curl -s -k -X GET -m 5 '
            .$docSettings['server']->address
            .str_replace(' ', '%20', $docSettings['dbName']).'/'
            .str_replace(' ', '%20', $docSettings['id']);//.' -d \'{"id":"'.$docSettings['id'].'"}\'';
        $ca = cdb_exec ($cmd, $actualCodeLocation); // $ca = $connectionAttempt
        if (
            $ca['result']!==0
            || strpos($ca['output'][0],'"error":')!==false
        ) {
            $r = array (
                'fromCodeLocation' => $actualCodeLocation,
                'status' => 'FAILED',
                'errorMessage' => 'invalid $callSettings',
                'curl command' => $cmd,
                'curl result' => $ca['result'],
                'curl output' => $ca['output']
            ); $r1 = cdb_debug ($r, $actualCodeLocation);
            return $r1;
        } else {
            file_put_contents ('/home/rene/data1/htdocs/nicer.app/t.json', $ca['output']);
            return json_decode($ca['output'][0], true);
        }
        
        $doc = new couchdb_document ($docSettings);
        return $doc;    
    }

    public function getAllDocs ($docSettings, $fromCodeLocation='couchdb_database->getAllDocs') {
        $codeLocation = 'couchdb_database->getAllDocs';
        if ($fromCodeLocation!==$codeLocation) {
            $actualCodeLocation = $fromCodeLocation.'(...)--->'.$codeLocation;
        } else {
            $actualCodeLocation = $codeLocation;
        };
        
        $cmd = 'curl -s -k -X GET -m 5 '
            .$docSettings['server']->address
            .$docSettings['dbName'].'/_all_docs';
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
        }/* else {
            return json_decode($ca['output'][0], true);
        }*/
        
        $data = implode($ca['output']);
        $data = json_decode($data, true);
        
        //var_dump ($data); die();
        
        
        $r = array();
        foreach($data['rows'] as $idx=>$row) {
            $doc = new couchdb_document($row);
            $r[] = $doc;
        }
        
        return $r;    
    }
    
    public function updateDoc ($docSettings=null, $fromCodeLocation='couchdb_database->createDoc') {
        $codeLocation = 'couchdb_database->updateDoc';
        if ($fromCodeLocation!==$codeLocation) {
            $actualCodeLocation = $fromCodeLocation.'(...)--->'.$codeLocation;
        } else {
            $actualCodeLocation = $codeLocation;
        };
        
        $cmd = 'curl -s -k -X PUT -m 5 '
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

    public function deleteDoc ($docSettings = null, $fromCodeLocation='couchdb_database->deleteDoc') {
        $codeLocation = 'couchdb_database->deleteDoc';
        if ($fromCodeLocation!==$codeLocation) {
            $actualCodeLocation = $fromCodeLocation.'(...)--->'.$codeLocation;
        } else {
            $actualCodeLocation = $codeLocation;
        };
        
        $cmd = 'curl -s -k -X DELETE -m 5 '
            .$docSettings['server']->address
            .$docSettings['dbName'].'/'
            .$docSettings['id']
            .'?rev='.$docSettings['value']['_rev'];
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
    }
}
?>
