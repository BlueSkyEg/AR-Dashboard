<?php

use App\Http\Controllers\CarouselSlideController;
use App\Http\Controllers\DonationCore\DonationFormController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\Post\BlogController;
use App\Http\Controllers\Post\CareerController;
use App\Http\Controllers\Post\PostCategoryController;
use App\Http\Controllers\Post\ProjectController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Modules\PostCore\Project\Services\ProjectService;
use App\Modules\PostCore\Career\Services\CareerService;


Route::post('/create-blog-categories', function () {
    $categories = json_decode(file_get_contents('db/blog-categories.json'), true)['data'];

    foreach ($categories as $category) {
        \App\Modules\PostCore\PostCategory\PostCategory::create([
            'post_type' => \App\Enums\PostTypeEnum::BLOG->value,
            'name' => $category['name'],
            'slug' => $category['slug']
        ]);
    }
    return 'Categories Created Successfully';
});

Route::post('/create-blogs', function () {
    $blogs = json_decode(file_get_contents('db/blogs.json'), true);
    foreach (array_reverse($blogs) as $blogObj) {
        $featuredImage = \App\Modules\Image\Image::create([
            'src' => date('Y/') . date('m/') . Str::afterLast($blogObj['featuredImage']['src'], '/'),
            'alt_text' => $blogObj['featuredImage']['alt'],
        ]);

        $post = \App\Modules\PostCore\Post\Post::create([
            'title' => $blogObj['title'],
            'excerpt' => null
        ]);

        $post->created_at = \Carbon\Carbon::parse($blogObj['date'])->toDateTimeString();
        $post->save();

        $post->blog()->create([
            'slug' => $blogObj['slug'],
            'location' => $blogObj['location'],
            'implementation_date' => \Carbon\Carbon::parse($blogObj['implementationDate'])->toDateTimeString(),
            'donation_form_id' => $blogObj['donationForm']['id'],
            'featured_image_id' => $featuredImage->id,
        ]);

        $contents = [];
        foreach ($blogObj['content'] as $content) {
            $contents[] = [
                'heading' => $content['heading'],
                'description' => $content['description'],
            ];
        }
        $post->contents()->createMany($contents);

        $gallery = [];
        foreach ($blogObj['gallery'] as $image) {
            $gallery[] = [
                'src' => date('Y/') . date('m/') . Str::afterLast($image['src'], '/'),
                'alt_text' => $image['alt']
            ];
        }
        $post->images()->createMany($gallery);

        $categoriesSlug = [];
        foreach ($blogObj['categories'] as $category) {
            $categoriesSlug[] = $category['slug'];
        }
        $categoriesIds = \App\Modules\PostCore\PostCategory\PostCategory::whereIn('slug', $categoriesSlug)->pluck('id');

        $post->categories()->attach($categoriesIds);
    }

    return 'Blogs Created Successfully';
});

Route::post('/create-project-categories', function () {
    $categories = json_decode(file_get_contents('db/project-categories.json'), true)['data'];

    foreach ($categories as $category) {
        \App\Modules\PostCore\PostCategory\PostCategory::create([
            'post_type' => \App\Enums\PostTypeEnum::PROJECT->value,
            'name' => $category['name'],
            'slug' => $category['slug']
        ]);
    }
    return 'Categories Created Successfully';
});

// Route::post('/create-projects', [ProjectService::class, 'createProjectsFromJsonFile']);

Route::post('/create-projects', function () {
    $projects = json_decode(file_get_contents('db/projects_copy.json'), true);
    foreach (array_reverse($projects) as $projectObj) {

        $imageName = date('Y/') . date('m/') . Str::afterLast($projectObj['featured_image']['src'], '/');
        $imageContents = file_get_contents($projectObj['featured_image']['src']);
        \Illuminate\Support\Facades\Storage::put("images/$imageName", $imageContents);

        $featuredImage = \App\Modules\Image\Image::create([
            'src' => $imageName,
            'alt_text' => $projectObj['featured_image']['alt_text']
        ]);

        $post = \App\Modules\PostCore\Post\Post::create([
            'title' => $projectObj['title'],
            'excerpt' => $projectObj['excerpt']
        ]);

        $post->project()->create([
            'slug' => $projectObj['slug'],
            'donation_form_id' => $projectObj['donation_form_id'],
            'featured_image_id' => $featuredImage->id,
        ]);

        $contents = [];
        foreach ($projectObj['contents'] as $index => $content) {

            $contentBody = $content['body'];

            if ($content['type'] === 'list') {
                $contentBody = implode('$$$', $contentBody);
            } elseif ($content['type'] === 'image') {
                $imageName = date('Y/') . date('m/') . Str::afterLast($content['body']['src'], '/');
                $imageContents = file_get_contents($content['body']['src']);
                \Illuminate\Support\Facades\Storage::put("images/$imageName", $imageContents);

                $image = \App\Modules\Image\Image::create([
                    'src' => $imageName,
                    'alt_text' => $content['body']['alt_text']
                ]);

                $contentBody = $image->id;
            }

            $contents[] = [
                'type' => $content['type'],
                'body' => $contentBody,
                'order' => $index
            ];
        }
        $post->contents()->createMany($contents);

        $categoryId = \App\Modules\PostCore\PostCategory\PostCategory::where('slug', $projectObj['categories'][0]['slug'])->where('post_type', \App\Enums\PostTypeEnum::PROJECT->value)->pluck('id');

        $post->categories()->attach($categoryId);
    }

    return 'Projects Created Successfully';
});

