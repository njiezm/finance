<?php

use App\Http\Controllers\Finance\DashboardController;
use App\Http\Controllers\Push\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'overview'])->name('overview');
Route::get('/comptes', [DashboardController::class, 'accountsPage'])->name('accounts.page');
Route::get('/transactions', [DashboardController::class, 'transactionsPage'])->name('transactions.page');
Route::get('/objectifs', [DashboardController::class, 'goalsPage'])->name('goals.page');
Route::get('/recommandations', [DashboardController::class, 'recommendationsPage'])->name('recommendations.page');

Route::post('/transactions', [DashboardController::class, 'storeTransaction'])->name('transactions.store');
Route::patch('/transactions/{transaction}', [DashboardController::class, 'updateTransaction'])->name('transactions.update');
Route::delete('/transactions/{transaction}', [DashboardController::class, 'destroyTransaction'])->name('transactions.destroy');
Route::post('/transactions/transfer', [DashboardController::class, 'storeTransfer'])->name('transactions.transfer');

Route::post('/budgets', [DashboardController::class, 'storeBudget'])->name('budgets.store');

Route::post('/goals', [DashboardController::class, 'storeGoal'])->name('goals.store');
Route::patch('/goals/{goal}', [DashboardController::class, 'updateGoal'])->name('goals.update');
Route::delete('/goals/{goal}', [DashboardController::class, 'destroyGoal'])->name('goals.destroy');
Route::post('/goals/{goal}/archive', [DashboardController::class, 'archiveGoal'])->name('goals.archive');

Route::post('/accounts', [DashboardController::class, 'upsertAccount'])->name('accounts.upsert');
Route::delete('/accounts/{account}', [DashboardController::class, 'destroyAccount'])->name('accounts.destroy');

Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');
Route::post('/push/test', [PushSubscriptionController::class, 'sendTest'])->name('push.test');
