<?php

namespace App\DataTables\Admin;

use App\Models\Admin;
use Common, Config, Auth;
use App\Models\OrgTransaction;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Services\DataTable;

class OrgTransactionDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        $transactions = $this->query();

        return datatables()
            ->of($transactions)
            ->addColumn('uuid', function ($transaction) {
                return $transaction->uuid;
            })
            ->addColumn('organization_id', function ($transaction) {
                return $transaction->organization->name;
            })
            ->addColumn('amount', function ($transaction) {
                return $transaction->amount;
            })
            ->addColumn('commission_rate', function ($transaction) {
                return $transaction->commission_rate;
            })
            ->addColumn('admin_id', function ($transaction) {
                return $transaction->admin->first_name;
            })
            ->addColumn('commission_amount', function ($transaction) {
                return $transaction->commission_amount;
            })
            ->addColumn('total_amount', function ($transaction) {
                return $transaction->total_amount;
            })
            ->addColumn('balance', function ($transaction) {
                return $transaction->balance;
            })
            ->addColumn('note', function ($transaction) {
                return $transaction->note;
            })
            // ->addColumn('action', function ($transaction) {
            //     $edit = (Common::has_permission(auth('admin')->user()->id, 'view_staff')) ? '<a href="'.url(config('adminPrefix').'/org-transaction/edit/'.$transaction->id).'" class="btn btn-xs btn-primary"><i class="fa fa-edit f-14"></i></a>&nbsp;' : '';
            //     $delete = (Common::has_permission(auth('admin')->user()->id, 'view_staff')) ? '<a href="'.url(config('adminPrefix').'/org-transaction/delete/'.$transaction->id).'" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>' : '';

            //     return $edit.$delete;
            // })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function query()
    {
        $transactions = OrgTransaction::select([
            'id',
            'uuid',
            'organization_id',
            'amount',
            'commission_rate',
            'admin_id',
            'commission_amount',
            'total_amount',
            'balance',
            'note',
            'created_at',
        ])->orderBy('created_at', 'desc');  

        return $this->applyScopes($transactions);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'uuid', 'name' => 'org_transactions.uuid', 'title' => __('Transaction ID')])
            ->addColumn(['data' => 'organization_id', 'name' => 'org_transactions.organization_id', 'title' => __('Organization')])
            ->addColumn(['data' => 'amount', 'name' => 'org_transactions.amount', 'title' => __('Amount')])
            ->addColumn(['data' => 'commission_rate', 'name' => 'org_transactions.commission_rate', 'title' => __('Commission Rate')])
            ->addColumn(['data' => 'admin_id', 'name' => 'org_transactions.admin_id', 'title' => __('Admin')])
            ->addColumn(['data' => 'commission_amount', 'name' => 'org_transactions.commission_amount', 'title' => __('Commission Amount')])
            ->addColumn(['data' => 'total_amount', 'name' => 'org_transactions.total_amount', 'title' => __('Total Amount')])
            ->addColumn(['data' => 'balance', 'name' => 'org_transactions.balance', 'title' => __('Balance')])
            ->addColumn(['data' => 'note', 'name' => 'org_transactions.note', 'title' => __('Note')])
            // ->addColumn(['data'  => 'action', 'name'  => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters($this->dataTableOptions());
    }

    protected function dataTableOptions()
    {
        return [
            'dom' => 'Bfrtip',
            'buttons' => ['excel', 'csv', 'pdf', 'print'],
            'order' => [[0, 'desc']],
        ];
    }
}