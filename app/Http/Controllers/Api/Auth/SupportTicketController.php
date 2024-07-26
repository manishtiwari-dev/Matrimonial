<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController as BaseController;

use App\Traits\SupportApiTicketManager;

class SupportTicketController extends BaseController
{
    use SupportApiTicketManager;
    // public function __construct()
    // {
    //     parent::__construct();
    //     $this->layout = 'frontend';

    //     $this->middleware(function ($request, $next) {
    //         $this->user = auth()->user();
    //         if ($this->user) {
    //             $this->layout = 'master';
    //         }
    //         return $next($request);
    //     });

    //  //   $this->redirectLink = 'ticket.view';
    //     $this->userType     = 'user';
    //     $this->column       = 'user_id';
    // }


  
}
