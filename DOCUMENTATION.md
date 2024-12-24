# Favorite Asset Package Documentation

This package provides functionalities to manage user-favorite assets in a Laravel application. It includes a controller, models, and traits to simplify the integration of favorite features into your application.

---

## Installation
1. Add the package to your project via Composer:
   ```bash
   composer require ofaws/favorite
   ```

2. Then run the installation command:
    ```bash
    php artisan favorite:install
    ```

3. Update the settings in the `favorite.php` config file and add traits to your models

---

## Setup

### Add Traits
- **User Model**: Add the `HasFavorites` trait to enable user-specific favorite relationships:
  ```php
  use Ofaws\Favorite\Traits\HasFavorites;

  class User extends Authenticatable
  {
      use HasFavorites;
  }
  ```

- **Asset Models**: Add the `CanBeFavorite` trait to any model that can be favorited:
  ```php
  use Ofaws\Favorite\Traits\CanBeFavorite;

  class Post extends Model
  {
      use CanBeFavorite;
  }
  ```

### Update Configuration
- Register asset models in the `morph_map` and `assets` keys of the `favorite` config file:
  ```php
  'morph_map' => [
      'Category' => 'categories',
      '\App\Models\Book' => 'books',
  ],

  'assets' => [
      \App\Models\Book => ['id', 'title', 'position']
  ],
  ```

---

## Database Requirements
- Ensure that each model using the `CanBeFavorite` trait has a `type` column in its corresponding table to support the `filtered` scope.

---

## Basic Example
This would be the simplest way to add a favorite in a Laravel application:

```php
use App\Models\User;
use App\Models\Post;

// Retrieve the user and the post
$user = User::find(1);
$post = Post::find(42);

// Add the post to the user's favorites
$post->favorited()->create(['user_id' => $user->id, 'position' => 15]);

or
 $user->favorites()->create(['asset_type' => Post::class, 'asset_id' => $post->id]);
```

---

## Learn by Example
A good way to get started is to utilize the provided traits and explore the relationships:

### User Favorites
```php
$user = User::find(1);
$favorites = $user->favorites; // Retrieves all user favorites
```

### Adding Favorites
```php
$post = Post::find(42);
$post->favorited()->create(['user_id' => $user->id, 'position' => 1]);
```

### Filtering Favorites
```php
$posts = Post::query()->filterByFavorite()->get();
```

### Retrieving Favorites

```php
$posts = Post::find(1)->favorited;
```

### Getting Authenticated User's Favorite

```php
$favorite = Post::find(1)->myFavorite;
```

### Adding Favorite Status to Model's Query

```php
$posts = Post::query()->addIsFavorite()->get(); // 'is_favorite' => true
```

## Available Endpoints
The package provides the following endpoints:

### 1. List Favorites
**GET** `/favorite`
- Retrieves a paginated list of the authenticated user's favorites.
- Supports filtering and sorting using query parameters.

### 2. Add a Favorite
**POST** `/favorite`
- Adds a new favorite for the authenticated user.
- Requires data from the `StoreFavoriteRequest`.

To add a favorite, pass the following fields in the request:

1. `user_id` (optional): Must be an integer and represent an existing user ID in the database. If omitted, the authenticated user's ID will be used automatically.
2. `asset_id` (optional): An integer representing the ID of the asset you wish to favorite. Should match the asset type specified.
3. `asset_type` (optional): A string representing the type of the asset (e.g., "video", "image"). It must match one of the allowed asset types defined in `Favorite::allowedAssetsRule()`.
4. `position` (optional): A numeric value representing the position of the favorite. It must be a positive number (greater than or equal to 0).

### 3. View a Favorite
**GET** `/favorite/{id}`
- Fetches details of a specific favorite.

### 4. Update a Favorite
**PATCH** `/favorite/{id}`
- Updates the position of a favorite passed in the request body.

### 5. Delete a Favorite
**DELETE** `/favorite/{id}`
- Deletes a favorite record.

Check the available routes using:
```bash
php artisan route:list
```

---

## Using the Traits

### `HasFavorites` Trait
Provides the `favorites()` method to establish a `hasMany` relationship for user favorites.

### `CanBeFavorite` Trait
Provides the following methods:
- **Relationships**
    - `favorited()`: `MorphMany` relationship for all users who favorited the asset.
    - `myFavorite()`: `MorphOne` relationship for the authenticated user's favorite of the asset.

- **Query Scopes**
    - `addIsFavorite()`: Adds an `is_favorite` attribute to the query results.
    - `filterByFavorite()`: Filters query results based on the `favorited` parameter (true or false).

---

## Favorite Model
- **`asset` Relation** 
  Returns the model morphed to `Favorite` due to usage of `CanBeFavorite` trait

- **`filtered` Scope**
  Applies filters based on asset types, specific assets, or custom criteria.
  Available filters:
    -  `asset` (string) - filters by `asset_type`
    -  `assets` (array of strings) - filters by array of `asset_type`s
    -  `asset_type` (string) - filters by the value in the `type` column of attached model
    -  `asset_types` (array of strings) - filters by the values in the `type` column of attached model

- **`withConstrainedAsset` Scope**
  Ensures that asset relationships are constrained by the `assets` parameter in `favorite.php` config.

- **`byAuth` and `by` Scopes**
  Filter favorites based on the authenticated user or a specific user ID.

---

## Additional Features
- Use the `filterByFavorite()` method on models with the `CanBeFavorite` trait to filter based on user favorites.
- Extend functionality by customizing the package-provided scopes and methods.

---

## Notes
- The `destroy` and `update` methods in the controller validate user authorization to ensure secure operations.
- Use the package's traits if you only need the relationships without the controller endpoints.
- This package is designed to be flexible and extensible for various use cases in managing user favorites.

---

For more information, refer to the package source code or contact the package maintainer.

