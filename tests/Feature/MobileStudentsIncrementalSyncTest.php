<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE 7H/7I — incremental student pulls with server-derived cursors.
 *
 * GET /api/mobile/students?updated_after=... returns only rows changed since
 * the cursor, wrapped with server_time so the app never does clock arithmetic
 * on the phone. The legacy bare-array shape stays for pre-Phase-7 clients.
 */
class MobileStudentsIncrementalSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    private function admin(): string
    {
        $user = User::factory()->create();
        $user->roles()->sync(Role::where('role_name', 'Admin')->pluck('role_id'));

        $this->app['auth']->forgetGuards();

        return $user->createToken('mobile', ['mobile:access'])->plainTextToken;
    }

    private function createStudent(string $name): Student
    {
        return Student::create([
            'admission_no' => 'ADM-' . substr(md5($name . uniqid('', true)), 0, 12),
            'first_name' => $name,
            'last_name' => 'Tester',
            'date_of_birth' => '2010-01-01',
            'gender' => 'female',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'status' => 'active',
        ]);
    }

    public function test_full_pull_keeps_legacy_bare_array_shape(): void
    {
        $this->createStudent('Ada');
        $this->createStudent('Ben');

        $res = $this->withToken($this->admin())->getJson('/api/mobile/students');

        $res->assertOk()->assertJsonCount(2);
    }

    public function test_incremental_pull_wraps_rows_with_server_time(): void
    {
        $this->createStudent('Ada');

        $res = $this->withToken($this->admin())
            ->getJson('/api/mobile/students?updated_after=2000-01-01T00:00:00Z');

        $res->assertOk()
            ->assertJsonStructure(['data', 'server_time'])
            ->assertJsonCount(1, 'data');
    }

    public function test_rows_unchanged_since_cursor_are_excluded(): void
    {
        $this->createStudent('Ada'); // updated_at ≈ now
        $old = $this->createStudent('Ben');
        $old->forceFill(['updated_at' => '2020-01-01 00:00:00'])->save();

        $res = $this->withToken($this->admin())
            ->getJson('/api/mobile/students?updated_after=' . urlencode(now()->subMinutes(5)->toIso8601String()));

        $res->assertOk();
        $names = collect($res->json('data'))->pluck('first_name')->all();

        $this->assertContains('Ada', $names);
        $this->assertNotContains('Ben', $names);
    }

    public function test_invalid_cursor_is_a_422_with_message_key(): void
{
        $res = $this->withToken($this->admin())
            ->getJson('/api/mobile/students?updated_after=not-a-date');

        $res->assertStatus(422)->assertJsonStructure(['message']);
    }

    public function test_updated_at_touch_is_returned_by_the_next_incremental_pull(): void
    {
        $student = $this->createStudent('Ada');
        $cursor = now()->subMinutes(5)->toIso8601String();

        // A later rename (or any column touch) bumps students.updated_at.
        $student->update(['first_name' => 'Ada-Maria']);

        $res = $this->withToken($this->admin())
            ->getJson('/api/mobile/students?updated_after=' . urlencode($cursor));

        $res->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('Ada-Maria', $res->json('data.0.first_name'));
    }
}
