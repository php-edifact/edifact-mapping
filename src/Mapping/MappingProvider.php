<?php
namespace EDI\Mapping;

class MappingProvider {

    private $directory = 'D95B';
    private $path;

    public function __construct($directory = 'D95B', $path = null) {
        if (!defined("SEPARATOR")) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                define("SEPARATOR", "\\");
            } else {
                define("SEPARATOR", "/");
            }
        }

        if (is_null($path)) {
            $path = dirname(__FILE__);
        }

        $this->directory = $directory;
        $this->path = $path;
    }

    public function getCodes() {
        return $this->path.SEPARATOR.$this->directory.SEPARATOR."codes.xml";
    }

    public function getSegments() {
        return $this->path.SEPARATOR.$this->directory.SEPARATOR."segments.xml";
    }

    public function getMessage($message = "codeco") {
        return $this->path.SEPARATOR.$this->directory.SEPARATOR."messages".SEPARATOR.strtolower($message).".xml";
    }

    public function getBasePath() {
        return $this->path;
    }
    
    public function getServiceSegments($version = '3') {
        return $this->path.SEPARATOR."Service_V".$version.SEPARATOR."segments.xml";
    }

    public function getServiceMessages($version = '3', $message = 'contrl') {
        return $this->path.SEPARATOR."Service_V".$version.SEPARATOR."messages".SEPARATOR.strtolower($message).".xml";
    }
}
