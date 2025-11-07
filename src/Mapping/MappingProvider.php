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
    private $codes;


    /**
     * Constructor
     *
     * @param string $directory
     * @param string|null $path
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
            $path = __DIR__;
        }

        $this->_directory = $this->checkDirectoryFormat($directory);
        $this->_path = $path;
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
     * UN/EDIFACT folders are in the format D\d{2}[A-C]{1}
     *
     * @param string $directory
     *
     * @return void
     */
    public function checkDirectoryFormat(string $directory)
    {

        if (preg_match('/^\d{2}[A-C]{1}$/', $directory)) {
            return 'D'.$directory;
        }

        return $directory;
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
     * Get path to codes.xml
     *
     * @return string
     */
    public function getCodes()
    {
        return $this->_path.SEPARATOR.$this->_directory.SEPARATOR."codes.xml";
    }

    /**
     * get all data element codes
     *
     * @return array|false
     */
    public function loadCodesXml()
    {
        $xmlFilePath = $this->getCodes();
        $codesXmlString = \file_get_contents($xmlFilePath);
        if ($codesXmlString === false) {
            return false;
        }

        $codesXml = new \SimpleXMLIterator($codesXmlString);
        $this->codes = [];
        foreach ($codesXml as $codeCollection) {
            assert($codeCollection instanceof \SimpleXMLIterator);

            $codeCollectionAttributes = $codeCollection->attributes();
            if ($codeCollectionAttributes === null) {
                continue;
            }

            $id = (string) $codeCollectionAttributes->id;
            $this->codes[$id] = [];
            foreach ($codeCollection as $codeNode) {
                assert($codeNode instanceof \SimpleXMLIterator);

                $codeAttributes = $codeNode->attributes();
                if ($codeAttributes !== null) {
                    $code = (string) $codeAttributes->id;
                    $this->codes[$id][$code] = (string) $codeAttributes->desc;
                }
            }
        }

        return $this->codes;
    }

    /**
     * Load a specific data element code list
     *
     * @param string $codeId The data element code ID (e.g., '0001', '3055')
     *
     * @return array|false
     */
    public function loadCodeDefinition($codeId)
    {
        $xmlFile = $this->getCodes();
        $codes_xml = \file_get_contents($xmlFile);
        if ($codes_xml === false) {
            return false;
        }

        $xml = \simplexml_load_string($codes_xml);
        if ($xml === false) {
            return false;
        }

        // Find the data_element using XPath
        $result = $xml->xpath("//data_element[@id='".$codeId."']");
        if (empty($result)) {
            return false;
        }

        $codeNode = $result[0];
        $code = [
            'attributes' => $this->readAttributesArray($codeNode),
            'codes' => []
        ];

        // Parse individual code values
        foreach ($codeNode->children() as $codeValue) {
            \assert($codeValue instanceof \SimpleXMLElement);
            $code['codes'][] = $this->readAttributesArray($codeValue);
        }

        return $code;
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

    public function loadServiceSegmentsXml()
    {
        return $this->loadXml($this->getServiceSegments(3));
    }

    public function loadSegmentsXml()
    {
        return $this->loadXml($this->getSegments());
    }

    /**
     * Load a specific segment definition
     *
     * @param string $segmentId The segment ID (e.g., 'UNH', 'BGM')
     *
     * @return array|false
     */
    public function loadSegmentDefinition($segmentId)
    {
        $xmlFile = $this->getSegments();
        $segments_xml = \file_get_contents($xmlFile);
        if ($segments_xml === false) {
            return false;
        }

        $xml = \simplexml_load_string($segments_xml);
        if ($xml === false) {
            return false;
        }

        // Find the segment using XPath
        $result = $xml->xpath("//segment[@id='".$segmentId."']");
        if (empty($result)) {
            return false;
        }

        $segmentNode = $result[0];
        $segment = [
            'attributes' => $this->readAttributesArray($segmentNode),
            'elements' => []
        ];

        // Parse data elements and composite data elements
        foreach ($segmentNode->children() as $element) {
            \assert($element instanceof \SimpleXMLElement);

            $elementName = $element->getName();
            $elementData = [
                'type' => $elementName, // 'data_element' or 'composite_data_element'
                'attributes' => $this->readAttributesArray($element)
            ];

            // If it's a composite, parse its components
            if ($elementName === 'composite_data_element') {
                $elementData['components'] = [];
                foreach ($element->children() as $component) {
                    \assert($component instanceof \SimpleXMLElement);
                    $elementData['components'][] = [
                        'attributes' => $this->readAttributesArray($component)
                    ];
                }
            }

            $segment['elements'][] = $elementData;
        }

        return $segment;
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

    public function loadMessageXml($message = "codeco")
    {
        $xmlFilePath = $this->getMessage($message);
        return $this->loadXml($xmlFilePath, false);
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
     * Get directory names from the selected folder
     *
     * @return array
     */
    public function listDirectories()
    {
        return array_diff(scandir($this->_path), ['.', '..', 'MappingProvider.php']);
    }

    /**
     * convert segment definition from XML to array. Sequence of data_elements and
     * composite_data_element same as in XML
     *
     * @return array|false
     */
    private function loadXml($xmlFile, $keepDefaults = true)
    {
        $segments_xml = \file_get_contents($xmlFile);
        if ($segments_xml === false) {
            return false;
        }

        $xml = \simplexml_load_string($segments_xml);
        if ($xml === false) {
            return false;
        }

        unset($segments_xml);

        $result = [];

        // Handle defaults separately if needed
        if ($keepDefaults && isset($xml->defaults)) {
            $result['defaults'] = $this->readAttributesArray($xml->defaults);
        }

        $result['structure'] = $this->parseStructure($xml);

        return $result;
    }

    private function parseStructure($parentNode)
    {
        $structure = [];

        foreach ($parentNode as $node) {
            \assert($node instanceof \SimpleXMLElement);

            $nodeName = $node->getName();

            // Skip defaults at this level
            if ($nodeName === 'defaults') {
                continue;
            }

            $nodeAttributes = $node->attributes();
            if ($nodeAttributes === null) {
                continue;
            }

            $item = [
                'type' => $nodeName, // 'segment' or 'group'
                'attributes' => $this->readAttributesArray($node)
            ];

            // If it's a group, recursively parse its children
            if ($nodeName === 'group') {
                $item['children'] = $this->parseStructure($node);
            }

            $structure[] = $item;
        }

        return $structure;
    }

    /**
     * return an xml elements attributes in as array
     *
     * @param \SimpleXMLElement $element
     *
     * @return array
     */
    private function readAttributesArray(\SimpleXMLElement $element): array
    {
        $attributes = [];
        foreach ($element->attributes() ?? [] as $attrName => $attr) {
            $attributes[(string) $attrName] = (string) $attr;
        }

        return $attributes;
    }

    /**
     * read message segments and groups
     *
     * @param \SimpleXMLElement $element
     *
     * @return array
     */
    private function readXmlNodes(\SimpleXMLElement $element): array
    {
        $arrayElements = [];
        foreach ($element as $name => $node) {
            if ($name == 'defaults') {
                continue;
            }
            $arrayElement = [];
            $arrayElement['type'] = $name;
            $arrayElement['attributes'] = $this->readAttributesArray($node);
            $details = $this->readXmlNodes($node);
            if (!empty($details)) {
                $arrayElement['details'] = $details;
            }
            $arrayElements[] = $arrayElement;
        }

        return $arrayElements;
    }
}
