<?php
namespace Laraspace\Http\Controllers;

use Laraspace\UsersRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Auth;
use Laraspace\Http\Controllers\CommanController;


class UsersRolesController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }
    public function index()
    {
        $common = new CommanController();
        $nouserrolesmsg=$common->get_msg('no_user_roles_found',1)?$common->get_msg('no_user_roles_found',1):"No user roles available.";
        $usersroles = UsersRoles::get();
        return view('admin.usersroles.index',compact('nouserrolesmsg','usersroles'));
    }

    public function create() {
         
          //$user_module=$this->user_module();
          //$master_module_list=$this->master_module_list();
          //$user_module_list=$this->user_module_list();
        return view('admin.usersroles.addedit');
       //return view('admin.usersroles.addedit',compact('user_module','master_module_list','user_module_list'));
    }

    public function postCreate(Request $request) {
          $common = new CommanController();

         $master_sub_create=($request->input('create'))?($request->input('create')):'';
         $master_sub_update=($request->input('update'))?($request->input('update')):'';
         $master_sub_delete=($request->input('delete'))?($request->input('delete')):'';
         $master_sub_view=($request->input('view'))?($request->input('view')):'';
        
         $role_name=($request->role_name)?($request->role_name):'';
         $checkduplicate = DB::table('user_roles')->where([['role_name', '=',$role_name]])->count();
         if ($checkduplicate > 0) {
             flash()->error($common->get_msg('already_role_name',1));
             return redirect()->to('/admin/usersroles/create');
         
         } else {
              $user = new UsersRoles();
              $user->role_name=$role_name;
              $user->created_at=date("Y-m-d H:i:s");
              $user->save();
              $role_id=$user->role_id;
              if ($master_sub_create) {
                foreach ($master_sub_create as $key => $value) {
                  $checkduplicate = DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id','=',$key]])->count();
                   if ($checkduplicate==0) {
                  $insert=DB::table('users_permision')->insert(
               ['is_create'=>$value,'menu_id'=>$key,'created_at'=>date('Y-m-d H:i:s'),'role_id'=>$role_id]);
                } else {
                   $update=DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id', '=', $key]])->update(
           ['is_create'=>$value,'menu_id'=>$key,'updated_at'=>date('Y-m-d H:i:s')]);
                }
              }
            }

            if ($master_sub_update) {
                foreach ($master_sub_update as $key => $value) {
                  $checkduplicate = DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id','=',$key]])->count();
                   if ($checkduplicate==0) {
                  $insert=DB::table('users_permision')->insert(
               ['is_update'=>$value,'menu_id'=>$key,'created_at'=>date('Y-m-d H:i:s'),'role_id'=>$role_id]);
                } else {
                   $update=DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id', '=', $key]])->update(
           ['is_update'=>$value,'menu_id'=>$key,'updated_at'=>date('Y-m-d H:i:s')]);
                }
              }
            }

            if ($master_sub_delete) {
                foreach ($master_sub_delete as $key => $value) {
                  $checkduplicate = DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id','=',$key]])->count();
                   if ($checkduplicate==0) {
                  $insert=DB::table('users_permision')->insert(
               ['is_delete'=>$value,'menu_id'=>$key,'created_at'=>date('Y-m-d H:i:s'),'role_id'=>$role_id]);
                } else {
                   $update=DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id', '=', $key]])->update(
           ['is_delete'=>$value,'menu_id'=>$key,'updated_at'=>date('Y-m-d H:i:s')]);
                }
              }
            }

            if ($master_sub_view) {
                foreach ($master_sub_view as $key => $value) {
                  $checkduplicate = DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id','=',$key]])->count();
                   if ($checkduplicate==0) {
                  $insert=DB::table('users_permision')->insert(
               ['is_view'=>$value,'menu_id'=>$key,'created_at'=>date('Y-m-d H:i:s'),'role_id'=>$role_id]);
                } else {
                   $update=DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id', '=', $key]])->update(
           ['is_view'=>$value,'menu_id'=>$key,'updated_at'=>date('Y-m-d H:i:s')]);
                }
              }
            }

              $msg=$common->get_msg('new_role',1)?$common->get_msg('new_role',1):"New role has been added successfully.";
              session()->flash('success',$msg);
             return redirect()->to('/admin/usersroles');
          
         }

    }

    public function postEdit(Request $request,$id) {
          $common = new CommanController();
         $master_sub_create=($request->input('create'))?($request->input('create')):'';
         $master_sub_update=($request->input('update'))?($request->input('update')):'';
         $master_sub_delete=($request->input('delete'))?($request->input('delete')):'';
         $master_sub_view=($request->input('view'))?($request->input('view')):'';
         $role_name=($request->role_name)?($request->role_name):'';
         $user = UsersRoles::find($id);
         $user->role_name=$role_name;
         $user->created_at=date("Y-m-d H:i:s");
         $user->save();
         $role_id=$id;

         $delete=DB::delete('delete from users_permision where role_id IN ('.$role_id.')');
         
         if ($master_sub_create) {
                foreach ($master_sub_create as $key => $value) {
                  $checkduplicate = DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id','=',$key]])->count();
                   if ($checkduplicate==0) {
                  $insert=DB::table('users_permision')->insert(
               ['is_create'=>$value,'menu_id'=>$key,'created_at'=>date('Y-m-d H:i:s'),'role_id'=>$role_id]);
                } else {
                   $update=DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id', '=', $key]])->update(
           ['is_create'=>$value,'menu_id'=>$key,'updated_at'=>date('Y-m-d H:i:s')]);
                }
              }
            }

            if ($master_sub_update) {
                foreach ($master_sub_update as $key => $value) {
                  $checkduplicate = DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id','=',$key]])->count();
                   if ($checkduplicate==0) {
                  $insert=DB::table('users_permision')->insert(
               ['is_update'=>$value,'menu_id'=>$key,'created_at'=>date('Y-m-d H:i:s'),'role_id'=>$role_id]);
                } else {
                   $update=DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id', '=', $key]])->update(
           ['is_update'=>$value,'menu_id'=>$key,'updated_at'=>date('Y-m-d H:i:s')]);
                }
              }
            }

            if ($master_sub_delete) {
                foreach ($master_sub_delete as $key => $value) {
                  $checkduplicate = DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id','=',$key]])->count();
                   if ($checkduplicate==0) {
                  $insert=DB::table('users_permision')->insert(
               ['is_delete'=>$value,'menu_id'=>$key,'created_at'=>date('Y-m-d H:i:s'),'role_id'=>$role_id]);
                } else {
                   $update=DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id', '=', $key]])->update(
           ['is_delete'=>$value,'menu_id'=>$key,'updated_at'=>date('Y-m-d H:i:s')]);
                }
              }
            }

            if ($master_sub_view) {
                foreach ($master_sub_view as $key => $value) {
                  $checkduplicate = DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id','=',$key]])->count();
                   if ($checkduplicate==0) {
                  $insert=DB::table('users_permision')->insert(
               ['is_view'=>$value,'menu_id'=>$key,'created_at'=>date('Y-m-d H:i:s'),'role_id'=>$role_id]);
                } else {
                   $update=DB::table('users_permision')->where([['role_id', '=',$role_id],['menu_id', '=', $key]])->update(
           ['is_view'=>$value,'menu_id'=>$key,'updated_at'=>date('Y-m-d H:i:s')]);
                }
              }
            }
            $msg=$common->get_msg('update_role',1)?$common->get_msg('update_role',1):"Role has been updated successfully.";
            session()->flash('success',$msg);
             return redirect()->to('/admin/usersroles');

    }

    public function getEdit($id) {
         
        //$user_module=$this->user_module();
          //$master_module_list=$this->master_module_list();
          //$user_module_list=$this->user_module_list();
        $users = UsersRoles::find($id);
       return view('admin.usersroles.addedit',compact('users'));
       //return view('admin.usersroles.addedit',compact('users','user_module','master_module_list','user_module_list'));
    }

    public function getView($id) {
         
        $user_module=$this->user_module();
          $master_module_list=$this->master_module_list();
          $user_module_list=$this->user_module_list();
        $users = UsersRoles::find($id);
       return view('admin.usersroles.view',compact('users','user_module','master_module_list','user_module_list'));
    }

    public function Delete($id) {
        $common = new CommanController();
        $checkuser = DB::table('users')->where([['role_id', '=',$id]])->count();
         if ($checkuser > 0) {
          $msg=$common->get_msg('role_delete',1)?$common->get_msg('role_delete',1):"This Role can not be deleted because this role are using by user.";
         flash()->error($msg);
             return redirect()->to('/admin/usersroles');

       } else {
        $delete=DB::delete('delete from user_roles where role_id IN ('.$id.')');
        $msg=$common->get_msg('delete_role_sucess',1)?$common->get_msg('delete_role_sucess',1):"Role user has been deleted successfully.";
        flash()->success($msg);
             return redirect()->to('/admin/usersroles');
      }
    }

    public function show($id)
    {
        $usersroles = UsersRoles::findOrFail($id);

        return view('admin.usersroles.show')->with('usersroles', $usersroles);
    }

    public function destroy($id)
    {
        $usersroles = UsersRoles::findOrFail($id);
        $usersroles->delete();
        session()->flash('success','User Deleted');
        return redirect()->back();
    }
}
