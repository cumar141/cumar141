<?php

namespace App\DataTables\Admin;

use  Config, Auth;
use App\Models\Wallet;
use App\Http\Helpers\Common;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Services\DataTable;


class OrganizationWalletDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {

        $walletInfo = $this->query()->get();
        Log::info('The Wallet info ' . $walletInfo);

        return datatables()
            ->of($walletInfo)
            ->addColumn('organization_name', function ($walletInfo) {
                return ucfirst($walletInfo->organization_name);
            })

            ->addColumn('balance', function ($walletInfo) {
                return number_format($walletInfo->balance, 2);
            })

            ->rawColumns(['Merchant'])
            ->make(true);
    }
    public function query()
    {

        $walletInfo = Wallet::select(
            'organizations.name AS organization_name',
            'merchants.merchant_uuid',
            'merchants.business_name',
            'wallets.balance',
            'wallets.updated_at AS last_updated_at'
        )
            ->leftJoin('users', 'wallets.user_id', '=', 'users.id')
            ->leftJoin('merchants', 'users.id', '=', 'merchants.user_id')
            ->leftJoin('organizations', 'merchants.merchant_uuid', '=', 'organizations.merchant_uuid')
            ->where('users.type', 'merchant')
            ->where('wallets.currency_id', 1)
            ->whereNotNull('organizations.name')
            ->groupBy(
                'organizations.name',
                'merchants.merchant_uuid',
                'merchants.business_name',
                'wallets.balance',
            );
        // Log::info('The Wallet info '.$walletInfo->toSql());


        return $walletInfo;
    }





    public function html()
    {
        return $this->builder()
            ->columns([
                ['data' => 'organization_name', 'name' => 'organizations.name', 'title' => __('Organization Name')],

                ['data' => 'merchant_uuid', 'name' => 'merchants.merchant_uuid', 'title' => __('Link Account')],

                ['data' => 'balance', 'name' => 'wallets.balance', 'title' => __('Balance')],
            ])
            ->parameters(dataTableOptions());
    }
}
