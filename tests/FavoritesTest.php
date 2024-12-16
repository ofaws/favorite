<?php

use Illuminate\Testing\Fluent\AssertableJson;
use Ofaws\Favorite\Http\Controllers\FavoriteAssetController;
use Ofaws\Favorite\Models\Favorite;
use Ofaws\Favorite\Tests\Models\Book;
use Ofaws\Favorite\Tests\Models\Image;
use Ofaws\Favorite\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

it('can test', function () {
    expect(true)->toBeTrue();
});

test('user can mark an asset as favorite', function () {
    $book = Book::create(['title' => 'Test Book']);

    $favorite = $this->testUser->favorites()->create([
        'asset_id' => $book->id,
        'asset_type' => Book::class,
        'position' => 3,
    ]);

    expect($this->testUser->favorites()->count())->toBe(1)
        ->and($this->testUser->favorites()->find($favorite->id)->asset)->toBeInstanceOf(Book::class);
});

test('user can remove asset from favorites', function () {
    $image = Image::create(['title' => 'Test Image']);

    $favorite = $image->favorited()->create([
        'user_id' => $this->testUser->id,
    ]);

    expect($this->testUser->favorites()->count())->toBe(1)
        ->and($this->testUser->favorites()->find($favorite->id)->asset)->toBeInstanceOf(Image::class);

    $this->testUser->favorites()->where('id', $favorite->id)->delete();

    expect($this->testUser->favorites()->count())->toBe(0);
});

test('user can get a list of his favorite items', function () {
    $otherUser = User::forceCreate([
        'name' => 'Jane Doe',
        'email' => 'jane@user.com',
        'password' => bcrypt('secret'),
    ]);

    $book1 = Book::create(['title' => 'Test Book 1']);
    $book2 = Book::create(['title' => 'Test Book 2']);

    $image1 = Image::create(['title' => 'Test Image 1']);
    $image2 = Image::create(['title' => 'Test Image 2']);

    $book1->favorited()->create(['user_id' => $this->testUser->id]);
    $book2->favorited()->create(['user_id' => $otherUser->id]);

    $image1->favorited()->create(['user_id' => $this->testUser->id]);
    $image2->favorited()->create(['user_id' => $otherUser->id]);

    expect($this->testUser->favorites()->count())->toBe(2)
        ->and($otherUser->favorites()->count())->toBe(2);

    actingAs($this->testUser);

    $books = Book::with('myFavorite')->get();

    expect($books->where('id', $book1->id)->first()->myFavorite)->toBeInstanceOf(Favorite::class)
        ->and($books->where('id', $book2->id)->first()->myFavorite)->toBeNull();

    $booksWithFavoriteFlags = Book::select('*')->addIsFavorite()->get();

    expect($booksWithFavoriteFlags->where('id', $book1->id)->first()->is_favorite)->not->toBeNull()
        ->and($booksWithFavoriteFlags->where('id', $book2->id)->first()->is_favorite)->toBeNull();
});

test('assets constrains can be discovered', function () {
    $constrains = \Ofaws\Favorite\Favorite::constrainedAssets();

    expect($constrains)->toHaveCount(2);
});

test('favorited assets can be listed with constrains set in config', function () {
    $book1 = Book::create(['title' => 'Test Book 1', 'overview' => 'Some overview']);
    $image1 = Image::create(['title' => 'Test Image 1', 'overview' => 'Some overview']);

    $book1->favorited()->create(['user_id' => $this->testUser->id]);
    $image1->favorited()->create(['user_id' => $this->testUser->id]);

    $contrained = $this->testUser->favorites()->withConstrainedAsset()->get();

    expect($contrained->first()->asset->getAttributes())->toHaveCount(2)
        ->and($contrained[1]->asset->getAttributes())->toHaveCount(2);

    $regular = $this->testUser->favorites()->with('asset')->get();

    expect($regular->first()->asset->getAttributes())->toHaveCount(5)
        ->and(array_keys($regular->first()->asset->getAttributes()))
        ->toEqual(['id', 'title', 'overview', 'created_at', 'updated_at'])
        ->and($regular[1]->asset->getAttributes())->toHaveCount(5)
        ->and(array_keys($regular[1]->asset->getAttributes()))
        ->toEqual(['id', 'title', 'overview', 'created_at', 'updated_at']);
});

