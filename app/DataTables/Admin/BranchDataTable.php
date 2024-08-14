<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;

class BranchDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        $branches = $this->query();

        return datatables()
            ->of($branches)
            ->addColumn('action', function ($branch) {
                $edit = '<a href="' . route('branch.edit', $branch->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;';
                $delete = '<a href="' . route('branch.destroy', $branch->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>';

                return $edit . $delete;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function query()
    {
        return Branch::query()->orderBy('id');
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'id', 'title' => 'ID'])
            ->addColumn(['data' => 'name', 'name' => 'name', 'title' => 'Name'])
            ->addColumn(['data' => 'address', 'name' => 'address', 'title' => 'Address'])
            ->addColumn(['data' => 'email', 'name' => 'email', 'title' => 'Email'])
            ->addColumn(['data' => 'phone', 'name' => 'phone', 'title' => 'Phone'])
            ->addColumn(['data' => 'code', 'name' => 'code', 'title' => 'Code'])
            ->addColumn(['data' => 'status', 'name' => 'status', 'title' => 'Status'])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
