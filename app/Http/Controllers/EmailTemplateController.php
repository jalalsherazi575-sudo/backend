<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\EmailTemplateRequest;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Laraspace\EmailTemplate;
use Illuminate\Support\Str;
use DataTables;

use Auth;

class EmailTemplateController extends Controller

{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        $this->middleware('auth');
    }
    /**
     * Display a System Message listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()

    {    
    	$emailtemplate = EmailTemplate::orderBy('id','DESC')->get();
        return view('admin.emailtemplate.index',compact('emailtemplate'));
    }
    

    /**
     * Show the form for creating a new System Message resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    	return view('admin.emailtemplate.create');
    }

    /**
     * Store a newly created new Email Template in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
      */
    public function store(Request $request)
    {
        $request->validate([
            'email_name'        => 'required',
            'mail_to'           => 'required|email',
            'subject'           => 'required',
            'description'       => 'required',
            'status'            => 'required|not_in:-- Choose Status --]',
        ],[
            'email_name.required'   => 'Please enter the Template Name.',
            'mail_to.required'      => 'Please enter the Email to.',
            'subject.required'      => 'Please enter the Subject.',
            'description.required'  => 'Please enter the Description.',
            'status.required'       => 'Please select the status.',
        ]);
       	$create = new EmailTemplate();
        $create->email_name                     = $request->email_name;
        $create->email_name_slug                = Str::slug($request->email_name);
        $create->mail_to                        = $request->mail_to;
        $create->mail_cc                        = $request->mail_cc;
        $create->mail_bcc                       = $request->mail_bcc;
        $create->subject                        = $request->subject;
        $create->description                    = $request->description;
        $create->status                         = $request->status;
        $create->save(); 
         /*Message */
       
        return redirect()->route('email')->with('success', 'Added Email Template successfully!'); 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    	$email = EmailTemplate::findOrFail($id);
        return view('admin.emailtemplate.edit',compact('email'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $request->validate([
            //'email_name'                    => 'required',
            //'mail_cc'                       => 'required|email',
            //'mail_bcc'                      => 'required|email',
            'mail_to'                       => 'required|email',
            'subject'                       => 'required',
            'description'                   => 'required',
            'status'                        => 'required|not_in:-- Choose Status --]',
        ],[
            'email_name.required'                   => 'Please enter the Template Name.',
            'mail_to.required'                      => 'Please enter the Email to.',
           // 'mail_cc.required'                      => 'Please enter the Email cc.',
           // 'mail_bcc.required'                     => 'Please enter the Email bcc.',
            'subject.required'                      => 'Please enter the Subject.',
            'description.required'                  => 'Please enter the Description.',
            'status.required'                       => 'Please select the status.',
        ]);
        $update = EmailTemplate::findOrFail($id); 
      /*  $update->email_name                     = $request->email_name;
        $update->email_name_slug                = Str::slug($request->email_name);*/
        $update->mail_to                        = $request->mail_to;
        $update->mail_cc                        = $request->mail_cc;
        $update->mail_bcc                       = $request->mail_bcc;
        $update->subject                        = $request->subject;
        $update->description                    = $request->description;
        $update->status                         = $request->status;
        $update->save();   
        return redirect()->route('email.show',$id)->with('success','Email Template update sucessfuly!');
    }
     /**
     * Show the specified resource Email Template storage.
     *
     * @param  \App\Sample_data  $sample_data
     * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        $email = EmailTemplate::findOrFail($id);
        return view('admin.emailtemplate.show', compact('email'));
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Sample_data  $sample_data
     * @return \Illuminate\Http\Response
     */
    public function destroy($id ,Request $request)
    {
        $emaildelete = EmailTemplate::findOrFail($id);
        if($emaildelete->delete()){
            return Response(['status'=>'success','message'=> 'Email Template deleted successfully.']);  
        } else {
            /*Message */
            return Response(['status'=>'error','message'=> 'Something went wrong!']); 

        }
    }
}