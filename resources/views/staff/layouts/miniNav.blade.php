@php
// use UserPermission helper functions
use App\Http\Helpers\UserPermission;
@endphp

@if(UserPermission::has_permission(auth()->guard('staff')->user()->id, 'managers'))
<div class="card bg-slate-500 text-white">
    <div class="card-body d-flex justify-content-evenly">
        <a href="{{ route('showTellerInfo') }}" class="btn btn-info btn-sm p-2 m-2 rounded">Single</a>
        <a href="{{ route('bulkDeposit') }}" class="btn btn-primary btn-sm p-2 m-2 rounded">Deposit
            All</a>
        <a href="{{ route('managerControlPanel') }}" class="btn btn-danger btn-sm p-2 m-2 rounded">Close All</a>
    </div>
</div>
@endif
@if(UserPermission::has_permission(auth()->guard('staff')->user()->id, 'Treasurers'))
<div class="card bg-slate-500 text-white">
    <div class="card-body d-flex justify-content-evenly">
        <a href="{{ route('staff.treasurer.show_managers') }}" class="btn btn-info btn-sm p-2 m-2 rounded">Single Managers</a>
        <a href="{{ route('bulkDeposit') }}" class="btn btn-primary btn-sm p-2 m-2 rounded">Deposit 
            All Managers</a>
        <a href="{{ route('managerControlPanel') }}" class="btn btn-danger btn-sm p-2 m-2 rounded">Close All Managers</a>
    </div>
</div>
@endif
