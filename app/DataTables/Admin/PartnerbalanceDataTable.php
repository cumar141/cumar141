<?php

namespace App\DataTables\Admin;

use App\Models\Organization;
use App\Models\PartnerBalance;
use Yajra\DataTables\Services\DataTable;
use Common;
use App\Models\OrganizationWallet;
use Illuminate\Http\JsonResponse;

class PartnerbalanceDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        $partnerBalance = $this->query(); 
    
        return datatables()
            ->of($partnerBalance)
            ->addColumn('partner', function ($partnerBalance) {
                return $partnerBalance->partner;
            })
            ->addColumn('type', function ($partnerBalance) {
                return $partnerBalance->type; // Assuming type is directly available in $partnerBalance
            })
            ->addColumn('balance', function ($partnerBalance) {
                return $partnerBalance->balance; // Assuming balance is directly available in $partnerBalance
            })
            ->addColumn('action', function ($partnerBalance) {
                if ($partnerBalance->can_be_altered) {
                    $edit = '<a href="' . route('staff.partner-balance.edit', $partnerBalance->id) . '" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit</a>';
                    $delete = '<a href="' . route('staff.partner-balance.delete', $partnerBalance->id) . '" class="btn btn-danger btn-sm delete-btn"><i class="fa fa-trash"></i> Delete</a>';
                    return $edit . ' ' . $delete;
                } else {
                    $edit = '<button  class="btn btn-primary btn-sm" disabled><i class="fas fa-edit"></i> Edit</button>';
                    $delete = '<button  class="btn btn-danger btn-sm delete-btn" disabled><i class="fa fa-trash"></i> Delete</button>';
                    return $edit . ' ' . $delete;
                }
            })
          
            ->rawColumns(['action'])
            ->make(true);
    }

    public function query()
    {
        $partnerBalance = PartnerBalance::all();
        return $this->applyScopes($partnerBalance);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' =>'partner', 'title' => __('Partner')])
            ->addColumn(['data' =>  'type', 'title' => __('Type')])
            ->addColumn(['data' => 'balance', 'title' => __('Balance')])
            ->addColumn(['data' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }

}
