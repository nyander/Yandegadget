<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Enums\RequestStatus;
use App\Http\Presenters\RequestPresenter;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** The account home: shortcuts for what this person can do, and a customer's latest requests. */
class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $isCustomer = $user->can(Permission::RequestProducts->value) && ! $user->can(Permission::ManageRequests->value);

        $requests = $isCustomer
            ? ProductRequest::where('customer_id', $user->id)->with(['category', 'condition'])->latest()->get()
            : collect();

        return Inertia::render('dashboard', [
            'recentRequests' => $requests->take(3)->map(RequestPresenter::listItem(...))->values()->all(),
            'activeRequests' => $requests->filter(fn (ProductRequest $r) => $r->status->isActive() || $r->status === RequestStatus::AwaitingDeposit)->count(),
            'readyRequests' => $requests->where('status', RequestStatus::ReadyForCollection)->count(),
        ]);
    }
}
