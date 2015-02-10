<?php

namespace SMW\MediaWiki;

use StripState;
use Parser;

/**
 * @license GNU GPL v2+
 * @since 2.2
 *
 * @author mwjames
 */
class TextStripMarkerDecoder {

	/**
	 * @var StripState
	 */
	private $stripState = null;

	/**
	 * @var boolean|text
	 */
	private $nonUnstrippedText = false;

	/**
	 * @var boolean
	 */
	private $decoderState = false;

	/**
	 * @since 2.2
	 *
	 * @param StripState $stripState
	 */
	public function __construct( StripState $stripState ) {
		$this->stripState = $stripState;
	}

	/**
	 * @since 2.2
	 *
	 * @param boolean $decoderState
	 */
	public function setDecoderState( $decoderState ) {
		$this->decoderState = $decoderState;
	}

	/**
	 * @since 2.2
	 *
	 * @return boolean
	 */
	public function canUseDecoder() {
		return $this->decoderState;
	}

	/**
	 * @since 2.2
	 *
	 * @param string $text
	 *
	 * @return boolean
	 */
	public function hasStripMarker( $text ) {
		return strpos( $text, Parser::MARKER_SUFFIX );
	}

	/**
	 * @since 2.2
	 *
	 * @return text
	 */
	public function getRawText() {
		return $this->rawText;
	}

	/**
	 * @since 2.2
	 *
	 * @return text
	 */
	public function unstrip( $text ) {

		$this->rawText = $text;

		if ( ( $value = $this->stripState->unstripNoWiki( $text ) ) !== '' && !$this->hasStripMarker( $value ) ) {
			return $this->addNoWikiToUnstripValue( htmlspecialchars( $value ) );
		}

		if ( ( $value = $this->stripState->unstripGeneral( $text ) ) !== '' && !$this->hasStripMarker( $value ) ) {
			return htmlspecialchars( $value );
		}

	    return $this->unstrip( $value );
	}

	private function addNoWikiToUnstripValue( $text ) {
		return '<nowiki>' . $text . '</nowiki>';
	}

}
