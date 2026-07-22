<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCustomGameRequest;
use App\Models\CustomGame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class CustomGameController extends Controller
{
    public function update(UpdateCustomGameRequest $request, CustomGame $customGame): RedirectResponse
    {
        $customGame->update($request->validated());

        Cache::forget(
            "user:{$request->user()->id}:game-details:custom-{$customGame->id}"
        );

        return back();
    }
}
