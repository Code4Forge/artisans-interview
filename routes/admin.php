<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:100,1'])
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
            ->middleware('permission:roles.manage');

        Route::resource('roles', RolePermissionController::class)
            ->middleware('permission:roles.manage');

        Route::resource('teachers', TeacherController::class)
            ->middlewareFor(['index', 'show'], 'permission:teachers.view')
            ->middlewareFor(['create', 'store'], 'permission:teachers.create')
            ->middlewareFor(['edit', 'update'], 'permission:teachers.update')
            ->middlewareFor('destroy', 'permission:teachers.delete');

        Route::resource('students', StudentController::class)
            ->middlewareFor(['index', 'show'], 'permission:students.view')
            ->middlewareFor(['create', 'store'], 'permission:students.create')
            ->middlewareFor(['edit', 'update'], 'permission:students.update')
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
            ->middlewareFor(['create', 'store'], 'permission:announcements.create')
            ->middlewareFor('destroy', 'permission:announcements.delete');
    });
