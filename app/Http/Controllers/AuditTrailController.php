<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->query('user_id');
        $module = $request->query('module');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($module) {
            $query->where('module', $module);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $logs = $query->paginate(25);
        $users = User::all();
        $modules = AuditLog::select('module')->distinct()->pluck('module');

        return view('audit.index', compact('logs', 'users', 'modules', 'userId', 'module', 'startDate', 'endDate'));
    }
}
