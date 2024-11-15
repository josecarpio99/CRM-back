<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SectorController;
use App\Http\Controllers\Api\SourceController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ScorecardController;
use App\Http\Controllers\Api\SmartListController;
use App\Http\Controllers\Api\UserReportController;
use App\Http\Controllers\Api\ConfirmDealController;
use App\Http\Controllers\Api\ContactDealController;
use App\Http\Controllers\Api\ConvertLeadController;
use App\Http\Controllers\Api\DealContactController;
use App\Http\Controllers\Api\OwnerReportController;
use App\Http\Controllers\Api\BranchReportController;
use App\Http\Controllers\Api\GlobalSearchController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\BulkDeleteDealController;
use App\Http\Controllers\Api\BulkDeleteLeadController;
use App\Http\Controllers\Api\ExportCustomerController;
use App\Http\Controllers\Api\SellsByAdvisorController;
use App\Http\Controllers\Api\SyncBranchUserController;
use App\Http\Controllers\Api\SyncManagerUserController;
use App\Http\Controllers\Api\UserAuditReportController;
use App\Http\Controllers\Api\DealSourceReportController;
use App\Http\Controllers\Api\DealStatusReportController;
use App\Http\Controllers\Api\BranchReportPeriodController;
use App\Http\Controllers\Api\BulkDeleteCustomerController;
use App\Http\Controllers\Api\ConvertOpportunityController;
use App\Http\Controllers\Api\ToggleCustomerStarController;
use App\Http\Controllers\Api\MassUpdateStatusDealController;
use App\Http\Controllers\Api\OwnerReportWithPeriodController;
use App\Http\Controllers\Api\DealStatusToInProgressController;
use App\Http\Controllers\Api\UpdateDealMonitoringTaskController;
use App\Http\Controllers\Api\DealOpportunitiesAwaitingResponseController;

Route::post('login', [AuthController::class, 'login'])->name('token.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/search', GlobalSearchController::class);

    Route::post('/notification/mark-as-read', [NotificationController::class, 'markAsRead']);

    Route::get('/user/auth', [AuthController::class, 'user']);
    Route::get('/user/list', [UserController::class, 'list']);
    Route::post('/user/{user}/sync-manager-user', SyncManagerUserController::class);
    Route::post('/user/{user}/sync-branch-user', SyncBranchUserController::class);
    Route::apiResource('user', UserController::class);
    Route::apiResource('smart-list', SmartListController::class);

    Route::get('/customer/list', [CustomerController::class, 'list']);
    Route::post('/customer/bulk-delete', BulkDeleteCustomerController::class);
    Route::post('/customer/{customer}/star', ToggleCustomerStarController::class);
    Route::get('/customer/export', ExportCustomerController::class);
    Route::apiResource('customer', CustomerController::class);

    Route::post('/lead/bulk-delete', BulkDeleteLeadController::class);
    Route::apiResource('lead', LeadController::class);
    Route::post('lead/{lead}/convert', ConvertLeadController::class);

    Route::post('/deal/bulk-delete', BulkDeleteDealController::class);
    Route::get('deal/pending', DealOpportunitiesAwaitingResponseController::class);
    Route::apiResource('deal', DealController::class);
    Route::post('deal/{deal}/confirm', ConfirmDealController::class);
    Route::post('deal/{deal}/convert', ConvertOpportunityController::class);
    Route::post('deal/{deal}/update-to-in-progress', DealStatusToInProgressController::class);
    Route::post('deal/update-status', MassUpdateStatusDealController::class);
    Route::post('deal/{deal}/contact/alter', DealContactController::class);
    Route::post('deal/{deal}/contact/alter-contact', ContactDealController::class);
    Route::post('deal/{deal}/update-monitoring-tasks', UpdateDealMonitoringTaskController::class);


    Route::apiResource('note', NoteController::class)->except('index');
    Route::apiResource('task', TaskController::class)->except('index');

    Route::post('document', [DocumentController::class, 'store']);
    Route::patch('document/{media}', [DocumentController::class, 'update']);
    Route::delete('document/{media}', [DocumentController::class, 'destroy']);

    // Route::post('contact', [ContactController::class, 'store'])->name('contact.store');
    // Route::put('contact/{contact}', [ContactController::class, 'update'])->name('contact.update');

    Route::apiResource('contact', ContactController::class, ['only' => ['store', 'update','index']]);

    Route::get('source', SourceController::class);
    Route::get('sector', SectorController::class);
    Route::get('country', CountryController::class);

    Route::prefix('report')->group(function () {
        Route::get('branch', BranchReportController::class);
        Route::get('branch-period', BranchReportPeriodController::class);
        Route::get('owner', OwnerReportController::class);
        Route::get('owner-period', OwnerReportWithPeriodController::class);
        Route::get('user/audit', UserAuditReportController::class);
        Route::get('deal/status', DealStatusReportController::class);
        Route::get('deal/source', DealSourceReportController::class);
        Route::get('scorecard', ScorecardController::class);
        Route::get('sells-by-advisor', SellsByAdvisorController::class);
        Route::get('reporte-de-asesor/{user}', [UserReportController::class, 'report']);

    });
});


