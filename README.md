# [LarakitRoute] 

Проблема, которые решает данный инструмент:
- не надо держать в голове структуру формирования роутов и групп за счет использования автодополнения кода в IDE

Массивы - это удобно и просто. Но каждый раз лезти в документацию - это  потери рабочего времени
~~~php
Route::group(['middleware' => 'auth'], function () {
    Route::get('/', function ()    {
        // Uses Auth Middleware
    });

    Route::get('user/profile', function () {
        // Uses Auth Middleware
    });
});
~~~

Рассмотрим типовые потребности, которые легко и красиво реализовывает данный пакет

###Простая страница с расширением
~~~php
\Larakit\Route\Route::group('about')
  ->routeIndex()
  ->setExt('html')
  ->put();
~~~
<img src="https://habrastorage.org/files/023/f58/34a/023f5834aef342e3b050b6c9f5de7a93.png" />

Все хорошо, но мы хотим, чтобы эта страница была доступна только методом GET

###Ограничим доступные методы
~~~php
\Larakit\Route\Route::group('about')
  ->routeIndex()
  ->setExt('html')
  ->put('get');
~~~
<img src="https://habrastorage.org/files/ffb/34b/595/ffb34b59519446a5a8b1d6c2c9bfa8bd.png" />

Заметьте, имя контроллера, пространство имен, имя метода контроллера была сгенерированы на основе имени роута автоматически, но это можно изменить.

###Изменение пространства имен
По умолчанию, пространство имен берется из App::getNamespace(). Но его можно изменить на уровне группы:
~~~php
\Larakit\Route\Route::group('about')
    ->setNamespace('Qwerty')
    ->routeIndex()
    ->setExt('html')
    ->put('get');
~~~
<img src="https://habrastorage.org/files/e41/cfe/018/e41cfe01836b420dadb805f9b12f7942.png" />

Все, из полученного исключения сразу понятно, какое пространство имен контроллера ожидалось - создаем его и все работает.

###Изменение имени контроллера
~~~php
\Larakit\Route\Route::group('about')
    ->routeIndex()
    ->setController('Static')
    ->setExt('html')
    ->put('get');
~~~
<img src="https://habrastorage.org/files/b6e/e16/23c/b6ee1623cf6c4acd91e62d4f4db6ca98.png" />

Так же можно задать полное имя класса контроллера
~~~php
use \App\Http\Controllers\StaticController;
\Larakit\Route\Route::group('about')
    ->routeIndex()
    ->setControllerClass(StaticController::class)
    ->setExt('html')
    ->put('get');
~~~
<img src="https://habrastorage.org/files/94d/27c/afb/94d27cafbbc14fd5a5f847d75c843ccf.png" />

###Изменение домена
Если у вас на одном проекте работают много доменов, например
~~~
habrahabr.ru
<username>.habrahabr.ru
~~~
то можно сделать так, чтобы роут был доступен только на одном домене
~~~php
\Larakit\Route\Route::group('about')
    ->setDomain('habrahabr.ru')
    ->routeIndex()
    ->put('get');
~~~
<img src="https://habrastorage.org/files/3d5/420/49c/3d542049c7be49568c21b0083a7793bb.png" />

или так:

~~~php
\Larakit\Route\Route::group('about')
    ->setDomain('*.habrahabr.ru')
    ->routeIndex()
    ->setControllerClass(\App\Http\Controllers\StaticController::class)
    ->put('get');
\Larakit\Route\Route::group('about_groove')
    ->setDomain('groove.habrahabr.ru')
    ->routeIndex()
    ->setControllerClass(\App\Http\Controllers\StaticController::class)
    ->put('get');
~~~
<img src="https://habrastorage.org/files/f66/de9/b93/f66de9b938024a6aa990327256c0d57b.png" />

###Создание группы связанных роутов
Попытаемся сделать связку роутов для админки, которые будут заниматься управлением пользователями
В итоге должна получиться такая структура
~~~
/admincp/users/
/admincp/users/add/
/admincp/users/123/
/admincp/users/123/edit
/admincp/users/123/delete
~~~


**Рекомендация**: разбивайте имя роута точками по слэшам

