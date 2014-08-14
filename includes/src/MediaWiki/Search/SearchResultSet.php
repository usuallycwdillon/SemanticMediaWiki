<?php

namespace SMW\MediaWiki\Search;

use SearchResult;

/**
 * @ingroup SMW
 *
 * @licence GNU GPL v2+
 * @since   2.1
 *
 * @author  Stephan Gambke
 */
class SearchResultSet extends \SearchResultSet {

	private $pages;
	private $count = null;

	public function __construct( \SMWQueryResult $result, $count = null ) {

		$this->pages = $result->getResults();
		$this->count = $count;
	}

	/**
	 * Return number of rows included in this result set.
	 *
	 * @return int|void
	 */
	public function numRows() {
		return count( $this->pages );
	}

	/**
	 * Return true if results are included in this result set.
	 *
	 * @return bool
	 */
	public function hasResults() {
		return $this->numRows() > 0;
	}

	/**
	 * Fetches next search result, or false.
	 *
	 * @return SearchResult
	 */
	public function next() {
		return ( list( , $page ) = each( $this->pages ) ) !== false ? ( SearchResult::newFromTitle( $page->getTitle() ) ) : false;
	}

	/**
	 * Returns true, so Special:Search won't offer the user a link to a create
	 * a page named by the search string because the name would contain the
	 * search syntax, i.e. the SMW query.
	 *
	 * @return bool
	 */
	public function searchContainedSyntax() {
		return true;
	}

	public function getTotalHits() {
		return $this->count;
	}

}