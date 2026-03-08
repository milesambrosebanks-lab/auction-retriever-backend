<?php

use App\Http\Controllers\Web\Backend\Access\PermissionController;
use App\Http\Controllers\Web\Backend\Access\RoleController;
use App\Http\Controllers\Web\Backend\Access\SubscriptionPlanController;
use App\Http\Controllers\Web\Backend\Access\UserController;
use App\Http\Controllers\Web\Backend\AttributeController;
use App\Http\Controllers\Web\Backend\BlogController;
use App\Http\Controllers\Web\Backend\ChatController;
use App\Http\Controllers\Web\Backend\CMS\Web\Home\HomeAboutController;
use App\Http\Controllers\Web\Backend\CMS\Web\Home\HomeBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\Home\HomeHeroController;
use App\Http\Controllers\Web\Backend\CMS\Web\Home\SectionInfoController;
use App\Http\Controllers\Web\Backend\ContactController;
use App\Http\Controllers\Web\Backend\CountryController;
use App\Http\Controllers\Web\Backend\CourseController;
use App\Http\Controllers\Web\Backend\CurdController;
use App\Http\Controllers\Web\Backend\CurriculumController;
use App\Http\Controllers\Web\Backend\Settings\FirebaseController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\MailSettingController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;
use App\Http\Controllers\Web\Backend\Settings\SocialController;
use App\Http\Controllers\Web\Backend\Settings\StripeController;
use App\Http\Controllers\Web\Backend\Settings\GoogleMapController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\EmailLogController;
use App\Http\Controllers\Web\Backend\ExtractionLogController;
use App\Http\Controllers\Web\Backend\FaqController;
use App\Http\Controllers\Web\Backend\FileManagerController;
use App\Http\Controllers\Web\Backend\ListingController;
use App\Http\Controllers\Web\Backend\OrderController;
use App\Http\Controllers\Web\Backend\ProductController;
use App\Http\Controllers\Web\Backend\PropertyController;
use App\Http\Controllers\Web\Backend\Settings\CaptchaController;
use App\Http\Controllers\Web\Backend\Settings\EnvController;
use App\Http\Controllers\Web\Backend\Settings\LogoController;
use App\Http\Controllers\Web\Backend\Settings\OtherController;
use App\Http\Controllers\Web\Backend\Settings\SignatureController;
use App\Http\Controllers\Web\Backend\SocialLinkController;
use App\Http\Controllers\Web\Backend\SubscriberController;
use App\Http\Controllers\Web\Backend\TransactionController;
use App\Http\Controllers\Web\Backend\QuizController;
use App\Http\Controllers\Web\Backend\ReportController;
use Illuminate\Support\Facades\Artisan;

Route::get("dashboard", [DashboardController::class, 'index'])->name('dashboard')->middleware(['role:admin|staff']);

