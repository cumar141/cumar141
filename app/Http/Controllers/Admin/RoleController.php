<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\RolesDataTable;
use App\DataTables\Admin\RolesDataTable2;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new \Common();
    }

    public function index(RolesDataTable $dataTable)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'role';

        return $dataTable->render('admin.roles.view', $data);
    }

    public function indexRole(RolesDataTable2 $dataTable)
    {
        $data['menu'] = 'staff';
        $data['sub_menu'] = 'role_list';

        return $dataTable->render('admin.roles.listRole', $data);
    }

    public function add(Request $request)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'role';

        if (!$request->isMethod('post')) {
            $data['permissions'] = $permissions = Permission::where(['user_type' => 'Admin'])->select('id', 'group', 'display_name', 'user_type')->get();

            $perm = [];
            if (!empty($permissions)) {
                foreach ($permissions as $key => $value) {
                    $perm[$value->group][$key]['id'] = $value->id;
                    $perm[$value->group][$key]['display_name'] = $value->display_name;
                    $perm[$value->group][$key]['user_type'] = $value->user_type;
                }
            }
            $data['perm'] = $perm;

            return view('admin.roles.add', $data);
        } elseif ($request->isMethod('post')) {
            $rules = [
                // 'name'         => 'required|unique:roles|max:255',
                'name' => 'required',
                'display_name' => 'required',
                'description' => 'required',
            ];

            $fieldNames = [
                'name' => 'Name',
                'display_name' => 'Display Name',
                'description' => 'Description',
            ];

            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                $role = new Role();
                $role->name = $request->name;
                $role->display_name = $request->display_name;
                $role->description = $request->description;
                $role->user_type = 'Admin';
                $role->save();

                foreach ($request->permission as $key => $value) {
                    PermissionRole::firstOrCreate(['permission_id' => $value, 'role_id' => $role->id]);
                }
                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('role')]));

                return redirect(config('adminPrefix').'/settings/roles');
            }
        } else {
            return redirect(config('adminPrefix').'/settings/roles');
        }
    }

    public function storeRole(Request $request)
    {
        $data['menu'] = 'staff';
        $data['sub_menu'] = 'role_list';

        if (!$request->isMethod('post')) {
            $data['permissions'] = $permissions = Permission::where(['user_type' => 'Staff'])->select('id', 'group', 'display_name', 'user_type')->get();

            $perm = [];
            if (!empty($permissions)) {
                foreach ($permissions as $key => $value) {
                    $perm[$value->group][$key]['id'] = $value->id;
                    $perm[$value->group][$key]['display_name'] = $value->display_name;
                    $perm[$value->group][$key]['user_type'] = $value->user_type;
                }
            }
            $data['perm'] = $perm;

            return view('admin.roles.createRole', $data);
        } elseif ($request->isMethod('post')) {
            $rules = [
                // 'name'         => 'required|unique:roles|max:255',
                'name' => 'required',
                'display_name' => 'required',
                'description' => 'required',
            ];

            $fieldNames = [
                'name' => 'Name',
                'display_name' => 'Display Name',
                'description' => 'Description',
            ];

            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                $role = new Role();
                $role->name = $request->name;
                $role->display_name = $request->display_name;
                $role->description = $request->description;
                $role->user_type = 'Staff';
                $role->customer_type = 'staff';
                $role->save();

                foreach ($request->permission as $key => $value) {
                    PermissionRole::firstOrCreate(['permission_id' => $value, 'role_id' => $role->id]);
                }
                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('role')]));

                return redirect(config('adminPrefix').'/roles');
            }
        } else {
            return redirect(config('adminPrefix').'/roles');
        }
    }

    public function update(Request $request)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'role';

        if ($request->isMethod('post')) {
            $rules = [
                'name' => 'required',
                'display_name' => 'required',
                'description' => 'required',
            ];

            $fieldNames = [
                'name' => 'Name',
                'display_name' => 'Display Name',
                'description' => 'Description',
            ];

            $validator = \Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                $role = Role::find($request->id);
                $role->name = $request->name;
                $role->display_name = $request->display_name;
                $role->description = $request->description;
                $role->user_type = 'Admin';

                $role->save();

                $stored_permissions = Role::permission_role($request->id);

                foreach ($stored_permissions as $key => $value) {
                    if (!in_array($value, $request->permission)) {
                        PermissionRole::where(['permission_id' => $value, 'role_id' => $request->id])->delete();
                    }
                }
                foreach ($request->permission as $key => $value) {
                    PermissionRole::firstOrCreate(['permission_id' => $value, 'role_id' => $request->id]);
                }

                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('role')]));

                return redirect(config('adminPrefix').'/settings/roles');
            }
        }

        $data['result'] = Role::find($request->id);
        $data['stored_permissions'] = $stored_permissions = Role::permission_role($request->id)->toArray();
        $permissions = Permission::where(['user_type' => 'Admin'])->select('id', 'group', 'display_name', 'user_type')->get();

        $perm = [];
        if (!empty($permissions)) {
            foreach ($permissions as $key => $value) {
                $perm[$value->group][$key]['id'] = $value->id;
                $perm[$value->group][$key]['display_name'] = $value->display_name;
                $perm[$value->group][$key]['user_type'] = $value->user_type;
            }
        }
        $data['permissions'] = $perm;

        return view('admin.roles.edit', $data);
    }

    public function updateRole(Request $request)
    {
        $data['menu'] = 'staff';
        $data['sub_menu'] = 'role_list';

        $rules = [
            'name' => 'required',
            'display_name' => 'required',
            'description' => 'required',
        ];

        $fieldNames = [
            'name' => 'Name',
            'display_name' => 'Display Name',
            'description' => 'Description',
        ];

        $validator = \Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            $role = Role::find($request->id);
            $role->name = $request->name;
            $role->display_name = $request->display_name;
            $role->description = $request->description;
            $role->user_type = 'Staff';
            $role->customer_type = 'staff';

            $role->save();

            $stored_permissions = Role::permission_role($request->id);

            foreach ($stored_permissions as $key => $value) {
                if (!in_array($value, $request->permission)) {
                    PermissionRole::where(['permission_id' => $value, 'role_id' => $request->id])->delete();
                }
            }
            foreach ($request->permission as $key => $value) {
                PermissionRole::firstOrCreate(['permission_id' => $value, 'role_id' => $request->id]);
            }

            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('role')]));

            return redirect(config('adminPrefix').'/roles');
        }
    }

    public function editRole($id)
    {
        $data['menu'] = 'staff';
        $data['sub_menu'] = 'role_list';

        $data['result'] = Role::find($id);
        $data['stored_permissions'] = $stored_permissions = Role::permission_role($id)->toArray();
        $permissions = Permission::where(['user_type' => 'Staff'])->select('id', 'group', 'display_name', 'user_type')->get();

        $perm = [];
        if (!empty($permissions)) {
            foreach ($permissions as $key => $value) {
                $perm[$value->group][$key]['id'] = $value->id;
                $perm[$value->group][$key]['display_name'] = $value->display_name;
                $perm[$value->group][$key]['user_type'] = $value->user_type;
            }
        }
        $data['permissions'] = $perm;

        return view('admin.roles.editRole', $data);
    }

    public function duplicateRoleCheck(Request $request)
    {
        $req_id = $request->id;
        if (isset($request->id)) {
            if (isset($request->user_type) && $request->user_type == 'Admin') {
                $name = Role::where(['user_type' => $request->user_type, 'name' => $request->name])
                ->where(function ($query) use ($req_id) {
                    $query->where('id', '!=', $req_id);
                })
                ->exists();
            } else {
                $User = $request->user_type;
                $name = Role::where(['user_type' => $User, 'name' => $request->name])
                ->where(function ($query) use ($req_id) {
                    $query->where('id', '!=', $req_id);
                })
                ->exists();
            }
        } else {
            if (isset($request->user_type) && $request->user_type == 'Admin') {
                $name = Role::where(['user_type' => $request->user_type, 'name' => $request->name])->exists();
            } else {
                $User = $request->user_type;
                $name = Role::where(['user_type' => $User, 'name' => $request->name])->exists();
            }
        }

        if ($name) {
            $data['status'] = true;
            $data['fail'] = __('The :x is already exist.', ['x' => __('role name')]);
        } else {
            $data['status'] = false;
            $data['success'] = __('The :x is available.', ['x' => __('role name')])
            ;
        }

        return json_encode($data);
    }

    public function delete(Request $request)
    {
        Role::where('id', $request->id)->delete();
        PermissionRole::where('role_id', $request->id)->delete();

        $role_user = \DB::table('role_user')->where(['role_id' => $request->id, 'user_type' => 'Admin'])->first();

        if (isset($role_user)) {
            $role_user->delete();
        }
        $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('role')]));

        return redirect(config('adminPrefix').'/settings/roles');
    }

    public function deleteRole(Request $request)
    {
        Role::where('id', $request->id)->delete();
        PermissionRole::where('role_id', $request->id)->delete();

        $role_user = \DB::table('role_user')->where(['role_id' => $request->id, 'user_type' => 'Admin'])->first();

        if (isset($role_user)) {
            $role_user->delete();
        }
        $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('role')]));

        return redirect(config('adminPrefix').'/roles');
    }
}
