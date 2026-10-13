<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{User, Institution, State};
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class EvaluatorController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255', 'rows' => 'nullable|integer|in:5,10,25,50',
            'sort_field' => ['nullable', Rule::in(['id','name','email','created_at'])],
            'sort_direction' => 'nullable|in:asc,desc',
        ]);
        $users = User::role('Evaluador')->with(['roles','institution','evaluatorInstitution','evaluatorState'])
            ->when($filters['search'] ?? null, fn ($q,$s) => $q->where(fn ($q) =>
                $q->where('name','like',"%{$s}%")->orWhere('email','like',"%{$s}%")->orWhere('curp','like',"%{$s}%")))
            ->orderBy($filters['sort_field'] ?? 'created_at', $filters['sort_direction'] ?? 'desc')
            ->paginate($filters['rows'] ?? 10)->withQueryString();
        return Inertia::render('Admin/Security/Evaluators/Index', [
            'users' => UserResource::collection($users), 'filters' => $filters,
            'registrationLink' => route('register.evaluator'),
            'institutions' => Institution::orderBy('name')->get(['id','name']),
            'states' => State::orderBy('name')->get(['id','name']),
        ]);
    }

    public function updateConfiguration(Request $request, User $user)
    {
        abort_unless($user->hasRole('Evaluador'), 404);
        $data = $request->validate([
            'evaluator_status' => 'required|in:pending,authorized,suspended',
            'evaluator_level' => 'nullable|required_if:evaluator_status,authorized|in:local,state,national',
            'evaluator_institution_id' => ['nullable', 'required_if:evaluator_level,local',
                Rule::exists('institutions','id')->whereNull('deleted_at')],
            'evaluator_state_id' => 'nullable|required_if:evaluator_level,state|exists:states,id',
        ]);
        if ($data['evaluator_status'] === 'authorized' && !$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages(['evaluator_status' => 'El evaluador debe verificar su correo antes de ser autorizado.']);
        }
        $level = $data['evaluator_level'] ?? null;
        $user->forceFill([
            'evaluator_status' => $data['evaluator_status'], 'evaluator_level' => $level,
            'evaluator_institution_id' => $level === 'local' ? $data['evaluator_institution_id'] : null,
            'evaluator_state_id' => $level === 'state' ? $data['evaluator_state_id'] : null,
            'evaluator_authorized_by' => $data['evaluator_status'] === 'authorized' ? $request->user()->id : null,
            'evaluator_authorized_at' => $data['evaluator_status'] === 'authorized' ? now() : null,
        ])->save();
        return back()->with('success', 'Configuración del evaluador guardada.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        abort_unless($user->hasRole('Evaluador'), 404);
        $user->delete();
        return to_route('admin.evaluators.index')->with('success','Evaluador eliminado correctamente.');
    }
}