Route::group(['middleware' => ['web-admin']], function () {

    Route::controller(ProductController::class)->prefix('product')->name('product.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create/single', 'singleCreate')->name('create.single');
        Route::post('/store/single', 'singleStore')->name('store.single');
        Route::get('/edit/{id}/single', 'singleEdit')->name('edit.single');
        Route::post('/update/{id}/single', 'singleUpdate')->name('update.single');


        Route::get('/bundle', 'bundle')->name('bundle');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/show/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');

        Route::delete('/delete/{id}', 'destroy')->name('destroy');
        Route::get('/status/{id}', 'status')->name('status');
    });

    Route::controller(OrderController::class)->prefix('order')->name('order.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/show/{id}', 'show')->name('show');
        Route::get('/status/{id}', 'status')->name('status');
    });

    Route::controller(ExtractionLogController::class)->prefix('extraction')->name('extraction.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/show/{id}', 'show')->name('show');
    });
    Route::controller(ListingController::class)->prefix('listing')->name('listing.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/show/{id}', 'show')->name('show');
    });








    Route::controller(SocialLinkController::class)->prefix('social')->name('social.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::delete('/delete/{id}', 'destroy')->name('destroy');
        Route::get('/status/{id}', 'status')->name('status');
    });

    Route::controller(FaqController::class)->prefix('faq')->name('faq.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/show/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::delete('/delete/{id}', 'destroy')->name('destroy');
        Route::get('/status/{id}', 'status')->name('status');
    });


    Route::post('/send-email', [EmailLogController::class, 'store'])->name('send.email');

    Route::controller(SubscriberController::class)->prefix('subscriber')->name('subscriber.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/show/{id}', 'show')->name('show');
        Route::delete('/delete/{id}', 'destroy')->name('destroy');
    });

    Route::controller(ContactController::class)->prefix('contact')->name('contact.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/status/{id}', 'status')->name('status');
    });

    Route::controller(TransactionController::class)->prefix('transaction')->name('transaction.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/show/{id}', 'show')->name('show');
    });


    /*
    * CMS
    */

    Route::prefix('cms')->name('cms.')->group(function () {
        Route::prefix('home/section-info')->name('home.section_info.')->controller(SectionInfoController::class)->group(function () {
            Route::post('/{page}/{section}', 'store')->name('store');
        });

        Route::prefix('home/banner')->name('home.banner.')->controller(HomeBannerController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/content', 'content')->name('content');
        });
        Route::prefix('home/hero')->name('home.hero.')->controller(HomeHeroController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/content', 'content')->name('content');
        });
        //Home About
        Route::prefix('home/about')->name('home.about.')->controller(HomeAboutController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{id}/show', 'show')->name('show');

            Route::put('/content', 'content')->name('content');
            Route::get('/display', 'display')->name('display');
        });
    });

    /*
    * Chating Route
    */

    Route::controller(ChatController::class)->prefix('chat')->name('chat.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/list', 'list')->name('list');
        Route::post('/send/{receiver_id}', 'send')->name('send');
        Route::get('/conversation/{receiver_id}', 'conversation')->name('conversation');
        Route::get('/room/{receiver_id}', 'room');
        Route::get('/search', 'search')->name('search');
        Route::get('/seen/all/{receiver_id}', 'seenAll');
        Route::get('/seen/single/{chat_id}', 'seenSingle');
    });


    /*
    * Users Access Route
    */

    Route::resource('users', UserController::class);
    Route::resource('my_plan', SubscriptionPlanController::class);

    Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('/status/{id}', 'status')->name('status');
        Route::get('/new', 'new')->name('new.index');
        Route::get('/ajax/new/count', 'newCount')->name('ajax.new.count');
        Route::get('/card/{slug}', 'card')->name('card');
    });
    Route::resource('permissions', PermissionController::class);
    Route::resource('roles', RoleController::class);

    /*
    *settings
    */

    //! Route for Profile Settings
    Route::controller(ProfileController::class)->group(function () {
        Route::get('setting/profile', 'index')->name('setting.profile.index');
        Route::put('setting/profile/update', 'UpdateProfile')->name('setting.profile.update');
        Route::put('setting/profile/update/Password', 'UpdatePassword')->name('setting.profile.update.Password');
        Route::post('setting/profile/update/Picture', 'UpdateProfilePicture')->name('update.profile.picture');
    });

    //! Route for Mail Settings
    Route::controller(MailSettingController::class)->group(function () {
        Route::get('setting/mail', 'index')->name('setting.mail.index');
        Route::patch('setting/mail', 'update')->name('setting.mail.update');

        Route::post('setting/send', 'send')->name('setting.mail.send');
    });

    //! Route for Stripe Settings
    Route::controller(StripeController::class)->prefix('setting/stripe')->name('setting.stripe.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::patch('/update', 'update')->name('update');
    });

    //! Route for Firebase Settings
    Route::controller(FirebaseController::class)->prefix('setting/firebase')->name('setting.firebase.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::patch('/update', 'update')->name('update');
    });

    //! Route for Environment Settings
    Route::controller(EnvController::class)->group(function () {
        Route::get('setting/env', 'index')->name('setting.env.index');
        Route::patch('setting/env', 'update')->name('setting.env.update');
    });

    //! Route for Firebase Settings
    Route::controller(SocialController::class)->prefix('setting/social')->name('setting.social.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::patch('/update', 'update')->name('update');
    });

    //! Route for Stripe Settings
    Route::controller(SettingController::class)->group(function () {
        Route::get('setting/general', 'index')->name('setting.general.index');
        Route::patch('setting/general', 'update')->name('setting.general.update');
    });

    //! Route for Logo Settings
    Route::controller(LogoController::class)->group(function () {
        Route::get('setting/logo', 'index')->name('setting.logo.index');
        Route::patch('setting/logo', 'update')->name('setting.logo.update');
    });

    //! Route for Google Map Settings
    Route::controller(GoogleMapController::class)->group(function () {
        Route::get('setting/google/map', 'index')->name('setting.google.map.index');
        Route::patch('setting/google/map', 'update')->name('setting.google.map.update');
    });

    //! Route for Google Map Settings
    Route::controller(SignatureController::class)->group(function () {
        Route::get('setting/signature', 'index')->name('setting.signature.index');
        Route::patch('setting/signature', 'update')->name('setting.signature.update');
    });

    //! Route for Google Map Settings
    Route::controller(CaptchaController::class)->group(function () {
        Route::get('setting/captcha', 'index')->name('setting.captcha.index');
        Route::patch('setting/captcha', 'update')->name('setting.captcha.update');
    });

    //Ajax settings
    Route::prefix('setting/other')->name('setting.other')->group(function () {
        Route::get('/', [OtherController::class, 'index'])->name('.index');
        Route::get('/mail', [OtherController::class, 'mail'])->name('.mail');
        Route::get('/sms', [OtherController::class, 'sms'])->name('.sms');
        Route::get('/recaptcha', [OtherController::class, 'recaptcha'])->name('.recaptcha');
        Route::get('/pagination', [OtherController::class, 'pagination'])->name('.pagination');
        Route::get('/reverb', [OtherController::class, 'reverb'])->name('.reverb');
        Route::get('/debug', [OtherController::class, 'debug'])->name('.debug');
        Route::get('/access', [OtherController::class, 'access'])->name('.access');
    });

    // Run artisan commands for optimization and cache clearing
    Route::get('/optimize', function () {
        Artisan::call('system:clear-cache');
        return redirect()->back()->with('t-success', 'Message sent successfully');
    })->name('optimize');

    //Filter
    Route::controller(AttributeController::class)->prefix('attribute')->name('attribute.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/show/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::delete('/delete/{id}', 'destroy')->name('destroy');
        Route::get('/status/{id}', 'status')->name('status');
    });

    Route::controller(PropertyController::class)->prefix('property')->name('property.')->group(function () {
        Route::get('index/{attribute_id?}', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/show/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::delete('/delete/{id}', 'destroy')->name('destroy');
        Route::get('/status/{id}', 'status')->name('status');
    });

    //address
    Route::controller(CountryController::class)->prefix('location.country')->name('location.country.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/show/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::delete('/delete/{id}', 'destroy')->name('destroy');

        Route::get('/status/{id}', 'status')->name('status');

        Route::get('/import', 'import')->name('import');
        Route::get('/export', 'export')->name('export');
    });

    /*
    # Quiz
    */
    Route::controller(QuizController::class)->prefix('quiz')->name('quiz.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/show/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::delete('/delete/{id}', 'destroy')->name('destroy');
        Route::get('/status/{id}', 'status')->name('status');
    });

    /*
    # CRUD
    */
    Route::controller(CurdController::class)->prefix('curd')->name('curd.')->group(function () {
        Route::get('/', 'index')->name('index');
    });

    /*
    # Blog
    **/
    Route::controller(BlogController::class)->prefix('blog')->name('blog.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/show/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::delete('/delete/{id}', 'destroy')->name('destroy');
        Route::get('/status/{id}', 'status')->name('status');
    });

    /*
    # Course
    **/
    Route::controller(CourseController::class)->prefix('course')->name('course.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/show/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/update/{id}', 'update')->name('update');
        Route::delete('/delete/{id}', 'destroy')->name('destroy');
        Route::get('/status/{id}', 'status')->name('status');
    });

    Route::controller(CurriculumController::class)->prefix('curriculum')->name('curriculum.')->group(function () {
        Route::post('/store', 'store')->name('store');
        Route::post('/update/{id}', 'update')->name('update');
        Route::get('/delete/{id}', 'destroy')->name('destroy');
    });
});

//livewire
Route::get('livewire/crud', function () {
    return view('backend.layouts.livewire.index');
})->name('livewire.crud.index');

//File-Manager
Route::get('file-manager', [FileManagerController::class, 'index'])->name('file-manager');
Route::post('file-upload', [FileManagerController::class, 'upload']);
Route::delete('file-delete/{file}', [FileManagerController::class, 'delete']);


Route::controller(ReportController::class)->prefix('report')->name('report.')->group(function () {
    Route::get('/users', 'users')->name('users');
});
