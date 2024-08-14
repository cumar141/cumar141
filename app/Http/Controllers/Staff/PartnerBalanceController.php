<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PartnerBalance;
use App\DataTables\Admin\PartnerbalanceDataTable;
class PartnerBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(PartnerbalanceDataTable $dataTable)
    {

        return $dataTable->render('staff.partner.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('staff.partner.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'partner' => 'required|max:100',
            'type' => 'required|max:100',
            'balance' => 'required|numeric|min:0'
        ]);
    
        // Create a new partner balance record
        $partnerBalance = new PartnerBalance();
        $partnerBalance->partner = $validatedData['partner'];
        $partnerBalance->type = $validatedData['type'];
        $partnerBalance->balance = $validatedData['balance'];
        $partnerBalance->save();
    
        // Optionally, you can return a response or redirect the user
        return redirect()->route('staff.partner-balance.index')->with('success', 'Partner balance created successfully.');
    }
    



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Retrieve the partner balance record by its ID
        $partnerBalance = PartnerBalance::find($id);
    
        // Check if the partner balance record exists
        if ($partnerBalance) {
            // If the record exists, return the edit form view with the record data
            return view('staff.partner.edit', compact('partnerBalance'));
        } else {
            // If the record does not exist, return a not found response or redirect the user
            return redirect()->route('staff.partner-balance.index')->with('error', 'Partner balance not found.');
        }
    }
    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Retrieve the partner balance record by its ID
        $partnerBalance = PartnerBalance::find($id);
    
        // Check if the partner balance record exists
        if ($partnerBalance) {
            // Validate the incoming request data
            $request->validate([
                'partner' => 'required',
                'type' => 'required',
                'balance' => 'required|numeric',
            ]);
    
            // Update the partner balance record with the new data
            $partnerBalance->partner = $request->input('partner');
            $partnerBalance->type = $request->input('type');
            $partnerBalance->balance = $request->input('balance');
            
            // Save the updated partner balance record
            $partnerBalance->save();
    
            // Redirect the user to the partner balance index page with a success message
            return redirect()->route('staff.partner-balance.index')->with('success', 'Partner balance updated successfully.');
        } else {
            // If the partner balance record does not exist, return a not found response or redirect the user
            return redirect()->route('staff.partner-balance.index')->with('error', 'Partner balance not found.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Find the partner balance record by its ID
        $partnerBalance = PartnerBalance::find($id);
    
        // Check if the partner balance record exists
        if ($partnerBalance) {
            // If the record exists, delete it from the database
            $partnerBalance->delete();
            
            // Optionally, return a success response or redirect the user
            return redirect()->route('staff.partner-balance.index')->with('success', 'Partner balance deleted successfully.');
        } else {
            // If the record does not exist, return a not found response or redirect the user
            return redirect()->route('staff.partner-balance.index')->with('error', 'Partner balance not found.');
        }
    }
    
}
