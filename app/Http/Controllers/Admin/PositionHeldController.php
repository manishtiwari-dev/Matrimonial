<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PositionHeld;

class PositionHeldController extends Controller
{
    public function index(){
        $pageTitle = 'All PositionHeld';
       
        $positionHeld = PositionHeld::all();
        return view('admin.positionHeld', compact('pageTitle', 'positionHeld'));
    }

    public function save(Request $request, $id=0){
        $request->validate([
            'name' => 'required|unique:position_helds,name,'.$id
        ]);
        $positionHelds = new PositionHeld();
        $notification = 'PositionHeld added successfully';
        if($id){
            $positionHelds = PositionHeld::findOrFail($id);
            $notification = 'PositionHeld updated successfully';
        }
        $positionHelds->name = $request->name;
        $positionHelds->save();

        $notify[] = ['success', $notification];
        return back()->with($notify);
    }

    public function delete($id){
        $positionHelds = PositionHeld::findOrFail($id);
        $positionHelds->delete();

        $notify[] = ['success', 'PositionHeld deleted successfully'];
        return back()->with($notify);
    }
}



