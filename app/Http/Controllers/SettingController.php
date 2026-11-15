<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\DoctorSchedule;
use App\Models\Employee;
use App\Models\Polyclinic;
use App\Models\Role;
use App\Models\ServiceTariff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Master Tariffs & Medical Fees
     */
    public function tariffs(Request $request): View
    {
        $tariffs = ServiceTariff::with('polyclinic')->paginate($this->perPage($request));
        $polyclinics = Polyclinic::where('is_active', true)->get();

        return view('settings.tariffs', compact('tariffs', 'polyclinics'));
    }

    public function storeTariff(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:service_tariffs,code',
            'name' => 'required|string|max:255',
            'category' => 'required|in:administrasi,konsultasi,tindakan_medis,tindakan_keperawatan,laboratorium,bmhp,lainnya',
            'polyclinic_id' => 'nullable|exists:polyclinics,id',
            'icd9cm_code' => 'nullable|string|max:20',
            'tariff' => 'required|numeric|min:0',
            'doctor_fee' => 'nullable|numeric|min:0',
            'nurse_fee' => 'nullable|numeric|min:0',
            'clinic_fee' => 'nullable|numeric|min:0',
        ]);

        $data['is_active'] = true;
        ServiceTariff::create($data);

        AuditLog::log('TARIFF_CREATED', 'SETTINGS', "Master tarif baru ditambahkan: {$data['name']} ({$data['code']})");

        return back()->with('success', 'Tarif layanan baru berhasil ditambahkan.');
    }

    public function updateTariff(Request $request, ServiceTariff $tariff): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:administrasi,konsultasi,tindakan_medis,tindakan_keperawatan,laboratorium,bmhp,lainnya',
            'polyclinic_id' => 'nullable|exists:polyclinics,id',
            'icd9cm_code' => 'nullable|string|max:20',
            'tariff' => 'required|numeric|min:0',
            'doctor_fee' => 'nullable|numeric|min:0',
            'nurse_fee' => 'nullable|numeric|min:0',
            'clinic_fee' => 'nullable|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $tariff->update($data);

        AuditLog::log('TARIFF_UPDATED', 'SETTINGS', "Master tarif diperbarui: {$tariff->name}");

        return back()->with('success', 'Tarif layanan berhasil diperbarui.');
    }

    /**
     * Doctor Schedules Management
     */
    public function schedules(Request $request): View
    {
        $schedules = DoctorSchedule::with(['employee', 'polyclinic'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate($this->perPage($request));

        $doctors = Employee::where('profession', 'dokter')->where('is_active', true)->get();
        $polyclinics = Polyclinic::where('is_active', true)->get();

        return view('settings.schedules', compact('schedules', 'doctors', 'polyclinics'));
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'polyclinic_id' => 'required|exists:polyclinics,id',
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required',
            'end_time' => 'required',
            'quota' => 'required|integer|min:1',
        ]);

        $data['is_active'] = true;
        DoctorSchedule::create($data);

        AuditLog::log('SCHEDULE_CREATED', 'SETTINGS', 'Jadwal dokter berhasil ditambahkan');

        return back()->with('success', 'Jadwal dokter berhasil ditambahkan.');
    }

    public function toggleSchedule(DoctorSchedule $schedule): RedirectResponse
    {
        $schedule->update(['is_active' => ! $schedule->is_active]);

        return back()->with('success', 'Status jadwal berhasil diubah.');
    }

    /**
     * Users & Employees Management
     */
    public function users(Request $request): View
    {
        $users = User::with(['roles', 'employee'])->paginate($this->perPage($request));
        $roles = Role::all();
        $departments = Department::where('is_active', true)->get();

        return view('settings.users', compact('users', 'roles', 'departments'));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'profession' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'sip_str_number' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        $user->roles()->sync([$data['role_id']]);

        // Auto create employee
        $empNum = 'EMP-'.strtoupper(substr($data['profession'], 0, 3)).'-'.str_pad($user->id, 3, '0', STR_PAD_LEFT);
        Employee::create([
            'user_id' => $user->id,
            'department_id' => $data['department_id'],
            'employee_number' => $empNum,
            'name' => $data['name'],
            'gender' => 'L',
            'profession' => $data['profession'],
            'sip_str_number' => $data['sip_str_number'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'is_active' => true,
        ]);

        AuditLog::log('USER_CREATED', 'SETTINGS', "Akun pengguna baru dibuat: {$user->username} ({$user->name})");

        return back()->with('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    public function toggleUser(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri!');
        }

        $user->update(['is_active' => ! $user->is_active]);

        AuditLog::log('USER_STATUS_TOGGLED', 'SETTINGS', "Status akun {$user->username} diubah menjadi ".($user->is_active ? 'Aktif' : 'Nonaktif'));

        return back()->with('success', "Status akun pengguna {$user->name} berhasil diubah.");
    }
}
