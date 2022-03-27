<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\URL;
use App\Models\Track;
use App\Models\Artist;
use App\Models\Album;
use App\Models\Genre;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/mail', function () {
    Mail::raw('What is your favorite framework?', function ($message) {
        $message->to('dtang@usc.edu')->subject('Hello David');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::middleware(['prevent-blocked-users'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');

        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoice.index');
        Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoice.show');

        Route::view('/blocked', 'blocked')->name('blocked');
    });
});

Route::get('/register', [RegistrationController::class, 'index'])->name('registration.index');
Route::post('/register', [RegistrationController::class, 'register'])->name('registration.create');
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

Route::get('/albums', [AlbumController::class, 'index'])->name('album.index');
Route::get('/albums/new', [AlbumController::class, 'create'])->name('album.create');
Route::post('/albums', [AlbumController::class, 'store'])->name('album.store');
Route::get('/albums/{id}/edit', [AlbumController::class, 'edit'])->name('album.edit');
Route::post('/albums/{id}', [AlbumController::class, 'update'])->name('album.update');

Route::get('/itunes', function (Request $request) {
    $term = $request->query('term');
    $cacheKey = "itunes-api-$term";

    $response = Cache::remember($cacheKey, 60, function () use ($term) {
        return Http::get("https://itunes.apple.com/search?term=$term")->object();
    });

    return view('api.itunes', [
        'response' => $response,
    ]);
});

Route::get('/reddit/{subreddit}', function ($subreddit) {
    $response = Cache::remember("reddit-$subreddit", 60, function () use ($subreddit) {
        return Http::get("https://www.reddit.com/r/$subreddit.json")->object();
    });

    return view('api.reddit', [
        'response' => $response,
    ]);
});

Route::get('/yelp', function () {
    // We can set headers for our request using withHeaders
    // https://laravel.com/docs/9.x/http-client#headers
    return Http::withHeaders([
        'Authorization' => "Bearer " . env('YELP_API_KEY')
    ])
        ->get("https://api.yelp.com/v3/businesses/search?term=vegan&location=Los Angeles")
        ->json();

    // OR you can use withToken to set the Authorization header
    // https://laravel.com/docs/9.x/http-client#bearer-tokens

    return Http::withToken(env('YELP_API_KEY'))
        ->get("https://api.yelp.com/v3/businesses/search?term=vegan&location=Los Angeles")
        ->json();
});

Route::get('/eloquent', function() {
    // QUERYING many records from a table
    // return Artist::all();
    // return Track::all();
    // return Artist::orderBy('name', 'desc')->get();
    // return Track::where('unit_price', '>', 0.99)->orderBy('name')->get();

    // QUERYING a record by the id column
    // return Artist::find(3);

    // CREATING
    // $genre = new Genre();
    // $genre->name = 'Hip Hop';
    // $genre->save();
    // return Genre::all();

    // DELETING
    // Genre::where('name', '=', 'Hip Hop')->delete();
    // return Genre::all();

    // UPDATING
    // $genre = Genre::where('name', '=', 'Alternative & Punk')->first();
    // $genre->name = 'Alternative and Punk';
    // $genre->save();
    // return Genre::all();

    // RELATIONSHIPS (ONE TO MANY)
    // return Artist::find(50); // 50 = Metallica
    // return Artist::find(50)->albums;
    // return Album::find(152)->artist; // 152 = Master of Puppets

    // return Track::find(1837); // 1837 = Seek and Destroy
    // return Track::find(1837)->genre;
    // return Genre::find(3)->tracks; // 3 = Metal

    // EAGER LOADING
    // return view('eloquent', [
    //     // 'tracks' => Track::where('unit_price', '>', 0.99)
    //     //     ->orderBy('name')
    //     //     ->limit(5)
    //     //     ->get()
    //     'tracks' => Track::with(['genre', 'album'])
    //         ->where('unit_price', '>', 0.99)
    //         ->orderBy('name')
    //         ->get()
    // ]);
});

if (env('APP_ENV') !== 'local') {
    URL::forceScheme('https');
}