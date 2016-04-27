<?php
namespace Larakit\Route;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class RouteItem {
    public $item = [
        'attributes'   => [],
        'item_param'   => 'id',
        'item_pattern' => '[0-9]+',
    ];

    protected $as;
    protected $package;

    /**
     * @param null $as
     *
     * @return RouteItem
     */
    static function factory($as = null) {
        return new RouteItem($as);
    }

    function __construct($as = null) {
        $this->as = $as;
        $this->setBaseUrl(Route::makeBaseUrl($as));
        $this->setController(Route::makeController($as));
        $this->setNamespace(Route::makeNamespace($as));
        $this->setAction(Route::makeAction($as));
        $parse    = Route::parseRouteName($as);
        $_package = Arr::get($parse, 'namespace');
        if ($this->package) {
            $k = str_replace($this->package . '::', '', $as);
            if (isset($disabled[$k]) && $disabled[$k]) {
                return false;
            }
        }

        if ($_package) {
            $this->package = $_package;
        }
    }

    /**
     * @param bool $pattern
     *
     * @return $this
     */
    function setItemPattern($value = true) {
        return $this->set(__FUNCTION__, (true === $value) ? '[0-9]+' : $value);
    }

    /**
     * @param bool $pattern
     *
     * @return $this
     */
    function setItemParam($value = '{id}') {
        $value = trim($value, '{}');

        return $this->set(__FUNCTION__, $value);
    }

    function set($prop, $value) {
        $prop              = Str::snake(substr($prop, 3));
        $this->item[$prop] = $value;

        return $this;
    }

    function get($prop, $default = null) {
        $prop = Str::snake(substr($prop, 3));

        return Arr::get($this->item, $prop, $default);
    }

    /**
     * @param $k
     * @param $v
     *
     * @return $this
     */
    function getKeyProp($prop, $action = null) {
        $prop = substr($prop, 3);
        switch (true) {
            case ('ItemMethod' == substr($prop, 0, 10)):
                $key  = $this->as . '.item.' . $action;
                $prop = substr($prop, 10);
                break;
            case ('Item' == substr($prop, 0, 4)):
                $key  = $this->as . '.item';
                $prop = substr($prop, 4);
                break;
            case ('Method' == substr($prop, 0, 6)):
                $key  = $this->as . '.' . $action;
                $prop = substr($prop, 6);
                break;
            default:
                $key = $this->as;
                break;
        }

        return [
            $key,
            Str::snake($prop)
        ];
    }

    function getAttribute($prop, $action = null, $default = null) {
        list($key, $prop) = $this->getKeyProp($prop, $action);

        return isset($this->item['attributes'][$key][$prop]) ? $this->item['attributes'][$key][$prop] : $default;
    }

    function setAttribute($prop, $value, $action = null) {
        list($key, $prop) = $this->getKeyProp($prop, $action);
        $this->item['attributes'][$key][$prop] = $value;

        return $this;
    }

    function getItem() {
        return $this->item;
    }

    function setPlaceholders($val) {
        return $this->set(__FUNCTION__, $val);
    }

    function getPlaceholders() {
        return (array)$this->get(__FUNCTION__);
    }

    /**
     * @param $val
     *
     * @return $this
     */
    function setController($val) {
        if ('Controller' != substr($val, 0, 10)) {
            $val = 'Controller' . $val;
        }
        $val = str_replace('/', '_', $val);
        $val = Str::studly($val);

        return $this->set(__FUNCTION__, $val);
    }

    /**
     * @param $val
     *
     * @return $this
     */
    function setPrefix($val) {
        return $this->set(__FUNCTION__, trim($val, '/'));
    }

    /**
     * @param $val
     *
     * @return $this
     */
    function setFilter($val) {
        return $this->set(__FUNCTION__, $val);
    }

    function addFilterRole($role) {
        $filters   = $this->getFilter();
        $filters[] = 'role_' . $role;
        $filters   = array_unique($filters);

        return $this->setFilter($filters);
    }

    function addFilterAuth() {
        $filters   = $this->getFilter();
        $filters[] = 'auth';
        $filters   = array_unique($filters);

        return $this->setFilter($filters);
    }

    function addFilterContent() {
        $filters   = $this->getFilter();
        $filters[] = 'content';
        $filters   = array_unique($filters);

        return $this->setFilter($filters);
    }

    function addFilterAdmin() {
        $filters   = $this->getFilter();
        $filters[] = 'admin';
        $filters   = array_unique($filters);

        return $this->setFilter($filters);
    }

    function addFilterGuest() {
        $filters   = $this->getFilter();
        $filters[] = 'guest';
        $filters   = array_unique($filters);

        return $this->setFilter($filters);
    }

    function addFilterCsrf() {
        $filters   = $this->getFilter();
        $filters[] = 'csrf';
        $filters   = array_unique($filters);

        return $this->setFilter($filters);
    }

