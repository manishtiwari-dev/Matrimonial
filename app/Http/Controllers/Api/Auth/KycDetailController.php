<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\User;
use App\Models\KycDetail;
use Illuminate\Http\Request;
use App\Rules\FileTypeValidate;


class KycDetailController extends BaseController
{


    public function store(Request $request)
    {
       // $all = $request->all();
        // return  $this->sendResponse(true, $all, 'Kyc Details updated successfully .');

        $this->validate($request, [
            'designation' => 'required',
            // 'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])]
        ]);


        $user = auth()->user();
        $userId = auth()->id();
        $kycDetails = new KycDetail();
        $kycDetails->user_id =  $userId;
        $kycDetails->designation = $request->designation;
        $kycDetails->identity_proof = $request->identity_proof;
        $kycDetails->location = $request->location;


        // if (!empty($request->image)) {
        //     if ($request->hasFile('image')) {
        //         $file = $request->image;
        //         $path = getFilePath('kyc');
        //         try {
        //             $filename = fileUploader($file, $path);
        //         } catch (\Exception $exp) {
        //             $notify[] = ['error', 'Image could not be uploaded'];
        //             return back()->withNotify($notify);
        //         }
        //         $kycDetails->file = $filename;
        //     }
        // }


        // if ($request->hasFile('image')) {
        //     $directory = date("Y") . "/" . date("m") . "/" . date("d");
        //     $file = $request->image;
        //     $path = getFilePath('verify') . '/' . $directory;
        //     $value = $directory . '/' . fileUploader($file, $path);
        //     $kycDetails->file = $value;
        // }


        $kycDetails->save();


        return  $this->sendResponse(true, $kycDetails, 'Kyc Details updated successfully .');
    }
}
