<?php

defined( 'ABSPATH' ) or die( 'No direct access.' );

/**
 * Config Template
 *
 * This file has string substitutions for different builds.
 */

/**
 * Assets Base URL
 * This works one of three ways:
 *  - If it starts with http or https, use as base URL for assets.
 *  - If it doesn't, use as a relative URL starting from plugin base URL.
 *  - If null, use plugin base URL.
 */
define( 'CALYPSO_PLUGIN_ASSETS_URL', '//localhost:8090/dist/' );

/**
 * Randomizes the url for each asset so they can't be cached.
 * This is very useful for development.
 */
define( 'CALYPSO_PLUGIN_BUST_ASSET_CACHE', true );

