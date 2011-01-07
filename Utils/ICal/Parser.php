<?php

namespace Inpa\Utlis\ICal;

/**
 * ICal file parser
 *
 * @author Pavel Máca
 */
class Parser {
	
	const BEGIN = "BEGIN";
	const END = "END";
	
	/** @var string when parsing multiline value, using this key */
	private $lastKey;

	/** @var ICalObject document tree */
	private $document = array();
	
	/** @var string uid of actual component */
	private $active = NULL;


	/**
	 * Generate ICalObject tree from ical file
	 * @param string|resource $file ICal file path or ICal resource
	 * @return ICalObject
	 */
	static function fromFile($file){
		if(is_string($file)){
			if(!is_readable($file)){
				throw new \InvalidArgumentException("File $file must be readable");
			}
			$stream = fopen($file, 'r');
		}elseif(is_resource($file)){
			$stream = $file;
		}
		else{
			throw new \InvalidArgumentException("\$file must be valid resource or path");
		}
		
		$_this = new self;
		
		while (!feof($stream))                                                                                
		{
			$_this->readLine(stream_get_line($stream, 1024, "\r\n"));
		}  
		
		return $_this->getActiveComponent();
	
	}
	
	/**
	 * Generate ICalObject tree from ical file
	 * @param string valid ical string
	 * @return ICalObject
	 */
	static function fromString($ical_string){
		$_this = new self;

		$ical_string = explode("\r\n", $ical_string);

		foreach($ical_string as $line){
                    $_this->readLine($line);
		}

		return $_this->getActiveComponent();

	}
	
	/**
	 * Process one line from ical document
	 * @param string $line 
	 */
	private function readLine($line){
		preg_match('~^(?P<key>[A-Z-]+):(?P<value>.+)?$~', $line, $matched);
		
		if($matched && isset($matched["value"])){
			//dump($matched["key"]);
			//dump($matched["value"]);
			
			switch($matched["key"]){
				case self::BEGIN: 
					$this->createComponent($matched["value"]);
					break;
				case self::END: 
					$this->flushComponent($matched["value"]);
					break;
				default: 
					$this->lastKey = strtolower($matched["key"]);
					$this->getActiveComponent()->{$this->lastKey} = $matched["value"];
					break;
			}
		}
		elseif($matched){
			$this->getActiveComponent()->{strtolower($matched["key"])} = NULL;
		}
		else{
			$this->getActiveComponent()->{$this->lastKey} .= " ".ltrim($line);
		}
	}
	
	/**
	 * Crate new component
	 * @param string $name
	 * @throws ParserException
	 */
	private function createComponent($name){
		$class = $this->getClassName($name);
		if(!class_exists($class)){
			throw ParserException::noSupportedComponent($name, $class);
		}
		
		$component = new $class;
		
		$uid = spl_object_hash($component);
		
		$this->document[$uid] = array(
			"parent" => $this->getActiveComponentUid(),
			"component" => $component,
		);
		
		$this->active = $uid;
		
	}
	
	/**
	 * Return class name for given component name, cut first later ('V' prefix)
	 * @param string $component
	 * @return string 
	 * @todo handle component without 'V' prefix
	 */
	private function getClassName($component){
		// VCALENDAR => \__NAMESPACE__\Calendar 
		return "\\".__NAMESPACE__."\\".ucfirst(strtolower(substr($component, 1)));
	}
	
	private function getActiveComponentUid(){
		return ($this->active ?: NULL);
	}
	
	/**
	 * Return current processing component
	 * @return ICalObject
	 * @throws \Exception
	 */
	private function getActiveComponent(){
		if($this->getActiveComponentUid())
			return $this->document[$this->getActiveComponentUid()]["component"];
		else{
			throw new \Exception("error");
		}
	}

	
	/**
	 * Close current component and add it to document tree
	 * @param string $name
	 * @throws \Exception
	 */
	private function flushComponent($name){
		$reflection = new \ReflectionClass($this->getClassName($name));
		
		if( !$reflection->isInstance($this->getActiveComponent())){
			throw new \Exception("parsing error. closing bad component");
		}
		
		$parent = $this->document[$this->getActiveComponentUid()]["parent"];
		if($parent){
			$this->document[$parent]["component"]->{"set".$this->getActiveComponent()->getReflection()->getShortName()}($this->getActiveComponent());
			unset($this->document[$this->getActiveComponentUid()]);
			$this->active = $parent;
		}
	}
	
	
}


class ParserException extends \Exception{
	
	/**
	 * @param string $name
	 * @param string $class
	 * @return ParserException
	 */
	static function noSupportedComponent($name, $class){
		return new self("Component $name isn't supported, class '$class' not found");
	}
	
}