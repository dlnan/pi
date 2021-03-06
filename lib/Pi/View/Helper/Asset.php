<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Laminas\View\Helper\AbstractHelper;

/**
 * Helper for building asset URI
 *
 *
 * Usage inside a phtml template
 *
 * ```
 *  $cssUri = $this->asset('theme/default', 'css/style.css');
 *  $jsUri = $this->asset('module/demo', 'js/demo.js');
 * ```
 *
 * @see    Pi\Application\Service\Asset
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Asset extends AbstractHelper
{
    /**
     * Get URI of an asset
     *
     * @param string    $component
     * @param string    $file
     * @param bool|null $appendVersion
     *
     * @return  string
     */
    public function __invoke(
        $component,
        $file,
        $appendVersion = null
    ) {
        //$type = $isPublic ? 'public' : 'asset';

        $result = Pi::service('asset')->getAssetUrl(
            $component,
            $file,
            $appendVersion
        );

        return $result;
    }
}
