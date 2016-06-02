<?php
namespace Larakit\Route;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Route {

    const METHOD_POST               = 'post';
    const METHOD_GET                = 'get';
    const METHOD_PUT                = 'put';
    const METHOD_DELETE             = 'delete';
    const METHOD_PATCH              = 'patch';
    const PATTERN_ANY               = '.+';
    const PATTERN_NUMERIC_TEXT      = '[\w\d]+';
    const PATTERN_NUMERIC_TEXT_DASH = '[\w-\d]+';
    const PATTERN_NUMERIC           = '\d+';
    const PATTERN_DATE              = '\d+\-\d+\-\d+';
    const PATTERN_EMAIL             = '[A-Z0-9a-z._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,6}';
    static    $routes;
    protected $code;
    protected $model      = null;
    protected $namespace  = null;
    protected $domain     = null;
    protected $middleware = [];
    protected $base_url   = null;

    function __construct($code) {
        $this->code = $code;
        $this->setBaseUrl(self::makeBaseUrl($this->code));
    }

    static protected function makeBaseUrl($as) {
        $parse = self::parseRouteName($as);
        $as    = Arr::get($parse, 'as');
        $as    = str_replace(['.', '_'], ['/', '-'], $as);

        return $as;
    }

    /**
     * @param $as
     *
     * @return array
     */
    static function parseRouteName($as) {
        if(strpos($as, '::') !== false) {
            $namespace = Arr::get(explode('::', $as), 0);
            $as        = Arr::get(explode('::', $as), 1);
        } else {
            $namespace = \App::getNamespace();
        }

        return compact('namespace', 'as');
    }

    /**
     * @param $as
     *
     * @return Route
     */
    static function group($as) {
        if(!isset(self::$routes[$as])) {
            self::$routes[$as] = new Route($as);
        }

        return self::$routes[$as];
    }

    /**
     * @return RouteItem
     */
    function routeIndex() {
        return $this->route($this->code)->setUrl($this->getBaseUrl());
    }

    /**
     * @param $as
     *
     * @return RouteItem
     */
    protected function route($as) {
        $r = new RouteItem($as);

        return $r->setUrl(self::makeBaseUrl($as))
            ->setPattern($this->getModelPattern())
            ->setModelName($this->getModelName())
            ->setController(self::makeController($as))
            ->addMiddleware($this->getMiddleware())
            ->setAction('index')
            ->setNamespace($this->getNamespace())
            ->setDomain($this->getDomain())
            ->addMiddleware($this->getMiddleware())
            ->setController(self::makeController($as));
    }

    static protected function makeController($as) {
        $parse = self::parseRouteName($as);
        $as    = Arr::get($parse, 'as');

        return Str::studly(str_replace('.', '_', $as));
    }

    /**
     * @return null
     */
    public function getNamespace() {
        return $this->namespace ? : Route::makeNamespace($this->code);
    }

    /**
     * @param $namespace
     *
     * @return $this
     */
    public function setNamespace($namespace) {
        $this->namespace = $namespace;

        return $this;
    }

    static function makeNamespace($as) {
        $parse = self::parseRouteName($as);
//        dump($parse);
        $namespace             = Arr::get($parse, 'namespace');
        $section_namespace     = explode('-', $namespace);
        $last                  = array_pop($section_namespace);
        $section_namespace     = implode('-', $section_namespace);
        $section_namespace_php = trim(Str::studly($section_namespace) . '\\' . Str::studly($last), '\\');

        return $section_namespace_php . (($namespace == \App::getNamespace()) ? '\Http\Controllers' : '\Controllers');
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
        return $this->middleware;
    }

    /**
     * @param $middleware
     *
     * @return $this
     */
    public function setMiddleware($middleware) {
        $this->middleware = $middleware;

        return $this;
    }

    /**
     * @return null
     */
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

        return $this;
    }

    /**
     * @return RouteItem
     */
    function routeIndexItem() {
        return $this->route($this->code . '.item')->setUrl($this->getBaseUrl() . '{' . $this->getModelName() . '}/');
    }

    /**
     * @return RouteItem
     */
    function routeIndexMethod($method) {
        return $this->route($this->code . '.' . $method)->setUrl($this->getBaseUrl() . $method);
    }

    /**
     * @return RouteItem
     */
    function routeIndexItemMethod($method) {
        return $this->route($this->code . '.item.' . $method)->setUrl($this->getBaseUrl() . '{' . $this->getModelName() . '}/' . $method);
    }

    public function getModelName() {
        return $this->getModel('model_name') ? : 'id';
    }

    public function getModel($param = null) {
        if($param) {
            return Arr::get($this->model, $param);
        }

        return $this->model;
    }

    /**
     * @param      $model_name
     * @param      $model_class
     * @param bool $pattern
     *
     * @return $this
     */
    public function setModel($model_name, $model_class, $pattern = true) {
        \Route::model($model_name,
            $model_class,
            function ($id) {
                $e = new ModelNotFoundException('Ничего не найдено');

                return $e->setModel($id);
            });

        $this->model = compact('model_name', 'model_class', 'pattern');

        return $this;
    }

    public function getModelClass() {
        return $this->getModel('model_class');
    }

    public function getModelPattern() {
        return $this->getModel('pattern') ? : true;
    }

    static $route_params = [];
    static $route_seo = [];

    /**
     * @param null $route
     *
     * @return array|mixed
     */
    public static function getRouteSeo($route=null) {
        return $route ? Arr::get(self::$route_seo, $route, []) : self::$route_seo;
    }

    /**
     * @param      $route_seo
     * @param      $title
     * @param null $description
     * @param null $keywords
     */
    public static function setRouteSeo($route_seo, $title, $description=null, $keywords=null) {
        self::$route_seo[$route_seo] = compact('title', 'description', 'keywords');
    }

    /**
     * @param null $route
     *
     * @return array|mixed
     */
    public static function getRouteParams($route = null) {
        return $route ? Arr::get(self::$route_params, $route, []) : self::$route_params;
    }

    /**
     * @param $route
     * @param $params
     */
    public static function setRouteParams($route, $params) {
        self::$route_params[$route] = $params;
    }

}

\Route::pattern('any', Route::PATTERN_ANY);
\Route::pattern('user', Route::PATTERN_NUMERIC_TEXT);
\Route::pattern('action', Route::PATTERN_NUMERIC_TEXT_DASH);
\Route::pattern('date', Route::PATTERN_DATE);
\Route::pattern('id', Route::PATTERN_NUMERIC);