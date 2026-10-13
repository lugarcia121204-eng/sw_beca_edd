<?php
namespace App\Http\Controllers\SuperAdmin;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;
class ContactMessageController extends Controller
{
    public function index(Request $request) {
        $filters = $request->validate(['search' => 'nullable|string|max:255', 'status' => 'nullable|in:unread,read', 'rows' => 'nullable|integer|in:5,10,25,50']);
        $query = ContactMessage::query();
        if ($search = $filters['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                foreach (['name', 'email', 'institution_name', 'message'] as $column) {
                    $q->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }
        if (($filters['status'] ?? '') === 'unread') $query->whereNull('read_at');
        if (($filters['status'] ?? '') === 'read') $query->whereNotNull('read_at');
        return Inertia::render('SuperAdmin/ContactMessages/Index', [
            'messages' => $query->latest()->paginate($filters['rows'] ?? 10)->withQueryString(),
            'filters' => $filters, 'unreadCount' => ContactMessage::whereNull('read_at')->count(),
        ]);
    }
    public function update(ContactMessage $contactMessage) {
        if (!$contactMessage->read_at) $contactMessage->update(['read_at' => now()]);
        return back();
    }
}
