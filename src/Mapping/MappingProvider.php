<?php
/**
 * Path builder for xml mappings
 *
 * PHP version 7
 *
 * @category EDIFACT
 * @package  EDI\
 * @author   Stefano Sabatini <sabas88@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://github.com/php-edifact/edifact-mapping
 */
namespace EDI\Mapping;

/**
 * The class builds a path to xml mappings for usage in the other EDI classes
 */
class MappingProvider
{

    private $_directory = 'D95B';
    private $_path;

    /**
     * Constructor
     *
     * @param string $directory
     * @param string $path
     */
    public function __construct($directory = 'D95B', $path = null)
    {
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

        $this->_directory = $this->checkDirectoryFormat($directory);
        $this->_path = $path;
    }

    /**
     * UN/EDIFACT folders are in the format D\d{2}[A-C]{1}
     *
     * @param string $directory
     *
     * @return void
     */
    public function checkDirectoryFormat($directory)
    {

        if (preg_match('/^\d{2}[A-C]{1}$/', $directory)) {
            return 'D'.$directory;
        }

        return $directory;
    }

    /**
     * Set directory
     *
     * @param string $directory
     *
     * @return void
     */
    public function setDirectory($directory = 'D95B')
    {
        $this->_directory = $this->checkDirectoryFormat($directory);
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return void
     */
    public function setPath($path = null)
    {
        $this->_path = $path;
    }

    /**
     * Get path to codes.xml
     *
     * @return string
     */
    public function getCodes()
    {
        return $this->_path.SEPARATOR.$this->_directory.SEPARATOR."codes.xml";
    }

    /**
     * Get path to segments.xml
     *
     * @return string
     */
    public function getSegments()
    {
        return $this->_path.SEPARATOR.$this->_directory.SEPARATOR."segments.xml";
    }

    /**
     * Get path to xml message
     *
     * @param string $message Message name
     *
     * @return string
     */
    public function getMessage($message = "codeco")
    {
        $folder = $this->_path.SEPARATOR.$this->_directory.SEPARATOR."messages";
        return $folder.SEPARATOR.strtolower($message).".xml";
    }

    /**
     * Returns path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->_path;
    }

    /**
     * Get path to service segments.xml
     *
     * @param string $version
     *
     * @return string
     */
    public function getServiceSegments($version = '3')
    {
        return $this->_path.SEPARATOR."Service_V".$version.SEPARATOR."segments.xml";
    }

    /**
     * Get path to service messages xml
     *
     * @param string $version
     * @param string $message
     *
     * @return string
     */
    public function getServiceMessages($version = '3', $message = 'contrl')
    {
        $folder = $this->_path.SEPARATOR."Service_V".$version.SEPARATOR."messages";
        return $folder.SEPARATOR.strtolower($message).".xml";
    }

    /**
     * Get message names from folder in this directory
     *
     * @return array
     */
    public function listMessages()
    {
        $folder = $this->_path.SEPARATOR.$this->_directory.SEPARATOR."messages";
        $messages = array_slice(scandir($folder), 2);
        foreach ($messages as &$msg) {
            $msg = str_replace('.xml', '', $msg);
        }
        return $messages;
    }

    /**
     * Get directory names from the selected folder
     *
     * @return array
     */
    public function listDirectories()
    {
        return array_diff(scandir($this->_path), ['.', '..', 'MappingProvider.php']);
    }
}
