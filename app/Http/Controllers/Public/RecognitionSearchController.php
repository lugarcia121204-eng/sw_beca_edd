<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Recognition;
use App\Services\PdfGenerationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecognitionSearchController extends Controller
{
    protected PdfGenerationService $pdfService;

    public function __construct(PdfGenerationService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Show the recognition search page, with results if filters are present.
     */
    public function index(Request $request)
    {
        $curp  = trim($request->input('curp', ''));
        $name  = trim($request->input('name', ''));
        $folio = trim($request->input('folio', ''));

        $recognitions = null;
        $searched     = $curp !== '' || $name !== '' || $folio !== '';

        if ($searched) {
            $query = Recognition::with(['user', 'announcement', 'template'])
                ->where('active', true)
                ->whereNotNull('identifier')
                ->where(function ($q) {
                    $q->where('type', 'evaluator')
                      ->orWhere(function ($sub) {
                          // Allow teacher recognitions for announcements that have a results stage defined
                          // (covers current resultados window, already finished ones, and announcements with results planned)
                          $sub->where('type', 'postulante')
                              ->whereHas('announcement', function ($a) {
                                  $a->whereHas('calendar', function ($c) {
                                      $c->whereNotNull('results_start');
                                  });
                              });
                      });
                });

            if ($folio !== '') {
                $query->whereRaw('UPPER(identifier) = ?', [strtoupper($folio)]);
            }

            if ($curp !== '') {
                $query->whereHas('user', fn ($q) => $q->whereRaw('UPPER(curp) = ?', [strtoupper($curp)]));
            }

            if ($name !== '') {
                $query->whereHas('user', fn ($q) => $q->whereRaw('UPPER(name) LIKE ?', ['%' . strtoupper($name) . '%']));
            }

            $recognitions = $query->orderBy('sent_at', 'desc')->get()->map(function ($r) {
                return [
                    'id'            => $r->id,
                    'folio'         => $r->identifier,
                    'participant'   => $r->user ? $r->user->name : 'N/A',
                    'template_name' => $r->template ? $r->template->name : 'Reconocimiento',
                    'template_type' => $r->template ? $r->template->type : null,
                    'announcement'  => $r->announcement ? $r->announcement->name : 'N/A',
                    'year'          => $r->sent_at ? Carbon::parse($r->sent_at)->year : null,
                    'date'          => $r->sent_at ? Carbon::parse($r->sent_at)->isoFormat('D [de] MMMM [de] YYYY') : null,
                ];
            })->values()->all();
        }

        return Inertia::render('RecognitionSearch', [
            'recognitions' => $recognitions,
            'searched'     => $searched,
            'filters'      => [
                'curp'  => $curp,
                'name'  => $name,
                'folio' => $folio,
            ],
        ]);
    }

    /**
     * Generate and stream the recognition PDF for a given identifier (public).
     */
    public function download(string $identifier)
    {
        $recognition = Recognition::with(['user', 'announcement', 'template'])
            ->where('identifier', $identifier)
            ->where('active', true)
            ->where(function ($q) {
                $q->where('type', 'evaluator')
                  ->orWhere(function ($sub) {
                      $sub->where('type', 'postulante')
                          ->whereHas('announcement', function ($a) {
                              $a->whereHas('calendar', function ($c) {
                                  $c->whereNotNull('results_start');
                              });
                          });
                  });
            })
            ->firstOrFail();

        try {
            return $this->pdfService->generateRecognitionPdf($recognition, $recognition->user);
        } catch (\Exception $e) {
            abort(404, 'No se pudo generar el documento.');
        }
    }
}
