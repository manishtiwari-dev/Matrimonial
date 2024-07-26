<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\IgnoredProfile;
use Illuminate\Http\Request;
use Validator;

class IgnoredProfileController extends BaseController
{
    public function index()
    {
        $user = auth()->user();
        $pageTitle = 'Ignored Profile';
        $ignoredLists = IgnoredProfile::where('user_id', $user->id)->with('profile.basicInfo')->get();
 

        return  $this->sendResponse(true, $ignoredLists , ""); 
    }

    public function remove(Request $request)
    {
        $profile = IgnoredProfile::where('user_id', auth()->id())->find($request->id);
        if ($profile) {
            $profile->delete();

            return  $this->sendResponse(true, [] , "Successfully removed from ignored list");  
        } else {

            return  $this->sendResponse(true, [] , "Invalid request, please try again!");  
        }
    }
}
