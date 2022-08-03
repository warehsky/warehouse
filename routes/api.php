<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['prefix' => '/', 'namespace' => 'Api', 'as' => 'api.'], function () {
    // Каталог товаров
    Route::get('/getGoodsGroup', 'ApiController@getGoodsGroup')->name('getGoodsGroup');
    Route::get('/getGoodsItems', 'ApiController@getGoodsItems')->name('getGoodsItems');
    Route::get('/getTradePoints', 'ApiController@getTradePoints')->name('getTradePoints');
    Route::get('/getContractTypes', 'ApiController@getContractTypes')->name('getContractTypes');
    Route::get('/getCacheGroups', 'ApiController@getCacheGroups')->name('getCacheGroups');
    Route::get('/getItemGroups', 'ApiController@getItemGroups')->name('getItemGroups');
    Route::get('/setWebGroupItem', 'ApiController@setWebGroupItem')->name('setWebGroupItem');
    Route::get('/get_items', 'ApiController@getItems')->name('get_items');
    Route::get('/get_search_items', 'ApiController@getSearchItems')->name('get_search_items');
    Route::get('/get_suggests', 'ApiController@getSuggests')->name('get_suggests');
    Route::get('/add_suggest', 'ApiController@addSuggest')->name('add_suggest');
    Route::get('/del_suggest', 'ApiController@delSuggest')->name('del_suggest');
    // Слайдер в корзине 
    Route::get('/get_all_items_kassa', 'ApiController@getAllSearchItemsKassa')->name('get_all_items_kassa');
    Route::get('/get_items_kassa', 'ApiController@getItemsKassa')->name('get_items_kassa');
    Route::get('/add_in_items_kassa', 'ApiController@addInItemsKassa')->name('add_in_items_kassa');
    Route::get('/delete_in_items_kassa', 'ApiController@deleteInItemsKassa')->name('delete_in_items_kassa');
    Route::get('/change_itemsorder_items_kassa', 'ApiController@changeItemsorderInItemsKassa')->name('change_itemsorder_items_kassa');
    Route::get('/get_Date_items_kassa', 'ApiController@getDatesItemsKassa')->name('get_Date_items_kassa');
    // Пользователи
    Route::get('/AllWebUsers','ApiFreeController@getAllWebUsers')->name('getAllWebUsers');
    Route::get('/WebUsersChangeAutologin','ApiFreeController@WebUsersChangeAutologin')->name('WebUsersChangeAutologin');
    Route::get('/changeNoteStatus','ApiController@changeNoteStatus')->name('changeNoteStatus');



    
    Route::get('/changePopularValue','ApiController@changePopularValue')->name('changePopularValue');
    // Добавление изображений для товаров
    Route::get('/AllItemsForLoad','ApiController@getItemForLoad')->name('AllItemsForLoad');
    Route::post('/addImageItem','ApiController@addNewImageForItem')->name('addImageItem');
    Route::post('/saveAllImage','ApiController@saveAllImage')->name('saveAllImage');
    Route::get('/deleteItemImage','ApiController@deleteItemImage')->name('deleteItemImage');
    //Отчеты
    Route::get('/getNoStockTagOrPrice','ReportController@getNoStockTagOrPrice')->name('getNoStockTagOrPrice');
    Route::get('/allReport','ReportController@allReport')->name('allReport');
    Route::get('/getNoDescr','ReportController@getNoDescr')->name('getNoDescr');
    Route::get('/noImg','ReportController@noImg')->name('noImg');
    Route::get('/getNoPrice/{delete}','ReportController@getNoPrice')->name('getNoPrice');
    Route::get('/getNoDiscountPrice','ReportController@getNoDiscountPrice')->name('getNoDiscountPrice');
    Route::get('/changeDelStatus','ReportController@changeDelStatus')->name('changeDelStatus');
    Route::get('/getDiffPrice','ReportController@getDiffPrice')->name('getDiffPrice');
    Route::get('/getCountTypeDevice','ReportController@getCountTypeDevice')->name('getCountTypeDevice');
    Route::get('/getWebUsersSomeOrders','ReportController@getWebUsersSomeOrders')->name('getWebUsersSomeOrders');
    Route::get('/infoOrders','ReportController@infoOrders')->name('infoOrders');
    Route::get('/infoSite','ReportController@infoSite')->name('infoSite');
    Route::get('/getClickObject','ReportController@getClickObject')->name('getClickObject');
    Route::post('/checkExcelOrder','ReportController@checkExcelOrder')->name('checkExcelOrder');
    
    //Промокоды
    Route::get('/extendPromocode','ApiFreeController@extendPromocode')->name('extendPromocode');


    
    //Изображения для галереи 
    Route::get('/getGalleryImage','ApiController@getGalleryImage')->name('getGalleryImage');
    Route::post('/saveAllImageForGallery','ApiController@saveAllImageForGallery')->name('saveAllImageForGallery');
    // tags
    Route::get('/getTagGroups', 'ApiController@getTagGroups')->name('getTagGroups');
    Route::get('/getTags',      'ApiController@getTags')     ->name('getTags');
    Route::get('/addTagGroup',  'ApiController@addTagGroup') ->name('addTagGroup');
    Route::get('/delTagGroup',  'ApiController@delTagGroup') ->name('delTagGroup');
    // orders
    Route::get('/getOrders',  'ApiController@getOrders') ->name('getOrders');
    Route::get('/getOrderItems',  'ApiController@getOrderItems') ->name('getOrderItems');
    Route::get('/getOrderItemsWeight',  'ApiController@getOrderItemsWeight') ->name('getOrderItemsWeight');
    Route::get('/getOrdersByPhone', 'ApiController@getOrdersByPhone')->name('getOrdersByPhone');
    Route::get('/setOrder',  'ApiController@setOrder') ->name('setOrder');
    Route::post('/setOrder',  'ApiController@setOrder') ->name('setOrder');
    Route::post('/saveWarehouseOrder',  'ApiController@saveWarehouseOrder') ->name('saveWarehouseOrder');
    Route::post('/saveWarehouseOrderPacks',  'ApiController@saveWarehouseOrderPacks') ->name('saveWarehouseOrderPacks');
    Route::post('/saveArticleImg',  'ArticlesController@saveArticleImg') ->name('saveArticleImg');
    Route::post('/saveArticle',  'ArticlesController@saveArticle') ->name('saveArticle');
    Route::get('/refreshOrder',  'ApiController@refreshOrder') ->name('refreshOrder');
    Route::get('/getWavesReport',  'ApiController@getWavesReport') ->name('getWavesReport');
    Route::get('/getGifts',  'ApiController@getGifts') ->name('getGifts');
    Route::any('/setSotPoligon',  'ApiController@setSotPoligon') ->name('setSotPoligon');
    Route::any('/setZone',  'ApiController@setZone') ->name('setZone');
    // comments goods
    Route::get('/setComment',  'ApiController@setComment') ->name('setComment');
    Route::get('/setCommentFirm',  'ApiController@setCommentFirm') ->name('setCommentFirm');
    // chat
    Route::get('/getChatUsers', 'ChatMessagesController@getChatUsers')->name('getChatUsers');
    Route::get('/getChatMessages', 'ChatMessagesController@getChatMessages')->name('getChatMessages');
    Route::any('/confirmChatMessages','ChatMessagesController@confirmChatMessages')->name('confirmChatMessages');
    Route::any('/addChatMessage','ChatMessagesController@addChatMessage')->name('addChatMessage');
    Route::get('/getChatAnswers', 'ChatMessagesController@getChatAnswers')->name('getChatAnswers');
    Route::get('/updateChatAnswer', 'ChatMessagesController@updateChatAnswer')->name('updateChatAnswer');
    Route::get('/deleteChatAnswer', 'ChatMessagesController@deleteChatAnswer')->name('deleteChatAnswer');
    // alerts
    Route::get('/getAlerts',  'ApiController@getAlerts') ->name('getAlerts');
    //vacancy
    Route::get('/getVacancy','ApiFreeController@getVacancy')->name('getVacancy');
    //pageStock
    Route::get('/getPageStock','ApiFreeController@getPageStock')->name('getPageStock');
    // free
    Route::get('/getMenuItems',     'ApiFreeController@getMenuItems')     ->name('getMenuItems');
    Route::get('/getGroupData',     'ApiFreeController@getGroupData')     ->name('getGroupData');
    Route::get('/getSearchGroups',  'ApiFreeController@getSearchGroups')  ->name('getSearchGroups');
    Route::get('/getSearch',        'ApiFreeController@getSearch')        ->name('getSearch');
    Route::get('/sendOrder',        'ApiFreeController@sendOrder')        ->name('sendOrder');
    Route::get('/sendCodeSms',      'ApiFreeController@sendCodeSms')      ->name('sendCodeSms');
    Route::get('/checkSmsCode',     'ApiFreeController@checkSmsCode')     ->name('checkSmsCode');
    Route::get('/getItems',         'ApiFreeController@getItems')         ->name('getItems');
    Route::get('/getArticlesLast',  'ArticlesController@getArticlesLast') ->name('getArticlesLast');
    Route::get('/getArticles',      'ArticlesController@getArticles')     ->name('getArticles');
    Route::get('/getSuggestItems',  'ApiFreeController@getSuggestItems')  ->name('getSuggestItems');
    Route::get('/getComments',      'ApiFreeController@getComments')      ->name('getComments');
    Route::get('/addComment',       'ApiFreeController@addComment')       ->name('addComment');
    Route::get('/getCommentsfirm',  'ApiFreeController@getCommentsfirm')  ->name('getCommentsfirm');
    Route::get('/addCommentfirm',   'ApiFreeController@addCommentfirm')   ->name('addCommentfirm');
    Route::get('/getPath',          'ApiFreeController@getCrumbs')        ->name('getPath');
    Route::get('/getDeliveryZones', 'ApiFreeController@getDeliveryZones') ->name('getDeliveryZones');
    Route::get('/getPoligons',      'ApiFreeController@getPoligons')      ->name('getPoligons');
    Route::get('/getTimeWaves',     'ApiFreeController@getTimeWaves')     ->name('getTimeWaves');
    Route::get('/getTimeWavesGroup','ApiFreeController@getTimeWavesGroup')->name('getTimeWavesGroup');
    Route::get('/getUserBonus',     'ApiFreeController@getUserBonus')     ->name('getUserBonus');
    Route::get('/checkPromocode',   'ApiFreeController@checkPromocode')   ->name('checkPromocode');
    Route::get('/getUserCoupons',   'ApiFreeController@getUserCoupons')   ->name('getUserCoupons');
    Route::get('/checkOrderPay',    'ApiPayController@checkOrderPay')     ->name('checkOrderPay');
    Route::get('/checkOrderPayCRB', 'ApiCRBPayController@checkOrderPayCRB')->name('checkOrderPayCRB');
    Route::any('/setProfile',       'ApiWebUsersController@setProfile')   ->name('setProfile');
    Route::any('/getProfile',       'ApiWebUsersController@getProfile')   ->name('getProfile');
    Route::get('/getOrderCorrects', 'ApiOrderController@getOrderCorrects') ->name('getOrderCorrects');
    Route::any('/getPhonePrefixes', 'ApiFreeController@getPhonePrefixes') ->name('getPhonePrefixes');
    Route::get('/setOrderFeature',  'ApiOrderController@setOrderFeature') ->name('setOrderFeature');
    Route::get('/getOrdersLimit',   'ApiFreeController@getOrdersLimit')   ->name('getOrdersLimit');
    // warehouse
    Route::any('/warehouseLogin',  'ApiWarehouseController@warehouseLogin') ->name('warehouseLogin');
    Route::any('/getPickupOrders',  'ApiWarehouseController@getPickupOrders') ->name('getPickupOrders');
    Route::any('/getPickupChanges',  'ApiWarehouseController@getPickupChanges') ->name('getPickupChanges');
    Route::any('/finishPickup',  'ApiWarehouseController@finishPickup') ->name('finishPickup');
    Route::any('/startPickup',  'ApiWarehouseController@startPickup') ->name('startPickup');
    Route::any('/confirmDeliveryAdd', 'ApiWarehouseController@confirmDeliveryAdd')->name('/confirmDeliveryAdd');
    Route::any('/getDeliveryAdds', 'ApiWarehouseController@getDeliveryAdds')->name('/getDeliveryAdds');
    // Maps
    Route::get('/searchAdress', 'MapController@search')         ->name('searchAdress');
    Route::get('/getZone', 'MapController@getZone')             ->name('getZone');
    Route::get('/getAddress', 'MapController@getAddress')       ->name('getAddress');
    Route::any('/setOption', 'MapController@setOption')         ->name('setOption');
    Route::get('/getOption', 'MapController@get_option')         ->name('getOption');
    // Pay
    Route::get('/testpay',  'ApiController@testPay') ->name('testpay');

});
Route::match(['get', 'post'], '/getGroupsList', 'ItemsController@getGroupsList')->name('getGroupsList');
Route::middleware('authapi')->get('/user', 'UserController@AuthRouteAPI');

Route::group(['middleware' => ['authapi']], function () {  
    

});

//Route::any('/GetTradePointSchedule', 'TradePointSchedulesController@getTradePointSchedule')   ->name('/index.php/Api/GetTradePointSchedule');
//Route::any('/SetTradePointScheduleRequest', 'TradePointScheduleRequestsController@setTradePointScheduleRequest')   ->name('/index.php/Api/SetTradePointScheduleRequest');
