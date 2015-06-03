<?php
class TruncateHtmlString {
	
	function __construct($string, $limit, $endchar) {
		// create dom element using the html string
		$this->tempDiv = new \DomDocument();
		$this->tempDiv->loadXML('<div>'.$string.'</div>');
		// keep the characters count till now
		$this->charCount = 0;
		$this->encoding = 'UTF-8';
		// character limit need to check
		$this->limit = $limit;
		
		$this->endchar = $endchar;
	}
	function cut() {
		// create empty document to store new html
		$this->newDiv = new \DomDocument();
		// cut the string by parsing through each element
		$this->searchEnd($this->tempDiv->documentElement, $this->newDiv);
		$newhtml = $this->newDiv->saveHTML();
		return $newhtml;
	}

	function deleteChildren($node) {
		while (isset($node->firstChild)) {
			$this->deleteChildren($node->firstChild);
			$node->removeChild($node->firstChild);
		}
	} 
	function searchEnd($parseDiv, $newParent) {
		foreach($parseDiv->childNodes as $ele) {
			// not text node
			if($ele->nodeType != 3) {
				$newEle = $this->newDiv->importNode($ele, true);
				if(count($ele->childNodes) === 0) {
					$newParent->appendChild($newEle);
					continue;
				}
				$this->deleteChildren($newEle);
				$newParent->appendChild($newEle);
				$res = $this->searchEnd($ele, $newEle);
				if($res)
					return $res;
				else
					continue;
			}

			// the limit of the char count reached
			if(mb_strlen($ele->nodeValue, $this->encoding) + $this->charCount >= $this->limit) {
				$newEle = $this->newDiv->importNode($ele);
				$newEle->nodeValue = 
					preg_replace('/\s+?(\S+)?$/u', '', mb_substr($newEle->nodeValue, 0, $this->limit - $this->charCount)) .
				
					$this->endchar;
				
				$newParent->appendChild($newEle);
				return true;
			}
			$newEle = $this->newDiv->importNode($ele);
			$newParent->appendChild($newEle);
			$this->charCount += mb_strlen($newEle->nodeValue, $this->encoding);
		}
		return false;
	}
}


function smarty_modifier_truncatehtml($html, $limit=80, $endchar = '&hellip;') {
	try {
		$output = new TruncateHtmlString(
			html_entity_decode(preg_replace('#<([bh]r)>#','<$1/>',$html),ENT_HTML5|ENT_QUOTES),
			$limit,html_entity_decode($endchar));
		return $output->cut();
	}
	catch(Exception $e) { // parsing error
		return smarty_modifier_truncate(preg_replace('#<[^>]+>#', ' ', $html),$limit,$endchar);
	}
}
