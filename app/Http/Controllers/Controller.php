<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Resolve how many rows the current data table page should render.
     *
     * The value comes from the "per_page" query parameter and is validated
     * against the central list of allowed options so every module behaves
     * exactly the same way.
     */
    protected function perPage(Request $request): int
    {
        $default = (int) config('pagination.default', 50);
        $allowed = array_map('intval', (array) config('pagination.options', [25, 50, 100, 250]));
        $requested = (int) $request->input('per_page', $default);

        return in_array($requested, $allowed, true) ? $requested : $default;
    }
}
