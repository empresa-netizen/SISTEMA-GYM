<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Auth\OTPController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Auth::routes(['verify' => false]); // Disable default email verification

// Login Routes
Route::get('login', [LoginController::class, 'create'])->name('login');
Route::post('login', [LoginController::class, 'store']);
Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('/mobile/student/diets/{prescription}/print', [App\Http\Controllers\Api\Mobile\StudentController::class, 'signedDietPrint'])
    ->middleware('signed')
    ->name('mobile.student.diets.print');

// 2FA Routes
Route::middleware(['auth'])->group(function () {
    Route::get('2fa', [OTPController::class, 'show'])->name('login.2fa');
    Route::post('2fa/verify', [OTPController::class, 'verify'])->name('login.2fa.verify');
    Route::post('2fa/enable', [OTPController::class, 'enable'])->name('2fa.enable');
    Route::post('2fa/disable', [OTPController::class, 'disable'])->name('2fa.disable');
});

// Email Verification Routes
Route::middleware(['auth'])->group(function () {
    Route::get('email/verify', [EmailVerificationController::class, 'notice'])->name('email.verification.notice');
    Route::post('email/verification-notification', [EmailVerificationController::class, 'send'])->name('email.verification.send');
    Route::get('email/verify/{code}', [EmailVerificationController::class, 'verify'])->name('email.verify');
});

// Impersonation Routes
Route::impersonate();

