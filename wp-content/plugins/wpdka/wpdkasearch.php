<?php
/**
 * @package WP DKA
 * @version 1.0
 */

/**
 * Class that manages CHAOS data specific to
 * Dansk Kulturarv and registers attributes
 * for WPChaosObject
 */
class WPDKASearch {
	
	const QUERY_KEY_TYPE = 'type';
	const QUERY_KEY_ORGANIZATION = 'organisation';
	
	static $OBJECT_TYPE_IDS = array(WPDKAObject::OBJECT_TYPE_ID);

	/**
	 * List of organizations from the WordPress site
	 * @var array
	 */
	public static $organizations = array();

	/**
	 * Construct
	 */
	public function __construct() {

		WPChaosSearch::register_search_query_variable(2, WPDKASearch::QUERY_KEY_TYPE, '[\w+]+', true, ' ');
		WPChaosSearch::register_search_query_variable(3, WPDKASearch::QUERY_KEY_ORGANIZATION, '[\w+]+', true, ' ');
		
		// Define the free-text search filter.
		$this->define_search_filters();
		
	}

	/**
	 * Convert search parameters to SOLR query
	 * @return string 
	 */
	public function define_search_filters() {
		// Restrict to interesting Object types.
		add_filter('wpchaos-solr-query', function($query, $query_vars) {
			if($query) {
				$query = array($query);
			} else {
				$query = array();
			}
			
			$objectTypeQueries = array();
			foreach(WPDKASearch::$OBJECT_TYPE_IDS as $objectTypeID) {
				$objectTypeQueries[] = "ObjectTypeID:$objectTypeID";
			}
			$objectTypeQueries = '(' . implode('+OR+', $objectTypeQueries) . ')';
			
			$query[] = $objectTypeQueries;
			return implode("+AND+", $query);
		}, 9, 2);
		
		// Free text search.
		add_filter('wpchaos-solr-query', function($query, $query_vars) {
			if($query) {
				$query = array($query);
			} else {
				$query = array();
			}
				
			if(array_key_exists(WPChaosSearch::QUERY_KEY_FREETEXT, $query_vars)) {
				// For each known metadata schema, loop and add freetext search on this.
				$freetext = $query_vars[WPChaosSearch::QUERY_KEY_FREETEXT];
				$freetext = WPChaosClient::escapeSolrValue($freetext);
				$searches = array();
				foreach(WPDKAObject::$ALL_SCHEMA_GUIDS as $schemaGUID) {
					$searches[] = sprintf("(m%s_%s_all:(%s))", $schemaGUID, WPDKAObject::FREETEXT_LANGUAGE, $freetext);
				}
				$query[] = '(' . implode("+OR+", $searches) . ')';
			}
				
			return implode("+AND+", $query);
		}, 10, 2);
		
		// File format types
		add_filter('wpchaos-solr-query', function($query, $query_vars) {
			if($query) {
				$query = array($query);
			} else {
				$query = array();
			}
				
			if(array_key_exists(WPDKASearch::QUERY_KEY_TYPE, $query_vars)) {
				// For each known metadata schema, loop and add freetext search on this.
				$types = $query_vars[WPDKASearch::QUERY_KEY_TYPE];
				$searches = array();
				foreach($types as $type) {
					$searches[] = "(FormatTypeName:$type)";
				}
				if(count($searches) > 0) {
					$query[] = '(' . implode("+OR+", $searches) . ')';
				}
			}
				
			return implode("+AND+", $query);
		}, 11, 2);
		
		// Organizations
		add_filter('wpchaos-solr-query', function($query, $query_vars) {
			if($query) {
				$query = array($query);
			} else {
				$query = array();
			}
				
			if(array_key_exists(WPDKASearch::QUERY_KEY_ORGANIZATION, $query_vars)) {
				// For each known metadata schema, loop and add freetext search on this.
				$organizationSlugs = $query_vars[WPDKASearch::QUERY_KEY_ORGANIZATION];
				$organizations = WPDKASearch::get_organizations();
				$searches = array();
				foreach($organizationSlugs as $organizationSlug) {
					foreach($organizations as $title => $organization) {
						if($organization['slug'] == $organizationSlug) {
							$searches[] = "(DKA-Organization:\"$title\")";
						}
					}
				}
				if(count($searches) > 0) {
					$query[] = '(' . implode("+OR+", $searches) . ')';
				}
			}
				
			return implode("+AND+", $query);
		}, 11, 2);
	}

	/**
	 * Fetch organization title and slug from pages using the "chaos_organization" custom field
	 * That custom field value should correspond to the title given in CHAOS
	 * @return array
	 */
	public static function get_organizations() {
		if(empty(self::$organizations)) {
			$posts = new WP_Query(array(
				'meta_key' => 'chaos_organization',
				'post_type' => 'page',
				'post_status' => 'publish,private,future',
				'orderby' => 'title',
				'order' => 'ASC'
			));
			foreach($posts->posts as $post) {
				self::$organizations[$post->chaos_organization] = array(
					'title' => $post->post_title,
					'slug' => $post->post_name
				);
			} 
		}	
		return self::$organizations;
	}

}

//Instantiate
new WPDKASearch();

//eol