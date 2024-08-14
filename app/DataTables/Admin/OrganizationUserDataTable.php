<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use Common, Config, Auth;
use App\Models\OrganizationUser;
use Illuminate\Http\JsonResponse;

class OrganizationUserDataTable extends DataTable
{

    protected $id;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function ajax(): JsonResponse
    {
        $organizations = $this->query();

        return datatables()
            ->of($organizations)
            ->addColumn('Org. name', function ($organization) {
                return (Common::has_permission(auth('admin')->user()->id, 'edit_group')) ?
                    '<a href="' . url(config('adminPrefix') . '/organization/user/edit/' . $organization->id) . '">' . ucfirst($organization->organization->name) . '</a>' : ucfirst($organization->organization->name);
            })
            ->addColumn('email', function ($organization) {
                return $organization->email;
            })
            ->addColumn('username', function ($organization) {
                return $organization->username;
            })
            ->addColumn('action', function ($organization) {
                $edit = (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="' . url(config('adminPrefix') . '/organization/user/edit/' . $organization->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit f-14"></i></a>&nbsp;' : '';
                $delete = (Common::has_permission(auth('admin')->user()->id, 'delete_user')) ? '<a href="' . url(config('adminPrefix') . '/organization/user/delete/' . $organization->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>' : '';

                return $edit . $delete;
            })
            ->rawColumns(['Org. name', 'action'])
            ->make(true);
    }

    public function query()
    {
        $query = OrganizationUser::with('organization');

        if ($this->id) {
            $query->where('organization_id', $this->id);
        }

        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'Org. name', 'name' => 'organization.name', 'title' => __('Org. Name')])
            ->addColumn(['data' => 'email', 'name' => 'email', 'title' => __('Email')])
            ->addColumn(['data' => 'username', 'name' => 'username', 'title' => __('Username')]) // Assuming username is directly available in OrganizationUser model
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
