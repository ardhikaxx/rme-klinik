<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $role = Role::where('name', 'admisi_kasir')->firstOrFail();

    $this->user = User::create([
        'name' => 'Petugas Admisi',
        'username' => 'admisi',
        'email' => 'admisi@klinik.test',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $this->user->roles()->attach($role->id);
});

/**
 * Create deterministic patients so row counts can be asserted exactly.
 */
function seedPatientsForPagination(int $count): void
{
    foreach (range(1, $count) as $i) {
        Patient::create([
            'medical_record_number' => sprintf('RM-2026-%06d', $i),
            'nik' => sprintf('320123%010d', $i),
            'name' => "Pasien Uji {$i}",
            'gender' => $i % 2 === 0 ? 'P' : 'L',
            'date_of_birth' => '1990-01-01',
            'phone' => '081234567890',
            'address' => 'Jl. Uji Nomor '.$i,
        ]);
    }
}

/**
 * Pull the rendered number-only navigation block out of the response.
 */
function paginationBlock(string $html): string
{
    preg_match('/<ul class="pagination pagination-sm mb-0">.*?<\/ul>/s', $html, $matches);

    return $matches[0] ?? '';
}

/**
 * Page numbers that are rendered as a clickable link or as the active page.
 */
function paginationNumbers(string $block): array
{
    preg_match_all('/<a class="page-link"[^>]*>(\d+)</', $block, $matches);

    return array_map('intval', $matches[1]);
}

test('tables default to 50 rows per page', function () {
    seedPatientsForPagination(60);

    $response = $this->actingAs($this->user)->get(route('patients.index'));

    $response->assertOk();
    $response->assertSee('<strong>1</strong>&ndash;<strong>50</strong>', false);
    $response->assertSee('dari <strong>60</strong> pasien', false);
    expect(substr_count($response->getContent(), 'Pasien Uji '))->toBe(50);
});

test('rows per page can be switched to 25, 100 or 250', function () {
    seedPatientsForPagination(60);

    $twentyFive = $this->actingAs($this->user)->get(route('patients.index', ['per_page' => 25]));
    $twentyFive->assertOk();
    $twentyFive->assertSee('<strong>1</strong>&ndash;<strong>25</strong>', false);
    expect(substr_count($twentyFive->getContent(), 'Pasien Uji '))->toBe(25);

    $hundred = $this->actingAs($this->user)->get(route('patients.index', ['per_page' => 100]));
    $hundred->assertOk();
    $hundred->assertSee('<strong>1</strong>&ndash;<strong>60</strong>', false);
    $hundred->assertDontSee('rme-pagination');
    expect(substr_count($hundred->getContent(), 'Pasien Uji '))->toBe(60);

    foreach ([25, 50, 100, 250] as $option) {
        $hundred->assertSee('value="'.$option.'"', false);
    }
});

test('an unknown rows per page value falls back to the default of 50', function () {
    seedPatientsForPagination(60);

    $response = $this->actingAs($this->user)->get(route('patients.index', ['per_page' => 7]));

    $response->assertOk();
    $response->assertSee('<strong>1</strong>&ndash;<strong>50</strong>', false);
    expect(substr_count($response->getContent(), 'Pasien Uji '))->toBe(50);
});

test('pagination only renders page numbers without any arrow controls', function () {
    seedPatientsForPagination(60);

    $html = $this->actingAs($this->user)
        ->get(route('patients.index', ['per_page' => 25]))
        ->assertOk()
        ->getContent();

    $block = paginationBlock($html);

    expect($block)->not->toBeEmpty()
        ->and($block)->not->toContain('previous')
        ->and($block)->not->toContain('next')
        ->and($block)->not->toContain('first')
        ->and($block)->not->toContain('last')
        ->and($block)->not->toContain('fa-')
        ->and($block)->not->toContain('chevron')
        ->and($block)->not->toContain('aria-label="Sebelumnya"')
        ->and(paginationNumbers($block))->toBe([1, 2, 3]);

    expect($block)->toContain('aria-current="page">1<');
});

test('the active page is clearly marked and other pages stay clickable', function () {
    seedPatientsForPagination(60);

    $html = $this->actingAs($this->user)
        ->get(route('patients.index', ['per_page' => 25, 'page' => 2]))
        ->assertOk()
        ->getContent();

    $block = paginationBlock($html);

    expect($block)->toContain('aria-current="page">2<')
        ->and($block)->toContain('href=');

    // Page 1 and page 3 remain ordinary, clickable links.
    expect($block)->toMatch('/<a class="page-link" href="[^"]*">1<\/a>/')
        ->and($block)->toMatch('/<a class="page-link" href="[^"]*">3<\/a>/');
});

test('page links keep the active search and rows per page query string', function () {
    seedPatientsForPagination(60);

    $html = $this->actingAs($this->user)
        ->get(route('patients.index', ['search' => 'Uji', 'per_page' => 25, 'page' => 2]))
        ->assertOk()
        ->getContent();

    $block = paginationBlock($html);

    expect($block)->toContain('search=Uji')
        ->and($block)->toContain('per_page=25')
        ->and($block)->toContain('page=1');
});

