<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Installer\Resource;

use Pi;

/**
 * Application resource asset maintenance
 *
 * 1. Publish a module's assets:
 *      from a source path (module asset path)
 *      to target path (encrypted path inside `www/asset`)
 *    - Source path: `module/demo/asset` or `theme/default/module/demo/asset`
 * 2. Remove module published assets from `www/asset/<encrypted path>/`
 *
 * @see    Pi\Application\Service\Asset for asset maintenance
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Asset extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        $module = $this->event->getParam('module');

        // Remove published assets
        Pi::service('asset')->remove('module/' . $module);

        // Publish module assets
        Pi::service('asset')->publishModule($module);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        if ($this->skipUpgrade()) {
            return;
        }
        $module = $this->event->getParam('module');
        //$directory = $this->event->getParam('directory');

        // Remove published assets
        Pi::service('asset')->remove('module/' . $module);

        // Publish module assets
        Pi::service('asset')->publishModule($module);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');

        // Remove published assets
        Pi::service('asset')->remove('module/' . $module);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        $module = $this->event->getParam('module');

        // Remove published assets
        Pi::service('asset')->remove('module/' . $module);

        // Publish module native assets
        Pi::service('asset')->publishModule($module);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAction()
    {
        return;

        $module = $this->event->getParam('module');

        // Remove published assets
        Pi::service('asset')->remove('module/' . $module);
    }
}
