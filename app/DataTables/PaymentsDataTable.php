<?php
namespace App\DataTables;

use App\Models\AutoPayout;
use App\Models\User;
use Common;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Builder;

class PaymentsDataTable extends DataTable
{ 
    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('payment_method', function ($autopayout) {
                return "{$autopayout->payment_method} - {$autopayout->partner}";
            })->editColumn('fee', function ($autopayout) {
                return "{$autopayout->fee}%";
            })
            ->addColumn('action', function ($autopayout) {
                return '<div class="btn-group" role="group" aria-label="Action buttons">' .
                    '<button type="button" class="btn btn-info btn-sm retry" data-id="' . $autopayout->trx_reference . '" data-toggle="tooltip" data-placement="top" title="Retry">' .
                    '<i class="fas fa-redo"></i>' .
                    '</button>' .
                    '<button type="button" class="btn btn-success btn-sm approve" data-id="' . $autopayout->trx_reference . '" data-toggle="tooltip" data-placement="top" title="Approve">' .
                    '<i class="fas fa-check-double"></i>' .
                    '</button>' .
                    '<button type="button" class="btn btn-danger btn-sm block" data-id="' . $autopayout->trx_reference . '" data-toggle="tooltip" data-placement="top" title="Block">' .
                    '<i class="fas fa-ban"></i>' .
                    '</button>' .
                    '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function query()
    {
        $query = AutoPayout::where('status', 4);
    
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'created_at', 'name' => 'created_at', 'title' => __('Date'), 'searchable' => true, 'visible' => true])
            ->addColumn(['data' => 'sender', 'name' => 'sender', 'title' => __('Sender'), 'searchable' => true, 'visible' => true])
            ->addColumn(['data' => 'receiver', 'name' => 'receiver', 'title' => __('Receiver'), 'searchable' => true, 'visible' => true])
            ->addColumn(['data' => 'cleared_amount', 'name' => 'cleared_amount', 'title' => __('Cleared Amount'), 'searchable' => true, 'visible' => true])
            ->addColumn(['data' => 'amount', 'name' => 'amount', 'title' => __('Amount'), 'searchable' => true, 'visible' => true])
            ->addColumn(['data' => 'rate', 'name' => 'rate', 'title' => __('Rate'), 'searchable' => true, 'visible' => true])
            ->addColumn(['data' => 'fee', 'name' => 'fee', 'title' => __('Fee'), 'searchable' => true, 'visible' => true])
            ->addColumn(['data' => 'platform', 'name' => 'platform', 'title' => __('Platform'), 'searchable' => true, 'visible' => true])
            ->addColumn(['data' => 'payment_method', 'name' => 'payment_method', 'title' => __('Payment Method'), 'searchable' => true, 'visible' => true])
            ->addColumn(['data' => 'trx_reference', 'name' => 'trx_reference', 'title' => __('Trx Reference'), 'searchable' => true, 'visible' => true])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Actions'), 'orderable' => true, 'searchable' => true])
            ->parameters(dataTableOptions());
    }
}
