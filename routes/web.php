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
    Route::get('getClients', 'ClientsController@getClients')->name('getClients');
    Route::any('saveClient', 'ClientsController@saveClient')->name('saveClient');
    Route::get('getItems', 'ItemsController@getItems')->name('getItems');
    Route::any('saveItem', 'ItemsController@saveItem')->name('saveItem');
    Route::get('getOrder', 'OrdersController@getOrder')->name('getOrder');
    Route::any('saveOrder', 'OrdersController@saveOrder')->name('saveOrder');
    Route::get('getReminds', 'OrdersController@getReminds')->name('getReminds');
    Route::any('saveExpense', 'ExpensesController@saveExpense')->name('saveExpense');
    Route::get('getExpense', 'ExpensesController@getExpense')->name('getExpense');

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
    
    Route::delete('itemgroups_mass_destroy', 'Admin\ItemGroupsController@massDestroy')->name('itemgroups.mass_destroy');
    Route::resource('items', 'Admin\ItemsLinkController');
    Route::get('itemsSearch', 'Admin\ItemsLinkController@itemsSearch')->name('items.search');
    Route::get('/suggests', 'Admin\ItemGroupsController@suggests')->name('suggests');
    
    Route::resource('banners', 'Admin\BannersController');
    Route::delete('bannersMassDestroy','Admin\BannersController@massDestroy')->name('banners.MassDestroy');
    Route::resource('bannerItems', 'Admin\BannerItemsController');
    Route::delete('bannerItemsMassDestroy','Admin\BannerItemsController@massDestroy')->name('bannerItems.MassDestroy');
    


    //Пользователи
    Route::get('WebUsers','Admin\WebUsersController@showWebUsers')->name('WebUsers');
    Route::get('WebUsers/{id}','Admin\WebUsersController@show')->name('WebUsers_show');
    Route::post('WebUsers/addDiscount','Admin\WebUsersController@addDiscount')->name('WebUsers_addDiscount');
    Route::get('WebUsers/sendSMS/{id}','Admin\WebUsersController@WebUsersSMS')->name('WebUsers_WebUsersSMS');
    Route::get('WebUserNote','Admin\WebUsersController@WebUserNote')->name('WebUserNote');
    Route::delete('WebUserNoteDelete/{id}','Admin\WebUsersController@WebUserNoteDelete')->name('WebUserNoteDelete');
    Route::get('changeStatusDiscount','Admin\WebUsersController@changeStatusDiscount')->name('changeStatusDiscount');
    Route::get('getHistorySms','Admin\WebUsersController@getHistorySms')->name('getHistorySms');
    Route::get('/getStockForPerson','Admin\WebUsersController@getStockForPerson')->name('getStockForPerson');
    Route::get('/addNewNoteForWebUser','Admin\WebUsersController@addNewNoteForWebUser')->name('addNewNoteForWebUser');
    Route::get('/getHistoryNoteForPerson','Admin\WebUsersController@getHistoryNoteForPerson')->name('getHistoryNoteForPerson');
    Route::get('/updateNoteForWebUser','Admin\WebUsersController@updateNoteForWebUser')->name('updateNoteForWebUser');
    Route::get('/changeStatusWebUsersNote','Admin\WebUsersController@changeStatusWebUsersNote')->name('changeStatusWebUsersNote');
    Route::get('/getAllLostDelivery','Admin\WebUsersController@getAllLostDelivery')->name('getAllLostDelivery');
    Route::get('/changeWebUserAutoown','Admin\WebUsersController@changeWebUserAutoown')->name('changeWebUserAutoown');
    Route::get('/getSettings','Admin\WebUsersController@getSettings')->name('getSettings');

    
 
    //Отчеты
    

    //Настройки
    Route::get('/getOptions', 'Admin\OptionsController@getOptions')->name('getOptions');
    Route::get('/updateoptions', 'Admin\OptionsController@update')->name('updateoptions');
    Route::get('/optionsIndex', 'Admin\OptionsController@index')->name('optionsIndex');
    Route::get('/getOptionGroups', 'Admin\OptionsController@getOptionGroups')->name('getOptionGroups');
    Route::get('/newItems', 'Admin\OptionsController@newItems')->name('newItems');
    Route::get('/bestSeller', 'Admin\OptionsController@bestSeller')->name('bestSeller');

   
});

