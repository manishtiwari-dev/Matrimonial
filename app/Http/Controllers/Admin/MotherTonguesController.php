<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MotherTongue;

class MotherTonguesController extends Controller
{
    public function index(){
        $pageTitle = 'All Mother Tongues';
        $motherTongues = MotherTongue::all();
        return view('admin.mother_tongues', compact('pageTitle', 'motherTongues'));
    }

    public function save(Request $request, $id=0){
        $request->validate([
            'name' => 'required|unique:mother_tongues,name,'.$id
        ]);
        $motherTongues = new MotherTongue();
        $notification = 'Mother Tongues added successfully';
        if($id){
            $motherTongues = MotherTongue::findOrFail($id);
            $notification = 'Mother Tongues updated successfully';
        }
        $motherTongues->name = $request->name;
        $motherTongues->save();

        $notify[] = ['success', $notification];
        return back()->with($notify);
    }

    public function delete($id){
        $motherTongues = MotherTongue::findOrFail($id);
        $motherTongues->delete();

        $notify[] = ['success', ' Mother Tongues deleted successfully'];
        return back()->with($notify);
    }
}



