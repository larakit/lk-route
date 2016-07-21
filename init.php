<?php
/**
 * Created by Larakit.
 * Link: http://github.com/larakit
 * User: Alexey Berdnikov
 * Date: 29.06.16
 * Time: 9:37
 */

\Larakit\Twig::register_function('icon_by_route', function ($route_name) {
    return \Larakit\Route\Route::routeIcons($route_name);
});
