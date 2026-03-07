<?php

namespace Laraspace\Http\Controllers;

use Laraspace\Http\Requests\EmailTemplateRequest;
use Laraspace\Http\Controllers\CommanController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Laraspace\DefaultConfiguration;
use Illuminate\Support\Str;
use DataTables;

use Auth;

class DefaultConfigurationController extends Controller
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
     * Display a Header Script listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        $settingdata = DefaultConfiguration::first();
        return view('admin.defaultsetting.index',compact('settingdata'));
    }

     /*Add or Edit Data*/
    public function store(Request $request)
    {
    	$request->validate([  
            'mail_host'   		=> 'required',
            'mail_username'   	=> 'required',
            'mail_password'   	=> 'required',
            'mail_port'   		=> 'required',
            'mail_encryption'   => 'required',
            'mail_from_name'   	=> 'required',
            'mail_from_address'	=> 'required',

            ],[
            'mail_host.required' 		=> 'Please enter the mail host.',
            'mail_username.required' 	=> 'Please enter the mail username.',
            'mail_password.required' 	=> 'Please enter the mail password.',
            'mail_port.required' 		=> 'Please enter the mail port.',
            'mail_encryption.required' 	=> 'Please enter the mail encryption.',
            'mail_from_name.required' 	=> 'Please enter the mail from name.',
            'mail_from_address.required' => 'Please enter the mail from address.',
            ]);
    	$mailsetting =  DefaultConfiguration::where('id',$request->setting_id)->first();
        if(!empty($mailsetting))
        {
            $mailsetting->mail_host  = $request->mail_host;
            $mailsetting->mail_username  = $request->mail_username;
            $mailsetting->mail_password  = $request->mail_password;
            $mailsetting->mail_port  = $request->mail_port;
            $mailsetting->mail_encryption  = $request->mail_encryption;
            $mailsetting->mail_from_name  = $request->mail_from_name;
            $mailsetting->mail_from_address  = $request->mail_from_address;
            $mailsetting->save();
        } else {
            $create = new DefaultConfiguration;
            $create->mail_host  = $request->mail_host;
            $create->mail_username  = $request->mail_username;
            $create->mail_password  = $request->mail_password;
            $create->mail_port  = $request->mail_port;
            $create->mail_encryption  = $request->mail_encryption;
            $create->mail_from_name  = $request->mail_from_name;
            $create->mail_from_address  = $request->mail_from_address;
            $create->save();
        }
        return redirect()->route('defaultconfiguration')->with('success','Default Configuration update sucessfuly!');
    }
}