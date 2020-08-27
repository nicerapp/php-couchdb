<?php 
/*---
----- Built by : Rene AJM Veerman, owner of https://nicer.app
---*/
require_once (dirname(__FILE__).'/functions.php');

class couchdb_server {
    public $version = '1.0.0';
    public $lastModified = '2020-08(Aug)-25';
    public $compatibility = array ( 'Ubuntu 20.04', 'CouchDB 3.Y.Z', 'PHP7.Y.Z', 'Curl 7.68.0' );
    
    public $settings = null;
    public $info = null;
    public $address = null;
    
    public function __construct ($settings=null) {
        $checkSettings_connect = $this->checkSettings_connect ($settings, 'couchdb_server->__construct');
        if ($checkSettings_connect==='OK') {
            $this->settings = $settings;
            return $this;
        } else {
            return false;
        };
    }
    
    public function makeCall ($callSettings, $fromCodeLocation='couchdb_server->makeCall') {
        $codeLocation = 'couchdb_server->makeCall';
        if ($fromCodeLocation!==$codeLocation) {
            $actualCodeLocation = $fromCodeLocation.'(...)--->'.$codeLocation;
        } else {
            $actualCodeLocation = $codeLocation;
        };
        
        $cmd = 'curl -s -X GET -m 5 '.$callSettings['server']->address.'_all_dbs';
        $ca = cdb_exec ($cmd, $actualCodeLocation); // $ca = $connectionAttempt
        if (!is_array($ca)) {
            $r = array (
                'fromCodeLocation' => $actualCodeLocation,
                'status' => 'FAILED',
                'errorMessage' => 'invalid $callSettings',
                'curl result' => $ca,                
            ); $r1 = cdb_debug ($r, $actualCodeLocation);
            return $r1;
        } else if (
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
    
    
    public function checkSettings ($settings, $fromCodeLocation='couchdb_server->checkSettings') {
        $codeLocation = 'couchdb_server->checkSettings';
        if ($fromCodeLocation!==$codeLocation) {
            $actualCodeLocation = $fromCodeLocation.'(...)--->'.$codeLocation;
        } else {
            $actualCodeLocation = $codeLocation;
        };
        
        $r = 'OK';
        if (!is_array($settings)) {
            $r = array (
                'fromCodeLocation' => $fromCodeLocation,
                'status' => 'FAILED',
                'errorMessage' => '$settings should be an array'
            ); $r1 = cdb_debug ($r, $fromCodeLocation);
            return $r1;
        } else {
            // check if server settings even point to a valid couchdb server
            if (!array_key_exists('server', $settings) || !is_string($settings['server']) || $settings['server']==='') {
                $r = array (
                    'fromCodeLocation' => $actualCodeLocation,
                    'status' => 'FAILED',
                    'errorMessage' => '$settings["server"] should be a string and a valid IP address or domain name for the couchdb server that you\'re trying to connect to'
                ); $r1 = cdb_debug ($r, $actualCodeLocation);
                return $r1;
            } elseif (!array_key_exists('port',$settings) || !is_int($settings['port']) || $settings['port']<0 || $settings['port']>65535) {
                $r = array (
                    'fromCodeLocation' => $actualCodeLocation,
                    'status' => 'FAILED',
                    'errorMessage' => '$settings["port"] should be an integer between 0 and 65535, 5984 being the default for a couchdb server'
                ); $r1 = cdb_debug ($r, $actualCodeLocation);
                return $r1;
            } elseif (!array_key_exists('user',$settings) || !is_string($settings['user']) || $settings['user']==='') {
                $r = array (
                    'fromCodeLocation' => $actualCodeLocation,
                    'status' => 'FAILED',
                    'errorMessage' => '$settings["user"] should be a string and a valid couchdb user-id for '.$settings["server"].':'.$settings["port"]
                ); $r1 = cdb_debug ($r, $actualCodeLocation);
                return $r1;
            } elseif (!array_key_exists('password',$settings) || !is_string($settings['password']) || $settings['password']==='') {
                $r = array (
                    'froexit();mCodeLocation' => $actualCodeLocation,
                    'status' => 'FAILED',
                    'errorMessage' => '$settings["password"] should be a string and a valid couchdb password for user-id '.$settings["user"].' on server '.$settings["server"].':'.$settings["port"]
                ); $r1 = cdb_debug ($r, $actualCodeLocation);
                return $r1;
            } else {
                return 'OK';
            }
        }
    }
    
    public function checkSettings_connect ($settings, $codeFromLocation='couchdb_server->checkSettings_connect') {
        $codeLocation = 'couchdb_server->checkSettings_connect';
        if ($codeFromLocation!==$codeLocation) {
            $actualCodeLocation = $codeFromLocation.'(...)--->'.$codeLocation;
        } else {
            $actualCodeLocation = $codeLocation;
        };
        $cmd = 'curl -s -X GET -m 5 '.$this->constructCouchAdress($settings, $actualCodeLocation);
        $ca = cdb_exec ($cmd, $actualCodeLocation); // $ca = $connectionAttempt
        if (!is_array ($ca)) {
            $r = array (
                'fromCodeLocation' => $actualCodeLocation,
                'status' => 'FAILED',
                'errorMessage' => 'invalid $settings',
                '$settings' => $settings,
                'curl result' => $ca,
            ); $r1 = cdb_debug ($r, $actualCodeLocation);
            return $r1;
        } else if (
            $ca['result']!==0 
            || strpos($ca['output'][0],'"couchdb":"Welcome"')===false
        ) {
            $r = array (
                'fromCodeLocation' => $actualCodeLocation,
                'status' => 'FAILED',
                'errorMessage' => 'invalid $settings',
                '$settings' => $settings,
                'curl result' => $ca['result'],
                'curl output' => $ca['output']
            ); $r1 = cdb_debug ($r, $actualCodeLocation);
            return $r1;
        } else {
            $this->info['couchdb'] = $ca['output'][0];
            $this->address = $this->constructCouchAdress($settings, $actualCodeLocation);
            
            $cmd0 = 'uname -a';
            $info0 = cdb_exec ($cmd0, $actualCodeLocation);
            if ($info0['result']===0) $this->info['Operating System'] = $info0['output'];
            
            $cmd1 = 'php --version';
            $info1 = cdb_exec ($cmd1, $actualCodeLocation);
            if ($info1['result']===0) $this->info['PHP'] = $info1['output'];
            
            $cmd2 = 'curl --version';
            $info2 = cdb_exec ($cmd2, $actualCodeLocation);
            if ($info2['result']===0) $this->info['curl'] = $info2['output'];
            
            return 'OK';
        }
    }
    
    public function constructCouchAdress ($settings, $codeFromLocation='couchdb_server->constructCouchAdress') {
        $codeLocation = 'couchdb_server->constructCouchAdress';
        if ($codeFromLocation!==$codeLocation) {
            $actualCodeLocation = $codeFromLocation.'(...)--->'.$codeLocation;
        } else {
            $actualCodeLocation = $codeLocation;
        };
        $check = $this->checkSettings ($settings, $actualCodeLocation);
        if ($check==='OK') {
            $r = $settings['protocol'].'://'.$settings['user'].':'.$settings['password'].'@'.$settings['server'].':'.$settings['port'].'/';
            return $r;
        } else {
            $r = array (
                'fromCodeLocation' => $actualCodeLocation,
                'status' => 'FAILED',
                'errorMessage' => $check
            );
            return $r;
        }
    }

    

    
    
    public function connectToDB ($dbSettings, $fromCodeLocation='couchdb_server->connectToDB') {
        $codeLocation = 'couchdb_server->connectToDB';
        if ($fromCodeLocation!==$codeLocation) {
            $actualCodeLocation = $fromCodeLocation.'(...)--->'.$codeLocation;
        } else {
            $actualCodeLocation = $codeLocation;
        };
        
        if (is_null($this->address)) {
            $r = array (
                'status' => 'FAILED',
                'errorMessage' => '$this->address is not set, meaning this instance was not properly initialized',
                '$this' => $this
            ); $r1 = cdb_debug ($r, $actualCodeLocation);
            return $r1;
        };
        
        $check = $this->checkDBsettings ($dbSettings, $actualCodeLocation);
        if (!is_string($check) || $check!=='OK') {
            $r = array (
                'status' => 'FAILED',
                'errorMessage' => '$this->checkDBsettings failed',
                '$check' => $check
            ); $r1 = cdb_debug ($r, $actualCodeLocation);
            return $r1;
        } else {
            $this->settings = $dbSettings;
            if ($dbSettings['createIfNotExists']===true) $httpMethod='PUT'; else $httpMethod='GET';
            $cmd = 'curl -s -X '.$httpMethod.' '.$dbSettings['server']->address.$dbSettings['dbName'];
            $cr = cdb_exec($cmd, $actualCodeLocation);
            if (
                strpos($cr['output'][0],'"error"')!==false
                && strpos($cr['output'][0],'"error":"file_exists"')===false
            ) {
                $r = array (
                    'fromCodeLocation' => $actualCodeLocation,
                    'status' => 'FAILED',
                    'errorMessage' => 'invalid $dbSettings',
                    'curl result' => $cr['result'],
                    'curl output' => $cr['output'],
                    '$dbSettings' => $dbSettings
                ); $r1 = cdb_debug ($r, $actualCodeLocation);
                return $r1;
            } else {
                //echo PHP_EOL.PHP_EOL.'<pre>'; var_dump ($cr); echo '</pre>'.PHP_EOL.PHP_EOL;
                return new couchdb_database($dbSettings, $actualCodeLocation);
            }
        }
    }
    
    public function checkDBsettings ($dbSettings=null, $fromCodeLocation='couchdb_server->checkSettings') {
        $codeLocation = 'couchdb_server->checkSettings';
        if ($fromCodeLocation!==$codeLocation) {
            $actualCodeLocation = $fromCodeLocation.'(...)--->'.$codeLocation;
        } else {
            $actualCodeLocation = $codeLocation;
        };

        if (!is_array($dbSettings)) {
            $r = array (
                'status' => 'FAILED',
                'errorMessage' => '$dbSettings should be an array'
            ); $r1 = cdb_debug ($r, $actualCodeLocation);
            return $r1;
        } elseif (!array_key_exists('server', $dbSettings) || !$dbSettings['server'] instanceof couchdb_server) {
            $r = array (
                'status' => 'FAILED',
                'errorMessage' => '$dbSettings["server"] should be an instance of PHP object couchdb_server'
            ); $r1 = cdb_debug ($r, $actualCodeLocation);
            return $r1;
        } elseif (!array_key_exists('dbName', $dbSettings) || !is_string($dbSettings['dbName']) || $dbSettings['dbName']=='') {
            $r = array (
                'status' => 'FAILED',
                'errorMessage' => '$dbSettings["dbName"] should be a non-empty string'
            ); $r1 = cdb_debug ($r, $actualCodeLocation);
            return $r1;
        } elseif (!array_key_exists('createIfNotExists', $dbSettings) || !is_bool($dbSettings['createIfNotExists'])) {
            $r = array (
                'status' => 'FAILED',
                'errorMessage' => '$dbSettings["createIfNotExists"] should be a boolean value'
            ); $r1 = cdb_debug ($r, $actualCodeLocation);
            return $r1;
        } else {
            $r = 'OK';
            return $r;
        }
    }
    
    
}



?>
