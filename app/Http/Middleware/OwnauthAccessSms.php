<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Carbon\Carbon;

class OwnauthAccessSms {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $phone = $request->session()->get('ownPhone');
        if (!$phone) {
            return redirect(route('ownLogin'));
        }
        $session = $request->session()->get('ownSession');
        if (!$session) {
            return redirect(route('ownLogin'));
        }
        $now = Carbon::now()->timezone('Europe/Moscow');
        $last = Carbon::parse(($session))->timezone('Europe/Moscow');
        $diff = $now->diffInSeconds($last)/60;
        $ownLimit = 15;
        // $ownLimit = (int)$this->getOption('ownLimit'); // период действия кода из смс
        if($diff > $ownLimit){
            return redirect(route('ownLogin'));
        }

        return $next($request);
    }

}
