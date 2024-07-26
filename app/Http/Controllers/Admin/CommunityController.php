<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Community;
use App\Models\ReligionInfo;


class CommunityController extends Controller
{
    public function index(){
        $pageTitle = 'All Community';
       
        $community = Community::all();
        $religionInfo= ReligionInfo::all();
        return view('admin.communitys', compact('pageTitle', 'community','religionInfo'));
    }

    public function save(Request $request, $id=0){
        $request->validate([
            'name' => 'required|unique:communities,name,'.$id
        ]);
        $community = new Community();
        $notification = 'Community added successfully';
        if($id){
            $community = Community::findOrFail($id);
            $notification = 'Community updated successfully';
        }
        $community->name = $request->name;
        $community->religion_id = $request->religion_id;
        $community->save();

        $notify[] = ['success', $notification];
        return back()->with($notify);
    }

    public function delete($id){
        $community = Community::findOrFail($id);
        $community->delete();

        $notify[] = ['success', 'Community deleted successfully'];
        return back()->with($notify);
    }
}



