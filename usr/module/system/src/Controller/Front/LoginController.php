<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Front;

use Module\System\Form\LoginFilter;
use Module\System\Form\LoginForm;
use Pi;
use Pi\Authentication\Result;
use Pi\Mvc\Controller\ActionController;
use Laminas\Stdlib\RequestInterface as Request;
use Laminas\Stdlib\ResponseInterface as Response;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * User login/logout controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class LoginController extends ActionController
{
    protected $configs = [];

    public function dispatch(Request $request, Response $response = null)
    {
        header("X-Robots-Tag: noindex, nofollow", true);

        return parent::dispatch($request, $response); // TODO: Change the autogenerated stub
    }

    /**
     * Login form
     *
     * @return void
     */
    public function indexAction()
    {
        if (!$this->checkAccess()) {
            return;
        }

        // Display login form
        $form     = $this->getForm($this->getConfig());
        $redirect = $this->params('redirect');
        if (null === $redirect) {
            $redirect = $this->request->getServer('HTTP_REFERER');
        }
        if (null !== $redirect) {
            $redirect = $redirect ? urlencode($redirect) : '';
            $form->setData(['redirect' => $redirect]);
            $this->view()->assign('redirect', $redirect);
        }
        $this->renderForm($form);
    }

    /**
     * Render login form
     *
     * @param LoginForm $form
     * @param string $message
     */
    protected function renderForm($form, $message = '')
    {
        $loginTemplate = Pi::user()->config('login_template');
        if ($loginTemplate) {
            $this->view()->setLayout($loginTemplate);
        }

        $this->view()->setTemplate('login', '', 'front');
        $configs = $this->getConfig();

        if (!empty($configs['login_attempts'])) {
            $attempts = isset($_SESSION['PI_LOGIN']['attempts']) ? $_SESSION['PI_LOGIN']['attempts'] : 0;
            if (!empty($attempts)) {
                if ($attempts >= $configs['login_attempts']) {
                    $wait    = Pi::service('session')->manager()->getSaveHandler()->getLifeTime() / 60;
                    $message = sprintf(__('Login with the account is suspended, please wait for %d minutes to try again.'), $wait);
                    $this->view()->setTemplate('login-suspended', '', 'front');
                } else {
                    $remaining = $configs['login_attempts'] - $attempts;
                    $message   = sprintf(__('You have %d times to try.'), $remaining);
                }
            }
        }

        $this->view()->assign([
            'title'   => __('User login'),
            'message' => $message,
            'form'    => $form,
        ]);

        $this->view()->headTitle(__('Login'));
        $this->view()->headdescription(__('Use this page to connect to our website and participate, share contents, use our features, and interacts with other members'), 'set');
        $headKeywords = Pi::user()->config('head_keywords');
        if ($headKeywords) {
            $this->view()->headkeywords($headKeywords, 'set');
        }
    }

    /**
     * Process login submission
     *
     * @return void
     */
    public function processAction()
    {
        if (!$this->checkAccess()) {
            return;
        }

        if (!$this->request->isPost()) {
            $this->jump(
                ['action' => 'index'],
                __('Invalid request.'),
                'error'
            );
            return;
        }


        $configs = $this->getConfig();
        $post    = $this->request->getPost();
        $form    = $this->getForm($configs);
        $form->setData($post);
        $form->setInputFilter($this->getInputFilter($configs));

        $this->view()->assign('redirect', ['route' => 'home']);

        if (!$form->isValid()) {
            $this->renderForm($form);

            return;
        }

        $values       = $form->getData();
        $identityData = (array)$values['identity'];
        $identity     = array_shift($identityData);

        $field = '';
        if (!$configs['login_field']) {
            $field = '';
        } elseif (1 == count($configs['login_field'])) {
            $field = current($configs['login_field']);
        } elseif ($identityData) {
            $field = array_shift($identityData);
            if (!in_array($field, $configs['login_field'])) {
                $field = '';
            }
        }
        $field      = $field ?: 'identity';
        $credential = $values['credential'];

        if (!empty($configs['login_attempts'])) {
            $sessionLogin = isset($_SESSION['PI_LOGIN']) ? $_SESSION['PI_LOGIN'] : [];
            if (!empty($sessionLogin['attempts']) && $sessionLogin['attempts'] >= $configs['login_attempts']) {
                $this->jump(
                    ['route' => 'home'],
                    __('You have tried too many times. Please try later.'),
                    'error'
                );

                return;
            }
        }

        $result = Pi::service('authentication')->authenticate($identity, $credential, $field);
        $result = $this->verifyResult($result);

        if (!$result->isValid()) {
            if (!empty($configs['login_attempts'])) {
                if (!isset($_SESSION['PI_LOGIN'])) {
                    $_SESSION['PI_LOGIN'] = [];
                }
                $_SESSION['PI_LOGIN']['attempts'] = isset($_SESSION['PI_LOGIN']['attempts']) ? ($_SESSION['PI_LOGIN']['attempts'] + 1) : 1;
            }
            $message = __('Invalid credentials provided, please try again.');
            $this->renderForm($form, $message);

            return;
        }

        $uid = (int)$result->getData('id');
        try {
            Pi::service('user')->bind($uid);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->renderForm($form, $message);

            return;
        }

        Pi::service('session')->setUser($uid);

        $rememberMe = 0;
        if ($configs['rememberme'] && $values['rememberme']) {
            $rememberMe = $configs['rememberme'] * 86400;
            Pi::service('session')->manager()->rememberme($rememberMe);
        }

        if (isset($_SESSION['PI_LOGIN'])) {
            unset($_SESSION['PI_LOGIN']);
        }

        if (empty($values['redirect'])) {
            $redirect = ['route' => 'home'];
        } else {
            //$redirect = urldecode($values['redirect']);
            $redirect = ['route' => 'home'];
        }

        // Trigger login event
        $args = [
            'uid'           => $uid,
            'remember_time' => $rememberMe,
        ];
        Pi::service('event')->trigger('user_login', $args);

        $this->jump($redirect);
        $this->view()->assign('redirect', $redirect);
    }

    /**
     * Logout
     */
    public function logoutAction()
    {
        $uid = Pi::user()->getId();
        Pi::service('session')->manager()->destroy();
        Pi::service('user')->destroy();
        Pi::service('event')->trigger('logout', $uid);
        Pi::service('user')->killUser($uid);

        $redirect = $this->params('redirect');
        if ($redirect) {
            $redirect = urldecode($redirect);
        } else {
            $request = new \Laminas\Http\Request();
            $request->setMethod(\Laminas\Http\Request::METHOD_GET);
            $request->setUri($this->getRequest()->getServer('HTTP_REFERER'));
            $hasPermission = Pi::service('permission')->pagePermission(Pi::engine()->application()->getRouter()->match($request)->getParams());
            if ($hasPermission) {
                $redirect = $this->getRequest()->getServer('HTTP_REFERER');
            } else {
                $redirect = ['route' => 'home'];
            }
        }
        $this->jump($redirect, __('You logged out successfully.'));
    }

    /**
     * Load login form
     *
     * @param array $config
     *
     * @return LoginForm
     */
    protected function getForm(array $config)
    {
        $form = new LoginForm('login', $config);
        $form->setAttribute(
            'action',
            $this->url('', ['controller' => 'login', 'action' => 'process'])
        );

        return $form;
    }

    /**
     * Load login filter
     *
     * @param array $config
     *
     * @return LoginFilter
     */
    public function getInputFilter(array $config)
    {
        $filter = new LoginFilter($config);

        return $filter;
    }

    /**
     * Check access
     *
     * @return bool
     */
    protected function checkAccess()
    {
        if (('local' != Pi::authentication()->getStrategy()->getName())|| (Pi::service('module')->isActive('user') && 'user' != $this->getModule())) {
            $redirect = $this->params('redirect') ?: '';
            $this->redirect()->toUrl(Pi::authentication()->getUrl('login', $redirect));
            return false;
        }

        // If login disabled
        $loginDisable = $this->getConfig('login_disable');
        if ($loginDisable) {
            $this->view()->setTemplate('login-disabled', '', 'front');
            $this->view()->setLayout('layout-simple');
            return false;
        }

        // If already logged in
        if (Pi::service('user')->hasIdentity()) {
            $this->redirect()->toUrl(Pi::service('user')->getUrl('profile'));
            return false;
        }

        return true;
    }

    /**
     * Filtering Result after authentication
     *
     * @param Result $result
     *
     * @return Result
     */
    protected function verifyResult(Result $result)
    {
        return $result;
    }

    /**
     * Get user configs
     *
     * @param string $name
     *
     * @return array
     */
    protected function getConfig($name = '')
    {
        if (!$this->configs) {
            $this->configs = Pi::user()->config();
        }
        $result = $this->configs;
        //$result['login_attempts'] = 0;
        //$result['login_disable'] = 0;
        if ($name) {
            $result = isset($result[$name]) ? $result[$name] : null;
        }

        return $result;
    }
}
