[![Total Downloads](https://poser.pugx.org/larakit/lk-route/d/total.svg)](https://packagist.org/packages/larakit/lk-route)
[![Latest Stable Version](https://poser.pugx.org/larakit/lk-route/v/stable.svg)](https://packagist.org/packages/larakit/lk-route)
[![Latest Unstable Version](https://poser.pugx.org/larakit/lk-route/v/unstable.svg)](https://packagist.org/packages/larakit/lk-route)
[![License](https://poser.pugx.org/larakit/lk-route/license.svg)](https://packagist.org/packages/larakit/lk-route)

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

###Обычная страница "О нас"
Добавим роут в ./app/Http/routes.php
~~~php
\Larakit\Route\Route::item('about')
    ->put();
~~~

Посмотрим список роутов в консоли:
~~~bash
php artisan route:list
~~~
<img src="https://habrastorage.org/files/33e/066/e64/33e066e64ec84bca90aef7e1ea71318e.png"/>

Создадим контроллер "App\Http\Controllers\AboutController" и запустим команду **php artisan route:list** еще раз:

<img src="https://habrastorage.org/files/720/323/3ce/7203233ce064400d8ea20229fd40a946.png"/>

Все хорошо, но мы хотим, чтобы эта страница была доступна только методом GET

###Ограничим доступные методы
~~~php
\Larakit\Route\Route::item('about')
    ->put('get');
~~~
<img src="https://habrastorage.org/files/3be/04a/1fc/3be04a1fc8ec4ceebdf9f759e83c955b.png"/>

Заметьте, имя контроллера, пространство имен, имя метода контроллера была сгенерированы на основе имени роута автоматически, но это можно изменить.

###Изменение пространства имен
По умолчанию, пространство имен берется из App::getNamespace(). Но его можно изменить на уровне группы:
~~~php
\Larakit\Route\Route::item('about')
    ->setNamespace('Qwerty')
    ->put('get');
~~~
<img src="https://habrastorage.org/files/f9b/53e/3fb/f9b53e3fb31e48dba08a7cfa33ee8467.png"/>

Из полученного исключения сразу понятно, какое пространство имен контроллера ожидалось - создаем его и все работает.

###Изменение имени контроллера
~~~php
\Larakit\Route\Route::item('about')
    ->setNamespace('Qwerty')
    ->setController('AboutPage')
    ->put('get');
~~~
<img src="https://habrastorage.org/files/f27/57e/c07/f2757ec071a143bfbb365ac3b24c0dd4.png"/>

Так же можно задать callback
~~~php
\Larakit\Route\Route::item('about')
    ->setUses(function(){
        return 'Callback Text!';
    })
    ->put('get');
~~~
<img src="https://habrastorage.org/files/f2e/7df/ffd/f2e7dfffded6470c9b459dbbc6764e3e.png"/>

###Изменение домена
Если у вас на одном проекте работают много доменов, например
~~~
habrahabr.ru
<username>.habrahabr.ru
~~~
то можно сделать так, чтобы роут был доступен только на одном домене
~~~php
\Larakit\Route\Route::item('about')
    ->setDomain('habrahabr.ru')
    ->setUses(function(){
        return 'Callback Text!';
    })
    ->put('get');
~~~
<img src="https://habrastorage.org/files/0b5/ca9/b3e/0b5ca9b3e4b7468b9f77955224d88afb.png"/>

или так:

~~~php
\Larakit\Route\Route::item('about')
    ->setDomain('*.habrahabr.ru')
    ->setUses(function(){
        return 'Callback Text!';
    })
    ->put('get');
\Larakit\Route\Route::item('about_groove')
    ->setDomain('groove.habrahabr.ru')
    ->setUses(function(){
        return 'About Groove!';
    })
    ->put('get');
~~~
<img src="https://habrastorage.org/files/ea5/c2d/3f7/ea5c2d3f737c4573b18e5decd0edc307.png"/>

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
\Larakit\Route\Route::item('admin.users')
    //добавим страницу со списком пользователей
    ->put();
~~~
<img src="https://habrastorage.org/files/900/cf8/703/900cf870325b4db6b1549f4dd365f5d7.png"/>

Видим, что автоматически сформированный URL нас не устраивает, поправим его (он автоматически поменяется для всех вложенных в группу страниц)
~~~php
$group = \Larakit\Route\Route::item('admin.users')
    //изменим базовый URL
    ->setBaseUrl('admincp/users');

#/admincp/users/
$group->put();
~~~

<img src="https://habrastorage.org/files/3c6/263/fe6/3c6263fe6bd64145b46e06be3525e284.png"/>

Зарегистрируем страницу добавления пользователя
~~~php
$group = \Larakit\Route\Route::item('admin.users')
    //изменим базовый URL
    ->setBaseUrl('admincp/users');

#/admincp/users/
$group->put();

#/admincp/users/add
$group
    ->addSegment('add')
    ->put();
~~~

<img src="https://habrastorage.org/files/55d/f5e/614/55df5e6148e6422b9f9f33e422ff8805.png"/>

Сделаем так, чтобы у нас при запросе на этот роут методом GET отдавалась страница с формой добавления пользователя, а при запросе методом POST производилась попытка валидации и сохранения пользователя.

Причем, чтобы этот функционал был разнесен по разным методам контроллера.

~~~php
$group = \Larakit\Route\Route::item('admin.users')
    //изменим базовый URL
    ->setBaseUrl('admincp/users');

#/admincp/users/
$group->put();

#/admincp/users/add
$group
    ->addSegment('add')
    ->setAction('create')
    ->put('get')
    ->addSegment('add')
    ->setAction('store')
    ->put('post');
~~~
<img src="https://habrastorage.org/files/75a/6dd/660/75a6dd660ff945da9c6b48aa90cf73ee.png"/>

Продолжаем! Добавим вывод страницы пользователя

~~~php
$group = \Larakit\Route\Route::item('admin.users')
    //изменим базовый URL
    ->setBaseUrl('admincp/users');

#/admincp/users/
$group->put();

#/admincp/users/add
$group
    ->addSegment('add')
    ->setAction('create')
    ->put('get')
    ->addSegment('add')
    ->setAction('store')
    ->put('post');

#/admincp/users/{user_id}
$group
    //сделаем сброс добавленного сегмента "add" чтобы начать формировать новую ветку от базового URL
    ->clearSegments()
    ->addSegment('{user_id}')
    //зададим паттерн для этого параметра только этого роута
    //->addPattern('user_id', '[a-z0-9]+')
    //true означает проверку на целое число (как самое часто употребляемое)
    ->addPattern('user_id', true)
    ->put('get');
~~~
<img src="https://habrastorage.org/files/857/489/70c/85748970cb43484d8026f354f32d0fae.png"/>

Причем, автоматически будет произведена проверка, что параметр id является целым числом

Но нам хотелось бы, чтобы производилась проверка на наличие модели с таким идентификатором. Для этого на уровне группы добавим модель и опишем ее.

~~~php
//проверка на наличие пользователя с таким идентификатором
Route::model('user_id', \App\User::class, function ($id) {
    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User with ID='.$id.' not found!');
});

$group = \Larakit\Route\Route::item('admin.users')
    //изменим базовый URL
    ->setBaseUrl('admincp/users');

#/admincp/users/
$group->put();

#/admincp/users/add
$group
    ->addSegment('add')
    ->setAction('create')
    ->put('get')
    ->addSegment('add')
    ->setAction('store')
    ->put('post');

#/admincp/users/{user_id}
$group
    //сделаем сброс последнего добавленного сегмента "add" чтобы начать формировать новую ветку от /admincp/users/
    ->popSegment()
    ->addSegment('{user_id}')
    //зададим паттерн для этого параметра только этого роута
    //->addPattern('user_id', '[a-z0-9]+')
    ->addPattern('user_id', true)
    ->put('get');
~~~

Добавим оставшиеся методы
~~~php
//проверка на наличие пользователя с таким идентификатором
Route::model('user_id', \App\User::class, function ($id) {
    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User with ID='.$id.' not found!');
});

$group = \Larakit\Route\Route::item('admin.users')
    //изменим базовый URL
    ->setBaseUrl('admincp/users');

#/admincp/users/
$group->put();

#/admincp/users/add
$group
    ->addSegment('add')
    ->setAction('create')
    ->put('get')
    ->addSegment('add')
    ->setAction('store')
    ->put('post');

#/admincp/users/{user_id}
$group
    //сделаем сброс последнего добавленного сегмента "add" чтобы начать формировать новую ветку от /admincp/users/
    ->popSegment()
    ->addSegment('{user_id}')
    //зададим паттерн для этого параметра только этого роута
    //->addPattern('user_id', '[a-z0-9]+')
    ->addPattern('user_id', true)
    ->put('get');

#/admincp/users/{user_id}/edit
$group
    ->addSegment('edit')
    ->setAction('update')
    ->put('get')
    ->addSegment('edit')
    ->setAction('store')
    ->put('post');

#/admincp/users/{user_id}/delete
$group
    //сделаем сброс последнего добавленного сегмента "edit" чтобы начать формировать новую ветку от /admincp/users/{user_id}/
    ->popSegment()
    ->addSegment('delete')
    ->put('delete');

~~~
<img src="https://habrastorage.org/files/2d1/207/327/2d1207327b2e41579b8137f99926d5ef.png"/>

###Роуты с параметрами

Сделаем роут для перехода из контекстной рекламы по ссылке с UTM-метками.

При добавлении параметров роута следующие правила:
- сперва передаем имя параметра, если он не обязателен - добавьте после него вопрос, например 'param_name?'
- затем передаем правило валидации (регулярное выражение)

~~~
/{utm_source}/{utm_medium}/{utm_campaign}/{utm_term}/{utm_content}
~~~

~~~php
$group = \Larakit\Route\Route::item('utm')
    ->setController('Utm')
    
    //Источник кампании - utm_source
    //Источник перехода: google, yandex, newsletter и т.п.
    ->addSegment('{utm_source}')
    ->addPattern('utm_source', '(google|yandex|newsletter)')
    
    //Канал кампании - utm_medium
    //Тип трафика: cpc, ppc, banner, email и т.п.
    ->addSegment('{utm_medium}')
    ->addPattern('utm_medium', '[\w-\d]+')
    
    //Название кампании - utm_campaign
    //впишем сюда ID компании из местной CRM (целое число)
    //можно было вручную вписать "'[0-9]+'", но "true" короче и чаще всего используется
    ->addSegment('{utm_campaign}')
    ->addPattern('utm_campaign', true)
    
    //Ключевое слово - utm_term
    //(не обязательное поле, без валидации)
    ->addSegment('{utm_term?}')
    
    //Содержание кампании - utm_content
    //(не обязательное поле, без валидации)
    ->addSegment('{utm_content?}')
    ->put();


~~~
<img src="https://habrastorage.org/files/3e3/d86/f5f/3e3d86f5ff7044e29e85c69721602ee7.png"/>

###Роуты с расширением
Для того, чтобы сделать роут about.html и data.json нужно в метод put() вторым, необязательным параметром передать расширение
~~~php
\Larakit\Route\Route::item('about')
    ->put('get', 'html');
\Larakit\Route\Route::item('data')
    ->setUses(function(){
        return [
            [
                'name' => 'Toyota',
            ],
            [
                'name' => 'Nissan',
            ],
        ];
    })
    ->put('get', 'json');
~~~    
<img src="https://habrastorage.org/files/577/1ca/fdc/5771cafdca3745e7aa438754529e5bc2.png"/>

Вот так легко и непринужденно мы добились всех поставленных целей и избавились от необходимости держать в голове принцип построения роутинга в Laravel5
