<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $supplier = $user->supplier;

        return Inertia::render('supplier/profile', [
            'supplier' => $supplier?->only(['name', 'contact_name', 'email', 'phone', 'address']) ?? [
                'name' => '',
                'contact_name' => $user->name,
                'email' => $user->email,
                'phone' => '',
                'address' => '',
            ],
            'isNew' => $supplier === null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'address' => ['required', 'string', 'max:1000'],
        ]);

        $supplier = $user->supplier ?? new Supplier(['country_code' => 'GB', 'user_id' => $user->id]);
        $supplier->fill($data)->save();
        $user->setRelation('supplier', $supplier);

        return to_route('supplier.offers.index');
    }
}
