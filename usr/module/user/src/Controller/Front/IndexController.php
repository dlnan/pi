<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Front;

use Pi\Mvc\Controller\ActionController;

/**
 * User feed controller
 *
 * List feeds from users followed by the current user,
 * or from all users if use is not logged in.
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class IndexController extends ActionController
{
    /**
     * User feed page
     *
     * @return array|void
     */
    public function indexAction()
    {
        $this->redirect()->toRoute(
            '',
            ['controller' => 'profile']
        );

        return;
    }
}
