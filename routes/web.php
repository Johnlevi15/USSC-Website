<?php

use App\Http\Controllers\Admin\DocumentTypeController as AdminDocumentTypeController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\DocumentRequestController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LostFoundItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventController::class, 'calendar'])->name('home');

Route::get('/lost-found', [LostFoundItemController::class, 'browse'])->name('lost-found');
Route::get('/report-item', [LostFoundItemController::class, 'create'])->name('report-item');
Route::post('/report-item', [LostFoundItemController::class, 'store'])->name('report-item.store');
Route::get('/document-request', [DocumentRequestController::class, 'create'])->name('document-request');
Route::get('/document-types/{documentType}/fields', [DocumentRequestController::class, 'getFields'])->name('document-types.fields');
Route::post('/document-request', [DocumentRequestController::class, 'store'])->name('document-request.store');
Route::view('/track-request', 'track-request')->name('track-request');
Route::get('/admin-login', [AdminAuthController::class, 'create'])->name('admin-login');
Route::post('/admin-login', [AdminAuthController::class, 'store'])->name('admin-login.store');
Route::post('/admin-logout', [AdminAuthController::class, 'destroy'])->middleware('auth:admin')->name('admin-logout');

Route::resource('document-requests', DocumentRequestController::class)
    ->parameters(['document-requests' => 'documentRequest'])
    ->only(['index', 'store', 'show']);
Route::resource('lost-found-items', LostFoundItemController::class)
    ->parameters(['lost-found-items' => 'lostFoundItem'])
    ->only(['index', 'store', 'show']);
Route::resource('events', EventController::class)->only(['index', 'show']);

Route::middleware(['auth:admin', 'admin'])->group(function (): void {
    Route::get('/admin', [AdminDashboardController::class, 'dashboard'])->name('admin.dashboard');

    // Document Types Management
    Route::prefix('admin/document-types')->name('admin.document-types.')->group(function (): void {
        Route::get('/', [AdminDocumentTypeController::class, 'index'])->name('index');
        Route::get('/create', [AdminDocumentTypeController::class, 'create'])->name('create');
        Route::post('/', [AdminDocumentTypeController::class, 'store'])->name('store');
        Route::get('/{documentType}/edit', [AdminDocumentTypeController::class, 'edit'])->name('edit');
        Route::put('/{documentType}', [AdminDocumentTypeController::class, 'update'])->name('update');
        Route::delete('/{documentType}', [AdminDocumentTypeController::class, 'destroy'])->name('destroy');
        Route::post('/{documentType}/fields', [AdminDocumentTypeController::class, 'addField'])->name('fields.add');
        Route::put('/fields/{field}', [AdminDocumentTypeController::class, 'updateField'])->name('fields.update');
        Route::delete('/fields/{field}', [AdminDocumentTypeController::class, 'deleteField'])->name('fields.delete');
        Route::post('/{documentType}/fields/reorder', [AdminDocumentTypeController::class, 'reorderFields'])->name('fields.reorder');
    });
    Route::get('/admin/document-requests', [AdminDashboardController::class, 'documents'])->name('admin.documents');
    Route::patch('/admin/document-requests/{documentRequest}', [AdminDashboardController::class, 'updateDocument'])->name('admin.documents.update');
    Route::get('/admin/lost-found', [AdminDashboardController::class, 'lostFound'])->name('admin.lost-found');
    Route::patch('/admin/lost-found/{lostFoundItem}', [AdminDashboardController::class, 'updateLostFound'])->name('admin.lost-found.update');
    Route::get('/admin/events', [AdminDashboardController::class, 'events'])->name('admin.events');
    Route::post('/admin/events', [AdminDashboardController::class, 'storeEvent'])->name('admin.events.store');
    Route::get('/admin/logs', [AdminDashboardController::class, 'logs'])->name('admin.logs');

    Route::resource('document-requests', DocumentRequestController::class)
        ->parameters(['document-requests' => 'documentRequest'])
        ->only(['update', 'destroy']);
    Route::resource('lost-found-items', LostFoundItemController::class)
        ->parameters(['lost-found-items' => 'lostFoundItem'])
        ->only(['update', 'destroy']);
    Route::resource('events', EventController::class)->only(['store', 'update', 'destroy']);
});
