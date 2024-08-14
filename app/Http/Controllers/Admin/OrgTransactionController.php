<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\{
    OrgTransaction,
    Organization,
    OrganizationWallet
};
use App\DataTables\Admin\OrgTransactionDataTable;

class OrgTransactionController extends Controller
{
    public function index(OrgTransactionDataTable $dataTable)
    {
        $data['menu'] = 'organization';
        $data['sub_menu'] = 'organization_transaction_list';
        return $dataTable->render('admin.organization_transaction.index', $data);
    }

    public function ShowSearch()
    {
        $data['menu'] = 'organization';
        $data['sub_menu'] = 'organization_transaction_list';
        return view('admin.organization_transaction.search', $data);
    }

    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,id',
            'balance' => 'required|numeric',
            'commision_rate' => 'required|numeric',
        ]);
        $uuid = unique_code();

        // Calculate commission
        $commission_amount = ($request->balance * $request->commision_rate) / 100;
        $total_amount = $request->balance - $commission_amount;
        $admin_id = auth()->guard('admin')->user()->id;

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $organization = Organization::findOrFail($request->organization_id);

            $orgWallet = OrganizationWallet::where('organization_id', $organization->id)->first();
            if (!$orgWallet) {
                return redirect()->back()->with('error', 'Organization wallet not found');
            }

            DB::beginTransaction();

            $orgTransaction = new OrgTransaction();
            $orgTransaction->uuid = $uuid;
            $orgTransaction->organization_id = $organization->id;
            $orgTransaction->amount = $request->balance;
            $orgTransaction->commission_rate = $request->commision_rate;
            $orgTransaction->admin_id = $admin_id;
            $orgTransaction->commission_amount = $commission_amount;
            $orgTransaction->total_amount = $total_amount;
            $orgTransaction->balance = $orgWallet->balance + $total_amount;
            $orgTransaction->note = $request->note;
            $orgTransaction->status = 'Success';
            $orgTransaction->save();

            //update organization wallet
            $orgWallet->balance = $orgWallet->balance + $total_amount;
            $orgWallet->save();

            DB::commit();

            return redirect()->route('organization.transaction')->with('success', 'Transaction created successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function addTransaction($id)
    {

        $data['menu'] = 'organization';
        $data['sub_menu'] = 'organization_transaction_list';

        $organizations = Organization::where('id', $id)->first();

        if (empty($organizations)) {
            return view('admin.organization_user.search')->with('message', 'No results found');
        }

        $data['organizations'] = $organizations;

        return view('admin.organization_transaction.create', $data);
    }
}
