<?php

use Illuminate\Support\Facades\Route;
use Workdo\Ekyc\Http\Controllers\EkycController;
use Workdo\Ekyc\Http\Controllers\EkycLeadController;

// Public KYC Form Routes (no auth required)
Route::prefix('ekyc')->name('ekyc.')->group(function () {
    // Maintenance check middleware applied
    Route::middleware([\Workdo\Ekyc\Http\Middleware\CheckEkycMaintenance::class])->group(function () {
        Route::get('/start', [\Workdo\Ekyc\Http\Controllers\EkycFormController::class, 'start'])->name('form.start');
        Route::post('/verify-contact', [\Workdo\Ekyc\Http\Controllers\EkycFormController::class, 'verifyContact'])->name('form.verify-contact');
        Route::get('/otp-verify', [\Workdo\Ekyc\Http\Controllers\EkycFormController::class, 'showOtpVerify'])->name('form.otp-verify');
        Route::post('/verify-otp', [\Workdo\Ekyc\Http\Controllers\EkycOtpController::class, 'verify'])->name('otp.verify');
        Route::post('/resend-otp', [\Workdo\Ekyc\Http\Controllers\EkycOtpController::class, 'resend'])->name('otp.resend');
        
        Route::get('/step/{step}', [\Workdo\Ekyc\Http\Controllers\EkycFormController::class, 'showStep'])->name('form.step');
        Route::post('/step/{step}', [\Workdo\Ekyc\Http\Controllers\EkycFormController::class, 'submitStep'])->name('form.submit-step');
        Route::post('/confirm-aadhaar', [\Workdo\Ekyc\Http\Controllers\EkycFormController::class, 'confirmAadhaar'])->name('form.confirm-aadhaar');
        
        Route::get('/complete', [\Workdo\Ekyc\Http\Controllers\EkycFormController::class, 'complete'])->name('form.complete');
    });
    
    // Maintenance page (always accessible)
    Route::get('/maintenance', [\Workdo\Ekyc\Http\Controllers\EkycMaintenanceController::class, 'show'])->name('maintenance');
});

Route::group(['middleware' => ['web', 'auth']], function () {
    Route::get('dashboard/ekyc', [EkycController::class, 'index'])->name('ekyc.dashboard');
    Route::get('ekyc', [EkycLeadController::class, 'index'])->name('ekyc.index');
    Route::resource('ekyc-leads', EkycLeadController::class);
    
    Route::get('ekyc/reports', [\Workdo\Ekyc\Http\Controllers\SuperAdmin\ReportController::class, 'index'])->name('ekyc.reports');
    Route::post('ekyc-settings-save', [EkycController::class, 'saveSettings'])->name('ekyc.settings.save');
    Route::get('ekyc/settings', [EkycController::class, 'setting'])->name('ekyc.settings');
    
    // Client Side KYC Routes
    Route::get('client/kyc/{id}', [EkycController::class, 'clientKycJourney'])->name('client.kyc.journey');
    Route::post('client/kyc/verify-pan', [EkycController::class, 'verifyPan'])->name('client.kyc.verify.pan');
    Route::post('client/kyc/verify-aadhaar', [EkycController::class, 'verifyAadhaar'])->name('client.kyc.verify.aadhaar');
    Route::post('client/kyc/selfie-match', [EkycController::class, 'selfieMatch'])->name('client.kyc.selfie.match');
    Route::post('client/kyc/bank-verify', [EkycController::class, 'bankVerify'])->name('client.kyc.bank.verify');
    Route::post('client/kyc/video-kyc', [EkycController::class, 'videoKyc'])->name('client.kyc.video.kyc');
    Route::resource('ekyc-pipelines', \Workdo\Ekyc\Http\Controllers\EkycPipelineController::class)->names('ekyc.pipelines');
    Route::resource('ekyc-stages', \Workdo\Ekyc\Http\Controllers\EkycStageController::class)->names('ekyc.stages');
    Route::resource('ekyc-custom-fields', \Workdo\Ekyc\Http\Controllers\EkycCustomFieldController::class)->names('ekyc.custom-fields');
    
    // Submission Management Routes
    Route::prefix('admin/ekyc')->name('ekyc.admin.')->group(function () {
        Route::get('/submissions', [\Workdo\Ekyc\Http\Controllers\EkycSubmissionController::class, 'index'])->name('submissions.index');
        Route::get('/submissions/{id}', [\Workdo\Ekyc\Http\Controllers\EkycSubmissionController::class, 'show'])->name('submissions.show');
        Route::match(['get', 'post'], '/submissions/{id}/approve', [\Workdo\Ekyc\Http\Controllers\EkycSubmissionController::class, 'approve'])->name('submissions.approve');
        Route::match(['get', 'post'], '/submissions/{id}/reject', [\Workdo\Ekyc\Http\Controllers\EkycSubmissionController::class, 'reject'])->name('submissions.reject');
        Route::get('/submissions/{id}/image/{field}', [\Workdo\Ekyc\Http\Controllers\EkycSubmissionController::class, 'getImage'])->name('submission.image');
        Route::delete('/submissions/{id}', [\Workdo\Ekyc\Http\Controllers\EkycSubmissionController::class, 'destroy'])->name('submissions.destroy');
        Route::delete('/submissions/{id}/force', [\Workdo\Ekyc\Http\Controllers\EkycSubmissionController::class, 'forceDestroy'])->name('submissions.force-destroy');
        
        // UI Builder Routes
        Route::get('/ui-builder', [\Workdo\Ekyc\Http\Controllers\EkycUiBuilderController::class, 'index'])->name('ui-builder');
        Route::post('/ui-builder/save', [\Workdo\Ekyc\Http\Controllers\EkycUiBuilderController::class, 'save'])->name('ui-builder.save');
        Route::get('/ui-builder/preview/{template}', [\Workdo\Ekyc\Http\Controllers\EkycUiBuilderController::class, 'preview'])->name('ui-builder.preview');
        Route::post('/ui-builder/activate/{template}', [\Workdo\Ekyc\Http\Controllers\EkycUiBuilderController::class, 'activate'])->name('ui-builder.activate');
    });
});

// Webhook
Route::post('ekyc/webhook', [EkycController::class, 'webhook'])->name('ekyc.webhook');
