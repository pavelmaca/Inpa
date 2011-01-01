<?php

namespace Inpa\Xml;

/**
 * Provides better control over \SimpleXmlElement
 *
 * @author Pavel Máca
 */
class XmlElement extends \SimpleXMLElement {
		
	/**
	 * Get string value of element, return NULL if element has childrens.
	 * @return string|NULL
	 */
	public function toString(){
		return ($this->hasChildrens() ? NULL : (string) $this);
	}
	
	/**
	 * Get array of attributes from element
	 * @return array
	 */
	public function getAttributes(){		
		$attributes = (array) $this->attributes();
		return (isset($attributes["@attributes"]) ? $attributes["@attributes"] : array());
	}
	
	/**
	 * Simple get attribute value as string or NULL if attribute doesn't exist.
	 * @param string $name Attribute name
	 * @return string|NULL
	 * @todo Instend of return only first child value, return NULL or something else.
	 */
	public function getAttribute($name){
		return (isset($this[$name]) ? (string) $this[$name] : NULL);
	}
	
	/**
	 * Determine if reguired attribute exist.
	 * @param string $name
	 * @return bool
	 */
	public function hasAttribute($name){
		return isset($this[$name]);
	}
	
	/**
	 * Return XmlElement of childs of this element.
	 * @param string $name
	 * @return array
	 */
	public function getChildrens(){
		return $this->children();			
	}
	
	/**
	 * Return number of child elements.
	 * @return int
	 */
	public function countChildrens(){
		return count($this->children());
	}

	/**
	 * Determine if element has childrens.
	 * @return bool
	 */
	public function hasChildrens(){
		return ($this->countChildrens() > 0 ? true : false);
	}
	
	/**
	 * Determine if reguired child element exist.
	 * @param string $name
	 * @return bool
	 */
	public function hasChildren($name){
		return isset($this->children()->{$name});
	}
	
	/**
	 * Find all childrens element with name in $names, return NULL if nothing found.
	 * @param string|array $names
	 * @return array|null
	 */
	public function findByName($names){
		$names = (array) $names;
		$found = array();
		foreach($this->getChildrens() AS $child){
			 if( in_array($child->getName(), $names)){
				 $found[] = $child;
			 }
		}
		return ($found ?: NULL);
	}
}
