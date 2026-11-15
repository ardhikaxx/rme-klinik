<?php

namespace App\Http\Controllers;

use App\Models\Odontogram;
use App\Models\Polyclinic;
use App\Models\Visit;
use App\Services\OdontogramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OdontogramController extends Controller
{
    public function __construct(protected OdontogramService $odontogramService) {}

    public function index(Request $request): View
    {
        $polyGigi = Polyclinic::where('type', 'gigi')->first();

        $visits = Visit::with(['patient', 'odontogram.teeth'])
            ->when($polyGigi, fn ($q) => $q->where('polyclinic_id', $polyGigi->id))
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($this->perPage($request));

        return view('odontogram.index', compact('visits', 'polyGigi'));
    }

    public function show(Visit $visit): View
    {
        $odontogram = $this->odontogramService->getOdontogramForVisit($visit);
        $odontogram->load('teeth');

        // Group teeth by tooth_number for quick visual mapping
        $teethMap = $odontogram->teeth->keyBy('tooth_number');

        return view('odontogram.show', compact('visit', 'odontogram', 'teethMap'));
    }

    public function updateTooth(Request $request, Odontogram $odontogram): JsonResponse
    {
        $request->validate([
            'tooth_number' => 'required|string|max:5',
            'condition' => 'required|string|in:sound,caries,filling,missing,radix,crown,root_canal,bridge,unerupted',
            'surfaces' => 'nullable|string|max:50',
            'treatment_notes' => 'nullable|string',
        ]);

        $tooth = $this->odontogramService->updateTooth(
            $odontogram,
            $request->input('tooth_number'),
            $request->input('condition'),
            $request->input('surfaces'),
            $request->input('treatment_notes')
        );

        return response()->json([
            'success' => true,
            'tooth' => $tooth,
            'condition_label' => $tooth->condition_label,
            'color' => $tooth->condition_color,
        ]);
    }

    public function saveFull(Request $request, Odontogram $odontogram): RedirectResponse
    {
        $data = $request->input('teeth', []);
        $notes = $request->input('notes');

        $this->odontogramService->saveFullOdontogram($odontogram, $data, $notes);

        return redirect()->route('odontogram.show', $odontogram->visit_id)
            ->with('success', 'Data status odontogram gigi berhasil disimpan.');
    }

    public function print(Visit $visit): View
    {
        $odontogram = $this->odontogramService->getOdontogramForVisit($visit);
        $odontogram->load('teeth');
        $teethMap = $odontogram->teeth->keyBy('tooth_number');

        return view('odontogram.print', compact('visit', 'odontogram', 'teethMap'));
    }
}
