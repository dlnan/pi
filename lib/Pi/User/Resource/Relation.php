<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\User\Resource;

use Pi;

/**
 * User relation handler
 *
 * Relation APIs:
 *
 *   - relation->get($uid, $relation, $limit, $offset, $condition, $order)
 *   - relation->getCount($uid, $relation[, $condition]])
 *   - relation->hasRelation($uid, $relation)
 *   - relation->add($uid, $relation)
 *   - relation->delete($uid, $relation)
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Relation extends AbstractResource
{
    /**
     * If relation module available
     *
     * @var bool|null
     */
    protected $isAvailable = null;

    /**
     * Check if relation function available
     *
     * @return bool
     */
    protected function isAvailable()
    {
        if (null === $this->isAvailable) {
            $this->isAvailable = Pi::service('module')->isActive('relation');
        }

        return $this->isAvailable;
    }

    /**
     * Placeholder for APIs
     *
     * @param string $method
     * @param array  $args
     *
     * @return bool|void
     */
    public function __call($method, $args)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }
}
