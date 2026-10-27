<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('dashboard', DashboardController::class)
            ->only(['index'])
            ->middleware('permission:dashboard.view');

        Route::get('roles/{role}/permissions', [RolePermissionController::class, 'permissions'])
            ->name('roles.permissions')
            ->middleware('permission:roles.manage');

        Route::put('roles/{role}/permissions', [RolePermissionController::class, 'updatePermissions'])
            ->name('roles.permissions.update')
            ->middleware(['permission:roles.manage', 'throttle:10,1']);

        Route::resource('roles', RolePermissionController::class)
            ->middleware('permission:roles.manage')
            ->middlewareFor(['store', 'update'], 'throttle:10,1');

        Route::resource('teachers', TeacherController::class)
            ->middlewareFor(['index', 'show'], 'permission:teachers.view')
            ->middlewareFor('create', 'permission:teachers.create')
            ->middlewareFor('store', ['permission:teachers.create', 'throttle:10,1'])
            ->middlewareFor('edit', 'permission:teachers.update')
            ->middlewareFor('update', ['permission:teachers.update', 'throttle:10,1'])
            ->middlewareFor('destroy', 'permission:teachers.delete');

        Route::resource('students', StudentController::class)
            ->middlewareFor(['index', 'show'], 'permission:students.view')
            ->middlewareFor('create', 'permission:students.create')
            ->middlewareFor('store', ['permission:students.create', 'throttle:10,1'])
            ->middlewareFor('edit', 'permission:students.update')
            ->middlewareFor('update', ['permission:students.update', 'throttle:10,1'])
            ->middlewareFor('destroy', 'permission:students.delete');

        Route::get('announcements/recipients-search', [AnnouncementController::class, 'recipientsSearch'])
            ->name('announcements.recipients.search')
            ->middleware('permission:announcements.view');

        Route::get('announcements/{announcement}/download', [AnnouncementController::class, 'download'])
            ->name('announcements.download')
            ->middleware('permission:announcements.view');

        Route::resource('announcements', AnnouncementController::class)
            ->except(['edit', 'update'])
            ->middlewareFor(['index', 'show'], 'permission:announcements.view')
            ->middlewareFor('create', 'permission:announcements.create')
            ->middlewareFor('store', ['permission:announcements.create', 'throttle:10,1'])
            ->middlewareFor('destroy', 'permission:announcements.delete');
    });
