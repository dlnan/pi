<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Tag\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

class BuildController extends ActionController
{
    public function flushAction()
    {
        $this->getModel('tag')->delete([]);
        $this->getModel('link')->delete([]);
        $this->getModel('stats')->delete([]);

        $this->jump(['controller' => 'index', 'action' => 'index']);
    }

    public function importAction()
    {
        set_time_limit(0);

        $generateTags = function () {
            $count = rand(1, 10);
            $tags  = [];
            for ($i = 0; $i < $count; $i++) {
                $tags[] = 'tag-' . rand(1, 50);
            }

            if (rand(0, 1)) {
                $tags = implode(' ', $tags);
            }

            return $tags;
        };

        $module = 'demo';
        $type   = '';
        for ($i = 1; $i <= 1000; $i++) {
            $item = $i;
            Pi::service('tag')->add($module, $item, $type, $generateTags());
        }

        $this->jump(['controller' => 'index', 'action' => 'index']);
    }
}
