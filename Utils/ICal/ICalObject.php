<?php

namespace Inpa\Utlis\ICal;

/**
 * ICalObject
 *
 * @author Pavel Máca
 */
abstract class ICalObject extends \Nette\Object {
	
	final public function __set($property, $value){
		$this->{$property} = $value;
	}
	
	final public function &__get($property){
		return $this->{$property};
	}
	
	final public function __call($method_name, $args){
		if(preg_match('~^(?P<action>(set)|(get))(?P<property>[A-Z].+)~', $method_name, $found)){
			$num_args = count($args);
			switch($found["action"]){
				case "set":
					if($num_args  > 1){
						throw new \InvalidArgumentException(__CLASS__.":$method_name given to much arguments ($num_args), only 1 expected");
					}
					//TODO if property != NULL add to array()
					$this->{lcfirst($found["property"])} = $args[0];
					return $this;
					break;
				case "get":
					if($num_args  > 1){
						throw new \InvalidArgumentException(__CLASS__.":$method_name no arguments expected, given ($num_args)");
					}
					return $this->{lcfirst($found["property"])};
					break;
			}
		}
		
		parent::__call($method_name, $args);
	}
	
}
