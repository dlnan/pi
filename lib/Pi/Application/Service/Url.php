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
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Mvc\Router\Http\RouteMatch;
use Laminas\Mvc\Router\RouteStackInterface;
use Laminas\Uri\Http as HttpUri;

/**
 * URL handling service
 *
 * - Route/Dispatch a URL
 * ```
 *  $result = Pi::url()->route($url);
 *  $result = Pi::url()->match($url);
 *  // return RouteMatch
 * ```
 *
 * - Assemble URL
 * ```
 *  // With specified parameters
 *  Pi::service('url')->assemble(<route-name>, array(
 *      'module'        => <module-name>,
 *      'controller'    => <controller-name>,
 *      'action'        => <action-name>,
 *      'parama'        => <param-a>,
 *      'paramb'        => <param-b>,
 *  ));
 *
 *  // With current route and default route parameters
 *  Pi::service('url')->assemble('', array(
 *      'parama'        => <param-a>,
 *      'paramb'        => <param-b>,
 *  ));
 *
 *  // With current route and current route parameters
 *  Pi::service('url')->assemble('', array(
 *      'controller'    => <controller-name>,
 *      'parama'        => <param-a>,
 *      'paramb'        => <param-b>,
 *  ), true);
 *  Pi::service('url')->assemble('', array(
 *      'action'        => <action-name>,
 *      'parama'        => <param-a>,
 *      'paramb'        => <param-b>,
 *  ), true);
 *  Pi::service('url')->assemble('', array(
 *      'parama'        => <param-a>,
 *      'paramb'        => <param-b>,
 *  ), true);
 * ```
 *
 * - Assemble URL with specified router
 * ```
 *  Pi::service('url')->assemble('', array(<...>), array('router' => <router>));
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Url extends AbstractService
{
    /**
     * Router handler
     *
     * @var RouteStackInterface
     */
    protected $router;

    /** @var  RouteMatch */
    protected $routeMatch;

    /** @var  string Request URI */
    protected $requestUri;

    /**
     * Set router
     *
     * @param RouteStackInterface $router
     *
     * @return Url
     */
    public function setRouter(RouteStackInterface $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Get router and load if not specified
     *
     * @return RouteStackInterface
     */
    public function getRouter()
    {
        if (!$this->router instanceof RouteStackInterface) {
            $this->router = Pi::engine()->application()->getRouter();
        }

        return $this->router;
    }

    /**
     * Set RouteMatch
     *
     * @param RouteMatch $routeMatch
     *
     * @return Url
     */
    public function setRouteMatch(RouteMatch $routeMatch)
    {
        $this->routeMatch = $routeMatch;

        return $this;
    }

    /**
     * Get RouteMatch and load if not specified
     *
     * @return RouteMatch
     */
    public function getRouteMatch()
    {
        if (!$this->routeMatch instanceof RouteStackInterface) {
            $this->routeMatch = Pi::engine()->application()->getRouteMatch();
        }

        return $this->routeMatch;
    }

    /**
     * Generates an url given the name of a route.
     *
     * @param string     $route Route name
     * @param array      $params
     *                          Parameters to use in url generation, if any
     * @param array|bool $options
     *                          RouteInterface-specific options to use in url generation, if any.
     *                          If boolean, and no fourth argument, used as $reuseMatchedParams.
     * @param bool       $reuseMatchedParams
     *                          Whether to reuse matched parameters
     *
     * @return string                   For the link href attribute
     * @throws \RuntimeException
     * @see    Laminas\Mvc\Router\RouteInterface::assemble()
     *
     */
    public function assemble(
        $route = null,
        array $params = [],
        $options = [],
        $reuseMatchedParams = false
    ) {
        if (is_array($options) && isset($options['router'])) {
            $router = $options['router'];
            unset($options['router']);
        } elseif (!$router = $this->getRouter()) {
            throw new \RuntimeException(
                'No Router provided'
            );
        }

        $routeMatch = $this->getRouteMatch();
        // Canonize structural parameters: module, controller, action
        // If not specified, fetch from RouteMatch
        if (3 == func_num_args()) {
            if (is_bool($options)) {
                $reuseMatchedParams = $options;
                $options            = [];
            } elseif (isset($options['reuse_matched_params'])) {
                $reuseMatchedParams = (bool)$options['reuse_matched_params'];
                unset($options['reuse_matched_params']);
            }
        }
        if (isset($params['action'])) {
            $reuseMatchedParams = true;
        } elseif (isset($params['controller'])
            && !isset($params['module'])
            && $routeMatch
        ) {
            $params['module'] = $routeMatch->getParam('module');
        }
        if ($reuseMatchedParams && $routeMatch) {
            foreach (['module', 'controller', 'action'] as $key) {
                if (empty($params[$key])) {
                    $params[$key] = $routeMatch->getParam($key);
                }
            }
        }
        // Canonize route name
        if ($route) {
            $options['name'] = $route;
        } elseif (empty($options['name'])) {
            $options['name'] = $routeMatch
                ? $routeMatch->getMatchedRouteName() : 'default';
        }

        return $router->assemble($params, $options);
    }

    /**
     * Match a URL against routes and parse to parameters
     *
     * Note: host is not checked for match
     *
     * @param string $url
     * @param string $route
     *
     * @return RouteMatch|null
     * @throws \RuntimeException
     */
    public function match($url, $route = '')
    {
        if (!$this->getRouter()) {
            throw new \RuntimeException(
                'No RouteStackInterface instance provided'
            );
        }

        $uri     = new HttpUri($url);
        $request = new Request();
        $request->setUri($uri);
        if ($route) {
            $result = $this->getRouter()->parse($request, $route);
        } else {
            $result = $this->getRouter()->match($request);
        }

        return $result;
    }

    /**
     * Match a URL against routes and parse to parameters
     *
     * @param string $url
     * @param string $route
     *
     * @return RouteMatch|null
     */
    public function route($url, $route = '')
    {
        return $this->match($url, $route);
    }

    /**
     * Get current request URI
     *
     * @return string
     */
    public function getRequestUri()
    {
        if (null === $this->requestUri) {
            $this->requestUri = Pi::engine()->application()->getRequest()
                ->getRequestUri();
        }

        return $this->requestUri;
    }

    /**
     * Redirect to a URL
     *
     * @param string $url
     * @param bool   $return
     *
     * @return void
     */
    public function redirect($url, $return = false, $returnCode = 302)
    {
        if ($return) {
            $requestUri = $this->getRequestUri();
            if (false == strpos($url, '?')) {
                $url .= '?redirect=' . rawurlencode($requestUri);
            } else {
                $url .= '&redirect=' . rawurlencode($requestUri);
            }
        }

        $response = Pi::engine()->application()->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode($returnCode);
        $response->send();
        exit();
    }
}
