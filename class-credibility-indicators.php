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
				'description' => __( 'This article contains firsthand information gathered by reporters. This includes directly interviewing sources and analyzing primary source documents.', 'credibility-indicators' ),
				'label'       => __( 'Original Reporting', 'credibility-indicators' ),
				'slug'        => 'original_reporting',
				'icon'        => file_get_contents( CREDIBILITY_INDICATORS_PATH . '/assets/svg/original-reporting.svg' ),
			],
			[
				'description' => __( 'A journalist was physically present to report the article from some or all of the locations it concerns.', 'credibility-indicators' ),
				'label'       => __( 'On the Ground', 'credibility-indicators' ),
				'slug'        => 'on_the_ground',
				'icon'        => file_get_contents( CREDIBILITY_INDICATORS_PATH . '/assets/svg/on-the-ground.svg' ),
			],
			[
				'description' => __( 'This article includes a list of source material, including documents and people, so you can follow the story further.', 'sources-cited.svg' ),
				'label'       => __( 'References', 'credibility-indicators' ),
				'slug'        => 'sources_cited',
				'icon'        => file_get_contents( CREDIBILITY_INDICATORS_PATH . '/assets/svg/sources-cited.svg' ),
			],
			[
				'description' => __( 'The journalist and/or newsroom have/has a deep knowledge of the topic, location or community group covered in this article.', 'credibility-indicators' ),
				'label'       => __( 'Subject Specialist', 'credibility-indicators' ),
				'slug'        => 'subject_specialist',
				'icon'        => file_get_contents( CREDIBILITY_INDICATORS_PATH . '/assets/svg/subject-specialist.svg' ),
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

		$selected_indicators    = get_post_meta( get_the_ID(), 'credibility_indicators', true );
		$credibility_indicators = self::get_indicators();

		// Assemble and return markup.
		$indicators_closed_markup = '';
		$indicators_open_markup   = '';

		$wp_kses_for_svgs = [
			'g'    => [
				'fill'            => [],
				'fill-rule'       => [],
				'stroke'          => [],
				'stroke-linecap'  => [],
				'stroke-linejoin' => [],
			],
			'path' => [
				'd'         => [],
				'fill'      => [],
				'fill-rule' => [],
			],
			'svg'  => [
				'height'  => [],
				// 'viewBox' => [],
				'width'   => [],
				'xmlns'   => [],
			],
		];

		// Loop through each indicator available.
		foreach( $credibility_indicators as $credibility_indicator ) {

			// Destructure indicator.
			$label       = $credibility_indicator['label'] ?? '';
			$description = $credibility_indicator['description'] ?? '';
			$slug        = $credibility_indicator['slug'] ?? '';

			// Is this indicator active for this output context?
			$has_indicator = wp_validate_boolean( $selected_indicators[ $slug ] ?? false );

			// For now, skip the indicator displaying. We need to style this thing still.
			if ( ! $has_indicator ) {
				continue;
			}

			/**
			 * Indicator closed markup.
			 *
			 * Displays as: {Credibility} - {Description}
			 */
			$indicators_closed_markup .= '<li>';
			$indicators_closed_markup .= sprintf(
				'%2$s <span>%1$s</span>',
				esc_html( $credibility_indicator[ 'label' ] ),
				wp_kses( $credibility_indicator[ 'icon' ], $wp_kses_for_svgs ),
			);
			$indicators_closed_markup .= '</li>';

			/**
			 * Indicators open markup.
			 *
			 * Displays as: {Credibility} - {Description}
			 */
			$indicators_open_markup .= '<tr>';

			$row_markup = '
<td class="credibility-indicators__open__icon">%1$s</td>
<td class="credibility-indicators__open__label">%2$s</td>
<td class="credibility-indicators__open__description">%3$s</td>
			';

			$indicators_open_markup .= sprintf(
				$row_markup,
				wp_kses( $credibility_indicator[ 'icon' ], $wp_kses_for_svgs ),
				esc_html( $credibility_indicator[ 'label' ] ),
				esc_html( $credibility_indicator[ 'description' ] ),
			);
			$indicators_open_markup .= '</tr>';
		}

		// If we didn't encounter any valid indicators, we may not have any
		// markup.
		if ( empty( $indicators_closed_markup ) || empty( $indicators_open_markup ) ) {
			return '';
		}

		$label = __( 'THE TRUST PROJECT', 'credibility-indicators' );

return <<<EOT
<div class="credibility-indicators__wrapper">
	<div class="credibility-indicators__closed">
		<ul>$indicators_closed_markup</ul>
		<h4 class="credibility-indicators__closed__label">$label</h4>
	</div>
	<div class="credibility-indicators__open">
		<table>$indicators_open_markup</table>
	</div>
</div>
EOT;
	}

	/**
	 * Enqueue frontend assets.
	 */
	public static function enqueue_frontend_assets() {
		// Styles.
		wp_enqueue_style(
			'credibility-indicators-styles',
			CREDIBILITY_INDICATORS_URL . 'build/frontend.css',
			[],
			filemtime( CREDIBILITY_INDICATORS_PATH . '/build/frontend.css' )
		);

		// Script.
		wp_enqueue_script(
			'credibility-indicators-script',
			CREDIBILITY_INDICATORS_URL . 'build/frontend.js',
			[],
			filemtime( CREDIBILITY_INDICATORS_PATH . '/build/frontend.js' )
		);
	}
};