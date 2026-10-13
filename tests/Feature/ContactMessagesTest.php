<?php
namespace Tests\Feature;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
class ContactMessagesTest extends TestCase {
    use RefreshDatabase;
    public function test_only_superadmin_can_view_and_mark_messages(): void {
        $this->withoutVite();
        $message = ContactMessage::create(['name'=>'Visitante','email'=>'visitor@example.com','institution_name'=>'CENIDET','message'=>'Consulta de prueba']);
        $this->get(route('superadmin.contact-messages.index'))->assertRedirect(route('login'));
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('superadmin.contact-messages.index'))->assertForbidden();
        $this->patch(route('superadmin.contact-messages.update', $message))->assertForbidden();
        Role::findOrCreate('Super Admin', 'web'); $user->assignRole('Super Admin');
        $this->get(route('superadmin.contact-messages.index'))->assertInertia(fn(Assert $page)=>$page->component('SuperAdmin/ContactMessages/Index')->has('messages.data',1)->where('unreadCount',1));
        $this->patch(route('superadmin.contact-messages.update',$message))->assertRedirect();
        $this->assertNotNull($message->fresh()->read_at);
        $this->get(route('superadmin.contact-messages.index',['status'=>'unread']))->assertInertia(fn(Assert $page)=>$page->has('messages.data',0));
        $this->get(route('superadmin.contact-messages.index',['search'=>'absent']))->assertInertia(fn(Assert $page)=>$page->has('messages.data',0));
    }
}
