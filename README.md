# Yukon
The simple router

## Define routes
All routes define in your project root folder. Create routes.php file in the configs folder.

## Example:

```php
use FoxTool\Yukon\Core\Router;

Router::get('/', 'HomeController@index');
Router::get('/sign-in', 'AuthController@index');
Router::post('/authentication', 'AuthController@authentication');
```

Class _Router_ has static methods for each HTTP methods:

## Example:

```php
Router::get(...)
Router::post(...)
Router::put(...)
Router::patch(...)
Router::delete(...)
```

Each method gets two parameters, a route string and a "controller@action" string
or a closure function.

## Example:

```php
Router::get('/', 'HomeController@index');
Router::get('/version', function() {
    echo 'v 1.0';
});
```

Routes can be grouped by common prefix

## Example:

```php
Router::prefix('/admin')->group(function() {
    Router::get('/users', 'UserController@index');
    Router::post('/users', 'UserController@create');
    Router::get('/users/{id}', 'UserController@show');
    Router::put('/users/{id}', 'UserController@update');
    Router::delete('/users/{id}', 'UserController@delete');
});
```
In this case, we have combined routes: '/admin/user', '/admin/users/{id}' etc.

## Custom Controllers
Custom controllers should be created in the _app/Controller_ folder and should be extended
from the base controller FoxTool\Yukon\Core\Controller.

## Example:

```php
use FoxTool\Yukon\Core\Controller;

class UserController extends Controller {
    ...
}
```

## Middleware (Authentication Guard)

Each route can be protected by the guard. Currently, implemented the guard for protecting
API routes. It's the _ApiAuthMiddleware_ and it uses Bearer token to check access rights.

## Example:

```php
// Single route
Router::get('/posts', 'UserController@index')->middleware('api');

// Groups of routes
Router::prefix('/api')->group(function() {
    Router::get('/users', 'UserController@create')->middleware('api');
    Router::post('/users', 'UserController@create')->middleware('api');
    Router::put('/users/{id}', 'UserController@update')->middleware('api');
    Router::delete('/users/{id}', 'UserController@delete')->middleware('api');
});
```