    function addFilterTranslator() {
        $filters   = $this->getFilter();
        $filters[] = 'translator';
        $filters   = array_unique($filters);

        return $this->setFilter($filters);
    }


    /**
     * @param $val
     *
     * @return $this
     */
    function setNamespace($val) {
        return $this->set(__FUNCTION__, $val);
    }

    /**
     * @return mixed
     */
    protected function getNamespace() {
        return $this->get(__FUNCTION__);
    }

    /**
     * @param $val
     *
     * @return $this
     */
    function setBaseUrl($val) {
        $val = '/' . ltrim($val, '/');
        return $this->set(__FUNCTION__, $val);
    }

    /**
     * @param $val Percent
     *
     * @return $this
     */
    function setReadiness($val) {
        return $this->setAttribute(__FUNCTION__, $val);
    }

    function setAclRoles($val) {
        return $this->setAttribute(__FUNCTION__, $val);
    }

    /**
     * @param $val
     *
     * @return $this
     */
    function setIcon($val) {
        return $this->setAttribute(__FUNCTION__, $val);
    }

    /**
     * @param $val
     *
     * @return $this
     */
    function setItemIcon($val) {
        return $this->setAttribute(__FUNCTION__, $val);
    }

    /**
     * @param $val
     *
     * @return $this
     */
    function setItemMethodIcon($val, $action) {
        return $this->setAttribute(__FUNCTION__, $val, $action);
    }

    /**
     * @param $val
     *
     * @return $this
     */
    function setMethodIcon($val, $action) {
        return $this->setAttribute(__FUNCTION__, $val, $action);
    }

    /**
     * @param $val
     *
     * @return $this
     */
    function setAction($val) {
        return $this->setAttribute(__FUNCTION__, $val);
    }

    /**
     * @param $val
     *
     * @return $this
     */
    function setItemAction($val) {
        return $this->setAttribute(__FUNCTION__, $val);
    }

    /**
     * @param $val
     *
     * @return $this
     */
    function setItemMethodAction($val, $action) {
        return $this->setAttribute(__FUNCTION__, $val, $action);
    }

    /**
     * @param $val
     *
     * @return $this
     */
    function setMethodAction($val, $action) {
        return $this->setAttribute(__FUNCTION__, $val, $action);
    }

    /**
     * @param $model_name
     * @param $model_class
     *
     * @return $this
     */
    function model($model_name, $model_class) {
        \Route::model($model_name,
            $model_class,
            function () {
                return new ModelNotFoundException('Ничего не найдено');
            });

        return $this;
    }

    function getItemPattern() {
        return $this->get(__FUNCTION__, '[0-9]+');
    }

    function getItemParam() {
        return $this->get(__FUNCTION__, 'id');
    }

    function getReadiness() {
        return $this->getAttribute(__FUNCTION__, null, 1);
    }

    function getAction() {
        return $this->getAttribute(__FUNCTION__, null, 'index');
    }

    function getMethodAction($action, $default = null) {
        return $this->getAttribute(__FUNCTION__, $action, Str::slug($action, '_'));
    }

    function getItemMethodAction($action) {
        return $this->getAttribute(__FUNCTION__, $action, Str::slug($action, '_'));
    }

    function getItemAction() {
        return $this->getAttribute(__FUNCTION__, null, 'index');
    }

    function getIcon() {
        return $this->getAttribute(__FUNCTION__, 'fa fa-gear');
    }

    function getMethodIcon($action) {
        return $this->getAttribute(__FUNCTION__, $action);
    }

    function getItemMethodIcon($action) {
        return $this->getAttribute(__FUNCTION__, $action);
    }

    function getItemIcon() {
        return $this->getAttribute(__FUNCTION__, null);
    }

    function getPrefix() {
        $prefix = $this->get(__FUNCTION__);
        if ($prefix) {
            return '/' . $prefix;
        }

        return '';
    }

    function getFilter() {
        return $this->get(__FUNCTION__);
    }

    function getController() {
        $val       = $this->get(__FUNCTION__);
        $namespace = $this->getNamespace();

        return ($namespace ? $namespace . '\\' : '') . $val;
    }

    function getBaseUrl() {
        return rtrim($this->get(__FUNCTION__), '/').'/';
    }

    function normalizeMethod($method) {
        if (!in_array($method,
            [
                'get',
                'post',
                'put',
                'patch',
                'delete',
                'any'
            ])
        ) {
            return 'any';
        }

        return $method;
    }