test('a filter change restarts at the first page', function () {
    seedPatientsForPagination(60);

    $response = $this->actingAs($this->user)->get(route('patients.index', [
        'search' => 'Uji',
        'gender' => 'L',
    ]));

    $response->assertOk();
    $response->assertDontSee('page=2');
    $response->assertSee('<strong>1</strong>&ndash;', false);
});

test('large datasets keep a bounded number-only window with a visual ellipsis', function () {
    $render = fn (int $page): string => (new LengthAwarePaginator(
        [],
        300,
        25,
        $page,
        ['path' => '/patients', 'query' => ['search' => 'Uji']]
    ))->links('pagination.numbers')->render();

    foreach ([1, 6, 12] as $page) {
        $block = paginationBlock($render($page));
        $numbers = paginationNumbers($block);

        expect(count($numbers))->toBe(count(array_unique($numbers)))
            ->and(count($numbers))->toBeLessThanOrEqual(7)
            ->and($numbers)->toContain($page)
            ->and($block)->toContain('&hellip;')
            ->and($block)->not->toContain('previous')
            ->and($block)->not->toContain('next');
    }

    // Middle of the list: the neighbours of the active page are visible.
    expect(paginationNumbers(paginationBlock($render(6))))->toBe([1, 4, 5, 6, 7, 8, 12]);

    // End of the list: the last page is visible and the active page stays shown.
    expect(paginationNumbers(paginationBlock($render(12))))->toBe([1, 10, 11, 12]);

    // Links still carry the original query string.
    expect($render(6))->toContain('search=Uji');
});

test('a page beyond the range still shows a valid active page number', function () {
    $html = (new LengthAwarePaginator([], 60, 25, 99, ['path' => '/patients']))
        ->links('pagination.numbers')
        ->render();

    expect($html)->toContain('aria-current="page">3<')
        ->and($html)->not->toContain('&hellip;');
});

test('all tables show all historical data and place newest records on page 1 row 1', function () {
    // 1. Patient table ordering
    $olderPatient = Patient::create([
        'medical_record_number' => 'RM-2020-000001',
        'nik' => '3201230000000001',
        'name' => 'Pasien Lama 2020',
        'gender' => 'L',
        'date_of_birth' => '1980-01-01',
        'phone' => '081111111111',
        'address' => 'Jl. Lama',
        'created_at' => now()->subYears(2),
    ]);

    $newestPatient = Patient::create([
        'medical_record_number' => 'RM-2026-999999',
        'nik' => '3201239999999999',
        'name' => 'Pasien Baru Terbaru',
        'gender' => 'P',
        'date_of_birth' => '1995-05-05',
        'phone' => '082222222222',
        'address' => 'Jl. Baru',
        'created_at' => now(),
    ]);

    $respPatients = $this->actingAs($this->user)->get(route('patients.index'))->assertOk();
    $patients = $respPatients->viewData('patients');
    expect($patients->total())->toBeGreaterThanOrEqual(2)
        ->and($patients->first()->id)->toBe($newestPatient->id);

    // 2. Visit table shows all dates and places newest visit at page 1 row 1
    $dept = Department::create(['code' => 'DEP-MED-PAG', 'name' => 'Medis']);
    $poly = Polyclinic::create(['code' => 'POL-UMU-PAG', 'name' => 'Poli Umum', 'is_active' => true]);
    $docUser = User::create([
        'name' => 'dr. Dokter Test',
        'username' => 'drtest',
        'email' => 'drtest@klinik.test',
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);
    $doc = Employee::create([
        'user_id' => $docUser->id,
        'department_id' => $dept->id,
        'employee_number' => 'DOC-PAG-01',
        'name' => 'dr. Dokter Test',
        'gender' => 'L',
        'profession' => 'dokter',
        'is_active' => true,
    ]);

    $olderVisit = Visit::create([
        'visit_number' => 'REG-20200101-0001',
        'patient_id' => $olderPatient->id,
        'polyclinic_id' => $poly->id,
        'doctor_id' => $doc->id,
        'registered_by' => $this->user->id,
        'registration_date' => now()->subYears(2),
        'visit_type' => 'lama',
        'status' => 'selesai',
        'created_at' => now()->subYears(2),
    ]);

    $newestVisit = Visit::create([
        'visit_number' => 'REG-20261116-9999',
        'patient_id' => $newestPatient->id,
        'polyclinic_id' => $poly->id,
        'doctor_id' => $doc->id,
        'registered_by' => $this->user->id,
        'registration_date' => now(),
        'visit_type' => 'baru',
        'status' => 'menunggu_dokter',
        'created_at' => now(),
    ]);

    $respVisits = $this->actingAs($this->user)->get(route('visits.index'))->assertOk();
    $visits = $respVisits->viewData('visits');
    expect($visits->total())->toBeGreaterThanOrEqual(2)
        ->and($visits->first()->id)->toBe($newestVisit->id);
});
