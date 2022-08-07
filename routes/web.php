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
    //Обратная связь
    Route::get('/email','Admin\EmailController@emails')->name('email');
    Route::get('/email/{id}','Admin\EmailController@showOneEmail')->name('emailshow');
    Route::post('/email/{id}','Admin\EmailController@SendMail')->name('sendemail');
    Route::get('/email/{id}/look','Admin\EmailController@lookemail')->name('lookemail');
    //Страница акций
    Route::resource('pageStock','Admin\PageStockController');
    Route::get('pageStock/changeStatus/{id}', 'Admin\PageStockController@changeStatus')->name('changeStatus');
    //Страница вакансий
    Route::resource('vacancy', 'Admin\VacancyController');
    Route::get('vacancy/changeRequired/{id}', 'Admin\VacancyController@changeRequired')->name('changeRequired');
    Route::get('/helpPage', 'Admin\VacancyController@helpPageVacancy')->name('vacancy.helpPage');
    Route::resource('vacancyProperty', 'Admin\VacancyPropertyController');
    Route::delete('vacancyProperty/{id}/deleteProperty', 'Admin\VacancyPropertyController@destroyProperty')->name('destroyProperty');
    Route::post('vacancyProperty/{id}/storeProperty', 'Admin\VacancyPropertyController@storeProperty')->name('storeProperty');
    Route::get('vacancyProperty/{id}/editProperty/{editId}', 'Admin\VacancyPropertyController@editProperty')->name('editProperty');
    Route::post('vacancyProperty/{id}/updateProperty/{editId}', 'Admin\VacancyPropertyController@updateProperty')->name('updateProperty');
    Route::resource('vacancySpecialty', 'Admin\VacancySpecialtyController');
    Route::delete('vacancySpecialty/{id}/deleteSpecialty', 'Admin\VacancySpecialtyController@destroySpecialty')->name('destroySpecialty');
    Route::post('vacancySpecialty/{id}/storeSpecialty', 'Admin\VacancySpecialtyController@storeSpecialty')->name('storeSpecialty');
    Route::get('vacancySpecialty/{id}/editSpecialty/{editId}', 'Admin\VacancySpecialtyController@editSpecialty')->name('editSpecialty');
    Route::post('vacancySpecialty/{id}/updateSpecialty/{editId}', 'Admin\VacancySpecialtyController@updateSpecialty')->name('updateSpecialty');
    Route::delete('vacancy/MassDestroy', 'Admin\VacancyController@MassDestroy')->name('vacancy.MassDestroy');
    
    //Изменение последовательности товара
    Route::get('changeSequence/index','Admin\ChangeSequenceItems@index')->name('changeSequence.index');
    Route::get('changeSequence/findTagItems','Admin\ChangeSequenceItems@findTagItems')->name('changeSequence.findTagItems');
    Route::get('changeSequence/update','Admin\ChangeSequenceItems@update')->name('changeSequence.update');
    Route::get('changeSequence/allTag','Admin\ChangeSequenceItems@getAllTag')->name('changeSequenceItemsAllTag');
    Route::get('changeSequence/getAllParentTag','Admin\ChangeSequenceItems@getAllParentTag')->name('changeSequenceItemsGetAllParentTag');
    Route::get('changeSequence/getParentId','Admin\ChangeSequenceItems@getParentId')->name('changeSequenceItemsGetParentId');
    
    //Промокоды
    Route::get('promocode','Admin\PromocodeController@index')->name('promocode.index');
    Route::get('getAllPromocode','Admin\PromocodeController@getAllPromocode')->name('promocode.getAllPromocode');
    Route::get('PageCreatePromocode','Admin\PromocodeController@createPage')->name('promocode.pageCreate');
    Route::get('createManyCard','Admin\PromocodeController@createManyCard')->name('promocode.createManyCard');
    Route::post('createPromocode','Admin\PromocodeController@createPromocode')->name('promocode.create');
    Route::post('addManyDiscountCart','Admin\PromocodeController@addManyDiscountCart')->name('promocode.addManyDiscountCart');
    Route::get('showDiscountType','Admin\PromocodeController@showDiscountType')->name('showDiscountType');
    Route::get('editDiscountType','Admin\PromocodeController@editDiscountType')->name('editDiscountType');
    Route::get('deleteDiscountType','Admin\PromocodeController@deleteDiscountType')->name('deleteDiscountType');
    Route::get('addDiscountType','Admin\PromocodeController@addDiscountType')->name('addDiscountType');
    Route::get('extendPromocode','Admin\PromocodeController@extendPromocode')->name('extendPromocode');
    Route::post('UploadFileExcel','Admin\PromocodeController@UploadFileExcel')->name('UploadFileExcel');
    Route::get('activeWebUsers','Admin\PromocodeController@activeWebUsers')->name('activeWebUsers');
    
    //загрузка изображений
    Route::get('loadOneImageIndex','Admin\LoadImageController@index')->name('loadOneImageIndex');
    Route::post('loadOneImage','Admin\LoadImageController@loadImage')->name('loadOneImage');
    Route::get('getAllImage','Admin\LoadImageController@getAllImage')->name('getAllImage');
    Route::get('deleteImage','Admin\LoadImageController@deleteImage')->name('deleteImage');

    
    //Волны
    Route::get('timeWaves','Admin\TimeWavesController@index')->name('timeWaves');
    Route::get('getTimeWaves','Admin\TimeWavesController@getTimeWaves')->name('getTimeWaves');
    Route::get('getDeliveryZones','Admin\TimeWavesController@getDeliveryZones')->name('getDeliveryZones');
    Route::get('timeWaves/changeStatus','Admin\TimeWavesController@changeStatus')->name('timeWaves/changeStatus');
    Route::get('timeWaves/timeWavesDisable','Admin\TimeWavesController@timeWaveDisable')->name('timeWaves/timeWaveDisable');
    Route::get('timeWaves/deleteTimeWaveDisable','Admin\TimeWavesController@deleteTimeWaveDisable')->name('timeWaves/deleteTimeWaveDisable');
    Route::get('timeWaves/OrderLimit','Admin\TimeWavesController@indexOrderLimit')->name('timeWaves/OrderLimit');
    Route::get('timeWaves/getOrderLimit','Admin\TimeWavesController@getOrderLimit')->name('timeWaves/getOrderLimit');
    Route::get('timeWaves/saveOrderLimit','Admin\TimeWavesController@saveOrderLimit')->name('timeWaves/saveOrderLimit');
    Route::get('timeWaves/getEditLimitOrder','Admin\TimeWavesController@getEditLimitOrder')->name('timeWaves/getEditLimitOrder');

    
    

    //Номера телефонов
    Route::get('phoneNumber/updateDataNumbers','Admin\PhoneNumberController@updateDataNumbers')->name('updateDataNumbers');
    Route::resource('phoneNumber','Admin\PhoneNumberController');
    Route::delete('PhoneMassDestroy','Admin\PhoneNumberController@massDestroy')->name('PhoneMassDestroy');
    Route::get('phoneNumber/unsubscribe/{id}','Admin\PhoneNumberController@changeUnsubscribe')->name('ChangePhoneUnsubscribe');
    Route::post('phoneNumber/filter','Admin\PhoneNumberController@FilterIndex')->name('PhoneFilterIndex');
    Route::get('exportPhoneNumberStatistic','Admin\PhoneNumberController@exportPhoneNumberStatistic')->name('exportPhoneNumberStatistic');
    Route::get('getPhoneNumberStatistic','Admin\PhoneNumberController@getPhoneNumberStatistic')->name('getPhoneNumberStatistic');
    Route::get('phoneNumberStatistic','Admin\PhoneNumberController@phoneNumberStatistic')->name('phoneNumberStatistic');
    Route::get('updatePhoneNumberStatistic','Admin\PhoneNumberController@updatePhoneNumberStatistic')->name('updatePhoneNumberStatistic');
    Route::get('createPhoneNumberStatistic','Admin\PhoneNumberController@createPhoneNumberStatistic')->name('createPhoneNumberStatistic');
    Route::get('deletePhoneNumberStatistic','Admin\PhoneNumberController@deletePhoneNumberStatistic')->name('deletePhoneNumberStatistic');
    Route::get('backLinkPhoneIndex','Admin\PhoneNumberController@backLinkPhoneIndex')->name('backLinkPhoneIndex');
    Route::get('getPhoneNumberBackLink','Admin\PhoneNumberController@getPhoneNumberBackLink')->name('getPhoneNumberBackLink');
    Route::get('addPhoneNumberBackLink','Admin\PhoneNumberController@addPhoneNumberBackLink')->name('addPhoneNumberBackLink');
    Route::get('exportPhoneNumberBackLink','Admin\PhoneNumberController@exportPhoneNumberBackLink')->name('exportPhoneNumberBackLink');
    Route::get('addOrderInfo','Admin\PhoneNumberController@addOrderInfo')->name('addOrderInfo');
    

    //Группировка товаров
    Route::get('getChildItems','Admin\ItemGroupsController@getChildItems')->name('getChildItems');
    Route::get('deleteItemInGroup','Admin\ItemGroupsController@deleteItemInGroup')->name('deleteItemInGroup');
    Route::get('addNewItemInGroup','Admin\ItemGroupsController@addNewItemInGroup')->name('addNewItemInGroup');
    Route::get('changeMainStatusItem','Admin\ItemGroupsController@changeMainStatusItem')->name('changeMainStatusItem');

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

    
    
    
    //Зоны доставки
    Route::resource('deliveryZone', 'Admin\DeliveryZoneController');

    
    // Товары при кассе
    Route::get('itemsKassa','Admin\ItemGroupsController@itemsKassa')->name('itemsKassa');
    Route::get('/itemsKassaDate','Admin\ItemsKassaDateController@index')->name('itemsKassaDate');
    Route::post('/itemsKassaDate/update/{id}','Admin\ItemsKassaDateController@update')->name('itemsKassaDateUpdate');
    Route::get('/itemsKassaDate/create','Admin\ItemsKassaDateController@store')->name('itemsKassaDateStore');
    Route::delete('/itemsKassaDate/delete/{id}','Admin\ItemsKassaDateController@delete')->name('itemsKassaDateDelete');
    
    //Добавление изображений для товаров
    Route::get('/imageload','Admin\ItemsLinkController@loadImageItems')->name('imageload');
    Route::get('/loadImageForGallery','Admin\ItemsLinkController@loadImageForGallery')->name('loadImageForGallery');
    //Редактор зон
    Route::get('zonesEditor','Admin\DeliveryZoneController@zonesEditor')->name('zonesEditor');

    //Группировка товаров
    Route::get('/itemsMain','Admin\ItemsLinkController@itemsMain')->name('itemsMain');
    

    //Отчеты
    Route::get('/report', function(){ return view('Admin.report'); })->name('report');

    Route::resource('permissions', 'Admin\PermissionsController');
    Route::delete('permissions_mass_destroy', 'Admin\PermissionsController@massDestroy')->name('permissions.mass_destroy');
    Route::resource('roles', 'Admin\RolesController');
    Route::delete('roles_mass_destroy', 'Admin\RolesController@massDestroy')->name('roles.mass_destroy');
    Route::get('/orders', 'Admin\AdminController@orders')->name('orders');
    //Склад
    Route::get('/warehouse', 'Admin\AdminController@warehouse')->name('warehouse');
    Route::get('/warehousepacks', 'Admin\AdminController@warehousepacks')->name('warehousepacks');
    Route::get('/warehousePickup', 'Admin\WareHouseController@index')->name('warehousePickup');
    Route::get('/warehouseGetOrders', 'Admin\WareHouseController@getOrders')->name('warehouseGetOrders');
    Route::get('/warehousePickupCreate', 'Admin\WareHouseController@create')->name('warehousePickupCreate');
    Route::get('/warehouseGetItemFromOrder', 'Admin\WareHouseController@getItemFromOrder')->name('warehouseGetItemFromOrder');
    Route::any('/saveOrderCorrects','Admin\WareHouseController@saveOrderCorrects') ->name('saveOrderCorrects');
    Route::any('/createDeliveryAdd', 'DeliveryAddController@createDeliveryAdd')->name('/createDeliveryAdd');
    Route::any('/closeDeliveryAdd', 'DeliveryAddController@closeDeliveryAdd')->name('/closeDeliveryAdd');

    Route::get('/articles', 'Admin\ArticlesController@articles')->name('articles');
    Route::get('/article/{id}', 'Admin\ArticlesController@article')->name('/article/{id}');
    Route::post('/articleupdate/{id}', 'Admin\ArticlesController@update')->name('/articleupdate/{id}');
    Route::post('/articlestore', 'Admin\ArticlesController@create')->name('/articlestore');

    //Настройки
    Route::get('/getOptions', 'Admin\OptionsController@getOptions')->name('getOptions');
    Route::get('/updateoptions', 'Admin\OptionsController@update')->name('updateoptions');
    Route::get('/optionsIndex', 'Admin\OptionsController@index')->name('optionsIndex');
    Route::get('/getOptionGroups', 'Admin\OptionsController@getOptionGroups')->name('getOptionGroups');
    Route::get('/newItems', 'Admin\OptionsController@newItems')->name('newItems');
    Route::get('/bestSeller', 'Admin\OptionsController@bestSeller')->name('bestSeller');

    Route::get('/saveOrderChanges', 'Admin\AdminController@saveOrderChanges')->name('saveOrderChanges');
    // комментарии к товарам
    Route::get('/comments', 'Admin\CommentsController@comments')->name('comments');
    Route::resource('commentAnswer', 'Admin\CommentAnswersController');
    //чат
    Route::get('/chat', 'Admin\ChatMessagesController@chat')->name('chat');
    // комментарии к сервису
    Route::get('/commentsFirm', 'Admin\CommentsController@commentsFirm')->name('commentsFirm');
    Route::resource('commentFirmAnswer', 'Admin\CommentFirmAnswersController');
});
Route::get('/checkOrderLock', 'Admin\AdminController@checkOrderLock')->name('checkOrderLock');
Route::get('/ordersUnlock', 'Admin\AdminController@ordersUnlock')->name('ordersUnlock');
Route::get('/doc', 'Admin\AdminController@doc')->name('doc');
Route::get('/catalog', 'CatalogController@getCatalog')->name('catalog');
Route::get('/setItemCart', 'CatalogController@setItemCart')->name('setItemCart');
Route::get('/unsetItemCart', 'CatalogController@unsetItemCart')->name('unsetItemCart');

// Route::get('/sendOrder', 'CatalogController@sendOrder')->name('sendOrder');
Route::get('/getItemsJson', 'CatalogController@getItems')->name('getItemsJson');
Route::get('/fixFileName', 'CatalogController@fixFileName')->name('fixFileName');
Route::resource('/users', 'Admins');
Route::any('/Api/Login', 'Auth\LoginController@loginapi')->name('/index.php/Api/Login');

Route::get('/computeDiscount','Api\DiscountController@computeDiscount');