    /**
     * @return $this
     */
    function put($method = null) {
        if (!Route::isEnable($this->as)) {
            return $this;
        }
        $as     = $this->as;
        $method = $this->normalizeMethod($method);
        $uses   = '\\'.$this->getController() . '@' . $this->getAction();
        \Route::$method($this->getBaseUrl(),
            [
                'prefix' => $this->getPrefix(),
                'as'     => $this->as,
                'before' => $this->getFilter(),
                'uses'   => $uses,
            ]);
        Route::$data[$this->as] = [
            'as'           => $this->as,
            'filter'       => $this->getFilter(),
            'readiness'    => $this->getReadiness(),
            'placeholders' => $this->getPlaceholders(),
            'icon'         => $this->getIcon(),
            'url'          => $this->getPrefix() . $this->getBaseUrl(),
            'controller'   => $this->getController(),
            'action'       => $this->getAction(),
            'uses'         => $uses,
            //            'title'        => Route::seoTitle($as),
            //            'description'  => Route::seoDescription($as),
            //            'h1'           => Route::seoH1($as),
            //            'h1_ext'       => Route::seoH1Ext($as),
        ];

        return $this;
    }


    /**
     * ->putItemMethod('edit')
     * ->putItemMethod('delete')
     *
     * @param $pattern
     */
    function putMethod($action, $method = null) {
        $action_method = Str::slug($action, '_');
        $uses          = '\\'.$this->getController() . '@' . $this->getMethodAction($action, $action_method);
        $as            = $this->as . '.' . $action;
        $url           = $this->getBaseUrl() . Str::slug($action);
        if (!Route::isEnable($as)) {
            return $this;
        }
        $method = $this->normalizeMethod($method);
        \Route::$method($url,
            [
                'prefix' => $this->getPrefix(),
                'as'     => $as,
                'before' => $this->getFilter(),
                'uses'   => $uses,
            ]);
        Route::$data[$as] = [
            'as'           => $as,
            'method'       => $method,
            'filter'       => $this->getFilter(),
            'readiness'    => $this->getReadiness(),
            'placeholders' => $this->getPlaceholders(),
            'icon'         => $this->getMethodIcon($action),
            'url'          => $this->getPrefix() . $url,
            'controller'   => $this->getController(),
            'action'       => $this->getMethodAction($action, $action),
            'uses'         => $uses,
            //            'title'        => Route::seoTitle($as),
            //            'description'  => Route::seoDescription($as),
            //            'h1'           => Route::seoH1($as),
            //            'h1_ext'       => Route::seoH1Ext($as),
        ];

        return $this;
    }


    /**
     * ->putItem('[a-z-_0-9]+')
     *
     * @param bool $pattern
     *
     * @return $this
     */
    function putItem($method = null) {
        $url = $this->getBaseUrl() . '{' . $this->getItemParam() . '}/';
        $as  = $this->as . '.item';
        if (!Route::isEnable($as)) {
            return $this;
        }
        $method = $this->normalizeMethod($method);
        $uses   = '\\'.$this->getController() . '@item';
        $route  = \Route::$method($url,
            [
                'prefix' => $this->getPrefix(),
                'as'     => $as,
                'before' => $this->getFilter(),
                'uses'   => $uses,
            ]);
        $route->where($this->getItemParam(), $this->getItemPattern());
        Route::$data[$as] = [
            'as'           => $as,
            'method'       => 'item',
            'filter'       => $this->getFilter(),
            'readiness'    => $this->getReadiness(),
            'placeholders' => $this->getPlaceholders(),
            'icon'         => $this->getItemIcon(),
            'url'          => $this->getPrefix() . $url,
            'controller'   => $this->getController(),
            'action'       => 'item',
            'uses'         => $uses,
            //            'title'        => Route::seoTitle($as),
            //            'description'  => Route::seoDescription($as),
            //            'h1'           => Route::seoH1($as),
            //            'h1_ext'       => Route::seoH1Ext($as),
        ];

        return $this;
    }

    function putItemMethod($action, $method = null) {
        $method        = $this->normalizeMethod($method);
        $action_method = Str::slug($action, '_');
        $uses          = '\\'.$this->getController() . '@' . $this->getMethodAction($action, $action_method);
        $as            = $this->as . '.item.' . $action;
        if (!Route::isEnable($as)) {
            return $this;
        }
        $url = $this->getBaseUrl() . '{' . $this->getItemParam() . '}/' . Str::slug($action) . '/';
        \Route::$method($url,
            [
                'prefix' => $this->getPrefix(),
                'as'     => $as,
                'before' => $this->getFilter(),
                'uses'   => $uses,
            ]);
        Route::$data[$as] = [
            'as'           => $as,
            'method'       => $this->getMethodAction($action, $action_method),
            'filter'       => $this->getFilter(),
            'readiness'    => $this->getReadiness(),
            'placeholders' => $this->getPlaceholders(),
            'icon'         => $this->getItemMethodIcon($action),
            'url'          => $this->getPrefix() . $url,
            'controller'   => $this->getController(),
            'action'       => $this->getMethodAction($action, $action_method),
            'uses'         => $uses,
            //            'title'        => Route::seoTitle($as),
            //            'description'  => Route::seoDescription($as),
            //            'h1'           => Route::seoH1($as),
            //            'h1_ext'       => Route::seoH1Ext($as),
        ];

        return $this;
    }

}