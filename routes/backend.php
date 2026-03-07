<?php

use App\Http\Controllers\Web\Backend\Access\PermissionController;
use App\Http\Controllers\Web\Backend\Access\RoleController;
use App\Http\Controllers\Web\Backend\Access\UserController;
use App\Http\Controllers\Web\Backend\ApplyController;
use App\Http\Controllers\Web\Backend\AreaController;
use App\Http\Controllers\Web\Backend\BlogController;
use App\Http\Controllers\Web\Backend\BookingController;
use App\Http\Controllers\Web\Backend\BranchController;
use App\Http\Controllers\Web\Backend\CardController;
use App\Http\Controllers\Web\Backend\CategoryController;
use App\Http\Controllers\Web\Backend\ChatController;
use App\Http\Controllers\Web\Backend\CMS\App\AppDonationScreenController;
use App\Http\Controllers\Web\Backend\CMS\App\AppHomeScreenController;
use App\Http\Controllers\Web\Backend\CMS\App\AppOnboardingFirstController;
use App\Http\Controllers\Web\Backend\CMS\App\AppOnboardingSecondController;
use App\Http\Controllers\Web\Backend\CMS\App\AppOnboardingThirdController;
use App\Http\Controllers\Web\Backend\CMS\App\AppPartyPolicyScreenController;
use App\Http\Controllers\Web\Backend\CMS\App\AppWelcomeScreenController;
use App\Http\Controllers\Web\Backend\CMS\App\AppWhoWeAreCommittedController;
use App\Http\Controllers\Web\Backend\CMS\App\AppWhoWeAreDifferentController;
use App\Http\Controllers\Web\Backend\CMS\App\AppWhoWeAreDiscoverController;
use App\Http\Controllers\Web\Backend\CMS\App\AppWhoWeAreMessionAndVisionController;
use App\Http\Controllers\Web\Backend\CMS\Web\BlogsDetails\BlogsDetailsBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\Blogs\BlogsBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\Blogs\BlogsListController;
use App\Http\Controllers\Web\Backend\CMS\Web\Common\CommonFaqController;
use App\Http\Controllers\Web\Backend\CMS\Web\Common\CommonFooterController;
use App\Http\Controllers\Web\Backend\CMS\Web\Common\CommonHelpController;
use App\Http\Controllers\Web\Backend\CMS\Web\Common\CommonSignInController;
use App\Http\Controllers\Web\Backend\CMS\Web\Common\CommonSubscribeController;
use App\Http\Controllers\Web\Backend\CMS\Web\ContactUs\ContactUsBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\ContactUs\ContactUsFormController;
use App\Http\Controllers\Web\Backend\CMS\Web\ContactUs\ContactUsOfficeController;
use App\Http\Controllers\Web\Backend\CMS\Web\Donate\DonateBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\Donate\DonateFormController;
use App\Http\Controllers\Web\Backend\CMS\Web\EventDetails\EventDetailsRegisterController;
use App\Http\Controllers\Web\Backend\CMS\Web\EventDetails\EventsDetailsBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\EventDetails\EventsDetailsRelatedPostController;
use App\Http\Controllers\Web\Backend\CMS\Web\Events\EventsBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\Events\EventsGalleryController;
use App\Http\Controllers\Web\Backend\CMS\Web\Home\HomeAboutCmcController;
use App\Http\Controllers\Web\Backend\CMS\Web\Home\HomeBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\Home\HomeDonateController;
use App\Http\Controllers\Web\Backend\CMS\Web\Home\HomeEventsController;
use App\Http\Controllers\Web\Backend\CMS\Web\Home\HomeLeadersController;
use App\Http\Controllers\Web\Backend\CMS\Web\Home\HomeNewsController;
use App\Http\Controllers\Web\Backend\CMS\Web\JoinCmc\JoinCmcBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\JoinCmc\JoinCmcFormController;
use App\Http\Controllers\Web\Backend\CMS\Web\MeetOurLeadersDetails\MeetOurLeadersDetailsBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\MeetOurLeadersDetails\MeetOurLeadersDetailsRelatedPostController;
use App\Http\Controllers\Web\Backend\CMS\Web\MeetOurLeaders\MeetOurLeadersBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\MeetOurLeaders\MeetOurLeadersExecutiveController;
use App\Http\Controllers\Web\Backend\CMS\Web\MeetOurLeaders\MeetOurLeadersJoinCmcController;
use App\Http\Controllers\Web\Backend\CMS\Web\MeetOurLeaders\MeetOurLeadersRepresentativeController;
use App\Http\Controllers\Web\Backend\CMS\Web\MeetOurLeaders\MeetOurLeadersSenatorsController;
use App\Http\Controllers\Web\Backend\CMS\Web\NewsDetails\NewsDetailsBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\NewsDetails\NewsDetailsRelatedPostController;
use App\Http\Controllers\Web\Backend\CMS\Web\News\NewsBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\News\NewsListController;
use App\Http\Controllers\Web\Backend\CMS\Web\Polices\PolicesBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\Representative\RepresentativeBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\Representative\RepresentativeFormController;
use App\Http\Controllers\Web\Backend\CMS\Web\Senator\SenatorBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\Senator\SenatorFormController;
use App\Http\Controllers\Web\Backend\CMS\Web\ShopDetails\ShopDetailsBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\ShopDetails\ShopDetailsRelatedPostController;
use App\Http\Controllers\Web\Backend\CMS\Web\Shop\ShopBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\WhoWeAre\WhoWeAreBannerController;
use App\Http\Controllers\Web\Backend\CMS\Web\WhoWeAre\WhoWeAreCommittedController;
use App\Http\Controllers\Web\Backend\CMS\Web\WhoWeAre\WhoWeAreDifferenceController;
use App\Http\Controllers\Web\Backend\CMS\Web\WhoWeAre\WhoWeAreDiscoverValuesController;
use App\Http\Controllers\Web\Backend\CMS\Web\WhoWeAre\WhoWeAreMissionController;
use App\Http\Controllers\Web\Backend\CMS\Web\WhoWeAre\WhoWeAreVissionController;
use App\Http\Controllers\Web\Backend\ContactController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\DonationController;
use App\Http\Controllers\Web\Backend\EventController;
use App\Http\Controllers\Web\Backend\EventRegisterController;
use App\Http\Controllers\Web\Backend\FaqController;
use App\Http\Controllers\Web\Backend\FocusController;
use App\Http\Controllers\Web\Backend\LeaderController;
use App\Http\Controllers\Web\Backend\MemberController;
use App\Http\Controllers\Web\Backend\NewsController;
use App\Http\Controllers\Web\Backend\OrderController;
use App\Http\Controllers\Web\Backend\PageController;
use App\Http\Controllers\Web\Backend\PartyPoliceController;
use App\Http\Controllers\Web\Backend\PartyResourceController;
use App\Http\Controllers\Web\Backend\PhotoGalleryController;
use App\Http\Controllers\Web\Backend\PostController;
use App\Http\Controllers\Web\Backend\ProductController;
use App\Http\Controllers\Web\Backend\Settings\BackupController;
use App\Http\Controllers\Web\Backend\Settings\CaptchaController;
use App\Http\Controllers\Web\Backend\Settings\EnvController;
use App\Http\Controllers\Web\Backend\Settings\FirebaseController;
use App\Http\Controllers\Web\Backend\Settings\GoogleMapController;
use App\Http\Controllers\Web\Backend\Settings\MailSettingController;
use App\Http\Controllers\Web\Backend\Settings\OtherController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SettingController;
use App\Http\Controllers\Web\Backend\Settings\SocialController;
use App\Http\Controllers\Web\Backend\Settings\StripeController;
use App\Http\Controllers\Web\Backend\SocialLinkController;
use App\Http\Controllers\Web\Backend\SubcategoryController;
use App\Http\Controllers\Web\Backend\SubscriberController;
use App\Http\Controllers\Web\Backend\SurveyController;
use App\Http\Controllers\Web\Backend\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get("dashboard", [DashboardController::class, 'index'])->name('dashboard');

