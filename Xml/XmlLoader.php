<?php

namespace Inpa\Xml;

/**
 * Provide simple acces to create Xml object and throws XmlLoaderException on error in Xml file.
 *
 * @author Pavel Máca
 * @property $xmlElementClass Class to use instend of \SimpleXmlElement or NULL (use default \SimpleXmlElement)
 */
class XmlLoader{
	
	/**@var string|NULL */
	public static $xmlElementClass = '\Inpa\Xml\XmlElement';
	
	/**
	 * Interprets an XML file into an object 
	 * @param string $filename Path to the XML file
	 * @param int $options Since PHP 5.1.0 and Libxml 2.6.0, you may also use the options parameter to specify additional Libxml parameters (http://www.php.net/manual/en/libxml.constants.php)
	 * @param string $ns
	 * @param bool $is_prefix
	 * @return XmlElement
	 * @throws XmlLoaderException On errors in xml.
	 */
	public static function fromFile($filename, $options = 0, $ns = "", $is_prefix = false) {
		$libxml_errors = libxml_use_internal_errors(true);
		$xml = simplexml_load_file($filename, self::$xmlElementClass, (int) $options, (string) $ns, (bool) $is_prefix);
		if($xml === false){
			self::_triggerException();
		}
		libxml_use_internal_errors($libxml_errors);
		return $xml;
	}

	/**
	 * Interprets a string of XML into an object 
	 * @param string $data A well-formed XML string 
	 * @param int $options Since PHP 5.1.0 and Libxml 2.6.0, you may also use the options parameter to specify additional Libxml parameters (http://www.php.net/manual/en/libxml.constants.php)
	 * @param string $ns
	 * @param bool $is_prefix
	 * @return XmlElement
	 * @throws XmlLoaderException On errors in xml.
	 */
	public static function fromString($data, $options = 0, $ns = "", $is_prefix = false) {
		$libxml_errors = libxml_use_internal_errors(true);
		$xml = simplexml_load_string((string) $data, self::$xmlElementClass, (int) $options, (string) $ns, (bool) $is_prefix);
		if($xml === false){
			self::_triggerException();
		}
		libxml_use_internal_errors($libxml_errors);
		return $xml;
	}
	
	/**
	 * Get a SimpleXMLElement object from a DOM node.
	 * @param \DOMNode $node A DOM Element node 
	 * @return XmlElement
	 * @throws XmlLoaderException On errors in xml.
	 */
	public static function importDom(\DOMNode $node){
		$libxml_errors = libxml_use_internal_errors(true);
		$xml = simplexml_import_dom( $node, self::$xmlElementClass);
		if($xml === false){
			self::_triggerException();
		}
		libxml_use_internal_errors($libxml_errors);
		return $xml;
	}
	
	/**
	 * @throws XmlLoaderException
	 */
	private static function _triggerException(){
		$exception = false;
		foreach(libxml_get_errors() as $error) {
			$exception = new XmlLoaderException($error);
			break;
		}
		
		libxml_clear_errors();
		
		if($exception instanceof XmlLoaderException){
			throw $exception ;
		}
	}
}