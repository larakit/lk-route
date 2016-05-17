<?php
namespace Larakit\Route;

use Illuminate\Routing\Controller;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class RouteItem
 * @package Larakit\Route
 */
class RouteItem {

    /**
     * @var null
     */
    public $ext     = null;
    public $pattern = null;
    /**
     * @var null
     */
    public $model_name = null;
    /**
     * @var null
     */
    public $namespace = null;
    /**
     * @var null
     */
    protected $domain = null;
    /**
     * @var null
     */
    protected $url = null;
    /**
     * @var null
     */
    protected $controller = null;
    /**
     * @var null
     */
    protected $action = null;
    /**
     * @var null
     */
    protected $method = 'any';
    /**
     * @var null
     */
    protected $prefix = null;
    /**
     * @var array
     */
    protected $middleware = [];
    /**
     * @var array
     */
    protected $params = [];
    protected $params_url = '';
    /**
     * @var null
     */
    protected $as;

    /**
     * RouteItem constructor.
     *
     * @param null $as
     */
    function __construct($as = null) {
        $this->as = $as;
    }

    /**
     * @return null
     */
    public function getExt() {
        return $this->ext;
    }

    /**
     * @param null $ext
     *
     * @return RouteItem;
     */
    public function setExt($ext) {
        $this->ext = $ext;

        return $this;
    }

    /**
     * @param $middleware
     *
     * @return $this
     */
    public function addMiddleware($middleware) {
        if(is_array($middleware)) {
            foreach($middleware as $m) {
                $this->addMiddleware($m);
            }

            return $this;
        }

        $middleware                    = (string) $middleware;
        $this->middleware[$middleware] = $middleware;

        return $this;
    }

    /**
     * @param null $http_method
     *
     * @return $this
     */
    function put($http_method = null) {
        \Route::group([
            'middleware' => $this->getMiddleware(),
            'domain'     => $this->getDomain(),
        ], function () use ($http_method) {
            $uses   = '\\' . $this->getNamespace() . '\\' . $this->getController() . '@' . $this->getAction();
            $method = $this->getMethod($http_method);

            /** @var $route \Illuminate\Routing\Route */
            $route = \Route::$method($this->getUrl(), [
                'as'   => $this->as,
                'uses' => $uses,
            ])->where($this->getParams());
            if($this->getModelName()) {
                $route->where($this->getModelName(), $this->getPattern());

            }
        });

        return $this;
    }

    /**
     * @return array
     */
    public function getMiddleware() {
        return $this->middleware;
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
     * @return null
     */
    public function getNamespace() {
        return $this->namespace;
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

    /**
     * @return null
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * @param $controller
     *
     * @return $this
     */
    public function setController($controller) {
        if('Controller' != substr($controller, -10)) {
            $controller = $controller . 'Controller';
        }
        $controller = str_replace('/', '_', $controller);

        $this->controller = Str::studly($controller);

        return $this;
    }

    /**
     * @return null
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @param $action
     *
     * @return $this
     */
    public function setAction($action) {
        $this->action = Str::camel($action);

        return $this;
    }

    /**
     * Проверяем HTTP-метод, если он не входит в список разрешенных,
     * то разрешаем доступ к роуту любым методом
     *
     * @param $method
     *
     * @return string
     */
    public function getMethod($method) {
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
     * @return null
     */
    public function getUrl() {
        $ext    = $this->getExt();
        $url = $this->url;
        $url .= $this->params_url;
        if($ext){
            $url = rtrim($this->url, '/').'.'.$this->ext;
        }
        return $url ;
    }

    /**
     * @param $url
     *
     * @return $this
     */
    public function setUrl($url) {
        $this->url = '/' . trim($url, '/') . '/';

        return $this;
    }

    /**
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * @param      $name
     * @param null $pattern
     *
     * @return $this
     */
    function addParam($name, $pattern=null){
        if($pattern) {
            if(true === $pattern){
                $pattern = '[0-9]+';
            }
            $this->params[rtrim($name, '?')] = $pattern;
        }
        $this->params_url .= '{' . $name . '}/';
        return $this;
    }

    function clearParams(){
        $this->params = [];
        $this->params_url = '';
    }


    /**
     * @return null
     */
    public function getModelName() {
        return $this->model_name;
    }

    /**
     * @param null $model_name
     *
     * @return RouteItem;
     */
    public function setModelName($model_name) {
        $this->model_name = $model_name;

        return $this;
    }

    /**
     * @return null
     */
    public function getPattern() {
        return (true===$this->pattern)?Route::PATTERN_NUMERIC:$this->pattern;
    }

    /**
     * @param null $pattern
     *
     * @return RouteItem;
     */
    public function setPattern($pattern) {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * @param $controller_class
     *
     * @return $this
     */
    function setControllerClass($controller_class) {
        $r = new \ReflectionClass($controller_class);

        return $this->setController($r->getShortName())->setNamespace($r->getNamespaceName());
    }

}