/*
* CRUD
*/

Route::controller(PartyPoliceController::class)->prefix('party/police')->name('party.police.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

Route::controller(PartyResourceController::class)->prefix('party/resource')->name('party.resource.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

Route::controller(CategoryController::class)->prefix('category')->name('category.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

Route::controller(SubcategoryController::class)->prefix('subcategory')->name('subcategory.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

Route::controller(PostController::class)->prefix('post')->name('post.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

Route::controller(PageController::class)->prefix('page')->name('page.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
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

Route::controller(NewsController::class)->prefix('news')->name('news.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

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

Route::controller(PhotoGalleryController::class)->prefix('image-gallery')->name('image-gallery.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});
// routes/web.php
Route::delete('/photo-gallery/image/{id}', [PhotoGalleryController::class, 'deleteImage'])
    ->name('admin.photo-gallery.image.delete');

Route::controller(LeaderController::class)->prefix('leader')->name('leader.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');

    Route::post('sort', 'sort')->name('sort');
});

Route::controller(MemberController::class)->prefix('member')->name('member.')->group(function () {
    Route::get('/list/{type?}', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

Route::post('/members/{id}/print-status', [MemberController::class, 'updatePrintStatus'])
    ->name('member.updatePrintStatus');

Route::get('/members/import/create', [MemberController::class, 'createImportCsv'])->name('member.import.create');
Route::post('/members/import', [MemberController::class, 'importCsv'])->name('member.import');
// upload members avatar
Route::post('/members/upload-avatar', [MemberController::class, 'uploadAvatar'])->name('member.uploadAvatar');

Route::controller(EventController::class)->prefix('event')->name('event.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

Route::controller(EventRegisterController::class)->prefix('event/register')->name('event.register.')->group(function () {
    Route::get('/{event_id}', 'index')->name('index');
    Route::get('/create/{event_id}', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

Route::controller(SurveyController::class)->prefix('survey')->name('survey.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');

    Route::get('opinion/delete/{id}', 'removeOpinion')->name('opinion.destroy');
});

Route::controller(AreaController::class)->prefix('area')->name('area.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

Route::controller(BranchController::class)->prefix('branch')->name('branch.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

Route::controller(ProductController::class)->prefix('product')->name('product.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

Route::controller(BookingController::class)->prefix('booking')->name('booking.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
});

Route::controller(OrderController::class)->prefix('order')->name('order.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/show/{id}', 'show')->name('show');
});

Route::controller(TransactionController::class)->prefix('transaction')->name('transaction.')->group(function () {
    Route::get('/{user_id?}', 'index')->name('index');
    Route::get('/show/{id}', 'show')->name('show');
});

Route::controller(DonationController::class)->prefix('donation')->name('donation.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/show/{id}', 'show')->name('show');
});

Route::get('subscriber', [SubscriberController::class, 'index'])->name('subscriber.index');

Route::controller(ContactController::class)->prefix('contact')->name('contact.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/status/{id}', 'status')->name('status');
});

Route::controller(FocusController::class)->prefix('focus')->name('focus.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/show/{id}', 'show')->name('show');
    Route::get('/edit/{id}', 'edit')->name('edit');
    Route::post('/update/{id}', 'update')->name('update');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

Route::controller(ApplyController::class)->prefix('apply')->name('apply.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::delete('/delete/{id}', 'destroy')->name('destroy');
    Route::get('/status/{id}', 'status')->name('status');
});

/*
* CMS
*/

Route::prefix('cms')->name('cms.')->group(function () {

    //Home
    Route::prefix('home/banner')->name('home.banner.')->controller(HomeBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('home/about_cmc')->name('home.about_cmc.')->controller(HomeAboutCmcController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('home/donate')->name('home.donate.')->controller(HomeDonateController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('home/news')->name('home.news.')->controller(HomeNewsController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('home/leaders')->name('home.leaders.')->controller(HomeLeadersController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('home/events')->name('home.events.')->controller(HomeEventsController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    //Who We Are
    Route::prefix('whoweare/banner')->name('whoweare.banner.')->controller(WhoWeAreBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('whoweare/committed')->name('whoweare.committed.')->controller(WhoWeAreCommittedController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::patch('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{id}/status', 'status')->name('status');

        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('whoweare/difference')->name('whoweare.difference.')->controller(WhoWeAreDifferenceController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::patch('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{id}/status', 'status')->name('status');

        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('whoweare/mission')->name('whoweare.mission.')->controller(WhoWeAreMissionController::class)->group(function () {
        Route::get('/', 'index')->name('index');

        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('whoweare/vission')->name('whoweare.vission.')->controller(WhoWeAreVissionController::class)->group(function () {
        Route::get('/', 'index')->name('index');

        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('whoweare/discover_values')->name('whoweare.discover_values.')->controller(WhoWeAreDiscoverValuesController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::patch('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{id}/status', 'status')->name('status');

        Route::put('/content', 'content')->name('content');
    });

    //Meet Our Leaders
    Route::prefix('meeetourleaders/banner')->name('meeetourleaders.banner.')->controller(MeetOurLeadersBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('meeetourleaders/executive')->name('meeetourleaders.executive.')->controller(MeetOurLeadersExecutiveController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('meeetourleaders/join_cmc')->name('meeetourleaders.join_cmc.')->controller(MeetOurLeadersJoinCmcController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('meeetourleaders/representative')->name('meeetourleaders.representative.')->controller(MeetOurLeadersRepresentativeController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('meeetourleaders/senators')->name('meeetourleaders.senators.')->controller(MeetOurLeadersSenatorsController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    //Leader Details
    Route::prefix('meeetourleadersdetails/banner')->name('meeetourleadersdetails.banner.')->controller(MeetOurLeadersDetailsBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('meeetourleadersdetails/relatedpost')->name('meeetourleadersdetails.relatedpost.')->controller(MeetOurLeadersDetailsRelatedPostController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    // Polices
    Route::prefix('polices/banner')->name('polices.banner.')->controller(PolicesBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    // Events
    Route::prefix('events/banner')->name('events.banner.')->controller(EventsBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });
    Route::prefix('events/gallery')->name('events.gallery.')->controller(EventsGalleryController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    // Events Details
    Route::prefix('events_details/banner')->name('events_details.banner.')->controller(EventsDetailsBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('events_details/relatedpost')->name('events_details.relatedpost.')->controller(EventsDetailsRelatedPostController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('events_details/register')->name('events_details.register.')->controller(EventDetailsRegisterController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    // News
    Route::prefix('news/banner')->name('news.banner.')->controller(NewsBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('news/list')->name('news.list.')->controller(NewsListController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    // News Details
    Route::prefix('news_details/banner')->name('news_details.banner.')->controller(NewsDetailsBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('news_details/relatedpost')->name('news_details.relatedpost.')->controller(NewsDetailsRelatedPostController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    // Blogs
    Route::prefix('blogs/banner')->name('blogs.banner.')->controller(BlogsBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('blogs/list')->name('blogs.list.')->controller(BlogsListController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    // Blogs Details
    Route::prefix('blogs_details/banner')->name('blogs_details.banner.')->controller(BlogsDetailsBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    // Shop
    Route::prefix('shop/banner')->name('shop.banner.')->controller(ShopBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    // Shop Details
    Route::prefix('shop_details/banner')->name('shop_details.banner.')->controller(ShopDetailsBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('shop_details/relatedpost')->name('shop_details.relatedpost.')->controller(ShopDetailsRelatedPostController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    // contact us
    Route::prefix('contact_us/banner')->name('contact_us.banner.')->controller(ContactUsBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });
    Route::prefix('contact_us/form')->name('contact_us.form.')->controller(ContactUsFormController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });
    Route::prefix('contact_us/office')->name('contact_us.office.')->controller(ContactUsOfficeController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    // join cmc
    Route::prefix('join_cmc/banner')->name('join_cmc.banner.')->controller(JoinCmcBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });
    Route::prefix('join_cmc/form')->name('join_cmc.form.')->controller(JoinCmcFormController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    // Donate
    Route::prefix('donate/banner')->name('donate.banner.')->controller(DonateBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });
    Route::prefix('donate/form')->name('donate.form.')->controller(DonateFormController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    //Common
    Route::prefix('common/subscribe')->name('common.subscribe.')->controller(CommonSubscribeController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('common/footer')->name('common.footer.')->controller(CommonFooterController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('common/faq')->name('common.faq.')->controller(CommonFaqController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('common/help')->name('common.help.')->controller(CommonHelpController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('common/signin')->name('common.signin.')->controller(CommonSignInController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    /* Route::prefix('home/banner')->name('home.banner.')->controller(HomeBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::patch('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{id}/status', 'status')->name('status');

        Route::put('/content', 'content')->name('content');
    }); */

    //representative
    Route::prefix('representative/banner')->name('representative.banner.')->controller(RepresentativeBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('representative/form')->name('representative.form.')->controller(RepresentativeFormController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    //senators
    Route::prefix('senator/banner')->name('senator.banner.')->controller(SenatorBannerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    Route::prefix('senator/form')->name('senator.form.')->controller(SenatorFormController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/content', 'content')->name('content');
    });

    //app
    Route::prefix('app/welcome/screen')->name('app.welcome.screen.')->controller(AppWelcomeScreenController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}/show', 'show')->name('show');

        Route::put('/content', 'content')->name('content');
        Route::get('/display', 'display')->name('display');
    });

    Route::prefix('app/home/screen')->name('app.home.screen.')->controller(AppHomeScreenController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}/show', 'show')->name('show');

        Route::put('/content', 'content')->name('content');
        Route::get('/display', 'display')->name('display');
    });

    Route::prefix('app/donation/screen')->name('app.donation.screen.')->controller(AppDonationScreenController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}/show', 'show')->name('show');

        Route::put('/content', 'content')->name('content');
        Route::get('/display', 'display')->name('display');
    });

    Route::prefix('app/party_policy/screen')->name('app.party_policy.screen.')->controller(AppPartyPolicyScreenController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}/show', 'show')->name('show');

        Route::put('/content', 'content')->name('content');
        Route::get('/display', 'display')->name('display');
    });

    Route::prefix('app/onboarding/first')->name('app.onboarding.first.')->controller(AppOnboardingFirstController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}/show', 'show')->name('show');

        Route::put('/content', 'content')->name('content');
        Route::get('/display', 'display')->name('display');
    });

    Route::prefix('app/onboarding/second')->name('app.onboarding.second.')->controller(AppOnboardingSecondController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}/show', 'show')->name('show');

        Route::put('/content', 'content')->name('content');
        Route::get('/display', 'display')->name('display');
    });

    Route::prefix('app/onboarding/third')->name('app.onboarding.third.')->controller(AppOnboardingThirdController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}/show', 'show')->name('show');

        Route::put('/content', 'content')->name('content');
        Route::get('/display', 'display')->name('display');
    });

    Route::prefix('app/who_we_are/committed')->name('app.who_we_are.committed.')->controller(AppWhoWeAreCommittedController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}/show', 'show')->name('show');

        Route::put('/content', 'content')->name('content');
        Route::get('/display', 'display')->name('display');
    });

    Route::prefix('app/who_we_are/different')->name('app.who_we_are.different.')->controller(AppWhoWeAreDifferentController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/show', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::patch('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{id}/status', 'status')->name('status');

        Route::put('/content', 'content')->name('content');
        Route::get('/display', 'display')->name('display');
    });

    Route::prefix('app/who_we_are/mession_and_vision')->name('app.who_we_are.mession_and_vision.')->controller(AppWhoWeAreMessionAndVisionController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/show', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::patch('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{id}/status', 'status')->name('status');

        Route::put('/content', 'content')->name('content');
        Route::get('/display', 'display')->name('display');
    });

    Route::prefix('app/who_we_are/discover')->name('app.who_we_are.discover.')->controller(AppWhoWeAreDiscoverController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/show', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::patch('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{id}/status', 'status')->name('status');

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
Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
    Route::get('/new', 'new')->name('new.index');
    Route::get('/ajax/new/count', 'newCount')->name('ajax.new.count');
    Route::get('card/print/{slug}', [CardController::class, 'print'])->name('card.print');
});

Route::get('manage/member', [CardController::class, 'bulkPrint'])->name('manage.member');

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
});

//! Route for Stripe Settings
Route::controller(BackupController::class)->prefix('setting/backup')->name('setting.backup.')->group(function () {
    Route::get('/database', 'databaseBackup')->name('backup');
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

//! Route for Firebase Settings
Route::controller(SocialController::class)->prefix('setting/social')->name('setting.social.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::patch('/update', 'update')->name('update');
});

//! Route for Environment Settings
Route::controller(EnvController::class)->group(function () {
    Route::get('setting/env', 'index')->name('setting.env.index');
    Route::patch('setting/env', 'update')->name('setting.env.update');
});

//! Route for Stripe Settings
Route::controller(SettingController::class)->group(function () {
    Route::get('setting/general', 'index')->name('setting.general.index');
    Route::patch('setting/general', 'update')->name('setting.general.update');
});

//! Route for Google Map Settings
Route::controller(GoogleMapController::class)->group(function () {
    Route::get('setting/google/map', 'index')->name('setting.google.map.index');
    Route::patch('setting/google/map', 'update')->name('setting.google.map.update');
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
});
