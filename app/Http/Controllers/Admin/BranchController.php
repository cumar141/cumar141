<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\BranchDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB, Common, Config;
use App\Models\Backup;
use App\Models\Branch;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    protected $helper;
    protected $branch;

    public function __construct()
    {
        $this->helper = new Common();
        $this->branch = new Branch();
    }

    public function index(BranchDataTable $dataTable)
    {
        $data['menu'] = 'branch';
        $data['sub_menu'] = 'branch_list';

        return $dataTable->render('admin.branch.view', $data);
    }

    public function create()
    {
        $data['menu'] = 'branch';
        $data['sub_menu'] = 'branch_list';

        return view('admin.branch.create', $data);
    }
    public function edit($id)
    {
        $data['menu'] = 'branch';
        $data['sub_menu'] = 'branch_list';
        $data['branch'] = Branch::find($id);

        return view('admin.branch.edit', $data);
    }

    // store function with validation
    public function store(Request $request)
    {
        $data['menu'] = 'branch';
        $data['sub_menu'] = 'branch_list';

        // dd($request);

        $rules = [
            'name' => 'required',
            'address' => 'required',
            'email' => 'required|unique:branchs,email',
            'phone' => 'required',
            'status' => 'required',
        ];
        
        $fieldNames = [
            'name' => 'Name',
            'address' => 'Address',
            'email' => 'Email',
            'phone' => 'Phone',
            'status' => 'Status',
        ];

        $uuid =Str::random(6);

        $validator = \Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            \DB::beginTransaction();
            $branch = $this -> branch;
            $branch->name = $request->input('name');
            $branch->address = $request->input('address');
            $branch->email = $request->input('email');
            $branch->phone = $request->input('phone');
            $branch->code = $uuid;
            $branch->status = $request->input('status');
            $branch->save();
            \DB::commit();
            $this->helper->one_time_message('success', __('Branch Created Successfully'));

            return redirect('admin/branch');

        } catch (\Exception $e) {
            \DB::rollBack();
            dd($e->getMessage());
            $this->helper->one_time_message('error', $e->getMessage());

            return redirect()->back();
        }



    }

    // delete function
    public function destroy($id)
    {
        $branch = Branch::find($id);
        
        if (!$branch) {
            return back()->with('error', 'Branch not found');
        }

        try {
            $branch->delete();
            return redirect('admin/branch')->with('success', 'Branch deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function update(Request $request, $id)
{
    $data['menu'] = 'branch';
    $data['sub_menu'] = 'branch_list';

    // Validation rules
    $rules = [
        'name' => 'required',
        'address' => 'required',
        'email' => 'required|unique:branchs,email,' . $id,
        'phone' => 'required',
        'status' => 'required',
    ];

    // Custom field names for error messages
    $fieldNames = [
        'name' => 'Name',
        'address' => 'Address',
        'email' => 'Email',
        'phone' => 'Phone',
        'code' => 'Code',
        'status' => 'Status',
    ];

    // Generate a random code
    $uuid = Str::random(6);

    // Create validator instance
    $validator = \Validator::make($request->all(), $rules);
    $validator->setAttributeNames($fieldNames);

    // Check if validation fails
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    try {
        \DB::beginTransaction();

        // Find the branch by ID
        $branch = Branch::find($id);

        // Update branch details
        $branch->name = $request->input('name');
        $branch->address = $request->input('address');
        $branch->email = $request->input('email');
        $branch->phone = $request->input('phone');
        $branch->code = $uuid;
        $branch->status = $request->input('status');
        $branch->save();

        \DB::commit();
        $this->helper->one_time_message('success', __('Branch Updated Successfully'));

        return redirect('admin/branch');

    } catch (\Exception $e) {
        \DB::rollBack();
        dd($e->getMessage());
        $this->helper->one_time_message('error', $e->getMessage());

        return redirect()->back();
    }
}


}
