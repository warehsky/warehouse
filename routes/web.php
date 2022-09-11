<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
//Route::get('/', 'HomeController@index')->name('/');


Auth::routes();
Route::middleware(['adminauth'])->group(function () {
    Route::get('/', function(){ return view('Admin.dashboard'); })->name('home');
    Route::resource('orders', 'OrdersController');
    Route::resource('expenses', 'ExpensesController');
    Route::resource('operations', 'OperationsController');
    Route::resource('cargos', 'CargosController');
    Route::get('getOperations', 'OperationsController@getOperations')->name('getOperations');
    Route::get('getClients', 'ClientsController@getClients')->name('getClients');
    Route::any('saveClient', 'ClientsController@saveClient')->name('saveClient');
    Route::get('getItems', 'ItemsController@getItems')->name('getItems');
    Route::any('saveItem', 'ItemsController@saveItem')->name('saveItem');
    Route::get('getOrder', 'OrdersController@getOrder')->name('getOrder');
    Route::get('orderUnlock', 'OrdersController@orderUnlock')->name('orderUnlock');
    Route::any('saveOrder', 'OrdersController@saveOrder')->name('saveOrder');
    Route::get('getReminds', 'OrdersController@getReminds')->name('getReminds');
    Route::any('saveExpense', 'ExpensesController@saveExpense')->name('saveExpense');
    Route::get('getExpense', 'ExpensesController@getExpense')->name('getExpense');
    Route::get('expenseUnlock', 'ExpensesController@expenseUnlock')->name('expenseUnlock');

    Route::resource('permissions', 'Admin\PermissionsController');
    Route::delete('permissions_mass_destroy', 'Admin\PermissionsController@massDestroy')->name('permissions.mass_destroy');
    Route::resource('roles', 'Admin\RolesController');
    Route::delete('roles_mass_destroy', 'Admin\RolesController@massDestroy')->name('roles.mass_destroy');
});

    
//Route::get('/home', function(){ return redirect(route('admin')); })->name('home');

//Route::get('loginadmin', 'Auth\LoginController@loginadmin')->name('login.admin');
Route::get('admin/login', 'Auth\LoginAdminController@showLoginForm')->name('admin.login');
Route::post('admin/login', 'Auth\LoginAdminController@login')->name('admin.login');
Route::post('admin/logout', 'Auth\LoginAdminController@logout')->name('admin.logout');
Route::group(['prefix' => 'admin', 'middleware' => ['adminauth']], function () {
    Route::get('/', function () {
        return view('Admin.dashboard');
    });
    Route::resource('userAdmins', 'Admin\UsersController');
    
 
    //Отчеты
    

    //Настройки
    Route::get('/getOptions', 'Admin\OptionsController@getOptions')->name('getOptions');
    Route::get('/updateoptions', 'Admin\OptionsController@update')->name('updateoptions');
    Route::get('/optionsIndex', 'Admin\OptionsController@index')->name('optionsIndex');
    Route::get('/getOptionGroups', 'Admin\OptionsController@getOptionGroups')->name('getOptionGroups');
    Route::get('/newItems', 'Admin\OptionsController@newItems')->name('newItems');
    Route::get('/bestSeller', 'Admin\OptionsController@bestSeller')->name('bestSeller');

   
});

