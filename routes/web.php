<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CertificateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// =====================
// LOGIN (Session-based for SPA)
// =====================
Route::post('/login', [LoginController::class, 'login']);

// =====================
// LOGOUT
// =====================
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth');

// =====================
// GET AUTH USER
// =====================
Route::get('/user', [LoginController::class, 'user'])->middleware('auth');

// Certificates (admin)
Route::middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->group(function(){
    Route::get('/admin/certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::post('/admin/certificates/generate', [CertificateController::class, 'generate'])->name('certificates.generate');
    Route::get('/admin/certificates/{id}/edit', [CertificateController::class, 'edit'])->name('certificates.edit');
    Route::post('/admin/certificates/{id}', [CertificateController::class, 'update'])->name('certificates.update');
    Route::get('/admin/certificates/{id}/view', [CertificateController::class, 'view'])->name('certificates.view');
    Route::get('/admin/certificates/{id}/download', [CertificateController::class, 'download'])->name('certificates.download');
    Route::get('/admin/certificates/{id}/editor-partial', [CertificateController::class, 'editorPartial'])->name('certificates.partial');
    Route::get('/admin/certificates/page', [CertificateController::class, 'page'])->name('certificates.page');
    // Certificate Templates manager
    Route::get('/admin/certificate-templates', [\App\Http\Controllers\CertificateTemplateController::class, 'index'])->name('certificate_templates.index');
    Route::get('/admin/certificate-templates/create', [\App\Http\Controllers\CertificateTemplateController::class, 'create'])->name('certificate_templates.create');
    Route::post('/admin/certificate-templates', [\App\Http\Controllers\CertificateTemplateController::class, 'store'])->name('certificate_templates.store');
    Route::get('/admin/certificate-templates/{id}/edit', [\App\Http\Controllers\CertificateTemplateController::class, 'edit'])->name('certificate_templates.edit');
    Route::post('/admin/certificate-templates/{id}', [\App\Http\Controllers\CertificateTemplateController::class, 'update'])->name('certificate_templates.update');
    Route::delete('/admin/certificate-templates/{id}', [\App\Http\Controllers\CertificateTemplateController::class, 'destroy'])->name('certificate_templates.destroy');
    Route::get('/admin/certificate-templates/{id}/preview', [\App\Http\Controllers\CertificateTemplateController::class, 'preview'])->name('certificate_templates.preview');
});

// =====================
// CERTIFICATE PAGE
// =====================
Route::get('/certificate', [CertificateController::class, 'index']);
