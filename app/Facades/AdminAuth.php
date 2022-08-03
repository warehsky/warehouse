<?php
namespace app\Facades;
use Illuminate\Support\Facades\Facade;

class AdminAuth extends Facade {
        protected static function getFacadeAccessor() { return 'auth.driver_admin'; }
    }