// Protected Routes (Requires Authentication + 2FA Verification)
Route::middleware(['auth', 'verify2fa'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/notifications/inbox', [App\Http\Controllers\InAppNotificationController::class, 'index'])->name('notifications.inbox');
    Route::post('/notifications/inbox/read-all', [App\Http\Controllers\InAppNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::match(['get', 'post'], '/notifications/inbox/{id}/read', [App\Http\Controllers\InAppNotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/search', [App\Http\Controllers\SearchController::class, 'index'])->name('search');
    Route::get('/finance', [App\Http\Controllers\FinanceController::class, 'index'])->name('finance.index');

    // Coaching modules (coach-pro mirror)
    Route::get('/messages', [App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages/{conversation}', [App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    Route::match(['get', 'post'], '/messages/start/{member}', [App\Http\Controllers\MessageController::class, 'start'])->name('messages.start');
    Route::get('/feed', [App\Http\Controllers\FeedController::class, 'index'])->name('feed.index');
    Route::post('/feed', [App\Http\Controllers\FeedController::class, 'store'])->name('feed.store');
    Route::post('/feed/{item}/like', [App\Http\Controllers\FeedController::class, 'like'])->name('feed.like');
    Route::post('/feed/{item}/comment', [App\Http\Controllers\FeedController::class, 'comment'])->name('feed.comment');
    Route::get('/community', [App\Http\Controllers\CommunityController::class, 'index'])->name('community.index');
    Route::get('/community/{group}', [App\Http\Controllers\CommunityController::class, 'show'])->name('community.show');
    Route::post('/community/groups', [App\Http\Controllers\CommunityController::class, 'storeGroup'])->name('community.groups.store');
    Route::post('/community/{group}/posts', [App\Http\Controllers\CommunityController::class, 'storePost'])->name('community.posts.store');
    Route::get('/feedbacks', [App\Http\Controllers\FeedbackController::class, 'index'])->name('feedbacks.index');
    Route::patch('/feedbacks/{feedback}', [App\Http\Controllers\FeedbackController::class, 'updateStatus'])->name('feedbacks.update');
    Route::get('/library/diet', [App\Http\Controllers\LibraryDietController::class, 'index'])->name('library.diet.index');
    Route::get('/library/diet/foods', [App\Http\Controllers\LibraryDietController::class, 'foods'])->name('library.diet.foods');
    Route::post('/library/diet/foods', [App\Http\Controllers\LibraryDietController::class, 'storeFood'])->name('library.diet.foods.store');
    Route::get('/library/diet/formulas', [App\Http\Controllers\LibraryDietController::class, 'formulas'])->name('library.diet.formulas');
    Route::get('/library/diet/menus', [App\Http\Controllers\LibraryDietController::class, 'menus'])->name('library.diet.menus');
    Route::post('/library/diet/menus', [App\Http\Controllers\LibraryDietController::class, 'storeMenu'])->name('library.diet.menus.store');
    Route::get('/library/diet/predefined-meals', [App\Http\Controllers\LibraryDietController::class, 'predefinedMeals'])->name('library.diet.predefined-meals');
    Route::get('/products/coupons', [App\Http\Controllers\CouponController::class, 'index'])->name('products.coupons');
    Route::post('/products/coupons', [App\Http\Controllers\CouponController::class, 'store'])->name('products.coupons.store');
    Route::get('/members/renewals', [App\Http\Controllers\MemberHubController::class, 'renewals'])->name('members.renewals');
    Route::get('/members/pending', [App\Http\Controllers\MemberHubController::class, 'pending'])->name('members.pending');
    Route::get('/members/all', [App\Http\Controllers\MemberHubController::class, 'all'])->name('members.all');
    Route::get('/members/groups', [App\Http\Controllers\MemberHubController::class, 'groups'])->name('members.groups');
    Route::get('/members/engagement', [App\Http\Controllers\MemberHubController::class, 'engagement'])->name('members.engagement');
    Route::get('/members/dropouts', [App\Http\Controllers\MemberHubController::class, 'dropouts'])->name('members.dropouts');
    Route::get('/members/attendances', [App\Http\Controllers\MemberHubController::class, 'attendances'])->name('members.attendances');
    Route::get('/members/logbook', [App\Http\Controllers\MemberHubController::class, 'logbook'])->name('members.logbook');
    Route::delete('/members/logbook/{entry}', [App\Http\Controllers\MemberHubController::class, 'destroyLogbook'])->name('members.logbooks.destroy');
    Route::get('/tools/anamnesis', [App\Http\Controllers\ToolsController::class, 'anamnesis'])->name('tools.anamnesis');
    Route::get('/tools/groups', [App\Http\Controllers\MemberHubController::class, 'groups'])->name('tools.groups');
    Route::get('/tools/import/customers', [App\Http\Controllers\ToolsController::class, 'importCustomers'])->name('tools.import.customers');
    Route::post('/tools/import/customers', [App\Http\Controllers\ToolsController::class, 'importCustomersStore'])->name('tools.import.customers.store');
    Route::get('/tools/import/protocols', [App\Http\Controllers\ToolsController::class, 'importProtocols'])->name('tools.import.protocols');
    Route::get('/products/affiliates', [App\Http\Controllers\ProductHubController::class, 'affiliates'])->name('products.affiliates');
    Route::get('/products/cart-recovery', [App\Http\Controllers\ProductHubController::class, 'cartRecovery'])->name('products.cart-recovery');
    Route::get('/products/list', fn () => redirect()->route('membership-plans.index'))->name('products.list');
    Route::view('/library/workout', 'mgteam.library.workout')->name('library.workout');
    Route::get('/library/workout/templates', [App\Http\Controllers\LibraryWorkoutController::class, 'index'])->name('workout-templates.index');
    Route::post('/library/workout/templates', [App\Http\Controllers\LibraryWorkoutController::class, 'store'])->name('workout-templates.store');
    Route::post('/library/workout/templates/{template}/assign', [App\Http\Controllers\LibraryWorkoutController::class, 'assign'])->name('workout-templates.assign');
    Route::get('/library/courses', [App\Http\Controllers\LibraryDietController::class, 'courses'])->name('library.courses');
    Route::post('/library/courses', [App\Http\Controllers\LibraryDietController::class, 'storeCourse'])->name('library.courses.store');
    Route::get('/feed/news', [App\Http\Controllers\FeedController::class, 'news'])->name('feed.news');
    Route::get('/feed/community', [App\Http\Controllers\CommunityController::class, 'index'])->name('feed.community');
    Route::view('/help', 'mgteam.help')->name('help');
    Route::get('/profile', [App\Http\Controllers\ReportsController::class, 'profile'])->name('profile');
    Route::get('/account/settings', [App\Http\Controllers\AccountController::class, 'settings'])->name('account.settings');
    Route::post('/account/settings', [App\Http\Controllers\AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::get('/account/subscription', fn () => redirect()->route('subscriptions.mine'))->name('account.subscription');
    Route::get('/account/collaborators', [App\Http\Controllers\UserController::class, 'index'])->name('account.collaborators');
    Route::get('/team', [App\Http\Controllers\UserController::class, 'index'])->name('team.index');
    Route::get('/team/create', [App\Http\Controllers\UserController::class, 'create'])->name('team.create');
    Route::get('/awards', [App\Http\Controllers\HomeController::class, 'awards'])->name('awards');
    Route::get('/patch-notes', [App\Http\Controllers\ToolsController::class, 'patchNotes'])->name('patch-notes');
    Route::get('/reports', [App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');
    Route::post('/members/{member}/anamnesis', [App\Http\Controllers\MemberCrmController::class, 'storeAnamnesis'])->name('members.anamnesis.store');
    Route::post('/members/{member}/photos', [App\Http\Controllers\MemberCrmController::class, 'storePhoto'])->name('members.photos.store');
    Route::post('/members/{member}/diet-prescriptions', [App\Http\Controllers\MemberCrmController::class, 'storeDietPrescription'])->name('members.diet.store');
    Route::post('/members/{member}/logbooks', [App\Http\Controllers\MemberCrmController::class, 'storeLogbook'])->name('members.logbooks.store');
    Route::post('/members/{member}/cardio-plans', [App\Http\Controllers\MemberCrmController::class, 'storeCardioPlan'])->name('members.cardio.store');
    Route::post('/members/{member}/notes', [App\Http\Controllers\MemberCrmController::class, 'storeNote'])->name('members.notes.store');
    Route::post('/members/{member}/assume', [App\Http\Controllers\MemberCrmController::class, 'assumeClient'])->name('members.assume');
    Route::post('/members/{member}/notify', [App\Http\Controllers\MemberCrmController::class, 'notifyClient'])->name('members.notify');
    Route::post('/members/{member}/photos/compare', [App\Http\Controllers\MemberCrmController::class, 'comparePhotos'])->name('members.photos.compare');
    Route::post('/diet-prescriptions/{prescription}/send', [App\Http\Controllers\MemberCrmController::class, 'sendDietPrescription'])->name('diet-prescriptions.send');
    Route::put('/diet-prescriptions/{prescription}', [App\Http\Controllers\MemberCrmController::class, 'updateDietPrescription'])->name('diet-prescriptions.update');
    Route::get('/diet-prescriptions/{prescription}/print', [App\Http\Controllers\MemberCrmController::class, 'printDietPrescription'])->name('diet-prescriptions.print');
    Route::get('/prescriptions', [App\Http\Controllers\PrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::get('/apps', [App\Http\Controllers\MobileAppsController::class, 'index'])->name('apps.index');
    Route::get('/apps/status', [App\Http\Controllers\MobileAppsController::class, 'status'])->name('apps.status');
    Route::get('/products/hub', [App\Http\Controllers\ProductHubController::class, 'hub'])->name('products.hub');
    Route::post('/products/hub', [App\Http\Controllers\ProductHubController::class, 'quickStore'])->name('products.quick-store');
    Route::view('/library/hub', 'mgteam.library.hub')->name('library.hub');

    // Settings Management
    Route::get('settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    Route::get('settings/{key}', [App\Http\Controllers\SettingsController::class, 'show'])->name('settings.show');

    // User Management
    //    Route::resource('users', App\Http\Controllers\UserController::class);

    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
        Route::get('/', [App\Http\Controllers\UserController::class, 'index'])->name('index');
        Route::post('store', [App\Http\Controllers\UserController::class, 'store'])->name('store');
        Route::get('create', [App\Http\Controllers\UserController::class, 'create'])->name('create');

        Route::group(['prefix' => '{user}'], function () {
            Route::get('edit', [App\Http\Controllers\UserController::class, 'edit'])->name('edit');
            Route::get('show', [App\Http\Controllers\UserController::class, 'show'])->name('show');
            Route::put('update', [App\Http\Controllers\UserController::class, 'update'])->name('update');
            Route::post('delete', [App\Http\Controllers\UserController::class, 'destroy'])->name('destroy');
        });
    });

    // Member Management
    //    Route::resource('members', App\Http\Controllers\MemberController::class);

    Route::group(['prefix' => 'members', 'as' => 'members.'], function () {
        Route::get('/', [App\Http\Controllers\MemberController::class, 'index'])->name('index');
        Route::post('store', [App\Http\Controllers\MemberController::class, 'store'])->name('store');
        Route::get('create', [App\Http\Controllers\MemberController::class, 'create'])->name('create');

        Route::group(['prefix' => '{member}'], function () {
            Route::get('edit', [App\Http\Controllers\MemberController::class, 'edit'])->name('edit');
            Route::get('workouts', [App\Http\Controllers\MemberController::class, 'workouts'])->name('workouts');
            Route::get('diet', [App\Http\Controllers\MemberController::class, 'diet'])->name('diet');
            Route::get('show', [App\Http\Controllers\MemberController::class, 'show'])->name('show');
            Route::put('update', [App\Http\Controllers\MemberController::class, 'update'])->name('update');
            Route::post('delete', [App\Http\Controllers\MemberController::class, 'destroy'])->name('destroy');
        });
    });
    //    Route::resource('membership-plans', App\Http\Controllers\MembershipPlanController::class);

    Route::group(['prefix' => 'membership-plans', 'as' => 'membership-plans.'], function () {
        Route::get('/', [App\Http\Controllers\MembershipPlanController::class, 'index'])->name('index');
        Route::post('store', [App\Http\Controllers\MembershipPlanController::class, 'store'])->name('store');
        Route::get('create', [App\Http\Controllers\MembershipPlanController::class, 'create'])->name('create');

        Route::group(['prefix' => '{membershipPlan}'], function () {
            Route::get('edit', [App\Http\Controllers\MembershipPlanController::class, 'edit'])->name('edit');
            Route::get('show', [App\Http\Controllers\MembershipPlanController::class, 'show'])->name('show');
            Route::post('duplicate', [App\Http\Controllers\MembershipPlanController::class, 'duplicate'])->name('duplicate');
            Route::put('update', [App\Http\Controllers\MembershipPlanController::class, 'update'])->name('update');
            Route::post('delete', [App\Http\Controllers\MembershipPlanController::class, 'destroy'])->name('destroy');
        });
    });

    // Trainer Management
    //    Route::resource('trainers', App\Http\Controllers\TrainerController::class);

    Route::group(['prefix' => 'trainers', 'as' => 'trainers.'], function () {
        Route::get('/', [App\Http\Controllers\TrainerController::class, 'index'])->name('index');
        Route::post('store', [App\Http\Controllers\TrainerController::class, 'store'])->name('store');
        Route::get('create', [App\Http\Controllers\TrainerController::class, 'create'])->name('create');

        Route::group(['prefix' => '{trainer}'], function () {
            Route::get('edit', [App\Http\Controllers\TrainerController::class, 'edit'])->name('edit');
            Route::get('show', [App\Http\Controllers\TrainerController::class, 'show'])->name('show');
            Route::put('update', [App\Http\Controllers\TrainerController::class, 'update'])->name('update');
            Route::post('delete', [App\Http\Controllers\TrainerController::class, 'destroy'])->name('destroy');
        });
    });

    // Classes & Scheduling
    //    Route::resource('categories', App\Http\Controllers\CategoryController::class);

    Route::group(['prefix' => 'categories', 'as' => 'categories.'], function () {
        Route::get('/', [App\Http\Controllers\CategoryController::class, 'index'])->name('index');
        Route::post('store', [App\Http\Controllers\CategoryController::class, 'store'])->name('store');
        Route::get('create', [App\Http\Controllers\CategoryController::class, 'create'])->name('create');

        Route::group(['prefix' => '{category}'], function () {
            Route::get('edit', [App\Http\Controllers\CategoryController::class, 'edit'])->name('edit');
            Route::get('show', [App\Http\Controllers\CategoryController::class, 'show'])->name('show');
            Route::put('update', [App\Http\Controllers\CategoryController::class, 'update'])->name('update');
            Route::post('delete', [App\Http\Controllers\CategoryController::class, 'delete'])->name('destroy');
        });
    });
    Route::resource('gym-classes', App\Http\Controllers\GymClassController::class);

    // Workouts & Health Tracking
    Route::get('workouts/today', [App\Http\Controllers\WorkoutController::class, 'today'])->name('workouts.today');
    Route::resource('workouts', App\Http\Controllers\WorkoutController::class);
    Route::get('exercises', [App\Http\Controllers\ExerciseController::class, 'index'])->name('exercises.index');
    Route::resource('healths', App\Http\Controllers\HealthController::class);

    // Attendance Tracking
    Route::get('attendances/report', [App\Http\Controllers\AttendanceController::class, 'report'])->name('attendances.report');
    Route::resource('attendances', App\Http\Controllers\AttendanceController::class);

    // Financial Management
    Route::resource('types', App\Http\Controllers\TypeController::class);
    //    Route::resource('invoices', App\Http\Controllers\InvoiceController::class);

    Route::group(['prefix' => 'invoices', 'as' => 'invoices.'], function () {
        Route::get('/', [App\Http\Controllers\InvoiceController::class, 'index'])->name('index');
        Route::post('store', [App\Http\Controllers\InvoiceController::class, 'store'])->name('store');
        Route::get('create', [App\Http\Controllers\InvoiceController::class, 'create'])->name('create');

        Route::group(['prefix' => '{invoice}'], function () {
            Route::get('edit', [App\Http\Controllers\InvoiceController::class, 'edit'])->name('edit');
            Route::get('show', [App\Http\Controllers\InvoiceController::class, 'show'])->name('show');
            Route::put('update', [App\Http\Controllers\InvoiceController::class, 'update'])->name('update');
            Route::post('delete', [App\Http\Controllers\InvoiceController::class, 'destroy'])->name('destroy');
        });
    });
    Route::post('invoices/{invoice}/payment', [App\Http\Controllers\InvoiceController::class, 'addPayment'])->name('invoices.addPayment');
    //    Route::resource('expenses', App\Http\Controllers\ExpenseController::class);

    Route::group(['prefix' => 'expenses', 'as' => 'expenses.'], function () {
        Route::get('/', [App\Http\Controllers\ExpenseController::class, 'index'])->name('index');
        Route::post('store', [App\Http\Controllers\ExpenseController::class, 'store'])->name('store');
        Route::get('create', [App\Http\Controllers\ExpenseController::class, 'create'])->name('create');

        Route::group(['prefix' => '{expense}'], function () {
            Route::get('edit', [App\Http\Controllers\ExpenseController::class, 'edit'])->name('edit');
            Route::get('show', [App\Http\Controllers\ExpenseController::class, 'show'])->name('show');
            Route::put('update', [App\Http\Controllers\ExpenseController::class, 'update'])->name('update');
            Route::post('delete', [App\Http\Controllers\ExpenseController::class, 'destroy'])->name('destroy');
        });
    });

    // Locker, Event, Notice Board
    Route::resource('lockers', App\Http\Controllers\LockerController::class);
    Route::post('lockers/{locker}/assign', [App\Http\Controllers\LockerController::class, 'assign'])->name('lockers.assign');
    Route::resource('events', App\Http\Controllers\EventController::class);
    Route::get('schedule', [App\Http\Controllers\EventController::class, 'schedule'])->name('events.schedule');
    Route::get('events-feed', [App\Http\Controllers\EventController::class, 'feed'])->name('events.feed');
    Route::resource('notice-boards', App\Http\Controllers\NoticeBoardController::class);

    // Email Notification Templates
    Route::get('notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{notification}/edit', [App\Http\Controllers\NotificationController::class, 'edit'])->name('notifications.edit');
    Route::put('notifications/{notification}', [App\Http\Controllers\NotificationController::class, 'update'])->name('notifications.update');

    // Additional Modules - Phase 3
    //    Route::resource('products', App\Http\Controllers\ProductController::class);

    Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
        Route::get('/', [App\Http\Controllers\ProductController::class, 'index'])->name('index');
        Route::post('store', [App\Http\Controllers\ProductController::class, 'store'])->name('store');
        Route::get('create', [App\Http\Controllers\ProductController::class, 'create'])->name('create');

        Route::group(['prefix' => '{product}'], function () {
            Route::get('edit', [App\Http\Controllers\ProductController::class, 'edit'])->name('edit');
            Route::get('show', [App\Http\Controllers\ProductController::class, 'show'])->name('show');
            Route::put('update', [App\Http\Controllers\ProductController::class, 'update'])->name('update');
            Route::post('delete', [App\Http\Controllers\ProductController::class, 'destroy'])->name('destroy');
        });
    });
    Route::resource('contacts', App\Http\Controllers\ContactController::class);

    Route::post('contacts/{contact}/reply', [App\Http\Controllers\ContactController::class, 'reply'])->name('contacts.reply');
    Route::resource('support-tickets', App\Http\Controllers\SupportTicketController::class);
    Route::post('support-tickets/{ticket}/reply', [App\Http\Controllers\SupportTicketController::class, 'addReply'])->name('support-tickets.reply');

    // Subscription & Payment
    Route::get('subscriptions', [App\Http\Controllers\SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/{plan}/checkout', [App\Http\Controllers\SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
    Route::post('subscriptions/{plan}/purchase', [App\Http\Controllers\SubscriptionController::class, 'purchase'])->name('subscriptions.purchase');
    Route::get('subscriptions/{subscription}/success', [App\Http\Controllers\SubscriptionController::class, 'success'])->name('subscriptions.success');
    Route::get('subscriptions/{subscription}/paypal-success', [App\Http\Controllers\SubscriptionController::class, 'paypalSuccess'])->name('subscriptions.paypal.success');
    Route::get('subscriptions/{subscription}/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::get('my-subscription', [App\Http\Controllers\SubscriptionController::class, 'mySubscription'])->name('subscriptions.mine');
    Route::post('subscriptions/{subscription}/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancelSubscription'])->name('subscriptions.cancel.post');

    // User Profile Management
    Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('updatePassword');
});

// Payment Webhooks (No auth required)
Route::post('webhooks/stripe', [App\Http\Controllers\PaymentWebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('webhooks/paypal', [App\Http\Controllers\PaymentWebhookController::class, 'paypal'])->name('webhooks.paypal');

// Language Translation
Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);

// Super Admin Routes (Platform Administration)
Route::middleware(['auth', 'verify2fa', 'role:super-admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');

    // Customer Management
    //    Route::resource('customers', App\Http\Controllers\SuperAdmin\CustomerController::class);

    Route::group(['prefix' => 'customers', 'as' => 'customers.'], function () {
        Route::get('/', [App\Http\Controllers\SuperAdmin\CustomerController::class, 'index'])->name('index');
        Route::post('store', [App\Http\Controllers\SuperAdmin\CustomerController::class, 'store'])->name('store');
        Route::get('create', [App\Http\Controllers\SuperAdmin\CustomerController::class, 'create'])->name('create');

        Route::group(['prefix' => '{customer}'], function () {
            Route::get('edit', [App\Http\Controllers\SuperAdmin\CustomerController::class, 'edit'])->name('edit');
            Route::get('show', [App\Http\Controllers\SuperAdmin\CustomerController::class, 'show'])->name('show');
            Route::put('update', [App\Http\Controllers\SuperAdmin\CustomerController::class, 'update'])->name('update');
            Route::post('delete', [App\Http\Controllers\SuperAdmin\CustomerController::class, 'destroy'])->name('destroy');
        });
    });
    Route::post('customers/{customer}/impersonate', [App\Http\Controllers\SuperAdmin\CustomerController::class, 'impersonate'])->name('customers.impersonate');
    Route::post('customers/{customer}/suspend', [App\Http\Controllers\SuperAdmin\CustomerController::class, 'suspend'])->name('customers.suspend');

    // Platform Subscription Tier Management
    //    Route::resource('platform-subscriptions', App\Http\Controllers\SuperAdmin\PlatformSubscriptionController::class);

    Route::group(['prefix' => 'platform-subscriptions', 'as' => 'platform-subscriptions.'], function () {
        Route::get('/', [App\Http\Controllers\SuperAdmin\PlatformSubscriptionController::class, 'index'])->name('index');
        Route::post('store', [App\Http\Controllers\SuperAdmin\PlatformSubscriptionController::class, 'store'])->name('store');
        Route::get('create', [App\Http\Controllers\SuperAdmin\PlatformSubscriptionController::class, 'create'])->name('create');

        Route::group(['prefix' => '{platformSubscription}'], function () {
            Route::get('edit', [App\Http\Controllers\SuperAdmin\PlatformSubscriptionController::class, 'edit'])->name('edit');
            Route::get('show', [App\Http\Controllers\SuperAdmin\PlatformSubscriptionController::class, 'show'])->name('show');
            Route::put('update', [App\Http\Controllers\SuperAdmin\PlatformSubscriptionController::class, 'update'])->name('update');
            Route::post('delete', [App\Http\Controllers\SuperAdmin\PlatformSubscriptionController::class, 'destroy'])->name('destroy');
        });
    });

    // Platform Settings
    Route::get('settings', [App\Http\Controllers\SuperAdmin\PlatformSettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [App\Http\Controllers\SuperAdmin\PlatformSettingsController::class, 'update'])->name('settings.update');

    // Platform Analytics
    Route::get('analytics', [App\Http\Controllers\SuperAdmin\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/revenue', [App\Http\Controllers\SuperAdmin\AnalyticsController::class, 'revenue'])->name('analytics.revenue');
    Route::get('analytics/customers', [App\Http\Controllers\SuperAdmin\AnalyticsController::class, 'customers'])->name('analytics.customers');
    Route::get('analytics/subscriptions', [App\Http\Controllers\SuperAdmin\AnalyticsController::class, 'subscriptions'])->name('analytics.subscriptions');
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

Route::get('/', [App\Http\Controllers\WelcomeController::class, 'index'])->name('welcome');
