<?php
/**
 * This is a small plugin, so we keep all our PHP logic in this class which
 * only contains static methods.
 *
 * @package credibility-indicators
 */

namespace Credibility_Indicators;

/**
 * Main class.
 */
class Credibility_Indicators {

	/**
	 * Get all indicators.
	 *
	 * @return array
	 */
	public static function get_indicators(): array {
		$default_indicators = [
			[
				'description' => __( 'This article contains new, firsthand information uncovered by its reporter(s). This includes directly interviewing sources and research / analysis of primary source documents.', 'credibility-indicators' ),
				'label'         => __( 'Original Reporting', 'credibility-indicators' ),
				'slug'          => 'original_reporting',
			],
			[
				'description' => __( 'Indicates that a Newsmaker/Newsmakers was/were physically present to report the article from some/all of the location(s) it concerns.', 'credibility-indicators' ),
				'label'         => __( 'On the Ground', 'credibility-indicators' ),
				'slug'          => 'on_the_ground',
			],
			[
				'description' => __( 'As a news piece, this article cites verifiable, third-party sources which have all been thoroughly fact-checked and deemed credible by the Newsroom in accordance with the Civil Constitution.', 'credibility-indicators' ),
				'label'         => __( 'Sources Cited', 'credibility-indicators' ),
				'slug'          => 'sources_cited',
			],
			[
				'description' => __( 'This Newsmaker has been deemed by this Newsroom as having a specialized knowledge of the subject covered in this article.', 'credibility-indicators' ),
				'label'         => __( 'Subject Specialist', 'credibility-indicators' ),
				'slug'          => 'subject_specialist',
			],
		];

		return apply_filters( 'credibility_indicators\\indicators', $default_indicators );
	}

	/**
	 * Get a single indicator by key.
	 *
	 * @param string $key Indicator key.
	 * @return array or null.
	 */
	public static function get_indicator( string $key = '' ): ?array {
		return self::get_indicators()[ $key ] ?? null;
	}

	/**
	 * Expose credibility indicators to the REST API so we can use them in
	 * Gutenberg.
	 */
	public static function register_rest_field() {
		register_rest_field(
			'post',
			'credibility_indicators',
			[
				'get_callback' => function() {
					return self::get_indicators();
				},
			]
		);
	}

	/**
	 * Expose the credibility indicator statuses for a given post.
	 */
	public static function register_meta() {

		// Pluck indicator slugs into an array.
		$slugs = wp_list_pluck( self::get_indicators(), 'slug' );

		// Loop through each indicator slug, creating a schema.
		$properties = array_reduce(
			$slugs,
			function( $carry, $slug ) {
				$carry[ $slug ] = [
					'type'    => 'boolean',
					'default' => 'false',
				];
				return $carry;
			},
			[]
		);

		// Loop through each indicator slug, setting defaults.
		$default = array_reduce(
			$slugs,
			function( $carry, $slug ) {
				$carry[ $slug ] = false;
				return $carry;
			},
			[]
		);

		// Expose meta.
		register_meta(
			'post',
			'credibility_indicators',
			[
				'auth_callback' => '__return_true',
				'type'          => 'object',
				'single'        => true,
				'show_in_rest'  => [
					'schema' => [
						'type'       => 'object',
						'properties' => $properties,
						'default'    => $default,
					],
				],
			]
		);
	}

	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 * Behind the scenes, it registers also all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	public static function register_block() {
		register_block_type(
	        CREDIBILITY_INDICATORS_PATH . '/build',
	        [
	            'render_callback' => [ Credibility_Indicators::class, 'render_block' ]
	        ]
	    );
	}

	/**
	 * Callback for rendering this block.
	 *
	 * @return string
	 */
	public static function render_block() {
        return get_the_ID();
	}
};