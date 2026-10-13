<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\WorkDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
class WorkDocumentsTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); Storage::fake('local'); $this->withoutVite(); }
    private function admin(): User { $user = User::factory()->create(); Role::findOrCreate('Super Admin', 'web'); $user->assignRole('Super Admin'); return $user; }
    private function upload(bool $published = true): array { return ['title' => 'Formato de trabajo', 'description' => 'Documento de prueba', 'category' => 'formatos', 'published' => $published, 'file' => UploadedFile::fake()->create('formato.pdf', 20, 'application/pdf')]; }
    public function test_superadmin_upload_is_publicly_listed_and_downloadable(): void {
        $this->actingAs($this->admin())->post(route('superadmin.work-documents.store'), $this->upload())->assertRedirect(route('superadmin.work-documents.index'));
        $document = WorkDocument::firstOrFail(); Storage::disk('local')->assertExists($document->file_path);
        auth()->logout();
        $this->get(route('documents.index'))->assertInertia(fn (Assert $page) => $page->component('Documents')->has('workDocuments', 1)->where('workDocuments.0.title', 'Formato de trabajo'));
        $this->get(route('work-documents.download', $document))->assertOk()->assertDownload('formato.pdf');
        $this->get(route('work-documents.preview', $document))->assertOk()->assertHeader('content-type', 'application/pdf');
    }
    public function test_hidden_documents_are_not_public(): void {
        $this->actingAs($this->admin())->post(route('superadmin.work-documents.store'), $this->upload(false))->assertRedirect();
        $document = WorkDocument::firstOrFail(); auth()->logout();
        $this->get(route('documents.index'))->assertInertia(fn (Assert $page) => $page->has('workDocuments', 0));
        $this->get(route('work-documents.download', $document))->assertNotFound();
        $this->get(route('work-documents.preview', $document))->assertNotFound();
    }
    public function test_non_superadmin_cannot_manage_documents(): void {
        $this->post(route('superadmin.work-documents.store'), $this->upload())->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->post(route('superadmin.work-documents.store'), $this->upload())->assertForbidden();
        $this->assertDatabaseCount('work_documents', 0);
    }
    public function test_replacement_removes_old_file_and_deletion_removes_public_access(): void {
        $this->actingAs($this->admin())->post(route('superadmin.work-documents.store'), $this->upload());
        $document = WorkDocument::firstOrFail(); $old = $document->file_path;
        $this->put(route('superadmin.work-documents.update', $document), $this->upload())->assertRedirect();
        Storage::disk('local')->assertMissing($old); $document->refresh(); Storage::disk('local')->assertExists($document->file_path);
        $path = $document->file_path; $this->delete(route('superadmin.work-documents.destroy', $document))->assertRedirect();
        Storage::disk('local')->assertMissing($path); $this->assertSoftDeleted('work_documents', ['id' => $document->id]);
        $this->get(route('work-documents.download', $document))->assertNotFound();
    }
    public function test_import_is_repeatable_and_hidden_sources_do_not_reappear(): void {
        Http::fake(fn ($request) => Http::response(str_contains($request->url(), '.docx') ? 'PK-test-document' : '%PDF-test-document', 200));
        $this->artisan('work-documents:import-public')->assertSuccessful();
        $this->assertDatabaseCount('work_documents', 8);
        $this->artisan('work-documents:import-public')->assertSuccessful();
        $this->assertDatabaseCount('work_documents', 8); Http::assertSentCount(8);
        $document = WorkDocument::where('source_key', 'doc1')->firstOrFail(); $document->update(['published' => false]);
        $this->get(route('documents.index'))->assertInertia(fn (Assert $page) => $page->has('workDocuments', 7)->has('managedDocumentIds', 8));
        $document->delete();
        $this->get(route('documents.index'))->assertInertia(fn (Assert $page) => $page->has('workDocuments', 7)->has('managedDocumentIds', 8));
        $this->get(route('work-documents.download', $document))->assertNotFound();
    }
    public function test_edit_without_replacement_can_hide_document(): void {
        $this->actingAs($this->admin())->post(route('superadmin.work-documents.store'), $this->upload());
        $document = WorkDocument::firstOrFail(); $path = $document->file_path;
        $this->put(route('superadmin.work-documents.update', $document), ['title' => 'Nombre actualizado', 'description' => null, 'category' => 'guias', 'published' => false])->assertRedirect();
        $document->refresh(); $this->assertSame('Nombre actualizado', $document->title); $this->assertSame($path, $document->file_path); Storage::disk('local')->assertExists($path);
        $this->get(route('superadmin.work-documents.index'))->assertInertia(fn (Assert $page) => $page->component('SuperAdmin/Catalog/WorkDocuments/Index')->has('documents.data', 1));
        auth()->logout(); $this->get(route('work-documents.download', $document))->assertNotFound();
    }
    public function test_invalid_file_and_oversized_upload_are_rejected(): void {
        $data = $this->upload(); $data['file'] = UploadedFile::fake()->create('script.php', 1, 'application/x-php');
        $this->actingAs($this->admin())->post(route('superadmin.work-documents.store'), $data)->assertSessionHasErrors('file');
        $data['file'] = UploadedFile::fake()->create('large.pdf', 20481, 'application/pdf');
        $this->post(route('superadmin.work-documents.store'), $data)->assertSessionHasErrors('file');
        $this->assertDatabaseCount('work_documents', 0);
    }
}