//Route::post('/create-careers', [CareerService::class, 'createCareersFromJsonFile']);

Route::post('/create-careers', function () {
    $careers = json_decode(file_get_contents('db/careers.json'), true)['data'];
    foreach (array_reverse($careers) as $careerObj) {
        $post = \App\Modules\PostCore\Post\Post::create([
            'title' => $careerObj['title'],
            'excerpt' => $careerObj['content'][0]['description']
        ]);

        $post->career()->create([
            'slug' => $careerObj['slug']
        ]);

        $contents = [];
        foreach ($careerObj['content'] as $content) {
            if ($content['heading'] !== 'summary') {
                $contents[] = [
                    'heading' => $content['heading'],
                    'description' => implode(' ', $content['description'])
                ];
            }
        }
        $post->contents()->createMany($contents);
    }

    return 'Careers created successfully';
});

Route::post('/create-home-slider', function () {
    $slides = json_decode(file_get_contents('db/home-slider.json'), true);
    foreach (array_reverse($slides) as $slide) {
        $image = \App\Modules\Image\Image::create([
            'src' => date('Y/') . date('m/') . Str::afterLast($slide['image']['src'], '/'),
            'alt_text' => $slide['summary']
        ]);
        \App\Modules\CarouselSlide\CarouselSlide::create([
            'title' => $slide['summary'],
            'description' => $slide['description'],
            'destination_label' => 'Donate Now',
            'destination_type' => 'project_category',
            'destination_slug' => Str::afterLast($slide['destination'], '/'),
            'image_id' => $image->id,
            'carousel_type' => \App\Enums\CarouselTypeEnum::Home_Carousel->value
        ]);
    }

    return 'Home Slider Created Successfully';
});


/* --------------Blogs------------ */
Route::get('/blogs/categories', [PostCategoryController::class, 'getBlogCategories']);
Route::get('/blogs/related/{blogSlug}', [BlogController::class, 'getRelatedBlogs']);
Route::get('/blogs/gallery', [BlogController::class, 'getBlogsGallery']);
Route::get('/blogs/search/{searchTerm}', [BlogController::class, 'searchBlogs']);
Route::get('/blogs', [BlogController::class, 'getPublishedBlogs']);
Route::get('/blogs/{blogSlug}', [BlogController::class, 'getPublishedBlog']);


/* --------------Projects------------ */
Route::get('/projects/categories', [PostCategoryController::class, 'getProjectCategories']);
Route::get('/projects/related/{projectSlug}', [ProjectController::class, 'getRelatedProjects']);
Route::get('/projects/search/{searchTerm}', [ProjectController::class, 'searchProjects']);
Route::get('/projects', [ProjectController::class, 'getPublishedProjects']);
Route::get('/projects/{projectSlug}', [ProjectController::class, 'getPublishedProject']);


/* --------------Careers------------ */
Route::get('/careers', [CareerController::class, 'getPublishedCareers']);
Route::get('/careers/{careerSlug}', [CareerController::class, 'getPublishedCareer']);


/* --------------Carousels------------ */
Route::get('/carousels/{carouselType}', [CarouselSlideController::class, 'getCarousel']);


/* --------------Donation Forms------------ */
Route::get('/donation-forms/home', [DonationFormController::class, 'getHomePageDonationForm']);


/* --------------Mobile------------ */
Route::get('/mobile/home', [MobileController::class, 'getMobileHomeScreenData']);
