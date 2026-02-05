<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SignUpController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\TermsAndConditionController;
use App\Http\Controllers\Auth\FirebaseAuthController;
use App\Http\Controllers\Zoya\ZoyaReportController;


Route::prefix('v1')->group(function () {

    // ----------------------------
    // Public Routes
    // ----------------------------
    Route::post('register', [SignUpController::class, 'register']);
    Route::post('verify-otp', [SignUpController::class, 'verifyOtp']);

    Route::post('login', [LoginController::class, 'login']);
    Route::post('resend-otp', [LoginController::class, 'resendOtp']);

    Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
    Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword']);

    // Firebase using google login
    Route::post('firebase-login', [FirebaseAuthController::class, 'loginWithFirebase']);

   

    // Public Route: Get Terms & Conditions
    Route::get('terms', [TermsAndConditionController::class, 'get']);
    //Zoya Controller
   

    // ----------------------------
    // Protected Routes (Require Auth)
    // ----------------------------
    Route::middleware('auth:sanctum')->group(function () {

    // routes/api.php
Route::prefix('zoya')->group(function () {
    Route::get('stock', [ZoyaReportController::class, 'getStockReport'])->name('zoya.stock.report');
    Route::get('reports', [ZoyaReportController::class, 'getAllReports'])->name('zoya.all.reports');
    Route::get('compliant-stocks', [ZoyaReportController::class, 'getAllCompliantStocks'])->name('zoya.compliant.stocks');
    Route::get('advanced-report', [ZoyaReportController::class, 'getAdvancedReport'])->name('zoya.advanced.report');
    Route::get('international-report', [ZoyaReportController::class, 'getInternationalReport'])
    ->name('zoya.international.report');
     Route::get('regional-reports', [ZoyaReportController::class, 'getRegionalReports'])
        ->name('zoya.regional.reports');

 // Get all compliant stocks from MENA regions
    Route::get('mena-screens', [ZoyaReportController::class, 'getMENAScreens'])
        ->name('zoya.mena.screens');
    Route::get('etf-reports', [ZoyaReportController::class, 'getETFReports'])->name('zoya.etf.reports');
    // Available Zoya regions
Route::get('regions', [ZoyaReportController::class, 'getRegions'])->name('zoya.regions.available');

});


    
      

        // Admin Route: Create/Update Terms & Conditions
        Route::post('terms', [TermsAndConditionController::class, 'save']);

       
    });
});
