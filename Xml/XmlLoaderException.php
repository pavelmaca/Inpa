<?php

namespace Inpa\Xml;

/**
 * @author Pavel Máca
 */
class XmlLoaderException extends \Exception {

	public function __construct(\LibXMLError $error) {
		$this->file = $error->file;
		$this->message = $error->message . " [column: $error->column]";
		$this->line = $error->line;
		$this->code = $error->code;
	}

}
