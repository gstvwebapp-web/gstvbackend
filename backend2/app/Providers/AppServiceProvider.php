<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        /*view()->composer('*', function ($view) {
            if (auth()->check()) {
                $sidebar = \App\Models\Module::where('status', 'Active')
                    ->where('parent_id', 0)
                    ->orderBy('id', 'asc')
                    ->get();
                
                // Get submenus for each parent
                foreach ($sidebar as $menu) {
                    $menu->submenus = \App\Models\Module::where('status', 'Active')
                        ->where('parent_id', $menu->id)
                        ->orderBy('id', 'asc')
                        ->get();
                }
                
                $view->with('sidebar', $sidebar);
            }
        });*/
        view()->composer('*', function ($view) {
          if (auth()->check()) {
              $user = auth()->user();

              // Get allowed module IDs
              $ids = $user->moduleID ? explode(',', $user->moduleID) : [];

              // Get parent menus (only allowed)
              $sidebar = \App\Models\Module::where('status', 'Active')
                  ->where('parent_id', 0)
                  ->when(!empty($ids), function ($query) use ($ids) {
                      $query->whereIn('id', $ids);
                  })
                  ->orderBy('id', 'asc')
                  ->get();

              // Get submenus (only allowed)
              foreach ($sidebar as $menu) {
                  $menu->submenus = \App\Models\Module::where('status', 'Active')
                      ->where('parent_id', $menu->id)
                      ->when(!empty($ids), function ($query) use ($ids) {
                          $query->whereIn('id', $ids);
                      })
                      ->orderBy('id', 'asc')
                      ->get();
              }

              $view->with('sidebar', $sidebar);
          }
      });
        if (app()->environment('local', 'staging', 'production')) {
            DB::listen(function ($query) {
                if ($query->time > 2000) { // log only queries slower than 1 second
                    Log::channel('slowquery')->info('Slow Query Detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time,
                        'url' => Request::fullUrl(),
                    ]);
                }
            });
        }
    }
}
