<style>
    .btn-primary:hover {
        background-color: #fff;
        color: #007bff;
    }

    .btn-primary.active:hover {
        background-color: #fff;
        color: #007bff;
    }
</style>


<div class="container py-3">
    <div class="d-flex justify-content-around align-items-center p-3 rounded">
        <div>
            <a href="{{ route('staff.user.edit', $users->id) }}"
                class="btn btn-primary {{ request()->is('staff/edit/*') ? 'active' : '' }}">
                <i class="fas fa-user"></i> Profile
            </a>

            <a href="{{ route('staff.user.wallets', $users->id) }}"
                class="btn btn-primary {{ request()->is('staff/wallets/*') ? 'active' : '' }}">
                <i class="fas fa-money-bill"></i> Wallet
            </a>

            <a href="{{ route('staff.user.transactions', $users->id) }}"
                class="btn btn-primary {{ request()->is('staff/transactions/*') ? 'active' : '' }}">
                <i class="fas fa-info-circle"></i> Transactions
            </a>

        </div>

    </div>

</div>