<?php

namespace Wikibase\Api\Test;

use Wikibase\Api\Service\RevisionGetter;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers Wikibase\Api\Service\RevisionGetter
 */
class RevisionGetterTest extends \PHPUnit_Framework_TestCase {

	private function getMockApi() {
		$mock = $this->getMockBuilder( '\Mediawiki\Api\MediawikiApi' )
			->disableOriginalConstructor()
			->getMock();
		return $mock;
	}

	public function getMockDeserializer() {
		$mock = $this->getMockBuilder( '\Deserializers\Deserializer' )
			->disableOriginalConstructor()
			->getMock();
		return $mock;
	}

	public function testValidConstructionWorks() {
		new RevisionGetter( $this->getMockApi(), $this->getMockDeserializer() );
		$this->assertTrue( true );
	}

	public function provideIds() {
		return array(
			array( 'Q1' ),
			array( ItemId::newFromNumber( 1 ) ),
		);
	}

	/**
	 * @dataProvider provideIds
	 */
	public function testGetFromId( $id ) {
		$api = $this->getMockApi();
		$api->expects( $this->once() )
			->method( 'getAction' )
			->with(
				$this->equalTo( 'wbgetentities' ),
				$this->equalTo( array( 'ids' => 'Q1' ) )
			)
			->will( $this->returnValue( array( 'entities' => array( 'Q123' => array(
				'pageid' => '111',
				'lastrevid' => '222',
				'modified' => 'TIMESTAMP'
			) ) ) ) );
		$deserializer = $this->getMockDeserializer();
		$deserializer->expects( $this->once() )
			->method( 'deserialize' )
			->with( $this->equalTo( array(
						'pageid' => '111',
						'lastrevid' => '222',
						'modified' => 'TIMESTAMP'
			) ) )
			->will( $this->returnValue( Item::newEmpty() ) );

		$service = new RevisionGetter( $api, $deserializer );
		$result = $service->getFromId( $id );

		$this->assertInstanceOf( 'Mediawiki\DataModel\Revision', $result );
		$this->assertInstanceOf( 'Wikibase\DataModel\ItemContent', $result->getContent() );
		$this->assertInstanceOf( 'Wikibase\DataModel\Entity\Item', $result->getContent()->getNativeData() );
		$this->assertEquals( 111, $result->getPageId() );
		$this->assertEquals( 'TIMESTAMP', $result->getTimestamp() );
	}

} 