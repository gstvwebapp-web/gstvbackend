<?php
namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ElectionResultModuleController extends Controller
{
    // ─── Ensure tables/columns exist ────────────────────────────────────────────

    private function ensureSchema()
    {
        try {
            // election_groups table (parent)
            $tables = DB::select("SHOW TABLES LIKE 'election_groups'");
            if (count($tables) === 0) {
                DB::statement("
                    CREATE TABLE election_groups (
                        id BIGINT AUTO_INCREMENT PRIMARY KEY,
                        main_title VARCHAR(255) NOT NULL,
                        status VARCHAR(10) NOT NULL DEFAULT 'Active',
                        user_id BIGINT NULL,
                        created_at TIMESTAMP NULL,
                        updated_at TIMESTAMP NULL
                    )
                ");
            }

            // group_id column on election_modules
            $col = DB::select("SHOW COLUMNS FROM election_modules LIKE 'group_id'");
            if (count($col) === 0) {
                DB::statement("ALTER TABLE election_modules ADD COLUMN group_id BIGINT NULL AFTER id");
            }

            // status column on election_modules
            $col2 = DB::select("SHOW COLUMNS FROM election_modules LIKE 'status'");
            if (count($col2) === 0) {
                DB::statement("ALTER TABLE election_modules ADD COLUMN status VARCHAR(10) NOT NULL DEFAULT 'Active' AFTER total_seats");
            }

            // is_hidden column on election_columns
            $col3 = DB::select("SHOW COLUMNS FROM election_columns LIKE 'is_hidden'");
            if (count($col3) === 0) {
                DB::statement("ALTER TABLE election_columns ADD COLUMN is_hidden TINYINT(1) NOT NULL DEFAULT 0 AFTER column_name");
            }

            // color_code column on election_rows
            $col4 = DB::select("SHOW COLUMNS FROM election_rows LIKE 'color_code'");
            if (count($col4) === 0) {
                DB::statement("ALTER TABLE election_rows ADD COLUMN color_code VARCHAR(20) NOT NULL DEFAULT '#000000' AFTER party_name");
            }
        } catch (\Exception $e) {
            // ignore silently
        }
    }

    // ─── Listing ────────────────────────────────────────────────────────────────

    public function index()
    {
        return view('electionresultmodule.alllist');
    }

    // ─── AJAX: groups listing (parent rows) ─────────────────────────────────────

    public function fetchData(Request $request)
    {
        try {
            $this->ensureSchema();

            $draw   = intval($request->get('draw', 1));
            $start  = intval($request->get('start', 0));
            $length = intval($request->get('length', 25));
            $search = $request->get('search')['value'] ?? '';

            $baseQuery = function () {
                return DB::table('election_groups as eg')
                    ->select(
                        'eg.id',
                        'eg.main_title',
                        DB::raw("IFNULL(eg.status,'Active') as status"),
                        'eg.created_at',
                        'u.name as author_name',
                        DB::raw("COUNT(em.id) as module_count")
                    )
                    ->leftJoin('users as u', 'eg.user_id', '=', 'u.id')
                    ->leftJoin('election_modules as em', 'em.group_id', '=', 'eg.id')
                    ->groupBy('eg.id', 'eg.main_title', 'eg.status', 'eg.created_at', 'u.name');
            };

            $totalRecords    = DB::table('election_groups')->count();
            $filteredRecords = $totalRecords;

            $query = $baseQuery();
            if (!empty($search)) {
                $query->having('eg.main_title', 'like', "%{$search}%");
                $filteredRecords = (clone $query)->get()->count();
            }

            $data = $query->orderBy('eg.id', 'desc')->skip($start)->take($length)->get();

            $rows = [];
            foreach ($data as $i => $group) {
                $status   = $group->status ?? 'Active';
                $isActive = ($status === 'Active');

                $toggleUrl = url('electionresultmodule/groupStatus/' . ($isActive ? 'Inactive' : 'Active') . '/' . $group->id);
                $statusBtn = '<a href="' . $toggleUrl . '" class="btn btn-sm ' . ($isActive ? 'btn-success' : 'btn-warning') . '" title="' . ($isActive ? 'Active – Disable' : 'Inactive – Enable') . '">'
                           . '<i class="bi ' . ($isActive ? 'bi-eye' : 'bi-eye-slash') . '"></i></a>';

                $actions = $statusBtn
                    . ' <a href="' . url('electionresultmodule/edit/' . $group->id) . '" class="btn btn-sm btn-primary ms-1" title="Edit"><i class="bi bi-pencil"></i></a>'
                    . ' <a href="javascript:void(0)" onclick="deleteGroup(' . $group->id . ')" class="btn btn-sm btn-danger ms-1" title="Delete"><i class="bi bi-trash"></i></a>';

                $statusBadge = $isActive
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-warning text-dark">Inactive</span>';

                // Child modules for this group
                $modules = DB::table('election_modules as em')
                    ->select('em.id', 'em.title', 'em.total_seats', 's.name as state_name',
                             DB::raw("IFNULL(em.status,'Active') as status"))
                    ->leftJoin('state as s', 'em.state_id', '=', 's.id')
                    ->where('em.group_id', $group->id)
                    ->get();

                $childHtml = '';
                if ($modules->count() > 0) {
                    $childHtml = '<table class="table table-sm table-bordered mb-0 mt-1" style="background:#f8f9fa">'
                               . '<thead><tr><th>State</th><th>Election Title</th><th>Total Seats</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
                    foreach ($modules as $mod) {
                        $mActive  = ($mod->status === 'Active');
                        $mToggle  = url('electionresultmodule/updateStatus/' . ($mActive ? 'Inactive' : 'Active') . '/' . $mod->id);
                        $mStatus  = $mActive ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-warning text-dark">Inactive</span>';
                        $mActions = '<a href="' . $mToggle . '" class="btn btn-xs btn-sm ' . ($mActive ? 'btn-success' : 'btn-warning') . '" title="Toggle"><i class="bi ' . ($mActive ? 'bi-eye' : 'bi-eye-slash') . '"></i></a>';
                        $childHtml .= '<tr>'
                            . '<td>' . htmlspecialchars($mod->state_name ?? '—') . '</td>'
                            . '<td>' . htmlspecialchars($mod->title) . '</td>'
                            . '<td>' . $mod->total_seats . '</td>'
                            . '<td>' . $mStatus . '</td>'
                            . '<td>' . $mActions . '</td>'
                            . '</tr>';
                    }
                    $childHtml .= '</tbody></table>';
                }

                $rows[] = [
                    $start + $i + 1,
                    '<strong>' . htmlspecialchars($group->main_title) . '</strong>'
                        . '<br><small class="text-muted">' . $group->module_count . ' state(s)</small>'
                        . ($childHtml ? '<div class="mt-1">' . $childHtml . '</div>' : ''),
                    $statusBadge,
                    $group->author_name ?? '—',
                    $actions,
                ];
            }

            return response()->json([
                'draw'            => $draw,
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data'            => $rows,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'draw'            => intval($request->get('draw', 1)),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'Server error: ' . $e->getMessage(),
            ]);
        }
    }

    // ─── Toggle group status ─────────────────────────────────────────────────────

    public function groupStatus($status, $id)
    {
        if (!in_array($status, ['Active', 'Inactive'])) {
            return redirect('electionresultmodule')->with('error', 'Invalid status.');
        }
        $this->ensureSchema();
        DB::table('election_groups')->where('id', $id)->update([
            'status'     => $status,
            'updated_at' => now(),
        ]);
        $label = $status === 'Active' ? 'enabled' : 'disabled';
        return redirect('electionresultmodule')->with('success', 'Election group has been ' . $label . '.');
    }

    // ─── Toggle individual module status ────────────────────────────────────────

    public function updateStatus($status, $id)
    {
        if (!in_array($status, ['Active', 'Inactive'])) {
            return redirect('electionresultmodule')->with('error', 'Invalid status.');
        }
        $this->ensureSchema();
        DB::table('election_modules')->where('id', $id)->update([
            'status'     => $status,
            'updated_at' => now(),
        ]);
        $label = $status === 'Active' ? 'enabled' : 'disabled';
        return redirect('electionresultmodule')->with('success', 'Election entry has been ' . $label . '.');
    }

    // ─── Add ────────────────────────────────────────────────────────────────────

    public function add()
    {
        $this->ensureSchema();
        $states = DB::table('state')->where('status', 1)->orderBy('name')->get();
        return view('electionresultmodule.add', compact('states'));
    }

    // ─── Store ──────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'main_title'                    => 'required|string|max:255',
            'blocks'                        => 'required|array|min:1',
            'blocks.*.state_id'             => 'required',
            'blocks.*.title'                => 'required|string|max:255',
            'blocks.*.total_seats'          => 'required|integer|min:1',
            'blocks.*.columns'              => 'required|array|min:1',
            'blocks.*.columns.*'            => 'required|string|max:255',
            'blocks.*.rows'                 => 'required|array|min:1',
        ], [
            'main_title.required'           => 'Main title is required.',
            'blocks.required'               => 'Please add at least one election entry.',
            'blocks.*.state_id.required'    => 'Please select a state.',
            'blocks.*.title.required'       => 'Election title is required.',
            'blocks.*.total_seats.required' => 'Total seats is required.',
            'blocks.*.columns.required'     => 'At least one column is required.',
            'blocks.*.rows.required'        => 'At least one party row is required.',
        ]);

        $this->ensureSchema();

        DB::beginTransaction();
        try {
            // Create the parent group
            $groupId = DB::table('election_groups')->insertGetId([
                'main_title' => $request->main_title,
                'status'     => 'Active',
                'user_id'    => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create each child module
            foreach ($request->blocks as $block) {
                $electionId = DB::table('election_modules')->insertGetId([
                    'group_id'    => $groupId,
                    'state_id'    => $block['state_id'],
                    'title'       => $block['title'],
                    'total_seats' => $block['total_seats'],
                    'user_id'     => Auth::id(),
                    'status'      => 'Active',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                $columnIds = [];
                foreach ($block['columns'] as $index => $col) {
                    $columnIds[] = DB::table('election_columns')->insertGetId([
                        'election_id' => $electionId,
                        'column_name' => $col,
                        'is_hidden'   => isset($block['col_hidden'][$index]) ? 1 : 0,
                        'sort_order'  => $index,
                    ]);
                }

                foreach (($block['rows'] ?? []) as $row) {
                    $rowId = DB::table('election_rows')->insertGetId([
                        'election_id' => $electionId,
                        'party_name'  => $row['party'] ?? '',
                        'color_code'  => $row['color_code'] ?? '#000000',
                    ]);
                    foreach (($row['values'] ?? []) as $i => $val) {
                        if (isset($columnIds[$i])) {
                            DB::table('election_values')->insert([
                                'row_id'    => $rowId,
                                'column_id' => $columnIds[$i],
                                'value'     => $val ?? '',
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect('electionresultmodule')->with('success', 'Election group saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ─── Edit (load all active modules of the group) ─────────────────────────────

    public function edit($groupId)
    {
        $this->ensureSchema();
        $states = DB::table('state')->where('status', 1)->orderBy('name')->get();

        $group = DB::table('election_groups')->where('id', $groupId)->first();
        if (!$group) {
            return redirect('electionresultmodule')->with('error', 'Group not found.');
        }

        /*if (($group->status ?? 'Active') === 'Inactive') {
            return redirect('electionresultmodule')->with('error', 'This election group is inactive. Enable it first to edit.');
        }*/

        // Load only Active modules of this group
        $allElections = DB::table('election_modules')
            ->where('group_id', $groupId)
            ->where(function ($q) {
                $q->where('status', 'Active')->orWhereNull('status');
            })
            ->orderBy('id', 'asc')
            ->get();

        $electionBlocks = [];
        foreach ($allElections as $election) {
            $columns = DB::table('election_columns')
                ->where('election_id', $election->id)
                ->orderBy('sort_order')->get();

            $rows = DB::table('election_rows')
                ->where('election_id', $election->id)->orderBy('id', 'asc')->get();

            $rowIds = $rows->pluck('id')->toArray();
          $values = count($rowIds)
                    ? DB::table('election_values')
                        ->whereIn('row_id', $rowIds)
                        ->orderBy('id', 'asc') // IMPORTANT
                        ->get()
                        ->groupBy('row_id')
                    : collect();
            

            $electionBlocks[] = compact('election', 'columns', 'rows', 'values');
        }

        return view('electionresultmodule.edit', compact('states', 'group', 'electionBlocks'));
    }

    // ─── Update ─────────────────────────────────────────────────────────────────

    public function update(Request $request, $groupId)
    {
        $request->validate([
            'main_title'                    => 'required|string|max:255',
            'blocks'                        => 'required|array|min:1',
            'blocks.*.election_id'          => 'required|integer',
            'blocks.*.state_id'             => 'required',
            'blocks.*.title'                => 'required|string|max:255',
            'blocks.*.total_seats'          => 'required|integer|min:1',
            'blocks.*.columns'              => 'required|array|min:1',
            'blocks.*.columns.*'            => 'required|string|max:255',
            'blocks.*.rows'                 => 'required|array|min:1',
        ], [
            'main_title.required'           => 'Main title is required.',
            'blocks.*.state_id.required'    => 'Please select a state.',
            'blocks.*.title.required'       => 'Election title is required.',
            'blocks.*.total_seats.required' => 'Total seats is required.',
            'blocks.*.columns.required'     => 'At least one column is required.',
            'blocks.*.rows.required'        => 'At least one party row is required.',
        ]);

        $this->ensureSchema();

        DB::beginTransaction();
        try {
            // Update group main title
            DB::table('election_groups')->where('id', $groupId)->update([
                'main_title' => $request->main_title,
                'updated_at' => now(),
            ]);

            foreach ($request->blocks as $block) {
                $electionId = intval($block['election_id']);

                // ── election_id = 0 means a brand-new entry added via "Add More" ──
                if ($electionId === 0) {
                    $electionId = DB::table('election_modules')->insertGetId([
                        'group_id'    => $groupId,
                        'state_id'    => $block['state_id'],
                        'title'       => $block['title'],
                        'total_seats' => $block['total_seats'],
                        'user_id'     => Auth::id(),
                        'status'      => 'Active',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                } else {
                    // ── Update existing record ──
                    DB::table('election_modules')->where('id', $electionId)->update([
                        'state_id'    => $block['state_id'],
                        'title'       => $block['title'],
                        'total_seats' => $block['total_seats'],
                        'updated_at'  => now(),
                    ]);

                    // Delete old columns only (rows handled positionally below)
                    $oldRowIds = DB::table('election_rows')->where('election_id', $electionId)->pluck('id');
                    DB::table('election_values')->whereIn('row_id', $oldRowIds)->delete();
                    // NOTE: rows themselves are NOT deleted here — they are updated in place below
                    DB::table('election_columns')->where('election_id', $electionId)->delete();
                }

                // ── Insert columns ──
                $columnIds = [];
                foreach ($block['columns'] as $index => $col) {
                    $columnIds[] = DB::table('election_columns')->insertGetId([
                        'election_id' => $electionId,
                        'column_name' => $col,
                        'is_hidden'   => isset($block['col_hidden'][$index]) ? 1 : 0,
                        'sort_order'  => $index,
                    ]);
                }

                // ── Update rows & values in place (preserve original row IDs and order) ──
                // Get existing row IDs in their original insertion order
                $existingRowIds = DB::table('election_rows')
                    ->where('election_id', $electionId)
                    ->orderBy('id', 'asc')
                    ->pluck('id')
                    ->toArray();

                $submittedRows = array_values($block['rows'] ?? []);
                $submittedCount = count($submittedRows);
                $existingCount  = count($existingRowIds);

                foreach ($submittedRows as $pos => $row) {
                    $partyName  = $row['party']      ?? '';
                    $colorCode  = $row['color_code'] ?? '#000000';
                    $rowValues  = $row['values']     ?? [];

                    if ($pos < $existingCount) {
                        // ── UPDATE existing row in place (same ID, same position) ──
                        $rowId = $existingRowIds[$pos];

                        DB::table('election_rows')->where('id', $rowId)->update([
                            'party_name' => $partyName,
                            'color_code' => $colorCode,
                        ]);

                        // Delete old values for this row and re-insert with new column IDs
                        DB::table('election_values')->where('row_id', $rowId)->delete();

                    } else {
                        // ── INSERT new row (added via "Add Row" button) ──
                        $rowId = DB::table('election_rows')->insertGetId([
                            'election_id' => $electionId,
                            'party_name'  => $partyName,
                            'color_code'  => $colorCode,
                        ]);
                    }

                    // Insert values for this row against new column IDs
                    foreach ($rowValues as $i => $val) {
                        if (isset($columnIds[$i])) {
                            DB::table('election_values')->insert([
                                'row_id'    => $rowId,
                                'column_id' => $columnIds[$i],
                                'value'     => $val ?? '',
                            ]);
                        }
                    }
                }

                // ── DELETE rows that were removed by the user ──
                if ($existingCount > $submittedCount) {
                    $rowsToDelete = array_slice($existingRowIds, $submittedCount);
                    DB::table('election_values')->whereIn('row_id', $rowsToDelete)->delete();
                    DB::table('election_rows')->whereIn('id', $rowsToDelete)->delete();
                }
            }

            DB::commit();
          	return redirect('electionresultmodule/edit/' . $groupId)->with('success', 'Election group updated successfully.');
            //return redirect('electionresultmodule')->with('success', 'Election group updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ─── Delete group (cascades everything) ─────────────────────────────────────

    public function delete($groupId)
    {
        DB::beginTransaction();
        try {
            $moduleIds = DB::table('election_modules')->where('group_id', $groupId)->pluck('id');
            foreach ($moduleIds as $mid) {
                $rowIds = DB::table('election_rows')->where('election_id', $mid)->pluck('id');
                DB::table('election_values')->whereIn('row_id', $rowIds)->delete();
                DB::table('election_rows')->where('election_id', $mid)->delete();
                DB::table('election_columns')->where('election_id', $mid)->delete();
            }
            DB::table('election_modules')->where('group_id', $groupId)->delete();
            DB::table('election_groups')->where('id', $groupId)->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
