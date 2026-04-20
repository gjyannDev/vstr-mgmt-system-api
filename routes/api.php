<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
  require base_path('app/Features/Auth/Routes/api.php');
  require base_path('app/Features/Location/Routes/api.php');
});
