<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Observer service
 * Trigger some events to module's observers
 *
 * @author Frédéric TISSOT
 */
class Observer extends AbstractService
{
    /**
     * Start triggering event on inserted row
     *
     * @param Pi\Db\RowGateway\RowGateway $row
     */
    public function triggerInsertedRow($row)
    {
        // Set module list
        $moduleList = $this->moduleList();
        // Check all modules
        foreach ($moduleList as $module) {
            if (Pi::service('module')->isActive(strtolower($module))) {
                $class = sprintf('Module\%s\Api\Observer', ucfirst(strtolower($module)));
                if (class_exists($class)) {
                    if (method_exists($class, 'triggerInsertedRow')) {
                        Pi::api('observer', strtolower($module))->triggerInsertedRow($row);
                    }
                }
            }
        }
    }

    /**
     * Start triggering event on updated row
     *
     * @param $row
     * @param $oldData
     */
    public function triggerUpdatedRow($row, $oldData = [])
    {
        // Set module list
        $moduleList = $this->moduleList();
        // Check all modules
        foreach ($moduleList as $module) {
            if (Pi::service('module')->isActive(strtolower($module))) {
                $class = sprintf('Module\%s\Api\Observer', ucfirst(strtolower($module)));
                if (class_exists($class)) {
                    if (method_exists($class, 'triggerUpdatedRow')) {
                        Pi::api('observer', strtolower($module))->triggerUpdatedRow($row, $oldData);
                    }
                }
            }
        }
    }

    public function triggerDeletedRow($row, $oldData = [])
    {
        // Set module list
        $moduleList = $this->moduleList();
        // Check all modules
        foreach ($moduleList as $module) {
            if (Pi::service('module')->isActive(strtolower($module))) {
                $class = sprintf('Module\%s\Api\Observer', ucfirst(strtolower($module)));
                if (class_exists($class)) {
                    if (method_exists($class, 'triggerDeletedRow')) {
                        Pi::api('observer', strtolower($module))->triggerDeletedRow($row, $oldData);
                    }
                }
            }
        }
    }

    /**
     * Start triggering event on updated table
     *
     * @param Pi\Db\Table\AbstractTableGateway $table
     * @param                                  $set
     * @param                                  $where
     */
    public function triggerUpdatedTable($table, $set, $where)
    {
        // Set module list
        $moduleList = $this->moduleList();
        // Check all modules
        foreach ($moduleList as $module) {
            if (Pi::service('module')->isActive(strtolower($module))) {
                $class = sprintf('Module\%s\Api\Observer', ucfirst(strtolower($module)));
                if (class_exists($class)) {
                    if (method_exists($class, 'triggerUpdatedRow')) {
                        Pi::api('observer', strtolower($module))->triggerUpdatedTable($table, $set, $where);
                    }
                }
            }
        }
    }

    /**
     * Get list of active modules
     *
     * @return array
     */
    public function moduleList()
    {
        $moduleList = [];
        $modules    = Pi::registry('modulelist')->read('active');
        foreach ($modules as $module) {
            $moduleList[] = $module['name'];
        }

        return $moduleList;
    }
}
