<?php

namespace App\Http\Presenters;

use App\Models\ProductRequest;

/** A customer's request as shown in their lists. */
class RequestPresenter
{
    /** @return array<string, mixed> */
    public static function listItem(ProductRequest $request): array
    {
        return [
            'id' => $request->id,
            'reference' => $request->reference,
            'description' => $request->description,
            'status' => $request->status->value,
            'status_label' => $request->status->getLabel(),
            'category' => $request->category->name,
            'condition' => "{$request->condition->grade} – {$request->condition->name}",
            'created_at' => $request->created_at?->toIso8601String(),
        ];
    }
}
