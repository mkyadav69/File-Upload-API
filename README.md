# PROJECT SETUPS
1. Install laravel 9 
  composer create-project laravel/laravel file_upload_api
2. Permission to storage folder
    suod chmod -R 777 storage/
3. Update configuration file (.env) & do database setups 
    sudo vim .env
4. Clear configuration file
    php artisan config:clear
5. install & setup mysql server
    sudo apt install mysql-server
6. Run the laravel default migration 
    php artisan migrate
7. Run the local serevre 
    php artisan 
    

# API SECURITY SETUP
1. Install laravel passport library
    composer require laravel/
2. Run the defaul passport library migrations
   php artisan migrate
3. Create the encryption keys needed to generate secure access tokens
    php artisan passport:install
4.  deploying Passport to your application's servers for the first time, you will likely need to run the passport:keys command. This command generates the encryption keys Passport needs in order to generate access tokens. The generated keys are not typically kept in source control:
    php artisan passport:keys

# PROJECT IMPLEMENTAIONS
1. Create database
2. Create table migration to store all the uploaded files
    php artisan make:model FileUpload -m
3. Create file upload controller along with the api resource routes
    php artisan make:controller FileUploadController --api
4. Check all the generated routes(api end points)
    php artisan route:list
5. Define all the required api end points in api.php file
6. Protect all the important api inside auth:api middleware
Example : 
# File Upload API
Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('file', FileUploadController::class);
    Route::apiResource('user.file', FileUploadController::class);
    Route::prefix('user')->group(function () {
        Route::get('{user}/file/{file}', [FileUploadController::class, 'show'])->scopeBindings();
        Route::post('{user}/file/{file}', [FileUploadController::class, 'update'])->scopeBindings();
        Route::delete('{user}/file/{file}', [FileUploadController::class, 'destroy'])->scopeBindings();
    });
});

# User Register API
Route::post('register', [UserController::class, 'register']);

# Optional
Route::post('login', [UserController::class, 'login']);
Route::get('fail', [UserController::class, 'unaccess'])->name('login');

7. Implemenent logic inside the FileUpload Controller