<?php
namespace SimplyAdmire\Blog\TypoScript;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Neos\Domain\Exception;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject;

class ConvertExternalUrisImplementation extends AbstractTypoScriptObject {

	/**
	 * The string to be processed
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->tsValue('value');
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function evaluate() {
		$text = $this->getValue();

		if (!is_string($text)) {
			throw new Exception(sprintf('Only strings can be processed by this TypoScript object, given: "%s".', gettype($text)), 1382624080);
		}

		$currentContext = $this->tsRuntime->getCurrentContext();

		$node = $currentContext['node'];

		if (!$node instanceof NodeInterface) {
			throw new Exception(sprintf('The current node must be an instance of NodeInterface, given: "%s".', gettype($text)), 1382624087);
		}
		if ($node->getContext()->getWorkspace()->getName() !== 'live') {
			return $text;
		}

		/** @var \DOMDocument $dom */
		$dom = new \DOMDocument();
		$dom->loadHTML('<?xml encoding="UTF-8">' . $text);

		/** @var \DOMNode $node */
		foreach ($dom->getElementsByTagName('a') as $node) {
			if (preg_match('~^(?:f|ht)tps?://~i', $node->getAttribute('href'))){
				$node->setAttribute('target', '_blank');
			}
		}
		return $dom->saveHTML();
	}
}