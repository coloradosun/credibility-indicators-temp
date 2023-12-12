<?php
/**
 * Plugin Name: Credibility Indicators
 * Description: Signals for readers to gauge credibility for an article.
 * Version:     1.0.1
 * Author:      James W Burke
 * Text Domain: credibility-indicators
 *
 * @package     credibility-indicators
 */

namespace Credibility_Indicators;

define( 'CREDIBILITY_INDICATORS_PATH', dirname( __FILE__ ) );
define( 'CREDIBILITY_INDICATORS_URL', plugin_dir_url( __FILE__ ) );

// Includes.
require_once CREDIBILITY_INDICATORS_PATH . '/class-credibility-indicators.php';

// // Bootstrap actions.
add_action( 'init', [ Credibility_Indicators::class, 'register_block' ] );
add_action( 'init', [ Credibility_Indicators::class, 'register_meta' ] );
add_action( 'init', [ Credibility_Indicators::class, 'register_rest_field' ] );
add_action( 'wp_enqueue_scripts', [ Credibility_Indicators::class, 'enqueue_frontend_assets' ] );
