<?php
namespace Laraspace\Http\Controllers;

use Laraspace\User;
use Laraspace\UsersRoles;
use Laraspace\Http\Requests\UserRequest;
use Illuminate\Support\Facades\DB;
use Laraspace\Http\Controllers\CommanController;
use Auth;
use Laraspace\Country;


class UsersController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }
    public function index()
    {
        $role=Auth::user()->role;
        $user_id=Auth::user()->id;
        $common = new CommanController();
            $users = User::get();
        $usersroles = UsersRoles::get();

          $nousermsg=$common->get_msg('no_user_found',1)?$common->get_msg('no_user_found',1):"No user available.";

          //echo $user_id=Auth::user()->role;
          //exit;
        return view('admin.users.index',compact('users','usersroles','nousermsg'));
    }

    public function create() {
        $userroles = UsersRoles::get();
        $country = Country::where([['status', '=','1']])->get();
       return view('admin.users.addedit',compact('userroles','country'));
    }

    public function postCreate(UserRequest $request) {
        
         $checkduplicate = DB::table('users')->where([['name', '=',$request->name]])->count();
         $countemail = DB::table('users')->where('email', '=', $request->email)->count();
         if ($checkduplicate > 0) {
             
             flash()->error('This Admin user name has been already taken. Please try with another name.');
             return redirect()->to('/admin/users/add');
         } elseif ($countemail > 0) {
           flash()->error('This Admin user email address has been already taken. Please try with another address.');
             return redirect()->to('/admin/users/add');
         } else {
              $name=isset($request->name)?(ltrim($request->name)):"";
              $email=isset($request->email)?(ltrim($request->email)):"";
              $password=isset($request->password)?(ltrim(bcrypt($request->password))):"";
              $role=isset($request->role)?(ltrim($request->role)):0;
              $countryId=isset($request->countryId)?(ltrim($request->countryId)):0;
              
              $role_name="user";
              
              if ($role!=0) {
                $userroles = UsersRoles::find($role);
                $role_name=isset($userroles->role_name)?($userroles->role_name):"";
              }
              


              $user = new User();
              $user->name=$name;
              $user->email=$email;
              $user->password=$password;
              $user->role_id=$role;
              $user->role=$role_name;
              $user->countryId=$countryId;
              $user->save();
              session()->flash('success','Admin user has been added successfully.');
             return redirect()->to('/admin/users');
          
         }

    }

    public function postEdit(UserRequest $request,$id) {
        $name=isset($request->name)?(ltrim($request->name)):"";
        $email=isset($request->email)?(ltrim($request->email)):"";
        $password=isset($request->password)?(ltrim(bcrypt($request->password))):"";
        $role=isset($request->role)?(ltrim($request->role)):0;
        $countryId=isset($request->countryId)?(ltrim($request->countryId)):0;

        $role_name="user";
              
        if ($role!=0) {
          $userroles = UsersRoles::find($role);
          $role_name=isset($userroles->role_name)?($userroles->role_name):"";
        }

        

        $user = User::find($id);
        $user->name=$name;
        $user->email=$email;
        if ($password!='') {
        $user->password=$password;
         }
         $user->role_id=$role;
         $user->role=$role_name;
         $user->countryId=$countryId;
        $user->save();
        session()->flash('success','Admin user has been updated successfully.');
        return redirect()->to('/admin/users');

    }

    public function getEdit($id) {
         
        $users = User::find($id);
        $userroles = UsersRoles::get();
        $country = Country::where([['status', '=','1']])->get();
       return view('admin.users.addedit',compact('users','userroles','country'));
    }

    public function Delete($id) {
        $user = User::findOrFail($id);
        $user->delete();
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.show')->with('user', $user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        session()->flash('success','User Deleted.');
        return redirect()->back();
    }
}
