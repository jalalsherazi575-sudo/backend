<?php
namespace Laraspace\Http\Controllers;

use Illuminate\Http\Request;
use Laraspace\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Laraspace\Http\Controllers\CommanController;
use BusinessUsers;


class BusinessDashboardController extends Controller
{
	public function __construct()
    {
       $this->middleware('auth:businessuser');

        //$this->middleware('auth');
        
         
         
        //echo "sfsdf2111sdf";
        //exit;
        /*print_r(Auth::user());
        exit;*/
    	

       // $this->middleware('guest:businessuser');
    }
    public function index()
    {
        
             
        return view('admin.dashboard.businesbasic');
    }
}
