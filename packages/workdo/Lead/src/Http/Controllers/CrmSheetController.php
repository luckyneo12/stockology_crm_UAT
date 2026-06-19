<?php

namespace Workdo\Lead\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Workdo\Lead\Entities\CrmSheet;
use Workdo\Lead\Entities\CrmSheetCollaborator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class CrmSheetController extends Controller
{
    private function getSubordinateUserIds()
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }
        $accessibleUserIds = $user->getAccessibleUserIds();
        return array_diff($accessibleUserIds, [$user->id]);
    }


    private function hasAccess(CrmSheet $sheet)
    {
        $user = Auth::user();
        if ($user->type === 'company' || $user->type === 'super admin') {
            return true;
        }

        if ($sheet->created_by === $user->id) {
            return true;
        }

        // Check if accepted collaborator
        $isCollaborator = CrmSheetCollaborator::where('sheet_id', $sheet->id)
            ->where('user_id', $user->id)
            ->where('status', 'accepted')
            ->exists();
        if ($isCollaborator) {
            return true;
        }

        // Check if subordinate's sheet
        $subordinates = $this->getSubordinateUserIds();
        if (in_array($sheet->created_by, $subordinates)) {
            return true;
        }

        return false;
    }

    public function index()
    {
        $workspaceId = getActiveWorkSpace();
        $user = Auth::user();
        
        // 1. My Sheets / All Sheets for Company
        if ($user->type === 'company' || $user->type === 'super admin') {
            $mySheets = CrmSheet::where('workspace_id', $workspaceId)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $mySheets = CrmSheet::where('workspace_id', $workspaceId)
                ->where('created_by', $user->id)
                ->orderBy('id', 'desc')
                ->get();
        }

        // 2. Shared with Me
        $sharedSheets = CrmSheet::where('workspace_id', $workspaceId)
            ->whereHas('collaborators', function ($query) {
                $query->where('user_id', Auth::id())->where('status', 'accepted');
            })
            ->orderBy('id', 'desc')
            ->get();

        // 3. Pending Collaborations/Invites
        $pendingInvites = CrmSheetCollaborator::with(['sheet.creator'])
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->get();

        // 4. Department / Team Sheets (for managers)
        $subordinates = $this->getSubordinateUserIds();
        $isManager = !empty($subordinates) && $user->type !== 'company' && $user->type !== 'super admin';
        
        $deptSheets = collect();
        if ($isManager) {
            $deptSheets = CrmSheet::where('workspace_id', $workspaceId)
                ->whereIn('created_by', $subordinates)
                ->orderBy('id', 'desc')
                ->get();
        }

        $creators = [];
        foreach ($mySheets as $sheet) {
            if ($sheet->creator && $sheet->created_by !== Auth::id()) {
                $creators[$sheet->created_by] = $sheet->creator->name;
            }
        }

        return view('lead::sheets.index', compact('mySheets', 'sharedSheets', 'pendingInvites', 'deptSheets', 'isManager', 'creators'));
    }

    public function create()
    {
        return view('lead::sheets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'excel_file' => 'nullable|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $gridData = null;

        if ($request->hasFile('excel_file') && $request->file('excel_file')->isValid()) {
            try {
                $file = $request->file('excel_file');
                $spreadsheet = IOFactory::load($file->getRealPath());
                $activeSheet = $spreadsheet->getActiveSheet();
                
                $highestRow = $activeSheet->getHighestRow();
                $highestColumn = $activeSheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                
                $gridData = [];
                for ($row = 1; $row <= $highestRow; $row++) {
                    $gridRow = [];
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $cellValue = $activeSheet->getCell([$col, $row])->getValue();
                        
                        if ($cellValue instanceof \PhpOffice\PhpSpreadsheet\Cell\CellFormula) {
                            $cellValue = $activeSheet->getCell([$col, $row])->getCalculatedValue();
                        }
                        
                        $gridRow[] = $cellValue !== null ? (string)$cellValue : '';
                    }
                    $gridData[] = $gridRow;
                }
                
                // Pad sheet dimensions to ensure jSpreadsheet is comfortable (minimum 30 rows, 15 columns)
                $maxCols = 15;
                foreach ($gridData as $row) {
                    $maxCols = max($maxCols, count($row));
                }
                
                foreach ($gridData as &$row) {
                    while (count($row) < $maxCols) {
                        $row[] = '';
                    }
                }
                
                while (count($gridData) < 30) {
                    $gridData[] = array_fill(0, $maxCols, '');
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', __('Failed to parse Excel file: ') . $e->getMessage());
            }
        }

        if (!$gridData) {
            // Create empty grid template: 15 columns by 30 rows
            $gridData = array_fill(0, 30, array_fill(0, 15, ''));
        }

        CrmSheet::create([
            'name' => $request->name,
            'workspace_id' => getActiveWorkSpace(),
            'created_by' => Auth::id(),
            'data' => $gridData,
        ]);

        return redirect()->route('crm.sheets.index')->with('success', __('Spreadsheet created successfully.'));
    }

    public function export($id)
    {
        if (Auth::user()->type !== 'company') {
            abort(403, __('Unauthorized access.'));
        }

        $sheet = CrmSheet::where('workspace_id', getActiveWorkSpace())->findOrFail($id);

        if (!$this->hasAccess($sheet)) {
            abort(403, __('Unauthorized access.'));
        }

        try {
            $spreadsheet = new Spreadsheet();
            $activeSheet = $spreadsheet->getActiveSheet();
            
            // Set sheet title (limited to 31 chars in Excel)
            $sheetTitle = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $sheet->name), 0, 30);
            if (!empty($sheetTitle)) {
                $activeSheet->setTitle($sheetTitle);
            }

            $gridData = $sheet->data ?? [];
            
            foreach ($gridData as $rowIndex => $row) {
                foreach ($row as $colIndex => $cellValue) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                    $activeSheet->setCellValue($colLetter . ($rowIndex + 1), $cellValue);
                }
            }

            $fileName = str_replace(' ', '_', $sheet->name) . '.xlsx';

            if (ob_get_length()) {
                ob_end_clean();
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Failed to export Excel file: ') . $e->getMessage());
        }
    }

    public function view($id)
    {
        if (function_exists('sideMenuCacheForget')) {
            sideMenuCacheForget('company', Auth::id());
        }

        $sheet = CrmSheet::where('workspace_id', getActiveWorkSpace())->findOrFail($id);

        if (!$this->hasAccess($sheet)) {
            abort(403, __('Unauthorized access.'));
        }

        return view('lead::sheets.view', compact('sheet'));
    }

    public function updateData(Request $request, $id)
    {
        $sheet = CrmSheet::where('workspace_id', getActiveWorkSpace())->findOrFail($id);

        if (!$this->hasAccess($sheet)) {
            return response()->json(['success' => false, 'message' => __('Unauthorized access.')], 403);
        }

        $request->validate([
            'data' => 'required|array',
        ]);

        $sheet->update([
            'data' => $request->data,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Sheet saved successfully.'),
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->type === 'company' || $user->type === 'super admin') {
            $sheet = CrmSheet::where('workspace_id', getActiveWorkSpace())
                ->findOrFail($id);
        } else {
            $sheet = CrmSheet::where('workspace_id', getActiveWorkSpace())
                ->where('created_by', $user->id)
                ->findOrFail($id);
        }

        $sheet->delete();

        return redirect()->route('crm.sheets.index')->with('success', __('Spreadsheet deleted successfully.'));
    }

    public function share($id)
    {
        $sheet = CrmSheet::where('workspace_id', getActiveWorkSpace())
            ->where('created_by', Auth::id())
            ->findOrFail($id);

        // Fetch users in same workspace who are not already collaborators or the owner
        $collaboratorUserIds = CrmSheetCollaborator::where('sheet_id', $sheet->id)->pluck('user_id')->toArray();
        $excludeIds = array_merge([$sheet->created_by], $collaboratorUserIds);

        $users = User::where('workspace_id', getActiveWorkSpace())
            ->where('type', '!=', 'client')
            ->whereNotIn('id', $excludeIds)
            ->pluck('name', 'id')
            ->toArray();

        return view('lead::sheets.share', compact('sheet', 'users'));
    }

    public function storeShare(Request $request, $id)
    {
        $sheet = CrmSheet::where('workspace_id', getActiveWorkSpace())
            ->where('created_by', Auth::id())
            ->findOrFail($id);

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // Verify they aren't already added
        $exists = CrmSheetCollaborator::where('sheet_id', $sheet->id)
            ->where('user_id', $request->user_id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', __('Collaborator already invited.'));
        }

        CrmSheetCollaborator::create([
            'sheet_id' => $sheet->id,
            'user_id' => $request->user_id,
            'status' => 'pending',
        ]);

        return redirect()->route('crm.sheets.index')->with('success', __('Collaboration invite sent successfully.'));
    }

    public function acceptShare($id)
    {
        $invite = CrmSheetCollaborator::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $invite->update([
            'status' => 'accepted',
        ]);

        return redirect()->route('crm.sheets.index')->with('success', __('Invite accepted. You can now collaborate on this sheet.'));
    }

    public function declineShare($id)
    {
        $invite = CrmSheetCollaborator::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $invite->delete();

        return redirect()->route('crm.sheets.index')->with('success', __('Invitation declined.'));
    }
}
