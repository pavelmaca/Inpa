<?php

namespace Inpa\Utlis\ICal;

/**
 * Calendar component
 *
 * @author Pavel Máca
 */
class Calendar extends ICalObject {
		 
	protected $prodid;
	
	protected $version;
	
	protected $calscale;
	
	protected $methode;
	
	/** @var Event */
	private $events = array();
	
	/**
	 * @return array
	 */
	public function getEvents(){
		return $this->events;
	}
	
	/**
	 * @param Event $event 
	 */
	public function setEvent(Event $event){
		$this->events[] = $event;
	}
	
}
