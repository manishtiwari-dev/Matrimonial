<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Lib\SocialLogin;
use Illuminate\Http\Request;

class SocialiteController extends BaseController
{


    public function socialLogin($provider)
    {
        $socialLogin = new SocialLogin($provider);
        return $socialLogin->redirectDriver();
    }

    public function callback($provider)
    {
        $socialLogin = new SocialLogin($provider);
        try {
            return $socialLogin->login();
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return to_route('home')->withNotify($notify);
        }
    }
}