~~~php
$group = \Larakit\Route\Route::group('admin.users');
$group->routeIndex()->setAction('index')->put();
~~~
<img src="https://habrastorage.org/files/cfe/d3f/42e/cfed3f42e2734fcf9ca41c229f35eb23.png" />

Видим, что автоматически сформированный URL нас не устраивает, поправим его (он автоматически поменяется для всех вложенных в группу страниц)
~~~php
$group = \Larakit\Route\Route::group('admin.users')
  ->setBaseUrl('/admincp/users');

#/admincp/users/  
$group->routeIndex()->setAction('index')->put();
~~~

<img src="https://habrastorage.org/files/876/76a/cfc/87676acfcde744b488d13f2ca08fcbbb.png" />

Зарегистрируем страницу добавления пользователя
~~~php
$group = \Larakit\Route\Route::group('admin.users')
    ->setBaseUrl('/admincp/users');
    
#/admincp/users/    
$group->routeIndex()->setAction('index')->put();

#/admincp/users/add
$group->routeIndexMethod('add')->put();
~~~

<img src="https://habrastorage.org/files/129/1c4/a07/1291c4a07a3a42c9b628c98f9d18e627.png" />

Сделаем так, чтобы у нас при запросе на этот роут методом GET отдавалась страница с формой добавления пользователя, а при запросе методом POST производилась попытка валидации и сохранения пользователя.

Причем, чтобы этот функционал был разнесен по разным методам контроллера.

~~~php
$group = \Larakit\Route\Route::group('admin.users')
    ->setBaseUrl('/admincp/users');
    
#/admincp/users/    
$group->routeIndex()->setAction('index')->put();

#/admincp/users/add
$group->routeIndexMethod('add')->setAction('create')->put('get');
$group->routeIndexMethod('add')->setAction('store')->put('post');
~~~
<img src="https://habrastorage.org/files/34c/e42/3e5/34ce423e521f44b297b31025a43bf2ba.png" />

Продолжаем! Добавим вывод страницы пользователя

~~~php
$group = \Larakit\Route\Route::group('admin.users')
    ->setBaseUrl('/admincp/users');
    
#/admincp/users/    
$group->routeIndex()->setAction('index')->put();

#/admincp/users/add
$group->routeIndexMethod('add')->setAction('create')->put('get');
$group->routeIndexMethod('add')->setAction('store')->put('post');

#/admincp/users/123/
$group->routeIndexItem()->put();
~~~
<img src="https://habrastorage.org/files/514/292/c60/514292c60e4c42d3ad8531c910f7c0c6.png" />

Причем, автоматически будет произведена проверка, что параметр id является целым числом

Но нам хотелось бы, чтобы производилась проверка на наличие модели с таким идентификатором. Для этого на уровне группы добавим модель и опишем ее.

~~~php
$group = \Larakit\Route\Route::group('admin.users')
    ->setModel('user', \App\User::class)
    ->setBaseUrl('/admincp/users');
    
#/admincp/users/    
$group->routeIndex()->setAction('index')->put();

#/admincp/users/add
$group->routeIndexMethod('add')->setAction('create')->put('get');
$group->routeIndexMethod('add')->setAction('store')->put('post');


#/admincp/users/123/
$group->routeIndexItem()->put();
~~~
<img src="https://habrastorage.org/files/284/88a/c27/28488ac2764f465a880d174298ca5dff.png" />

Обратите внимание, что мы сменили имя параметра "id" на "user"

Добавим оставшиеся методы
~~~php
$group = \Larakit\Route\Route::group('admin.users')
    ->setModel('user', \App\User::class)
    ->setBaseUrl('/admincp/users');

#/admincp/users/
$group->routeIndex()->setAction('index')->put();

#/admincp/users/add/
$group->routeIndexMethod('add')->setAction('create')->put('get');
$group->routeIndexMethod('add')->setAction('store')->put('post');

#/admincp/users/123/
$group->routeIndexItem()->put();

#/admincp/users/123/edit
$group->routeIndexItemMethod('edit')->setAction('update')->put('get');
$group->routeIndexItemMethod('edit')->setAction('store')->put('post');

#/admincp/users/123/delete
$group->routeIndexItemMethod('delete')->put('delete');

~~~
<img src="https://habrastorage.org/files/5b3/010/07a/5b301007a4ce4b29b89e08e9d55a962a.png" />
