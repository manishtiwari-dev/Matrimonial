<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\Gallery;
use App\Models\UserLimitation;
use Illuminate\Http\Request;
use App\Rules\FileTypeValidate;
use Validator;

class GalleryController extends BaseController
{
    public function index()
    {
        $pageTitle = 'Gallery';
        $user = auth()->user()->load(['limitation', 'galleries']);
        $maxLimit = $user->limitation->image_upload_limit ?? 0;

        return  $this->sendResponse(true, [$user] , "");  
    }

    public function upload(Request $request)
    { 

        $validator = Validator::make($request->all(), [
            'images' => 'required|array', 
        ]);
        

        if ($validator->fails()){
            return  $this->sendResponse(true, [],  $validator->messages());
        }

        if(count($request->images) > 5){
            return  $this->sendResponse(true, [] , "Please Select Only 5 Image");  
        }


        $user = auth()->user()->load('galleries');

        $userLimitation = UserLimitation::where('user_id', $user->id)->first();

        if (!checkValidityPeriod($userLimitation)) {
            $notify[] = ['error', 'Your package\'s validity period has been expired'];
            return back()->withNotify($notify);
        }

        // if ($userLimitation->image_upload_limit ?? '' != -1 && $userLimitation->image_upload_limit ?? '' < (count($request->images) + $user->galleries->count())) {
            
        //     return  $this->sendResponse(true, [] , "Image upload limit is over");  
        // }

        $uploadedImages = [];
        foreach ($request->images as $key => $image) {
            try {
                $fileName = fileUploader($image, getFilePath('gallery'), null);
                $uploadedImage['user_id'] = $user->id;
                $uploadedImage['image'] = $fileName;
                $uploadedImage['created_at'] = now();
                $uploadedImages[] = $uploadedImage;
            } catch (\Exception $exp) { 
                return  $this->sendResponse(true, [] , "Couldn\'t upload the image");  
            }
        }

        Gallery::insert($uploadedImages);
 
        return  $this->sendResponse(true, [] , "Image uploaded successfully");  
    }

    public function delete(Request $request)
    { 

        $validator = Validator::make($request->all(), [
            'gallery_id' => 'required|integer|gt:0'
        ]);
        

        if ($validator->fails()){
            return  $this->sendResponse(true, [],  $validator->messages());
        }


        $gallery = Gallery::where('user_id', auth()->id())->findOrFail($request->gallery_id);
        $path = getFilePath('gallery');
        unlink($path . '/' . $gallery->image);
        $gallery->delete();
 
        return  $this->sendResponse(true, [] , "Image deleted successfully"); 
    }

    public function deleteUnpublishedImages(Request $request)
    {
        $maxLimit = auth()->user()->limitation->image_upload_limit ?? 0;
        if ($maxLimit <= 0) abort(404);

        $galleries   = Gallery::where('user_id', auth()->id());
        $total       = (clone $galleries)->count();
        $unpublished = $total - $maxLimit;

        if ($unpublished <= 0) abort(404);

        $galleries = $galleries->latest('id')->skip($maxLimit)->take($unpublished)->get();

        foreach ($galleries as $gallery) {
            $path = getFilePath('gallery');
            fileManager()->removeFile($path . '/' . $gallery->image);
            $gallery->delete();
        }
        $notify[] = ['success', 'Unpublished images has been deleted successfully'];
        return back()->withNotify($notify);
    }
}
