<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Models\MedicineReceipt;
use App\Models\MedicineStockMovement;
use App\Models\MedicineStockOpname;
use App\Models\MedicineUnit;
use App\Models\Prescription;
use App\Models\Supplier;
use App\Models\Visit;
use App\Services\PharmacyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PharmacyController extends Controller
{
    public function __construct(protected PharmacyService $pharmacyService) {}

    public function prescriptions(Request $request): View
    {
        $status = $request->query('status');

        $query = Prescription::with(['visit.patient.allergies', 'doctor', 'items.medicine', 'compounds.items.medicine'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $prescriptions = $query->paginate($this->perPage($request));

        return view('pharmacy.prescriptions', compact('prescriptions', 'status'));
    }

    public function showPrescription(Prescription $prescription): View
    {
        $prescription->load([
            'visit.patient.allergies',
            'doctor',
            'items.medicine.batches',
            'compounds.items.medicine.batches',
            'dispensingTransaction.items.batch',
        ]);

        return view('pharmacy.show_prescription', compact('prescription'));
    }

    public function createPrescription(Visit $visit): View
    {
        $medicines = Medicine::where('is_active', true)->with('batches')->get();

        return view('pharmacy.create_prescription', compact('visit', 'medicines'));
    }

    public function storePrescription(Request $request, Visit $visit): RedirectResponse
    {
        $data = $request->validate([
            'doctor_notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.medicine_id' => 'required_with:items|exists:medicines,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.dosage' => 'required_with:items|string',
            'items.*.frequency' => 'required_with:items|string',
            'items.*.route' => 'nullable|string',
            'items.*.duration_days' => 'nullable|integer|min:1',
            'items.*.instructions' => 'required_with:items|string',
            'compounds' => 'nullable|array',
        ]);

        $prescription = $this->pharmacyService->createPrescription($visit, $data);

        return redirect()->route('clinical.examination', $visit->id)
            ->with('success', "Resep elektronik {$prescription->prescription_number} berhasil dibuat dan dikirim ke instalasi farmasi.");
    }

    public function verify(Request $request, Prescription $prescription): RedirectResponse
    {
        $notes = $request->input('pharmacist_notes');
        $this->pharmacyService->verifyPrescription($prescription, $notes);

        return back()->with('success', "Resep {$prescription->prescription_number} telah diverifikasi.");
    }

    public function dispense(Request $request, Prescription $prescription): RedirectResponse
    {
        $notes = $request->input('notes');
        $dispensing = $this->pharmacyService->dispensePrescription($prescription, $notes);

        return redirect()->route('pharmacy.show_prescription', $prescription->id)
            ->with('success', "Obat resep {$prescription->prescription_number} berhasil didispensing dengan nomor transaksi {$dispensing->dispensing_number}. Stok batch obat telah dipotong (FEFO).");
    }

    public function printLabel(Prescription $prescription): View
    {
        $prescription->load(['visit.patient', 'doctor', 'items.medicine', 'compounds']);

        return view('pharmacy.print_label', compact('prescription'));
    }

    public function medicines(Request $request): View
    {
        $search = $request->input('search');
        $medicines = Medicine::query()
            ->with(['category', 'unit', 'batches'])
            ->when($search, function ($q) use ($search) {
                $q->where('brand_name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->paginate($this->perPage($request));

        $categories = MedicineCategory::all();
        $units = MedicineUnit::all();

        return view('pharmacy.medicines', compact('medicines', 'categories', 'units', 'search'));
    }

    public function storeMedicine(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:30|unique:medicines,code',
            'generic_name' => 'required|string|max:150',
            'brand_name' => 'required|string|max:150',
            'category_id' => 'required|exists:medicine_categories,id',
            'unit_id' => 'required|exists:medicine_units,id',
            'dosage_form' => 'required|string|max:50',
            'strength' => 'nullable|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        $data['is_active'] = true;
        Medicine::create($data);

        return back()->with('success', 'Obat baru berhasil ditambahkan.');
    }

    public function updateMedicine(Request $request, Medicine $medicine): RedirectResponse
    {
        $data = $request->validate([
            'generic_name' => 'required|string|max:150',
            'brand_name' => 'required|string|max:150',
            'category_id' => 'required|exists:medicine_categories,id',
            'unit_id' => 'required|exists:medicine_units,id',
            'dosage_form' => 'required|string|max:50',
            'strength' => 'nullable|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $medicine->update($data);

        return back()->with('success', 'Data obat berhasil diperbarui.');
    }

    public function stockCard(Request $request, Medicine $medicine): View
    {
        $medicine->load(['category', 'unit', 'batches']);
        $movements = MedicineStockMovement::with(['batch', 'creator'])
            ->where('medicine_id', $medicine->id)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage($request));

        return view('pharmacy.stock_card', compact('medicine', 'movements'));
    }

    public function receipts(Request $request): View
    {
        $receipts = MedicineReceipt::with(['supplier', 'receiver', 'items.medicine'])
            ->orderBy('received_date', 'desc')
            ->paginate($this->perPage($request));

        $suppliers = Supplier::where('is_active', true)->get();
        $medicines = Medicine::where('is_active', true)->get();

        return view('pharmacy.receipts', compact('receipts', 'suppliers', 'medicines'));
    }

    public function storeReceipt(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:50',
            'received_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.batch_number' => 'required|string|max:50',
            'items.*.expiry_date' => 'required|date',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
        ]);

        $receipt = $this->pharmacyService->recordReceipt($data);

        return redirect()->route('pharmacy.receipts')
            ->with('success', "Penerimaan barang {$receipt->receipt_number} berhasil dicatat dan stok batch telah bertambah.");
    }

    public function opnames(Request $request): View
    {
        $opnames = MedicineStockOpname::with(['conductor', 'approver', 'items.medicine', 'items.batch'])
            ->orderBy('opname_date', 'desc')
            ->paginate($this->perPage($request));

        $medicines = Medicine::with('batches')->get();

        return view('pharmacy.opnames', compact('opnames', 'medicines'));
    }

    public function storeOpname(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'opname_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.batch_id' => 'required|exists:medicine_batches,id',
            'items.*.physical_stock' => 'required|integer|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        $opnameNumber = MedicineStockOpname::generateNextOpnameNumber();
        $opname = MedicineStockOpname::create([
            'opname_number' => $opnameNumber,
            'opname_date' => $data['opname_date'],
            'status' => 'draft',
            'notes' => $data['notes'] ?? null,
            'conducted_by' => auth()->id() ?? 1,
        ]);

        foreach ($data['items'] as $item) {
            $batch = MedicineBatch::find($item['batch_id']);
            $systemStock = $batch ? $batch->current_stock : 0;
            $physicalStock = (int) $item['physical_stock'];
            $diff = $physicalStock - $systemStock;

            $opname->items()->create([
                'medicine_id' => $item['medicine_id'],
                'batch_id' => $item['batch_id'],
                'system_stock' => $systemStock,
                'physical_stock' => $physicalStock,
                'difference' => $diff,
                'reason' => $item['reason'] ?? null,
            ]);
        }

        return redirect()->route('pharmacy.opnames')
            ->with('success', "Draft stock opname {$opnameNumber} berhasil disimpan. Silakan minta persetujuan Kepala Klinik untuk penyesuaian stok.");
    }

    public function approveOpname(MedicineStockOpname $opname): RedirectResponse
    {
        $this->pharmacyService->approveStockOpname($opname);

        return redirect()->route('pharmacy.opnames')
            ->with('success', "Stock opname {$opname->opname_number} berhasil disetujui dan stok sistem telah disesuaikan.");
    }

    public function searchMedicinesAjax(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));
        $medicines = Medicine::query()
            ->where('is_active', true)
            ->where(function ($sub) use ($q) {
                $sub->where('brand_name', 'like', "%{$q}%")
                    ->orWhere('generic_name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            })
            ->with('batches')
            ->limit(25)
            ->get();

        return response()->json([
            'results' => $medicines->map(fn ($m) => [
                'id' => $m->id,
                'text' => "{$m->brand_name} ({$m->strength}) - Stok: {$m->total_stock} {$m->dosage_form} [Rp".number_format($m->selling_price, 0, ',', '.').']',
                'brand_name' => $m->brand_name,
                'generic_name' => $m->generic_name,
                'selling_price' => $m->selling_price,
                'stock' => $m->total_stock,
                'is_low' => $m->isLowStock(),
                'dosage_form' => $m->dosage_form,
            ]),
        ]);
    }
}
