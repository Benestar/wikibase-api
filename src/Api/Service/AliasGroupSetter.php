<?php

namespace Wikibase\Api\Service;

use Mediawiki\Api\MediawikiApi;
use UnexpectedValueException;
use Wikibase\DataModel\Entity\Entity;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\SiteLink;
use Wikibase\DataModel\Term\AliasGroup;

/**
 * @author Adam Shorland
 */
class AliasGroupSetter {

	/**
	 * @var MediawikiApi
	 */
	private $api;

	/**
	 * @param MediawikiApi $api
	 */
	public function __construct( MediawikiApi $api ) {
		$this->api = $api;
	}

	/**
	 * @since 0.2
	 *
	 * @param AliasGroup $aliasGroup
	 * @param EntityId|Entity|SiteLink $target
	 *
	 * @return bool
	 */
	public function set( AliasGroup $aliasGroup, $target ) {
		$this->throwExceptionsOnBadTarget( $target );

		$params = $this->getTargetParamsFromTarget(
			$this->getEntityIdentifierFromTarget( $target )
		);

		$params['language'] = $aliasGroup->getLanguageCode();
		$params['set'] = $aliasGroup->getAliases();

		$params['token'] = $this->api->getToken();
		$this->api->postAction( 'wbsetaliases', $params );
		return true;
	}

	/**
	 * @param mixed $target
	 *
	 * @throws UnexpectedValueException
	 *
	 * @todo Fix duplicated code
	 */
	private function throwExceptionsOnBadTarget( $target ) {
		if( !$target instanceof EntityId && !$target instanceof Entity && ! $target instanceof SiteLink ) {
			throw new UnexpectedValueException( '$target needs to be an EntityId, Entity or SiteLink' );
		}
		if( $target instanceof Entity && is_null( $target->getId() ) ) {
			throw new UnexpectedValueException( '$target Entity object needs to have an Id set' );
		}
	}

	/**
	 * @param EntityId|Entity $target
	 *
	 * @throws UnexpectedValueException
	 * @return EntityId|SiteLink
	 *
	 * @todo Fix duplicated code
	 */
	private function getEntityIdentifierFromTarget( $target ) {
		if ( $target instanceof Entity ) {
			return $target->getId();
		} else {
			return $target;
		}
	}

	/**
	 * @param EntityId|SiteLink $target
	 *
	 * @throws UnexpectedValueException
	 * @return array
	 *
	 * @todo Fix duplicated code
	 */
	private function getTargetParamsFromTarget( $target ) {
		if( $target instanceof EntityId ) {
			return array( 'id' => $target->getSerialization() );
		} elseif( $target instanceof SiteLink ) {
			return array(
				'site' => $target->getSiteId(),
				'title' => $target->getPageName(),
			);
		} else {
			throw new UnexpectedValueException( '$target needs to be an EntityId or SiteLink' );
		}
	}

} 