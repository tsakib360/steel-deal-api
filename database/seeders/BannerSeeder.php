<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $banners=[
          ['title' => 'banner1','image'=>'https://esterkth.sirv.com/banners/jon-parry-C8eSYwQkwHw-unsplash.jpg'],
          ['title' => 'banner2','image'=>'https://esterkth.sirv.com/banners/kaung-myat-min-n39-bU4c5kQ-unsplash.jpg'] ,
          ['title' => 'banner3','image'=>'https://esterkth.sirv.com/banners/marcel-l-PQewPJqNKwQ-unsplash.jpg'],
          ['title' => 'banner4','image'=>'https://esterkth.sirv.com/banners/rene-porter-hteGzeFuB7w-unsplash.jpg']
        ];
        foreach ($banners as $banner) {
            $newBanner = Banner::create([
                'title' => $banner['title']
//                'categoryId' => $banner['redirectsTo']
            ]);
            $newBanner->addMediaFromUrl($banner['image'])->toMediaCollection('banner');
        }
    }
}
