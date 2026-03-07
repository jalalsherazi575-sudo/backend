<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\BusinessUsersRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Laraspace\UnnecessaryWords;
use Laraspace\Helpers\Helper;

class UnnecessaryWordsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index() 
    { 
        $wordsData = UnnecessaryWords::orderBy('id','DESC')->get();
       return view('admin.unnecessarywords.index',compact('wordsData'));
    }
    /*Add Form*/
    public function create() 
    { 
      return view('admin.unnecessarywords.addedit');
    }
    /*Store Unnecessary Words*/
    public function store(Request $request)
    {
        /*$invoice = Helper::infakt_integration_get_invoice_data('TR-5JZ8-BNTYBNX');
        echo "<pre>";print_r($invoice);exit;*/
        $request->validate([
            'word' => 'required',
         ], [
            'word.required' => 'Please enter the word.',
        ]);
        $create = new UnnecessaryWords;
        $create->word = $request->word;
        $create->save();
        session()->flash('success','Unnecessary Words has added successfully.');
        return redirect()->to('/admin/unnecessarywords');
    }
    /*Edit Unnecessary Words*/
    public function edit($id)
    {
        $unnecessarywords = UnnecessaryWords::findOrFail($id);
        return view('admin.unnecessarywords.addedit',compact('unnecessarywords'));
    }
    /*Update Unnecessary Words*/
    public function update(Request $request,$id)
    {
        $request->validate([
            'word'          => 'required',
         ], [
            'word.required' => 'Please enter the word.',
        ]);
        $update          = UnnecessaryWords::findOrFail($id);
        $update->word    = $request->word;
        $update->save();
        session()->flash('success','Unnecessary Words has update successfully.');
        return redirect()->to('/admin/unnecessarywords');
    }

    /**
     * Remove the specified resource from storage Unnecessary Words.
     *
     * @param  \App\Sample_data  $sample_data
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete = UnnecessaryWords::findOrFail($id);
        $delete->delete();
        return Response(['status'=>'success','message'=> 'Unnecessary Words deleted successfully.']);  
    }
}