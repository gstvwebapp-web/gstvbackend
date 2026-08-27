<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Category;
use App\Models\Webstory;
use App\Models\News;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;

class ViewexportController extends Controller
{
    public function viewexport()
    {
        return view('user.viewalllist');
        $users = DB::table('users')
            ->select('users.*', 'roles.title as roleTitle')
            ->leftJoin('roles', 'roles.id', '=', 'users.roleID')
            ->get();

        // $news = DB::table('news')
        //     ->select(
        //         'news.viewer',
        //         'news.updated_at','news.created_at',
        //         'news.title',
        //         'u1.name as authorName',
        //         DB::raw('(SELECT GROUP_CONCAT(title SEPARATOR ", ") FROM categories WHERE FIND_IN_SET(categories.id, news.catID)) as categoryNames')
        //     )
        //     ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
        //     ->whereDate('news.created_at', Carbon::today())
        //     ->orderBy('news.created_at', 'DESC') // Order news by authorName ascending
        //     ->get();
        $news = DB::table('news')
            ->select(
                'news.viewer',
                'news.updated_at',
                'news.created_at',
                'news.title',
                'u1.name as authorName',
                DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id SEPARATOR ", ") as categoryNames')
            )
            ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
            ->leftJoin('news_category as nc', 'news.id', '=', 'nc.news_id')
            ->leftJoin('categories', 'nc.category_id', '=', 'categories.id')
            ->whereDate('news.created_at', Carbon::today())
            ->groupBy('news.id', 'news.viewer', 'news.updated_at', 'news.created_at', 'news.title', 'u1.name')
            ->orderBy('news.created_at', 'DESC')
            ->get();

        $webstories = DB::table('webstory')
            ->select(
                'webstory.viewer',
                'webstory.updated_at','webstory.created_at',
                'webstory.title',
                'u2.name as authorName',
                DB::raw('"Webstory" as categoryNames')
            )
            ->leftJoin('users as u2', 'webstory.userID', '=', 'u2.id')
            ->whereDate('webstory.created_at', Carbon::today())
            ->orderBy('webstory.created_at', 'DESC') // Order webstories by authorName ascending
            ->get();

        // Merge news and webstories collections
        $data = collect();
        $data = $data->merge($news);
        $data = $data->merge($webstories);

        // Group data by authorName
        $groupedData = $data->groupBy('authorName');

        // Sort each group by authorName in ascending order
        $sortedData = $groupedData->sortBy('authorName')->values()->all();

        // Flatten the sorted data to get a single array
        $data = collect($sortedData)->flatten(1)->all();
        return view('user.viewall', compact('data', 'users'));
    }    

    public function viewexportfetchData11(Request $request)
    {
        $columns = ['id', 'authorName', 'title','categoryNames','viewer', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');
        $status = $request->input('status', 'Active'); // Default to Active

     
        $query = DB::table('users')
                ->select('users.*', 'role.title as title')
                ->leftJoin('roles AS role', 'role.id', '=', 'users.roleID');

        // Apply search filter if any search value is provided
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('users.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('role.name', 'like', '%' . $searchValue . '%');
            });
        }

        // Get the total number of filtered records
        $totalRecords = $query->count();

        // Apply pagination
        $filteredData = $query->skip($start)
            ->take($length)
            ->get();
            $counter = $start + 1;
        // Map the data to add the 'action' column and other transformations
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            // Assign row number and increment counter
            $row->rowNumber = $counter++;
            // Generate the image URL for the category icon
            // Add the action button with the URL for viewing the category
            $row->name = '<a href="' . url('/user/profile', $row->id) . '">' . htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8') . '</a>';
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/user/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/user/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/user/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/user/destroy', $row->id) . '" method="POST" style="display: none;">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
            </form>';

            return $row;
        });

        // Return DataTable response
        return response()->json([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords, // Send the total filtered records count
            "data" => $formattedData,
        ]);
    }
    
    public function viewexportfetchData(Request $request)
    {
        $columns = ['id', 'authorName', 'title', 'categoryNames', 'viewer', 'date'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'authorName';
        $orderDirection = $request->input('order.0.dir', 'asc');

        // Get news data
        $news = DB::table('news')
            ->select(
                'news.id',
                'news.viewer',
                'news.updated_at',
                'news.created_at as date',
                'news.title',
                'u1.name as authorName',
                DB::raw('GROUP_CONCAT(DISTINCT categories.title ORDER BY categories.id SEPARATOR ", ") as categoryNames')
            )
            ->leftJoin('users as u1', 'news.userID', '=', 'u1.id')
            ->leftJoin('news_category as nc', 'news.id', '=', 'nc.news_id')
            ->leftJoin('categories', 'nc.category_id', '=', 'categories.id')
            ->whereDate('news.created_at', Carbon::today())
            ->groupBy('news.id', 'news.viewer', 'news.updated_at', 'news.created_at', 'news.title', 'u1.name');

        // Get webstory data
        $webstory = DB::table('webstory')
            ->select(
                'webstory.id',
                'webstory.viewer',
                'webstory.updated_at',
                'webstory.created_at as date',
                'webstory.title',
                'u2.name as authorName',
                DB::raw('"Webstory" as categoryNames')
            )
            ->leftJoin('users as u2', 'webstory.userID', '=', 'u2.id')
            ->whereDate('webstory.created_at', Carbon::today());

        // Merge the two queries using UNION
        $combined = $news->unionAll($webstory);

        // Convert to subquery to allow ordering/limit
        $data = DB::table(DB::raw("({$combined->toSql()}) as merged"))
            ->mergeBindings($combined)
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('title', 'like', "%$searchValue%")
                    ->orWhere('authorName', 'like', "%$searchValue%")
                    ->orWhere('categoryNames', 'like', "%$searchValue%");
                });
            })
            ->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        // Count total after filtering
        $totalRecords = DB::table(DB::raw("({$combined->toSql()}) as merged"))
            ->mergeBindings($combined)
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('title', 'like', "%$searchValue%")
                    ->orWhere('authorName', 'like', "%$searchValue%")
                    ->orWhere('categoryNames', 'like', "%$searchValue%");
                });
            })
            ->count();

        // Format data
        $counter = $start + 1;
        $formattedData = $data->map(function ($row) use (&$counter) {
            $row->rowNumber = $counter++;
            $row->date = \Carbon\Carbon::parse($row->date)->format('d-m-Y h:i A');
            return $row;
        });

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $formattedData,
        ]);
    }


}