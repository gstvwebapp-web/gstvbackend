<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Module;

class SidebarServiceProvider extends ServiceProvider
{
    public function boot()
    { 
        View::composer('include.sidebar', function ($view) {
            $user = Auth::user();
            
            if ($user && $user->moduleID) {
                $ids = explode(',', $user->moduleID);
                $modules = Module::where('status', 'Active')
                                ->whereIn('id', $ids)
                                ->orderBy('id','ASC')
                                ->get();
            } else {
                $modules = collect(); // empty collection to avoid errors in view
            }
    
            $view->with('sidebar', $modules);
        });
    }

    public function register()
    {
        //
    }
}
