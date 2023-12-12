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

		$trustmark = '<svg width="11" height="16" xmlns="http://www.w3.org/2000/svg"><path d="M7.55 14.085c-.32.602-.862.937-1.406.937-.383 0-.735-.2-.895-.502-.223-.401-.128-1.004.256-1.64.032-.066.128-.2.192-.267.671.335 1.406.569 2.173.736-.128.301-.224.569-.32.736m.767-1.673c-.83-.167-1.598-.401-2.3-.769l-.032-.033c-.288-.1-.64-.067-.864.167-.191.167-.415.535-.415.535-.767 1.305-.511 2.208-.288 2.643.32.636.959 1.004 1.694 1.004h.032c.895 0 1.726-.536 2.205-1.439.192-.335.384-.836.512-1.17a.697.697 0 0 0-.16-.737.848.848 0 0 0-.384-.2m.801-2.585c-.182.297-.394.627-.606 1.024H8.48c-.849-.397-1.607-.594-2.092-.727.363-.891.272-1.42.212-1.849-.06-.33-.091-.594.151-1.155.667-1.519 1.577-2.212 2.154-2.113.485.099.879.825 1.03 2.047.152 1.188-.211 1.75-.818 2.773m-.091-5.745c-1.062-.165-2.305.892-3.093 2.642-.364.825-.303 1.32-.212 1.75.06.363.12.693-.213 1.42a.952.952 0 0 0-.03.759c.09.198.273.33.485.396.394.099 1.274.297 2.184.726.12.066.212.066.333.066a.936.936 0 0 0 .789-.462c.212-.363.424-.693.576-.99.667-1.09 1.152-1.882.97-3.434-.273-2.18-1.122-2.774-1.79-2.873M3.262 8.852c-.12 1.038-.629 1.415-.928 1.54-.39.158-.838.126-1.108-.125-.299-.22-.419-.629-.359-1.1.03-.19.09-.472.18-.818.718.188 1.466.314 2.215.314a.21.21 0 0 1 0 .189m.868-.535c-.09-.314-.33-.534-.659-.566h-.03a8.568 8.568 0 0 1-2.334-.346h-.06c-.33-.031-.659.158-.749.472-.09.346-.239.818-.269 1.164-.12.817.15 1.54.689 1.98.329.284.748.41 1.167.41.27 0 .569-.063.838-.19.808-.377 1.347-1.194 1.467-2.295-.03 0 0-.409-.06-.629M1.438 5.514c.146-1.513.234-2.175 1.228-3.279.936-1.04 1.784-1.513 2.252-1.23.38.221.672 1.104.38 2.491-.117.536-.351.726-.644 1.01-.38.315-.818.725-1.052 1.702a11.74 11.74 0 0 0-2.194-.347c.03-.126.03-.252.03-.347M1.29 6.775c.995.032 1.813.253 2.223.347.058 0 .117.032.175.032.351 0 .644-.252.731-.694.147-.725.439-.946.76-1.23.352-.315.79-.662.966-1.544.35-1.672.029-2.996-.82-3.5-1.14-.662-2.543.599-3.274 1.387C.852 2.897.765 3.843.59 5.388c-.03.158-.03.284-.059.442-.03.22.03.441.176.63.146.19.38.284.584.315" fill="#8B8581" fill-rule="evenodd"></path></svg>'

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