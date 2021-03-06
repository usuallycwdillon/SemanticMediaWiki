<?php

namespace SMW\Tests\Integration\Parser;

use SMW\Tests\ByJsonTestCaseProvider;
use SMW\Tests\JsonTestCaseFileHandler;

use SMW\Tests\Utils\UtilityFactory;
use SMW\DIWikiPage;

/**
 * @group semantic-mediawiki-integration
 * @group medium
 *
 * @license GNU GPL v2+
 * @since 2.2
 *
 * @author mwjames
 */
class ByJsonParserTestCaseRunnerTest extends ByJsonTestCaseProvider {

	private $semanticDataValidator;
	private $stringValidator;

	protected function setUp() {
		parent::setUp();

		$this->semanticDataValidator = UtilityFactory::getInstance()->newValidatorFactory()->newSemanticDataValidator();
		$this->stringValidator = UtilityFactory::getInstance()->newValidatorFactory()->newStringValidator();
	}

	/**
	 * Version to match supported Json format
	 *
	 * @see ByJsonTestCaseProvider::getJsonTestCaseVersion
	 */
	protected function getJsonTestCaseVersion() {
		return '0.1';
	}

	/**
	 * @see ByJsonTestCaseProvider::getTestCaseLocation
	 */
	protected function getTestCaseLocation() {
		return __DIR__;
	}

	/**
	 * @see ByJsonTestCaseProvider::runTestCaseFile
	 *
	 * @param JsonTestCaseFileHandler $jsonTestCaseFileHandler
	 */
	protected function runTestCaseFile( JsonTestCaseFileHandler $jsonTestCaseFileHandler ) {

		$this->checkEnvironmentToSkipCurrentTest( $jsonTestCaseFileHandler );

		$permittedSettings = array(
			'smwgNamespacesWithSemanticLinks',
			'smwgPageSpecialProperties',
			'wgLanguageCode',
			'wgContLang'
		);

		foreach ( $permittedSettings as $key ) {
			$this->changeGlobalSettingTo(
				$key,
				$jsonTestCaseFileHandler->getSettingsFor( $key )
			);
		}

		$this->createPagesFor(
			$jsonTestCaseFileHandler->getListOfProperties(),
			SMW_NS_PROPERTY
		);

		$this->createPagesFor(
			$jsonTestCaseFileHandler->getListOfSubjects(),
			NS_MAIN
		);

		foreach ( $jsonTestCaseFileHandler->findParserTestCases() as $case ) {

			if ( !isset( $case['subject'] ) ) {
				break;
			}

			$this->assertSemanticDataForCase( $case );
			$this->assertParserOutputForCase( $case );
		}
	}

	private function assertSemanticDataForCase( $case ) {

		if ( !isset( $case['store'] ) || !isset( $case['store']['semantic-data'] ) ) {
			return;
		}

		$subject = DIWikiPage::newFromText(
			$case['subject'],
			isset( $case['namespace'] ) ? constant( $case['namespace'] ) : NS_MAIN
		);

		$semanticData = $this->getStore()->getSemanticData( $subject );

		if ( isset( $case['errors'] ) && $case['errors'] !== array() ) {
			$this->assertNotEmpty(
				$semanticData->getErrors()
			);
		}

		$this->semanticDataValidator->assertThatPropertiesAreSet(
			$case['store']['semantic-data'],
			$semanticData,
			false
		);
	}

	private function assertParserOutputForCase( $case ) {

		if ( !isset( $case['output'] ) || !isset( $case['output']['to-contain'] ) ) {
			return;
		}

		$subject = DIWikiPage::newFromText(
			$case['subject'],
			isset( $case['namespace'] ) ? constant( $case['namespace'] ) : NS_MAIN
		);

		$parserOutput = UtilityFactory::getInstance()->newPageReader()->getEditInfo( $subject->getTitle() )->output;

		$this->stringValidator->assertThatStringContains(
			$case['output']['to-contain'],
			$parserOutput->getText(),
			$case['about']
		);
	}

}
