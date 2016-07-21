<?php
namespace Larakit\Route;

use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Route {

    const METHOD_POST               = 'post';
    const METHOD_GET                = 'get';
    const METHOD_PUT                = 'put';
    const METHOD_DELETE             = 'delete';
    const METHOD_PATCH              = 'patch';
    const PATTERN_ANY               = '.+';
    const PATTERN_WITH_SLASHES      = '(.*(?:%2F:)?.*)';
    const PATTERN_NUMERIC_TEXT      = '[\w\d]+';
    const PATTERN_NUMERIC_TEXT_DASH = '[\w-\d]+';
    const PATTERN_NUMERIC           = '\d+';
    const PATTERN_DATE              = '\d+\-\d+\-\d+';
    const PATTERN_EMAIL             = '[A-Z0-9a-z._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,6}';
    static           $routes;
    static           $route_params = [];
    protected        $as;
    protected        $model        = null;
    protected        $namespace    = null;
    protected        $domain       = null;
    protected        $middleware   = [];
    protected        $base_url     = null;
    protected        $action;
    protected        $segments     = [];
    protected        $patterns     = [];
    protected        $controller   = null;
    protected        $uses         = null;
    protected        $prefix       = null;
    static protected $icons        = null;
    protected        $icon         = null;

    /**
     * @return null
     */
    public function getIcon() {
        return $this->icon;
    }

    static public function routeIcons($route = null) {
        return $route ? Arr::get(static::$icons, $route) : static::$icons;
    }

    /**
     * @param null $icon
     *
     * @return Route;
     */
    public function setIcon($icon) {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return null
     */
    public function getPrefix() {
        return $this->prefix;
    }

    /**
     * @param null $prefix
     *
     * @return Route;
     */
    public function setPrefix($prefix) {
        $this->prefix = $prefix;

        return $this;
    }

    function __construct($code) {
        if(strpos($code, '::') !== false) {
            $this->as        = $code;
            $this->namespace = '\\' . trim(Str::studly(Arr::get(explode('::', $code), 0)), '\\') . '\Controllers';
        } else {
            $this->as        = $code;
            $this->namespace = '\\' . \App::getNamespace() . 'Http\Controllers';
        }
        $this->base_url = '/' . trim(str_replace(['.', '_'], ['/', '-'], $this->as), '/') . '/';
        //dump('-----------', $code, $this->as, $this->base_url);
    }

    /**
     * @return array
     */
    public function getPatterns() {
        return $this->patterns;
    }

    function getNamespace() {
        return $this->namespace;
    }

    function getAs() {
        $ret = $this->as;
        foreach($this->segments as $segment_name => $segment) {
            $ret .= '.' . str_replace(['{', '}', '_', '?'], '', $segment_name);
        }

        return $ret;
    }

    public function getBaseUrl() {
        return $this->base_url;
    }

    /**
     * @param $base_url
     *
     * @return $this
     */
    public function setBaseUrl($base_url) {
        $this->base_url = '/' . trim($base_url, '/') . '/';
        $this->base_url = str_replace('//', '/', $this->base_url);

        return $this;
    }

    public function getUrl() {
        $url = $this->base_url;
        foreach($this->segments as $segment_name => $segment) {
            $url .= $segment . '/';
        }
        if('/' != $url) {
            $url = rtrim($url, '/');
        }

        return $url;
    }

    static function crud($as, $base_url, $icon = null) {
        $namespace  = \App::getNamespace() . 'Http\Controllers\\' . Str::studly(str_replace('.', '_', $as));
        $r          = self::item($as)
//            ->setNamespace($namespace)
            ->setIcon($icon)
            ->setBaseUrl($base_url)
//            ->setController('List')
            ->put('get');
        $controller = $r->getController();
        $r->addSegment('add')
            ->setController($controller)
            ->setIcon('fa fa-plus')
            ->setAction('add')
            ->put('get')
            ->setAction('add')
            ->put('post');
        $r->clearSegments()
            ->addSegment('{id}')
            ->setController($controller)
            ->setAction('item')
            ->put('get')
            ->addSegment('edit')
            ->setController($controller)
            ->setAction('itemEdit')
            ->put('get')
            ->setAction('itemEdit')
            ->put('post')
            ->popSegment()
            ->addSegment('delete')
            ->setController($controller)
            ->setAction('itemDelete')
            ->put('get')
            ->setAction('itemDelete')
            ->put('post');

    }

    /**
     * @param $as
     *
     * @return Route
     */
    static function item($as) {
        if(!isset(self::$routes[$as])) {
            self::$routes[$as] = new Route($as);
        }

        return self::$routes[$as];
    }

//    /**
//     * @param null $route
//     *
//     * @return array|mixed
//     */
//    public static function getRouteParams($route = null) {
//        return $route ? Arr::get(self::$route_params, $route, []) : self::$route_params;
//    }
//
//    /**
//     * @param $route
//     * @param $params
//     */
//    public static function setRouteParams($route, $params) {
//        self::$route_params[$route] = $params;
//    }

    /**
     * @return null
     */
    public function getUses() {
        return $this->uses ? : $this->getNamespace() . '\\' . $this->getController() . '@' . $this->getAction();
    }

    /**
     * @param null $uses
     *
     * @return Route;
     */
    public function setUses($uses) {
        $this->uses = $uses;

        return $this;
    }

    /**
     * @param $namespace
     *
     * @return $this
     */
    public function setNamespace($namespace) {
        $this->namespace = '\\' . trim($namespace, '\\');

        return $this;
    }

    /**
     * @return null
     */
    public function getController() {
        $as = $this->getAs();
        if(strpos($as, '::') !== false) {
            $as = Arr::get(explode('::', $as), 1);
        }

        $controller = Str::studly(str_replace('.', '_', $as));

        return $this->controller ? : $controller . 'Controller';
    }

    /**
     * @param null $controller
     *
     * @return Route;
     */
    public function setController($controller) {
        $controller = Str::studly($controller);
        if('Controller' != substr($controller, -10)) {
            $controller = $controller . 'Controller';
        }

        $this->controller = $controller;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAction() {
        return $this->action ? : 'index';
    }

    /**
     * @param mixed $action
     *
     * @return Route;
     */
    public function setAction($action) {
        $this->action = Str::camel($action, '_');

        return $this;
    }

    function addSegment($name) {
        $this->segments[$name] = $name;

        return $this;
    }

    function addPattern($name, $pattern = true) {
        if(true === $pattern) {
            $pattern = '[0-9]+';
        }
        $this->patterns[$name] = $pattern;

        return $this;
    }

    function clearSegments() {
        $this->segments = [];

        return $this;
    }

    function popSegment() {
        if(count($this->segments) > 1) {
            array_pop($this->segments);
        } else {
            $this->segments = [];
        }

        return $this;
    }

    /**
     * @return null
     */
    public function getDomain() {
        return $this->domain;
    }

    /**
     * @param $domain
     *
     * @return $this
     */
    public function setDomain($domain) {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return array
     */
    public function getMiddleware() {
        return array_values($this->middleware);
    }

    /**
     * @param $middleware
     *
     * @return $this
     */
    public function addMiddleware($middleware) {
        $this->middleware[$middleware] = $middleware;

        return $this;
    }

    public function clearMiddleware() {
        $this->middleware = [];

        return $this;
    }

    public static function normalizeMethod($method) {
        $method = mb_strtolower($method);
        if(!in_array($method,
            [
                Route::METHOD_DELETE,
                Route::METHOD_PATCH,
                Route::METHOD_GET,
                Route::METHOD_POST,
                Route::METHOD_PUT,
            ])
        ) {
            $method = 'any';
        }

        return $method;
    }

    /**
     * @param null $http_method
     *
     * @return $this
     */
    function put($http_method = null, $ext = null) {
        static::$icons[$this->getAs()] = $this->getIcon();
        \Route::group([
            'middleware' => $this->getMiddleware(),
            'domain'     => $this->getDomain(),
            'prefix'     => $this->getPrefix(),
        ], function () use ($http_method, $ext) {
            $method = self::normalizeMethod($http_method);
            /** @var $route \Illuminate\Routing\Route */
            $route = \Route::$method($this->getUrl() . ($ext ? '.' . $ext : ''), [
                'as'   => $this->getAs(),
                'uses' => $this->getUses(),
            ]);
            if(count($this->getPatterns())) {
                $route->where($this->getPatterns());
            }
        });

        $this->action = null;
        $this->icon   = null;

        return $this;
    }

    static function apiResourceOne($url) {
        return self::item('api.' . $url)
            ->setPrefix(env('API_PREFIX', '/api/v1'))
            ->setBaseUrl($url)
            //LIST
            ->put('get');
    }

    static function apiResource($url) {
        $group      = self::apiResourceOne($url)
            ->setAction('store')
            //ADD
            ->put('post');
        $controller = $group->getController();
        //ITEM
        $group
            ->setController($controller)
            ->setAction('delete')
            //DELETE
            ->put('delete')
            ->addSegment('{id}')
            ->setController($controller)
            ->setAction('show')
            ->put('get');

    }

    static function getRouteByUri($uri) {
        $uri    = parse_url($uri, PHP_URL_PATH);
        $routes = [];
        foreach(\Route::getRoutes()->getRoutes() as $route) {
            $routes[$route->getUri()] = $route->getName();
        }
        krsort($routes);
//        dd($routes);
        $uri = trim($uri, '/');
        if(!$uri) {
            return 'home';
        }
        $matched = false;;
        $_m  = [];
        $max = null;
        foreach($routes as $route_uri => $name) {
            /** @var \Illuminate\Routing\Route $route */
            $route_uri = preg_replace('/\/\{(.*)\?\}/U', '*', $route_uri);
            $route_uri = preg_replace('/\*\*/U', '*', $route_uri);
            $route_uri = preg_replace('/\{(.*)\}/U', '*', $route_uri);
            //dump($route_uri . ' | ' . $matched . ' | ' . $max);
            if(\Illuminate\Support\Str::is($route_uri, $uri)) {
                $_m[] = [$name, $route_uri];
                $m    = mb_substr_count($route_uri, '{');
                if(is_null($max) || !$m || ($m < $max)) {
                    $max     = $m;
                    $matched = $name;
                }
            }
        }
        //dump($_m);

        return $matched;
    }

}

\Route::pattern('any', Route::PATTERN_ANY);
\Route::pattern('user', Route::PATTERN_NUMERIC_TEXT);
\Route::pattern('action', Route::PATTERN_NUMERIC_TEXT_DASH);
\Route::pattern('date', Route::PATTERN_DATE);
\Route::pattern('id', Route::PATTERN_NUMERIC);