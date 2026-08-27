<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class CheckRolePermission
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $ids = $user->roleID;
        $roles = Role::where('id', $ids)->first();
        $checkurl = explode('/',$request->path());

        $styles = '';

        if ($roles->addRole == 0) {
            $styles .= '.addCls { display:none !important; } ';
        }

        if ($roles->editRole == 0) {
            $styles .= '.editCls { display:none !important; } ';
        }

        if ($roles->deleteRole == 0) {
            $styles .= '.delCls { display:none !important; } ';
        }

        if ($roles->publishRole == 0) {
            $styles .= '.showhideCls { display:none !important; } ';
        }
        // Implement your permission check logic
        if(!empty($checkurl[1])) {
            
            if (($roles->addRole != 1 && $checkurl[1]=='add') || 
            ($roles->editRole != 1 && $checkurl[1]=='edit') || 
            ($roles->publishRole != 1 && $checkurl[1]=='updateStatus') ||
            ($roles->deleteRole != 1 && $checkurl[1]=='destroy'))
            {
                abort(403, 'Unauthorized: You do not have permission to access this page.');
            }
        }
        $response = $next($request);

        // Modify the response content to add styles to the <head> tag
        $content = $response->getContent();

        // Find the position of the </head> tag
        $position = strripos($content, '</head>');

        if ($position !== false) {
            // Insert the styles before the </head> tag
            $content = substr_replace($content, '<style>' . $styles . '</style>', $position, 0);
            $response->setContent($content);
        }

        return $response;

        //return $next($request);
    }
}
