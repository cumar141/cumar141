<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    Organization,
    OrganizationWallet,
    Currency
};
use App\DataTables\Admin\organizationWalletDatatable;
use App\Http\Helpers\Common;
use App\Http\Controllers\Users\EmailController;
use Hash, Validator, Session, DB, Exception;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Auth;

class OrganizationWalletController extends Controller
{
    protected $helper;
    protected $email;
    protected $organization;
    protected $OrganizationWallet;
 
    public function __construct()
    {
        $this->helper = new Common();
        $this->email = new EmailController();
        $this->currency = new Currency();
        $this->organization = new Organization(); 
        $this->OrganizationWallet = new OrganizationWallet();
    }

    public function index(organizationWalletDatatable $dataTable)
    {
        $data['menu'] = 'organization';
        $data['sub_menu'] = 'organization_wallet_list';
     
      
        return $dataTable->render('admin.organization_wallet.index', $data);
    }

    public function create()
    {
        $data['menu']     = 'organization';
        $data['sub_menu'] = 'organization_wallet_list';

        return view('admin.organization_wallet.organization_wallet_search', $data);
    }

    public function search(Request $request)
    {
        $searchItem = $request->searchInput;
        $data['menu']     = 'organization';
        $data['sub_menu'] = 'organization_wallet_list';
        $organizations = Organization::where(function ($query) use ($searchItem) {
            // Define the columns to search in
            $columns = ['name', 'email', 'phone'];
    
            // Iterate over the columns
            foreach ($columns as $column) {
                // Use the like operator to search for the searchItem in each column
                $query->orWhere($column, 'like', '%' . $searchItem . '%');
            }
        })->first();
    
        if (empty($organizations)) {
            return view('admin.organization_user.search')->with('message', 'No results found');
        }
        $data['organizations'] = $organizations;
    
        return view('admin.organization_wallet.create', $data);
    }
    
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'organizations_id' => 'required',
            'balance' => 'required',
        ]);
    
        // Check if organization ID and balance are provided
        if (!$validatedData['organizations_id'] || !$validatedData['balance']) {
            $this->helper->one_time_message('error', 'Please select an organization and enter a balance');
            return redirect('admin/organization/wallet');
        }
    
        // Check if an organization wallet already exists
        $organizationWallet = OrganizationWallet::where('organization_id', $validatedData['organizations_id'])->first();
        if ($organizationWallet) {
            // Increment the organization wallet balance
            $organizationWallet->balance += $validatedData['balance'];
            $organizationWallet->save();
            $this->helper->one_time_message('success', 'Organization balance updated successfully with ' . $validatedData['balance']);
        } else {
            // Create a new organization wallet
            $organizationWallet = new OrganizationWallet();
            $organizationWallet->organization_id = $validatedData['organizations_id'];
            $organizationWallet->balance = $validatedData['balance'];
            $organizationWallet->save();
            $this->helper->one_time_message('success', 'Organization wallet created successfully');
        }
    
        return redirect('admin/organization/wallet');
    }

    public function edit($id)
    {
        $data['menu']     = 'organization';
        $data['sub_menu'] = 'organization_wallet_list';
        $data['OrganizationWallet'] = OrganizationWallet::with('organization')->find($id);
        
        return view('admin.organization_wallet.edit', $data);
    }
    
    public function update(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'id' => 'required',
            'balance' => 'required',
            'password' => 'required',
        ]);
    
        // Hash the provided password
        $hashedPassword = Auth::guard('admin')->user()->password;
    
        // Check if the provided password matches the hashed password from the database
        if (!Hash::check($validatedData['password'], $hashedPassword)) {
            return redirect()->back()->withErrors(['password' => 'Please enter a valid password']);
        }
    
        // Check if the balance is greater than 0
        if ($validatedData['balance'] <= 0) {
            return redirect()->back()->withErrors(['balance' => 'Balance should be greater than 0']);
        }
    
        // Check if a valid ID is provided
        if (!$validatedData['id']) {
            return redirect()->back()->withErrors(['id' => 'Please provide a valid ID']);
        }
    
        // Check if an organization wallet exists
        $organizationWallet = OrganizationWallet::where('organization_id', $validatedData['id'])->first();
        if ($organizationWallet) {
            // Update the organization wallet balance
            $organizationWallet->balance = $validatedData['balance'];
            $organizationWallet->save();
            $this->helper->one_time_message('success', 'Organization balance updated successfully with ' . $validatedData['balance']);
        } else {
            // Create a new organization wallet if it doesn't exist
            $organizationWallet = new OrganizationWallet();
            $organizationWallet->organization_id = $validatedData['id'];
            $organizationWallet->balance = $validatedData['balance'];
            $organizationWallet->save();
            $this->helper->one_time_message('success', 'New organization wallet created successfully with balance ' . $validatedData['balance']);
        }
    
        return redirect('admin/organization/wallet');
    }
    
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $organizationWallet = OrganizationWallet::find($id);
            if(!$organizationWallet){
                $this->helper->one_time_message('error', 'Organization not found');
                return redirect('admin/organization/wallet');
            }
            $organizationWallet->delete();

            DB::commit();

            $this->helper->one_time_message('success', 'Organization Deleted Successfully');
            return redirect('admin/organization/wallet');
        } catch (Exception $e) {
            DB::rollBack();
            $this->helper->one_time_message('error', 'Something went wrong! Please try again.');
            return back();
        }
    }
}