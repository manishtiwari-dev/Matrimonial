<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Drinking;

class DrinkingController extends Controller
{
    public function index(){
        $pageTitle = 'All Drinking';
       
        $drinking = Drinking::all();
        return view('admin.drinking', compact('pageTitle', 'drinking'));
    }

    public function save(Request $request, $id=0){
        $request->validate([
            'name' => 'required|unique:drinkings,name,'.$id
        ]);
        $drinking = new Drinking();
        $notification = 'Drinking added successfully';
        if($id){
            $drinking = Drinking::findOrFail($id);
            $notification = 'Drinking updated successfully';
        }
        $drinking->name = $request->name;
        $drinking->save();

        $notify[] = ['success', $notification];
        return back()->with($notify);
    }

    public function delete($id){
        $drinking = Drinking::findOrFail($id);
        $drinking->delete();

        $notify[] = ['success', 'Drinking deleted successfully'];
        return back()->with($notify);
    }
}



