<?php
/**
 * Created by Larakit.
 * Link: http://github.com/larakit
 * User: Alexey Berdnikov
 * Date: 29.06.16
 * Time: 9:37
 */

\Larakit\Twig::register_function('icon_by_uri', function ($uri) {
    return \Larakit\Route\Route::getIconByUri($uri);
});
