<?php
namespace Larakit\Route;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Larakit\Manager\ManagerSection;

class Route {

    static $data;

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
            $namespace = '';
        }

        return compact('namespace', 'as');
    }

    static function makeAction($as) {
        $parse = self::parseRouteName($as);
        $as    = Arr::get($parse, 'as');
        switch(true) {
            case (0 === mb_strpos($as, 'ajax.'));
                return 'ajax';
                break;
            case (false !== mb_strpos($as, '.item'));
                return 'item';
                break;
        }

        return 'index';
    }

    static function makeNamespace($as) {
        $parse     = self::parseRouteName($as);
        $namespace = Arr::get($parse, 'namespace');
        if($namespace) {
            $section_namespace     = explode('-', $namespace);
            $last                  = array_pop($section_namespace);
            $section_namespace     = implode('-', $section_namespace);
            $section_namespace_php = trim(Str::studly($section_namespace) . '\\' . Str::studly($last), '\\');
        } else {
            $section_namespace_php = 'Larakit';
        }
        $append = '';
        //        $sections = ['ajax' => 'ajax'];// + ManagerSection::get();
        //        foreach ($sections as $code => $name) {
        //            if (0 === mb_strpos(Arr::get($parse, 'as'), $code . '.')) {
        //                $append = '\\' . Str::studly($code);
        //            }
        //        }
        return $section_namespace_php . '\Controllers' . $append;

        return ($namespace ? \Str::studly(str_replace('-', '\\_', $namespace)) : 'Larakit') . '\Controller' . $append;
    }

    static function makeController($as) {
        $parse = self::parseRouteName($as);
        $as    = Arr::get($parse, 'as');

        return 'Controller' . Str::studly(str_replace('.', '_', $as));
    }

    static function makeBaseUrl($as) {
        $parse = self::parseRouteName($as);
        $as    = Arr::get($parse, 'as');
        if('ajax.' == mb_substr($as, 0, 5)) {
            $as = str_replace('ajax.', '!.ajax.', $as);
        }
        $as = str_replace('.', '/', $as);
        $as = str_replace('_', '-', $as);

        return '/' . trim($as, '/');
    }

    /**
     * @param null $as
     *
     * @return RouteItem
     */
    static function add($as = null) {
        return RouteItem::factory($as);
    }

    static function ajax($as = null) {
        $parse     = self::parseRouteName($as);
        $namespace = Arr::get($parse, 'namespace');
        $as        = Arr::get($parse, 'as');

        return self::add(($namespace ? $namespace . '::' : '') . 'ajax.' . $as);
    }

    static function get($as = null) {
        $as = is_null($as) ? \Route::currentRouteName() : $as;

        return isset(self::$data[$as]) ? self::$data[$as] : [];
    }

    static function _($as = null, $prop = null) {
        $route = self::get($as);
        if(!$prop) {
            return $route;
        }
        $prop = str_replace('get_', '', $prop);

        return isset($route[$prop]) ? $route[$prop] : null;
    }

    static function lang($as, $prop) {
        $prop = str_replace('get_', '', $prop);
        $key  = str_replace('.', '|', $as);
        if(mb_strpos($as, '::') !== false) {
            $key = str_replace('::', '::seo/' . $prop . '.', $key);
        } else {
            $key = 'seo/' . $prop . '.' . $key;
        }

        return \Lang::get($key);
    }

    static function get_title($as = null) {
        return self::lang($as, __FUNCTION__);
    }

    static function get_icon($as = null) {
        return self::_($as, __FUNCTION__);
    }

    static function get_h1_ext($as = null) {
        return self::lang($as, __FUNCTION__);
    }

    static function get_h1($as = null) {
        return self::lang($as, __FUNCTION__);
    }

    static function get_url($as = null) {
        return self::_($as, __FUNCTION__);
    }

    static function get_filter($as = null) {
        return self::_($as, __FUNCTION__);
    }

    static $filter_check_results = [];

    /**
     * Проверить один фильтр
     *
     * @param $as
     *
     * @return mixed|null
     */
    static function checkFilter($filter) {
        if(!isset(self::$filter_check_results[$filter])) {
            $ret             = Event::filter('route_filter:' . $filter);
            $result[$filter] = $ret;
        } else {
            $ret = Arr::get(self::$filter_check_results, $filter);
        }

        return $ret;
    }

    /**
     * @param $as
     *
     * @return mixed|null
     */
    static function checkRouteFilters($as) {
        $filters = (array) Route::get_filter($as);
        foreach($filters as $filter) {
            $ret = self::checkFilter($filter);
            if($ret) {
                return $ret;
            }
        }

        return null;
    }

    static function isEnable($as) {
        return !in_array($as, config('routes_disabled', []));
    }

    static function seo($as, $prop) {
        $_as = Route::parseRouteName($as);
        $n   = Arr::get($_as, 'namespace');
        $r   = str_replace('.', '|', Arr::get($_as, 'as'));
        $key = ($n ? $n . '::' : '') . 'seo/' . $prop . '.' . $r;
        $ret = laralang($key);

        return ($ret != $key) ? $ret : (\Config::get('app.debug') ? $key : '');
    }

    static function seoTitle($as) {
        return self::seo($as, 'title');
    }

    static function seoDescription($as) {
        return self::seo($as, 'description');
    }

    static function seoH1($as) {
        return self::seo($as, 'h1');
    }

    static function seoH1Ext($as) {
        return self::seo($as, 'h1_ext');
    }

    protected static function route_section($vendor, $package) {

    }

    static function thumb($vendor, $package) {

    }

}

\Route::pattern('any', '.+');
\Route::pattern('user', '[\w\d]+');
\Route::pattern('action', '[\w-\d]+');
\Route::pattern('date', '\d+\-\d+\-\d+');
\Route::pattern('id', '\d+');
