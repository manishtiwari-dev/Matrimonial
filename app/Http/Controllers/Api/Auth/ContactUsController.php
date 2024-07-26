<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Constants\Status;
use App\Models\Page;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use Carbon\Carbon;
use App\Models\AdminNotification;
use App\Models\SupportMessage;


class ContactUsController extends BaseController
{

    public function contact()
    {
        $contactContent = getContent('contact_us.content', true);
        $page = Page::where('tempname', $this->activeTemplate)->where('slug', 'contact')->firstOrFail();
        $data['pageTitle']  = "Contact Us";
        $data['contactContent']  = $contactContent;
        $data['sections'] =$page->secs;


        return  $this->sendResponse(true,$data,'Contact Us Details Retrieved successfully .');

    }


    public function contactSubmit(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'subject' => 'required|string|max:255',
            'message' => 'required',
        ]);

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        $request->session()->regenerateToken();

        $random = getNumber();

        $ticket = new SupportTicket();
        $ticket->user_id = auth()->id() ?? 0;
        $ticket->name = $request->name;
        $ticket->email = $request->email;
        $ticket->priority = Status::PRIORITY_MEDIUM;


        $ticket->ticket = $random;
        $ticket->subject = $request->subject;
        $ticket->last_reply = Carbon::now();
        $ticket->status = Status::TICKET_OPEN;
        $ticket->save();

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = auth()->user() ? auth()->user()->id : 0;
        $adminNotification->title = 'A new support ticket has opened ';
        $adminNotification->click_url = urlPath('admin.ticket.view', $ticket->id);
        $adminNotification->save();

        $message = new SupportMessage();
        $message->support_ticket_id = $ticket->id;
        $message->message = $request->message;
        $message->save();

        return  $this->sendResponse(true,$message,'Ticket created successfully!');

    }
    
}

