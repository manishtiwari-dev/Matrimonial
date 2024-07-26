<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\Conversation;
use App\Models\UserInterest;
use Illuminate\Http\Request;

class InterestController extends BaseController
{
    public function interestList(Request $request)
    {
        $pageTitle = 'My Interests';
        $user      = auth()->user();
        
        if($request->interesting_id){
        $interests = UserInterest::where('interesting_id', $request->interesting_id)->where('status',1)->searchable(['profile:username,firstname,lastname'])->with('user','profile.basicInfo', 'conversation')->orderBy('id', 'desc')->paginate(getPaginate());
        }
        else{
        
        $interests = UserInterest::with('user')->where('user_id', $user->id)->where('status',1)->searchable(['profile:username,firstname,lastname'])->with('profile.basicInfo', 'conversation')->orderBy('id', 'desc')->paginate(getPaginate());
        }

    
        return  $this->sendResponse(true, $interests , ""); 
         
    }

    public function interestRequests()
    {
        $pageTitle        = 'Interest Requests';
        $interestRequests = UserInterest::where('interesting_id', auth()->id())->searchable(['user:username,firstname,lastname'])->with('user.basicInfo', 'conversation')->orderBy('id', 'desc')->paginate(getPaginate());

        return  $this->sendResponse(true, $interestRequests , ""); 
    }

    public function acceptInterest(Request $request)
    { 

        $user     = auth()->user();
        
        $interest = UserInterest::where('interesting_id', $user->id)->findOrFail($request->id);
    
        if ($interest->status == 1) { 
            return  $this->sendResponse(true, [] , "Already accepted this request"); 
        }

        $interest->status = 1;
        $interest->save();

        $exist = Conversation::where(function ($query) use ($interest) {
            $query->where('sender_id', $interest->user_id)->orWhere('receiver_id', $interest->user_id);
        })
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
            })
            ->first();


        if (!$exist) {
            $conversation = new Conversation();
            $conversation->interest_id = $interest->id;
            $conversation->sender_id = $interest->user_id;
            $conversation->receiver_id = $user->id;
            $conversation->save();
        }
 
        return  $this->sendResponse(true, [] , "Request accepted successfully"); 
    }
}
