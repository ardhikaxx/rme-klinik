<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $summary = $this->reportService->getSummary($startDate, $endDate);
        $visitsByPoly = $this->reportService->getVisitsByPolyclinic($startDate, $endDate);
        $topDiseases = $this->reportService->getTopDiseases($startDate, $endDate, 10);
        $topMedicines = $this->reportService->getTopMedicines($startDate, $endDate, 10);
        $dailyTrend = $this->reportService->getDailyTrend($startDate, $endDate);
        $medicalFees = $this->reportService->medicalFeesQuery($startDate, $endDate)
            ->paginate($this->perPage($request));

        return view('reports.index', compact(
            'summary', 'visitsByPoly', 'topDiseases', 'topMedicines', 'dailyTrend', 'medicalFees', 'startDate', 'endDate'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $type = $request->input('type', 'visits');
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"laporan_{$type}_{$startDate}_{$endDate}.csv\"",
        ];

        return response()->stream(function () use ($type, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($type === 'diseases') {
                fputcsv($handle, ['No', 'Kode ICD-10', 'Deskripsi Penyakit', 'Jumlah Kasus']);
                $data = $this->reportService->getTopDiseases($startDate, $endDate, 50);
                foreach ($data as $idx => $row) {
                    fputcsv($handle, [$idx + 1, $row->icd10_code, $row->description, $row->total_cases]);
                }
            } elseif ($type === 'medicines') {
                fputcsv($handle, ['No', 'Nama Obat', 'Nama Generik', 'Bentuk Sediaan', 'Jumlah Terpakai', 'Total Nilai (Rp)']);
                $data = $this->reportService->getTopMedicines($startDate, $endDate, 50);
                foreach ($data as $idx => $row) {
                    fputcsv($handle, [$idx + 1, $row->brand_name, $row->generic_name, $row->dosage_form, $row->total_quantity, $row->total_revenue]);
                }
            } elseif ($type === 'fees') {
                fputcsv($handle, ['No', 'Tanggal', 'Nama Pasien', 'Dokter', 'Jasa Dokter (Rp)', 'Jasa Perawat (Rp)', 'Klinik (Rp)', 'Status']);
                $data = $this->reportService->getMedicalFees($startDate, $endDate);
                foreach ($data as $idx => $row) {
                    fputcsv($handle, [
                        $idx + 1,
                        $row->created_at->format('d-m-Y H:i'),
                        $row->visit?->patient?->name ?? '-',
                        $row->doctor?->formatted_name ?? '-',
                        $row->doctor_fee,
                        $row->nurse_fee,
                        $row->clinic_fee,
                        $row->status,
                    ]);
                }
            } else {
                fputcsv($handle, ['No', 'Poliklinik', 'Jumlah Kunjungan']);
                $data = $this->reportService->getVisitsByPolyclinic($startDate, $endDate);
                foreach ($data as $idx => $row) {
                    fputcsv($handle, [$idx + 1, $row->polyclinic_name, $row->total]);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }
}
