<?php

namespace App\Http\Controllers\Evaluator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Recognition;
use App\Services\PdfGenerationService;

class RecognitionController extends Controller
{
    protected $pdfGenerationService;

    public function __construct(PdfGenerationService $pdfGenerationService)
    {
        $this->pdfGenerationService = $pdfGenerationService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $rows = $request->input('rows', 10);
        $search = $request->input('search');

        // Fetch active recognitions for the current user
        $recognitions = Recognition::with('announcement')
            ->where('user_id', $user->id)
            ->where('type', 'evaluator')
            ->where('active', true)
            ->when($search, function ($query, $search) {
                $query->whereHas('announcement', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('sent_at', 'desc')
            ->paginate($rows)
            ->withQueryString();

        // Transform data
        $recognitions->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'announcement' => $item->announcement->name,
                'date' => \Carbon\Carbon::parse($item->sent_at)->isoFormat('D [de] MMMM [de] YYYY'),
                'year' => \Carbon\Carbon::parse($item->announcement->created_at)->year,
            ];
        });

        return Inertia::render('Evaluator/Recognitions/Index', [
            'recognitions' => $recognitions,
            'filters' => $request->all(['search', 'rows']),
        ]);
    }

    public function download($id)
    {
        $user = Auth::user();
        
        $recognition = Recognition::with('announcement')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('type', 'evaluator')
            ->where('active', true)
            ->firstOrFail();
        try {
            return $this->pdfGenerationService->generateRecognitionPdf($recognition, $user);
        } catch (\Throwable $e) {
            return response()->view('errors.pdf_missing', ['message' => $e->getMessage()], 404);
        }
    }
}
