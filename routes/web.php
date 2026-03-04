<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Students
    Route::livewire('students', 'pages::students.index')->name('students.index');
    Route::livewire('students/create', 'pages::students.create')->name('students.create');
    Route::livewire('students/{student}/edit', 'pages::students.edit')->name('students.edit');
});

require __DIR__.'/settings.php';
