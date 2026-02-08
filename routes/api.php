<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SignUpController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\TermsAndConditionController;
use App\Http\Controllers\Auth\FirebaseAuthController;
use App\Http\Controllers\Zoya\ZoyaReportController;
use App\Http\Controllers\Subscription\PlanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OurAnalysisController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Financial\IncomeController;
use App\Http\Controllers\Financial\ExpenseController;
use App\Http\Controllers\Financial\LoanController;
use App\Http\Controllers\FinancialManagerController;
use App\Http\Controllers\LoanCalculatorController;
use App\Http\Controllers\WealthDashboardController;
use App\Http\Controllers\MessageController;
use  App\Http\Controllers\PaymentController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ContactController;


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

   


    // Stripe Webhook
    Route::post('payment/webhook', [PaymentController::class, 'handleWebhook']);
     Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/payment/cancel', [PaymentController::class, 'paymentCancel'])->name('payment.cancel');





    // Public Route: Get Terms & Conditions
    Route::get('terms', [TermsAndConditionController::class, 'get']);
    //Zoya Controller

    //user contact with admin via email

    Route::post('contact',[ContactController::class,'store']);
   

    // ----------------------------
    // Protected Routes (Require Auth)
    // ----------------------------
    Route::middleware('auth:sanctum')->group(function () {


      //subscription payment
        Route::post('/payment/process', [PaymentController::class, 'processPayment']);
        Route::get('/all-payments', [PaymentController::class, 'index'])->name('payment.index');//admin show all



 Route::post('/fcm-token', [UserController::class, 'updateFcmToken']);

 //Messaing part

  Route::post('/chat/send', [MessageController::class,'send']);
    Route::get('/chat/{userId}', [MessageController::class,'chatWithUser']);
    Route::delete('/chat/delete/{id}', [MessageController::class,'delete']);
    Route::post('/chat/restore/{id}', [MessageController::class,'restore']);
    

   Route::prefix('subscriptions')->group(function () {
    Route::apiResource('', PlanController::class)->parameters(['' => 'id']);
});

Route::prefix('financial')->group(function() {
    // Manager and Wealth
    Route::get('manager', [FinancialManagerController::class, 'index']);

    Route::post('loan/calc', [LoanCalculatorController::class,'calculate']);

    Route::get('wealth', [WealthDashboardController::class, 'index']);


    // Income
    Route::apiResource('incomes', IncomeController::class);

    // Expense
    Route::apiResource('expenses', ExpenseController::class);

    // Loan
    Route::apiResource('loans', LoanController::class);
});




//OurAnalysisController
Route::prefix('analyses')->group(function () {
    Route::get('/', [OurAnalysisController::class, 'index']);
    Route::post('/', [OurAnalysisController::class, 'store']);
    Route::get('{id}', [OurAnalysisController::class, 'show']);
    Route::post('{id}', [OurAnalysisController::class, 'update']);
    Route::delete('{id}', [OurAnalysisController::class, 'destroy']);
});





//Wishlist
    Route::get('wishlist', [WishlistController::class, 'index']);
    Route::post('wishlist', [WishlistController::class, 'store']);
    Route::delete('wishlist/{id}', [WishlistController::class, 'destroy']);



//news part
Route::prefix('news')->group(function () {
    Route::get('/', [NewsController::class, 'index']);
    Route::post('/', [NewsController::class, 'store']);
    Route::get('{id}', [NewsController::class, 'show']);
    Route::put('{id}', [NewsController::class, 'update']);
    Route::delete('{id}', [NewsController::class, 'destroy']); // soft delete
});



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
