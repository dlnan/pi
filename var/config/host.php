<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * Pi Engine host specifications
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

/**
 * Host definition file
 *
 * Paths/URLs to system folders
 *
 * - URIs without a leading slash are considered relative
 *      to the current Pi Engine host location
 * - URIs with a leading slash are considered semi-relative
 *      requires proper rewriting rules in server conf
 */
return [
    // URIs to resources
    // If URI is a relative one then www root URI will be prepended
    'uri'       => [
        // WWW root URI
        'www'    => 'http://pi.tld',
        // URI to access uploads directory
        'upload' => 'http://pi.tld/upload',
        // URI to access static files directory
        'static' => 'http://pi.tld/static',
    ],

    // Paths to resources
    // If path is a relative one then www root path will be prepended
    'path'      => [
        // Sharable paths
        // WWW root path, dependent sub folders: `script`, `public`
        'www'    => 'path/to/pi-application/www',
        // Library directory
        'lib'    => 'path/to/pi-framework/lib',
        // User extension directory
        'usr'    => 'path/to/pi-framework/usr',
        // Application module directory
        'module' => 'path/to/pi-framework/usr/module',
        // Theme directory
        'theme'  => 'path/to/pi-framework/usr/theme',
        // Path to static files directory
        'static' => 'path/to/pi-framework/www/static',
        // Path to vendor library directory: default as `lib/vendor`
        'vendor' => 'path/to/pi-framework/lib/vendor',
        // Path to module custom directory: default as `usr/custom`
        'custom' => 'path/to/pi-framework/usr/custom',

        // Application specific paths
        // Path to uploads directory
        'upload' => 'path/to/pi-application/www/upload',
        // User data directory
        'var'    => 'path/to/pi-application/var',

        // Sub-paths of var
        // Path to global collective configuration directory
        'config' => 'path/to/pi-application/var/config',
        // Path to cache files directory
        'cache'  => 'path/to/pi-application/var/cache',
        // Path to logs directory
        'log'    => 'path/to/pi-application/var/log',
    ],

    // Paths dependent on upper paths
    'directory' => [
        'asset' => [
            'parent' => 'www',
            'folder' => 'asset',
        ],
    ],
];
