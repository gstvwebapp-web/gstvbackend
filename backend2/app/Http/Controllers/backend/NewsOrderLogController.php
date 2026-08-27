<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class NewsOrderLogController extends Controller
{

    public function index()
    {
        return view('newsorderlog.list');
    }
    public function fetchData12(Request $request)
    {
        $date = $request->logdate ?? date('Y-m-d');

        $file = storage_path("logs/newsorder-$date.log");

        $data = [];

        if (file_exists($file)) {

            $lines = file($file);

            $i = 1;

            foreach ($lines as $line) {

                preg_match('/\[(.*?)\]/', $line, $timeMatch);
                $time = $timeMatch[1] ?? '';

                $message = trim(substr($line, strpos($line, ']') + 1));
                
                // extract JSON from log
                preg_match('/\{.*\}/', $line, $jsonMatch);

                $userId = null;

                if (!empty($jsonMatch[0])) {
                    $jsonData = json_decode($jsonMatch[0], true);

                    if (isset($jsonData['userID'])) {
                        $userId = $jsonData['userID'];
                    }
                }

                $username = '';

                if ($userId) {
                    $user = User::select('name')->where('id', $userId)->first();

                    if ($user) {
                        $username = ucfirst($user->name);
                       
                    } else {
                        $username = '';
                    }
                }

                $data[] = [
                    'rowNumber' => $i++,
                    'username' => $username,
                    'message' => $message,
                    'time' => $time
                ];
            }
        }

        $search = $request->input('search.value');

        if (!empty($search)) {
            $data = array_filter($data, function ($row) use ($search) {
                return stripos($row['time'], $search) !== false ||
                    stripos($row['message'], $search) !== false;
            });
        }

        $data = array_values($data); // reset index

        return response()->json([
            "draw" => intval($request->draw),
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $data
        ]);
    } 
    public function fetchData224(Request $request)
    {
        $logPath = storage_path('logs');
        $files = glob($logPath . '/newsorder-*.log');

        rsort($files); // latest file first

        $data = [];
        $i = 1;

        foreach ($files as $file) {

            $filename = basename($file);

             $url = url('/newsorderlog/view/' . $filename);

            $data[] = [
                'rowNumber' => $i++,
                'message' => '<a href="'.$url.'" target="_blank">'.$filename.'</a>'
            ];
        }

        return response()->json([
            "draw" => intval($request->draw),
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $data
        ]);
    }
    public function fetchData(Request $request)
    {
        $logPath = storage_path('logs');
        $files = glob($logPath . '/newsorder-*.log');

        rsort($files); // latest first

        $data = [];
        $i = 1;

        foreach ($files as $file) {

            $filename = basename($file);

            // extract date from filename
            preg_match('/newsorder-(\d{4}-\d{2}-\d{2})\.log/', $filename, $match);

            $date = isset($match[1]) ? date('d-m-Y', strtotime($match[1])) : '';

            $url = url('/newsorderlog/view/' . $filename);

            $data[] = [
                'rowNumber' => $i++,
                'title' => $date,
                'view' => '<a href="'.$url.'" target="_blank" class="btn btn-sm btn-primary">View</a>'
            ];
        }

        return response()->json([
            "draw" => intval($request->draw),
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $data
        ]);
    }
     public function viewLog($file)
    {
        $path = storage_path('logs/' . $file);

        if (!file_exists($path)) {
            abort(404);
        }

        $content = file_get_contents($path);

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }


}