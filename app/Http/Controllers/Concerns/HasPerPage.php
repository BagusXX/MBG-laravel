<?php

namespace App\Http\Controllers\Concerns;
use Illuminate\Http\Request;

trait HasPerPage
{
    protected function resolvePerPage(Request $request): int
    {
        $allowed = [10, 25, 50, 100];
        $perPage = (int) $request->input('per_page', 10);

        return in_array($perPage, $allowed) ? $perPage : 10;
    }
}
