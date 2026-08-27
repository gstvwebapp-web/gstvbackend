<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\News;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use App\Imports\NewsDataImport; 
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Tag;
use Carbon\Carbon;

class CsvfileuploadController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('csvfile.add');
    }

    public function NewsCSVupload(Request $request)
    {
        $rules = [
            'csvfile' => 'required|file|mimes:csv,xls,xlsx'
        ];

        $customMessages = [
            'csvfile.required' => 'The File is required.',
            'csvfile.file' => 'The uploaded file must be a CSV, XLS, XLSX.',
            'csvfile.mimes' => 'Only CSV, XLS, XLSX file formats are allowed.',
        ];
    
        $request->validate($rules, $customMessages); 

        $file = $request->file('csvfile');
        $timestamp = Carbon::now();
        // Handle CSV file
        if ($file->getClientOriginalExtension() === 'csv') {
            $data = Excel::toCollection(new NewsDataImport, $file);
            foreach ($data[0] as $row) {
                if($row[0]!='Title') {
                    $cateName = explode(',',$row[3]);
                    $tags = implode('","', array_map('trim', $cateName));
                    $tagsval = ["$tags"];
                    $tagsArray = explode('","', trim($tags, '"'));
                    $category = DB::table('categories')->select('id')->whereIn('title', $tagsArray)->get();
                    $categoryIds = $category->pluck('id')->implode(',');
                    
                    $secondRow['title'][] = $row[0];
                    $secondRow['englistitle'][] = $row[1];
                    $secondRow['description'][] = $row[2];
                    $secondRow['category'][] = $categoryIds;
                    $secondRow['imageURL'][] = $row[4];
                }
            } 
            for($i=1;$i<count($secondRow);$i++)
             {
                 $datas[]=  [
                     'title' => $secondRow['title'][$i], 
                     'userID' => Auth::user()->id, 
                     'assignUserID' => Auth::user()->id, 
                     'englishTitle' => $secondRow['englistitle'][$i],
                     'slug' => Str::slug($secondRow['englistitle'][$i], '-'),
                     'description' => $secondRow['description'][$i],
                     'catID' => $secondRow['category'][$i],
                     'imageURL' => $secondRow['imageURL'][$i],
                     'status' => 'Inactive',
                 ];
             }
             News::insert($datas);
        }
        // Handle Excel file (xls or xlsx)
        if (in_array($file->getClientOriginalExtension(), ['xls', 'xlsx'])) {
            $data = Excel::toCollection(new NewsDataImport, $file);
            foreach ($data[0] as $row) {
                if($row[0]!='Title') {
                    $cateName = explode(',',$row[3]);
                    $tags = implode('","', array_map('trim', $cateName));
                    $tagsval = ["$tags"];
                    $tagsArray = explode('","', trim($tags, '"'));
                    $category = DB::table('categories')->select('id')->whereIn('title', $tagsArray)->get();
                    $categoryIds = $category->pluck('id')->implode(',');
                    
                    $secondRow['title'][] = $row[0];
                    $secondRow['englistitle'][] = $row[1];
                    $secondRow['description'][] = $row[2];
                    $secondRow['category'][] = $categoryIds;
                }
            }
            for($i=0;$i<count($secondRow);$i++)
            {
                $datas[]=  [
                    'title' => $secondRow['title'][$i], 
                    'userID' => Auth::user()->id, 
                    'assignUserID' => Auth::user()->id, 
                    'englishTitle' => $secondRow['englistitle'][$i],
                    'slug' => Str::slug($secondRow['englistitle'][$i], '-'),
                    'description' => $secondRow['description'][$i],
                    'catID' => $secondRow['category'][$i],
                    'status' => 'Inactive',
                    'metatitle' => $secondRow['title'][$i],
                    'metadesc' => $secondRow['title'][$i],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
            News::insert($datas);
        }
        return redirect('/news/')->with('success', 'CSV/Excel File data has been successfully stored');
    }

}

