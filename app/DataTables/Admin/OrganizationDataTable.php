<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use Common, Config, Auth;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;

class OrganizationDataTable extends DataTable
{

    public function ajax(): JsonResponse
{
    $organizations = $this->query();
    
    return datatables()
        ->of($organizations)
        ->addColumn('name', function ($organization) {
            return (Common::has_permission(auth('admin')->user()->id, 'edit_group')) ?
                '<a href="' . url(config('adminPrefix') . '/organization/edit/' . $organization->id) . '">' . ucfirst($organization->name) . '</a>' : ucfirst($organization->name);
        })
        ->addColumn('email', function ($organization) {
            return $organization->email;
        })
        ->addColumn('phone', function ($organization) {
            return $organization->phone;
        })
        ->addColumn('address', function ($organization) {
            return $organization->address;
        })
        ->addColumn('Account', function ($organization) {
            return $organization->Account ? $organization->Account : 'No Merchant Account Linked';
        })
        ->addColumn('action', function ($organization) {
            $transaction = (Common::has_permission(auth('admin')->user()->id, 'add_transaction')) ? '<a href="' . url(config('adminPrefix') . '/organization/add/transaction/' . $organization->id) . '" class="btn btn-xs btn-info"><i class="fa-solid fa-paperclip f-14"></i></a>&nbsp;' : '';
            $user = (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="' . url(config('adminPrefix') . '/organization/list/user/' . $organization->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-user f-14"></i></a>&nbsp;' : '';
            $edit = (Common::has_permission(auth('admin')->user()->id, 'edit_user')) ? '<a href="' . url(config('adminPrefix') . '/organization/edit/' . $organization->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit f-14"></i></a>&nbsp;' : '';
            $delete = (Common::has_permission(auth('admin')->user()->id, 'delete_user')) ? '<a href="' . url(config('adminPrefix') . '/organization/delete/' . $organization->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>' : '';
    
            return $transaction . $user . $edit . $delete;
        })
        ->rawColumns(['name', 'action'])
        ->make(true);
}


    public function query()
    {
        $organizations = Organization::select([
            'organizations.id',
            'organizations.name',
            'organizations.email',
            'organizations.phone',
            'organizations.address',
            'organizations.is_white_label',
            'merchants.merchant_uuid as Account',  
        ])
            ->leftJoin('merchants', 'organizations.merchant_uuid', '=', 'merchants.merchant_uuid');  

        return $this->applyScopes($organizations);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'name', 'name' => 'organizations.name', 'title' => __('Name')])
            ->addColumn(['data' => 'email', 'name' => 'organizations.email', 'title' => __('Email')])
            ->addColumn(['data' => 'phone', 'name' => 'organizations.phone', 'title' => __('Phone')])
            ->addColumn(['data' => 'address', 'name' => 'organizations.address', 'title' => __('Address')])
            ->addColumn(['data' => 'Account', 'name' => 'merchants.merchant_uuid', 'title' => __('Account')]) // Add this line
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
    
}
