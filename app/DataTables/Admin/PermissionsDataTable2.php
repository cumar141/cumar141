<?php 
namespace App\DataTables\Admin;

use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Services\DataTable;

class PermissionsDataTable2 extends DataTable
{
    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->addColumn('action', function ($permission) {
                $edit = (\Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="'.url(config('adminPrefix').'/permissions/edit/'.$permission->id).'" class="btn btn-xs btn-primary"><i class="fa fa-edit f-14"></i></a>&nbsp;' : '';
                $delete = (\Common::has_permission(auth('admin')->user()->id, 'delete_user')) ? '<a href="'.url(config('adminPrefix').'/permissions/delete/'.$permission->id).'" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>' : '';

                return $edit.$delete;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function query()
    {
        return Permission::select('id', 'group', 'name', 'display_name', 'user_type', 'description')
        ->whereIn('user_type', ['staff', 'Staff', 'STAFF']);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'id', 'title' => __('ID'), 'visible' => false])
            ->addColumn(['data' => 'group', 'name' => 'group', 'title' => __('Group')])
            ->addColumn(['data' => 'name', 'name' => 'name', 'title' => __('Name')])
            ->addColumn(['data' => 'display_name', 'name' => 'display_name', 'title' => __('Display Name')])
            ->addColumn(['data' => 'user_type', 'name' => 'user_type', 'title' => __('User Type')])
            ->addColumn(['data' => 'description', 'name' => 'description', 'title' => __('Description')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}

