<?php

use App\Http\Controllers\Api\Mobile\MobileAuthController;
use App\Http\Controllers\Api\Mobile\ProfessionalController;
use App\Http\Controllers\Api\Mobile\StudentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\FeedController;
use App\Http\Controllers\Api\V1\FinanceController;
use App\Http\Controllers\Api\V1\MemberController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\PrescriptionController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    // Auth (aliases curtos + namespace /auth) — rate limit anti brute-force
    Route::middleware('throttle:api-login')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/auth/login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth:sanctum', 'throttle:api-authenticated'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Smoke Sentry — apenas local/staging; bloqueado em production
        Route::get('/debug-sentry', function () {
            abort_if(app()->environment('production'), 404);

            throw new \RuntimeException('Teste de Integracao Sentry — Etapa 12');
        });

        Route::prefix('members')->group(function () {
            Route::get('/', [MemberController::class, 'index']);
            Route::get('/{member}', [MemberController::class, 'show']);
            Route::patch('/{member}', [MemberController::class, 'update']);
            Route::get('/{member}/workouts', [MemberController::class, 'workouts']);
            Route::get('/{member}/feedbacks', [MemberController::class, 'feedbacks']);
        });

        Route::prefix('messages')->group(function () {
            Route::get('/conversations', [MessageController::class, 'conversations']);
            Route::post('/conversations/start/{member}', [MessageController::class, 'start']);
            Route::get('/conversations/{conversation}', [MessageController::class, 'show']);
            Route::post('/conversations/{conversation}/messages', [MessageController::class, 'send']);
            Route::post('/conversations/{conversation}/read', [MessageController::class, 'markAsRead']);
        });

        Route::prefix('prescriptions')->group(function () {
            Route::get('/', [PrescriptionController::class, 'index']);
            Route::get('/member/{member}', [PrescriptionController::class, 'member']);
            Route::post('/diet', [PrescriptionController::class, 'storeDiet']);
            Route::post('/workout', [PrescriptionController::class, 'storeWorkout']);
        });

        Route::prefix('finance')->group(function () {
            Route::get('/dashboard', [FinanceController::class, 'dashboard']);
            Route::get('/invoices', [FinanceController::class, 'invoices']);
            Route::get('/invoices/{invoice}', [FinanceController::class, 'invoice']);
            Route::get('/payments', [FinanceController::class, 'payments']);
        });

        Route::prefix('events')->group(function () {
            Route::get('/', [EventController::class, 'index']);
            Route::post('/', [EventController::class, 'store']);
            Route::get('/{event}', [EventController::class, 'show']);
            Route::patch('/{event}', [EventController::class, 'update']);
        });

        Route::prefix('feed')->group(function () {
            Route::get('/', [FeedController::class, 'index']);
            Route::post('/', [FeedController::class, 'store']);
            Route::get('/feedbacks', [FeedController::class, 'feedbacks']);
        });

        Route::prefix('catalog')->group(function () {
            Route::get('/overview', [CatalogController::class, 'overview']);
            Route::get('/exercises', [CatalogController::class, 'exercises']);
            Route::get('/diet-menus', [CatalogController::class, 'dietMenus']);
            Route::get('/membership-plans', [CatalogController::class, 'membershipPlans']);
        });
    });
});

Route::prefix('auth')->group(function () {
    Route::post('/professional/login', [MobileAuthController::class, 'professionalLogin']);
    Route::post('/login', [MobileAuthController::class, 'clientLogin']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [MobileAuthController::class, 'me']);
    Route::get('/me', [MobileAuthController::class, 'me']);

    Route::prefix('professional')->group(function () {
        Route::get('/overview', [ProfessionalController::class, 'overview']);
        Route::get('/clients', [ProfessionalController::class, 'clients']);
        Route::get('/clients/{id}', [ProfessionalController::class, 'showClient']);
        Route::get('/feedbacks', [ProfessionalController::class, 'feedbacks']);
        Route::get('/conversations', [ProfessionalController::class, 'conversations']);
        Route::get('/feed', [ProfessionalController::class, 'feed']);
        Route::get('/posts', [ProfessionalController::class, 'posts']);
        Route::get('/community', [ProfessionalController::class, 'community']);
        Route::get('/prescriptions', [ProfessionalController::class, 'prescriptions']);
        Route::prefix('catalog')->group(function () {
            Route::get('/exercises', [ProfessionalController::class, 'catalogExercises']);
            Route::get('/foods', [ProfessionalController::class, 'catalogFoods']);
        });
    });

    Route::get('/prescriptions', [StudentController::class, 'prescriptions']);
    Route::post('/workouts/{workout}/activities/{activity}/log', [StudentController::class, 'logWorkoutActivity']);
    Route::delete('/workouts/{workout}/activities/{activity}/log', [StudentController::class, 'uncompleteWorkoutActivity']);
    Route::post('/workouts/{workout}/complete', [StudentController::class, 'completeWorkout']);
    Route::get('/diets/{prescription}/print', [StudentController::class, 'dietPrint']);
    Route::get('/diets/{prescription}/print-link', [StudentController::class, 'dietPrintLink']);
    Route::post('/diets/{prescription}/meals/{meal}/complete', [StudentController::class, 'completeDietMeal']);
    Route::delete('/diets/{prescription}/meals/{meal}/complete', [StudentController::class, 'uncompleteDietMeal']);
    Route::prefix('catalog')->group(function () {
        Route::get('/exercises', [StudentController::class, 'catalogExercises']);
        Route::get('/foods', [StudentController::class, 'catalogFoods']);
    });
    Route::get('/feed', [StudentController::class, 'feed']);
    Route::get('/feedbacks', [StudentController::class, 'feedbacks']);
    Route::post('/feedbacks', [StudentController::class, 'storeFeedback']);
    Route::get('/messages/conversation', [StudentController::class, 'messagesConversation']);
    Route::post('/messages/conversation', [StudentController::class, 'sendMessage']);
    Route::post('/messages/conversation/read', [StudentController::class, 'markMessagesRead']);
    Route::get('/logbooks', [StudentController::class, 'logbooks']);
    Route::post('/logbooks', [StudentController::class, 'storeLogbook']);
    Route::get('/photos', [StudentController::class, 'photos']);
    Route::post('/photos', [StudentController::class, 'storePhoto']);
    Route::get('/engagement', [StudentController::class, 'engagement']);
    Route::get('/groups', [StudentController::class, 'groups']);
    Route::post('/groups/{group}/posts', [StudentController::class, 'storeGroupPost']);
});

Route::get('/health', function () {
    try {
        DB::connection('mysql')->getPdo();
        $database = 'ok';
    } catch (\Throwable $exception) {
        $database = 'error';
    }

    return response()->json([
        'status' => 'ok',
        'database' => $database,
    ], $database === 'ok' ? 200 : 503);
});
