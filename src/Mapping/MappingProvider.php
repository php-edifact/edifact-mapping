<?php
namespace EDI\Mapping;

class MappingProvider {

    private $directory = 'D95B';

    public function __construct($directory = 'D95B') {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')           
            define("SEPARATOR", "\\");
        else 
            define("SEPARATOR", "/");
        $this->directory = $directory;
    }

    public function getCodes() {
        return dirname(__FILE__).SEPARATOR.$this->directory.SEPARATOR."codes.xml";
    }

    public function getSegments() {
        return dirname(__FILE__).SEPARATOR.$this->directory.SEPARATOR."segments.xml";
    }

    public function getMessage($message = "codeco") {
        return dirname(__FILE__).SEPARATOR.$this->directory.SEPARATOR."messages".SEPARATOR.strtolower($message).".xml";
    }

    public function getBasePath() {
        return dirname(__FILE__);
    }
    
    public function getServiceSegments($version = '3') {
        return dirname(__FILE__).SEPARATOR."Service_V".$version.SEPARATOR."segments.xml";
    }

    public function getServiceMessages($version = '3', $message = 'contrl') {
        return dirname(__FILE__).SEPARATOR."Service_V".$version.SEPARATOR."messages".SEPARATOR.strtolower($message).".xml";
    }
}
