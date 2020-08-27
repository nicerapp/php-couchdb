<?php 
require_once (dirname(__FILE__).'/boot.php');

class couchdb_document {
    public $settings = null;
    
    public function __construct ($docSettings, $fromCodeLocation='couchdb_document->__construct') {
        $this->settings = $docSettings;
    
    }
}
?>
