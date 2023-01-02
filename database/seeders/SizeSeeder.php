<?php

namespace Database\Seeders;

use App\Models\Size;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sizes= [
           ['size' =>'15*5'],
            ['size' => '25*5'],
            ['size' => '35*5'],
            ['size' => '45*5'],
            ['size' =>'55*5']
        ];

          foreach ($sizes as $size){
              Size::create(['size'=>$size['size']]);
          }
    }
}
