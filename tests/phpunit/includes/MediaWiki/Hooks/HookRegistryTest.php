<?php

namespace SMW\Tests\MediaWiki\Hooks;

use SMW\MediaWiki\Hooks\HookRegistry;

use Title;

/**
 * @covers \SMW\MediaWiki\Hooks\HookRegistry
 *
 * @group SMW
 * @group SMWExtension
 *
 * @license GNU GPL v2+
 * @since 2.1
 *
 * @author mwjames
 */
class HookRegistryTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SMW\MediaWiki\Hooks\HookRegistry',
			new HookRegistry()
		);
	}

	public function testInvalidHookDefinitionRequestThrowsException() {

		$instance = new HookRegistry();

		$this->setExpectedException( 'RuntimeException' );
		$instance->getDefinition( 'foo' );
	}

	public function testFunctionHookDefinition() {

		$instance = new HookRegistry();

		$this->assertThatDefinitionIsClosure(
			$instance,
			$instance->getListOfRegisteredFunctionHooks()
		);
	}

	public function testParserFunctionDefinition() {

		$instance = new HookRegistry();

		$this->assertThatDefinitionIsClosure(
			$instance,
			$instance->getListOfRegisteredParserFunctions()
		);
	}

	public function testCanExecuteEditPageForm() {

		$title = Title::newFromText( 'Foo' );

		$editPage = $this->getMockBuilder( '\EditPage' )
			->disableOriginalConstructor()
			->getMock();

		$editPage->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertThatHookIsExcutable(
			'EditPage::showEditForm:initial',
			array( $editPage, $outputPage )
		);
	}

	public function testCanExecuteInternalParseBeforeLinks() {

		$parser = $this->getMockBuilder( '\Parser' )
			->disableOriginalConstructor()
			->getMock();

		$stripState = $this->getMockBuilder( '\StripState' )
			->disableOriginalConstructor()
			->getMock();

		$text = '';

		$this->assertThatHookIsExcutable(
			'InternalParseBeforeLinks',
			array( &$parser, &$text, &$stripState )
		);
	}

	public function testCanExecuteParserAfterTidy() {

		$title = Title::newFromText( 'Foo' );

		$parserOutput = $this->getMockBuilder( '\ParserOutput' )
			->disableOriginalConstructor()
			->getMock();

		$parser = $this->getMockBuilder( '\Parser' )
			->disableOriginalConstructor()
			->getMock();

		$parser->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$parser->expects( $this->any() )
			->method( 'getOutput' )
			->will( $this->returnValue( $parserOutput ) );

		$text = '';

		$this->assertThatHookIsExcutable(
			'ParserAfterTidy',
			array( &$parser, &$text )
		);
	}

	private function assertThatHookIsExcutable( $name, array $arguments ) {

		$instance = new HookRegistry();

		$this->assertInternalType(
			'boolean',
			call_user_func_array( $instance->getDefinition( $name ), $arguments )
		);
	}

	private function assertThatDefinitionIsClosure( HookRegistry $instance, $listOfItems ) {

		foreach ( $listOfItems as $name ) {
			$this->assertInstanceOf(
				'\Closure',
				$instance->getDefinition( $name )
			);
		}
	}

}
