<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCustomGameRequest;
use App\Models\CustomGame;
use Illuminate\Http\RedirectResponse;

class CustomGameController extends Controller
{
    public function update(UpdateCustomGameRequest $request, CustomGame $customGame): RedirectResponse
    {
        $customGame->update($request->validated());

        return back();
    }
}