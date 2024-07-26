<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Smoking;

class SmokingController extends Controller
{
    public function index(){
        $pageTitle = 'All Smoking';
       
        $smoking = Smoking::all();
        return view('admin.smoking', compact('pageTitle', 'smoking'));
    }

    public function save(Request $request, $id=0){
        $request->validate([
            'name' => 'required|unique:smokings,name,'.$id
        ]);
        $smoking = new Smoking();
        $notification = 'Smoking added successfully';
        if($id){
            $smoking = Smoking::findOrFail($id);
            $notification = 'Smoking updated successfully';
        }
        $smoking->name = $request->name;
        $smoking->save();

        $notify[] = ['success', $notification];
        return back()->with($notify);
    }

    public function delete($id){
        $Smoking = Smoking::findOrFail($id);
        $Smoking->delete();

        $notify[] = ['success', 'Smoking deleted successfully'];
        return back()->with($notify);
    }
}



