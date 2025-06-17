<?php
/**
 * @author xzit.online <hallo@xzit.email>
 * @website https://github.com/basteyy
 * @website https://xzit.online
 * @package Xzit_UCM
 */

declare( strict_types=1 );

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit; // Exit if accessed directly.
}

xzit_ucm_uninstall( true );