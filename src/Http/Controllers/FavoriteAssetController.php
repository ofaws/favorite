<?php

namespace Ofaws\Favorite\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Ofaws\Favorite\Http\Requests\StoreFavoriteRequest;
use Ofaws\Favorite\Http\Requests\UpdateFavoriteRequest;
use Ofaws\Favorite\Http\Resources\FavoriteResource;
use Ofaws\Favorite\Models\Favorite;

class FavoriteAssetController
{
    public function index(): AnonymousResourceCollection
    {
        return FavoriteResource::collection(auth()->user()
            ->favorites()
            ->filtered()
            ->withConstrainedAsset()
            ->orderBy('position')
            ->orderByDesc('id')
            ->cursorPaginate(request()->integer('per_page', 15))
        );
    }

    public function store(StoreFavoriteRequest $request)
    {
        $favorite = auth()->user()->favorites()->create($request->validated());

        return new FavoriteResource($favorite->loadMissing('asset'));
    }

    public function show(Favorite $favorite)
    {
        return new FavoriteResource($favorite->loadMissing('asset'));
    }

    public function update(UpdateFavoriteRequest $request, Favorite $favorite)
    {
        throw_unless($favorite->user_id == auth()->id(), new AuthorizationException('You are not authorized to perform this action.', 403));

        $request->whenFilled('position', fn ($input) => $favorite->update(['position' => $input]));

        return new FavoriteResource($favorite->refresh());
    }

    public function destroy(Favorite $favorite): JsonResponse
    {
        throw_unless($favorite->user_id == auth()->id(), new AuthorizationException('You are not authorized to perform this action.', 403));

        return response()->json($favorite->delete());
    }
}