describe('favorite assets controller tests', function () {
    test('only logged in user can access favorites controller', function () {
        getJson(action([FavoriteAssetController::class, 'index']))->assertUnauthorized();
        postJson(action([FavoriteAssetController::class, 'store']))->assertUnauthorized();

        $book = Book::create(['title' => 'Test Book']);
        $favorite = $book->favorited()->create(['user_id' => $this->testUser->id]);

        getJson(action([FavoriteAssetController::class, 'show'], $favorite))->assertUnauthorized();
        putJson(action([FavoriteAssetController::class, 'update'], $favorite))->assertUnauthorized();
        deleteJson(action([FavoriteAssetController::class, 'destroy'], $favorite))->assertUnauthorized();
    });

    test('user can see a list of his favorite assets', function () {
        $otherUser = User::forceCreate([
            'name' => 'Jane Doe',
            'email' => 'jane@user.com',
            'password' => bcrypt('secret'),
        ]);

        $book1 = Book::create(['title' => 'Test Book 1']);
        $book2 = Book::create(['title' => 'Test Book 2']);

        $image1 = Image::create(['title' => 'Test Image 1']);
        $image2 = Image::create(['title' => 'Test Image 2']);

        $book1->favorited()->create(['user_id' => $this->testUser->id, 'position' => 15]);
        $book2->favorited()->create(['user_id' => $otherUser->id]);

        $image1->favorited()->create(['user_id' => $this->testUser->id, 'position' => 7]);
        $image2->favorited()->create(['user_id' => $otherUser->id]);

        actingAs($this->testUser);

        getJson(action([FavoriteAssetController::class, 'index']))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('data', 2)
                ->has('data.0.asset.title')
                ->missing('data.0.asset.overview')
                ->where('data.0.asset.title', 'Test Image 1')
                ->where('data.1.asset.title', 'Test Book 1')
                ->etc()
            );
    });

    test('asset can be marked as favorite', function () {
        $book = Book::create(['title' => 'Test Book', 'overview' => 'Some overview']);

        actingAs($this->testUser);

        postJson(action([FavoriteAssetController::class, 'store']), [
            'asset_id' => $book->id,
            'asset_type' => Book::class,
        ])->assertCreated()
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('data.asset.title', 'Test Book')
                ->where('data.asset.overview', 'Some overview')
                ->where('data.user_id', $this->testUser->id)
                ->etc()
            );
    });

    test('unsupported asset can not be marked as favorite', function () {
        actingAs($this->testUser);

        postJson(action([FavoriteAssetController::class, 'store']), [
            'asset_id' => 1,
            'asset_type' => 'NonExistentModel',
        ])->assertUnprocessable();
    });

    test('user can see, update or delete the favorite record he authors', function () {
        $book = Book::create(['title' => 'Test Book', 'overview' => 'Some overview']);
        $favorite = $book->favorited()->create(['user_id' => $this->testUser->id]);

        actingAs($this->testUser);

        getJson(action([FavoriteAssetController::class, 'show'], $favorite))->assertOk();
        putJson(action([FavoriteAssetController::class, 'update'], $favorite), ['position' => 1])->assertOk();
        deleteJson(action([FavoriteAssetController::class, 'destroy'], $favorite))->assertOk();
    });

    test('user can see but not edit the favorite record he does not author', function () {
        $book = Book::create(['title' => 'Test Book', 'overview' => 'Some overview']);
        $favorite = $book->favorited()->create(['user_id' => $this->testUser->id]);

        $otherUser = User::forceCreate([
            'name' => 'Jane Doe',
            'email' => 'jane@user.com',
            'password' => bcrypt('secret'),
        ]);
        actingAs($otherUser);

        getJson(action([FavoriteAssetController::class, 'show'], $favorite))->assertOk();
        putJson(action([FavoriteAssetController::class, 'update'], $favorite), ['position' => 1])->assertForbidden();
        deleteJson(action([FavoriteAssetController::class, 'destroy'], $favorite))->assertForbidden();
    });
});
