<?php

namespace database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'home',
                'title' => 'home page',
                'description' => 'This is the home page description.',
                'image' => 'images/logo.png',
            ],

        ];

        foreach ($pages as $page) {
            $page['slug'] = Str::lower($page['slug']);
            $page['title'] = Str::lower($page['title']);
            $page['image'] = 'glide/'.Str::lower($page['image']).'?width=1200';
            $page['updated_at'] = now();

            $existingPage = DB::table('pages')->where('slug', $page['slug'])->first();

            if ($existingPage) {
                $page['created_at'] = $existingPage->created_at ?? now();
            } else {
                $page['created_at'] = now();
            }

            DB::table('pages')->updateOrInsert(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
