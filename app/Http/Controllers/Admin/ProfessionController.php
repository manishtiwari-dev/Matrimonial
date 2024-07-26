<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profession;

class ProfessionController extends Controller
{
    public function index(){
        $pageTitle = 'All Profession';
       
        $profession = Profession::all();
        return view('admin.profession', compact('pageTitle', 'profession'));
    }

    public function save(Request $request, $id=0){
        $request->validate([
            'name' => 'required|unique:professions,name,'.$id
        ]);
        $professions = new Profession();
        $notification = 'Profession added successfully';
        if($id){
            $professions = Profession::findOrFail($id);
            $notification = 'Profession updated successfully';
        }
        $professions->name = $request->name;
        $professions->save();

        $notify[] = ['success', $notification];
        return back()->with($notify);
    }

    public function delete($id){
        $professions = Profession::findOrFail($id);
        $professions->delete();

        $notify[] = ['success', 'Profession deleted successfully'];
        return back()->with($notify);
    }
}



