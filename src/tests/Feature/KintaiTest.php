<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\User;
use App\Models\RestTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KintaiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_validation_name(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasErrors('name');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            'お名前を入力してください',
            $errors->get('name')[0]
        );
    }

    public function test_register_validation_email(): void
    {
        $response = $this->post('/register', [
            'name' => '田中太郎',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasErrors('email');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            'メールアドレスを入力してください',
            $errors->get('email')[0]
        );
    }

    public function test_register_validation_short_password(): void
    {
        $response = $this->post('/register', [
            'name' => '田中太郎',
            'email' => 'john@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ]);
        $response->assertSessionHasErrors('password');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            'パスワードは8文字以上で入力してください',
            $errors->get('password')[0]
        );
    }

    public function test_register_validation_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => '田中太郎',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'pass',
        ]);
        $response->assertSessionHasErrors('password');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            'パスワードと一致しません',
            $errors->get('password')[0]
        );
    }

    public function test_register_validation_password(): void
    {
        $response = $this->post('/register', [
            'name' => '田中太郎',
            'email' => 'john@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);
        $response->assertSessionHasErrors('password');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            'パスワードを入力してください',
            $errors->get('password')[0]
        );
    }

    public function test_register_success(): void
    {
        $response = $this->post('/register', [
            'name' => '田中太郎',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => '田中太郎',
            'email' => 'john@example.com',
        ]);
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_login_validation_email(): void
    {
        User::create([
            'name' => '田中太郎',
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);
        $response->assertSessionHasErrors('email');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            'メールアドレスを入力してください',
            $errors->get('email')[0]
        );
    }

    public function test_login_validation_password(): void
    {
        User::create([
            'name' => '田中太郎',
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => '',
        ]);
        $response->assertSessionHasErrors('password');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            'パスワードを入力してください',
            $errors->get('password')[0]
        );
    }

    public function test_login_validation_wrong(): void
    {
        User::create([
            'name' => '田中太郎',
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);
        $response->assertSessionHasErrors('email');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            'ログイン情報が登録されていません',
            $errors->get('email')[0]
        );
    }

    public function test_admin_login_validation_email(): void
    {
        Admin::create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);
        $response->assertSessionHasErrors('email');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            'メールアドレスを入力してください',
            $errors->get('email')[0]
        );
    }

    public function test_admin_login_validation_password(): void
    {
        Admin::create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);
        $response->assertSessionHasErrors('password');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            'パスワードを入力してください',
            $errors->get('password')[0]
        );
    }

    public function test_admin_login_validation_wrong(): void
    {
        Admin::create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);
        $response->assertSessionHasErrors('email');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            'ログイン情報が登録されていません',
            $errors->get('email')[0]
        );
    }

    public function test_now_datetime(): void
    {
        $now = Carbon::now();
        Carbon::setLocale('ja');
        setlocale(LC_TIME, 'ja_JP.UTF-8');

        $user = User::factory()->create();
        $this->actingAs($user);

        $datePart = $now->format('Y年n月j日(').$now->isoFormat('ddd').')';
        $timePart = $now->format('H:i');

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee($datePart);
        $response->assertSee($timePart);
    }

    public function test_attendance_status_is_not_working(): void
    {
        $fixedDate = Carbon::create(2025, 7, 27);
        Carbon::setTestNow($fixedDate);
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<span class="bg-gray-c8 rounded-pill text-gray-69
            status px-3p py-1p">勤務外
        </span>', false);
    }

    public function test_attendance_status_is_clock_in(): void
    {
        $fixedDate = Carbon::create(2025, 7, 27);
        Carbon::setTestNow($fixedDate);
        $user = User::factory()->create();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $fixedDate->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<span class="bg-gray-c8 rounded-pill text-gray-69
            status px-3p py-1p">出勤中
        </span>', false);
    }

    public function test_attendance_status_is_rest(): void
    {
        $fixedDate = Carbon::create(2025, 7, 27);
        Carbon::setTestNow($fixedDate);
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $fixedDate->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);
        $attendance->restTimes()->create([
            'start_time' => '12:00',
            'end_time' => null,
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<span class="bg-gray-c8 rounded-pill text-gray-69
            status px-3p py-1p">休憩中
        </span>', false);
    }

    public function test_attendance_status_is_clock_out(): void
    {
        $fixedDate = Carbon::create(2025, 7, 27);
        Carbon::setTestNow($fixedDate);
        $user = User::factory()->create();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $fixedDate->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '17:00',
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<span class="bg-gray-c8 rounded-pill text-gray-69
            status px-3p py-1p">退勤済
        </span>', false);
    }

    public function test_clock_in_button(): void
    {
        $fixedDate = Carbon::create(2025, 7, 27);
        Carbon::setTestNow($fixedDate);
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('出勤');
        $response->assertSee('clock_in');

        $response = $this->post('/attendance/list', [
            'action' => 'clock_in',
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<span class="bg-gray-c8 rounded-pill text-gray-69
            status px-3p py-1p">出勤中
        </span>', false);
    }

    public function test_not_show_clock_in_button_on_clock_out(): void
    {
        $fixedDate = Carbon::create(2025, 7, 27);
        Carbon::setTestNow($fixedDate);
        $user = User::factory()->create();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $fixedDate->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '17:00',
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<span class="bg-gray-c8 rounded-pill text-gray-69
            status px-3p py-1p">退勤済
        </span>', false);
        $response->assertDontSee('<button type="submit" name="action" value="clock_in">出勤</button>', false);
    }

    public function test_show_clock_in_on_index(): void
    {
        $fixedDate = Carbon::create(2025, 7, 27);
        Carbon::setTestNow($fixedDate);
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response = $this->post('/attendance/list', [
            'action' => 'clock_in',
        ]);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSeeText('2025/07');
        $response->assertSeeText('07/27(日)');
        $response->assertSeeText('00:00');
    }

    public function test_rest_start_button(): void
    {
        $fixedDate = Carbon::create(2025, 7, 27, 10, 0);
        Carbon::setTestNow($fixedDate);
        $user = User::factory()->create();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $fixedDate->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('休憩入');
        $response->assertSee('rest_start');
        $response = $this->post('/attendance/list', [
            'action' => 'rest_start',
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<span class="bg-gray-c8 rounded-pill text-gray-69
            status px-3p py-1p">休憩中
        </span>', false);
    }

    public function test_can_many_rests(): void
    {
        $startRestTime = Carbon::create(2025, 7, 27, 10, 0);
        Carbon::setTestNow($startRestTime);
        $user = User::factory()->create();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $startRestTime->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->post('/attendance/list', [
            'action' => 'rest_start',
        ]);

        $endRestTime = Carbon::create(2025, 7, 27, 11, 0);
        Carbon::setTestNow($endRestTime);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->post('/attendance/list', [
            'action' => 'rest_end',
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('休憩入');
        $response->assertSee('rest_start');
    }

    public function test_rest_end_button(): void
    {
        $fixedDate = Carbon::create(2025, 7, 27, 10, 0);
        Carbon::setTestNow($fixedDate);
        $user = User::factory()->create();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $fixedDate->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->post('/attendance/list', [
            'action' => 'rest_start',
        ]);

        $endRestTime = Carbon::create(2025, 7, 27, 11, 0);
        Carbon::setTestNow($endRestTime);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('休憩戻');
        $response->assertSee('rest_end');
        $response = $this->post('/attendance/list', [
            'action' => 'rest_end',
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<span class="bg-gray-c8 rounded-pill text-gray-69
            status px-3p py-1p">出勤中
        </span>', false);
    }

    public function test_can_many_end_rests(): void
    {
        $fixedDate = Carbon::create(2025, 7, 27, 10, 0);
        Carbon::setTestNow($fixedDate);
        $user = User::factory()->create();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $fixedDate->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->post('/attendance/list', [
            'action' => 'rest_start',
        ]);

        $endRestTime = Carbon::create(2025, 7, 27, 11, 0);
        Carbon::setTestNow($endRestTime);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->post('/attendance/list', [
            'action' => 'rest_end',
        ]);

        $startRestTime = Carbon::create(2025, 7, 27, 12, 0);
        Carbon::setTestNow($startRestTime);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->post('/attendance/list', [
            'action' => 'rest_start',
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('休憩戻');
        $response->assertSee('rest_end');
    }

    public function test_show_rest_times_on_index(): void
    {
        $fixedDate = Carbon::create(2025, 7, 27, 10, 0);
        Carbon::setTestNow($fixedDate);
        $user = User::factory()->create();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $fixedDate->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->post('/attendance/list', [
            'action' => 'rest_start',
        ]);

        $endRestTime = Carbon::create(2025, 7, 27, 11, 0);
        Carbon::setTestNow($endRestTime);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->post('/attendance/list', [
            'action' => 'rest_end',
        ]);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSeeText('2025/07');
        $response->assertSeeText('07/27(日)');
        $response->assertSeeText('1:00');
    }

    public function test_clock_out_button(): void
    {
        $fixedDate = Carbon::create(2025, 7, 27, 17, 0);
        Carbon::setTestNow($fixedDate);
        $user = User::factory()->create();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $fixedDate->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('退勤');
        $response->assertSee('clock_out');
        $response = $this->post('/attendance/list', [
            'action' => 'clock_out',
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<span class="bg-gray-c8 rounded-pill text-gray-69
            status px-3p py-1p">退勤済
        </span>', false);
    }

    public function test_show_clock_out_on_index(): void
    {
        $fixedDate = Carbon::create(2025, 7, 27, 9, 0);
        Carbon::setTestNow($fixedDate);
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->post('/attendance/list', [
            'action' => 'clock_in',
        ]);

        $fixedDate = Carbon::create(2025, 7, 27, 17, 0);
        Carbon::setTestNow($fixedDate);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response = $this->post('/attendance/list', [
            'action' => 'clock_out',
        ]);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSeeText('2025/07');
        $response->assertSeeText('07/27(日)');
        $response->assertSeeText('17:00');
    }

    public function test_show_attendance_list(): void
    {
        $user = User::factory()->create();
        $fixedDate = Carbon::create(2025, 7, 1);
        Carbon::setTestNow($fixedDate);
        Carbon::setLocale('ja');

        $attendances = Attendance::factory()->count(5)->sequence(
            fn ($sequence) => ['date' => $fixedDate->copy()->addDays($sequence->index)]
        )->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        foreach ($attendances as $attendance) {
            RestTime::factory()->create([
                'attendance_id' => $attendance->id,
                'start_time' => '12:00',
                'end_time' => '13:00',
            ]);
        }

        $this->actingAs($user);
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        Attendance::where('user_id', $user->id)->get()->each(function ($attendance) use ($response) {
            $response->assertSeeText(Carbon::parse($attendance->date)->translatedFormat('m/d(D)'));
            $response->assertSeeText(Carbon::parse($attendance->clock_in)->format('H:i'));
            $response->assertSeeText(Carbon::parse($attendance->clock_out)->format('H:i'));
            $response->assertSeeText($attendance->formatted_total_rest);
            $response->assertSeeText($attendance->formatted_total_work);
        });
    }

    public function test_show_attendance_list_with_now_month(): void
    {
        $now = Carbon::now();

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSeeText($now->format('Y/m'));
    }

    public function test_show_previous_month_attendance(): void
    {
        $now = Carbon::create(2025, 7, 27);
        Carbon::setTestNow($now);
        Carbon::setLocale('ja');

        $user = User::factory()->create();

        $startOfJune = Carbon::create(2025, 6, 1);

        Attendance::factory()->count(5)->sequence(
            fn ($sequence) => ['date' => $startOfJune->copy()->addDays($sequence->index)]
        )->create([
            'user_id' => $user->id,
        ]);

        $startOfJuly = $now->copy()->startOfMonth();

        Attendance::factory()->count(5)->sequence(
            fn ($sequence) => ['date' => $startOfJuly->copy()->addDays($sequence->index)]
        )->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);
        $url = route('index', ['year' => 2025, 'month' => 6]);
        $response = $this->get($url);
        $response->assertStatus(200);

        $response->assertSeeText('2025/06');

        Attendance::where('user_id', $user->id)
            ->whereMonth('date', 6)
            ->get()
            ->each(function ($attendance) use ($response) {
                $response->assertSeeText(Carbon::parse($attendance->date)->translatedFormat('m/d(D)'));
                $response->assertSeeText(Carbon::parse($attendance->clock_in)->format('H:i'));
                $response->assertSeeText(Carbon::parse($attendance->clock_out)->format('H:i'));
                $response->assertSeeText($attendance->formatted_total_rest);
                $response->assertSeeText($attendance->formatted_total_work);
            });
    }

    public function test_show_next_month_attendance(): void
    {
        $now = Carbon::create(2025, 7, 27);
        Carbon::setTestNow($now);
        Carbon::setLocale('ja');

        $user = User::factory()->create();

        $startOfJuly = $now->copy()->startOfMonth();

        Attendance::factory()->count(5)->sequence(
            fn ($sequence) => ['date' => $startOfJuly->copy()->addDays($sequence->index)]
        )->create([
            'user_id' => $user->id,
        ]);

        $startOfAugust = Carbon::create(2025, 8, 1);

        Attendance::factory()->count(5)->sequence(
            fn ($sequence) => ['date' => $startOfAugust->copy()->addDays($sequence->index)]
        )->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);
        $url = route('index', ['year' => 2025, 'month' => 8]);
        $response = $this->get($url);
        $response->assertStatus(200);

        $response->assertSeeText('2025/08');

        Attendance::where('user_id', $user->id)
            ->whereMonth('date', 8)
            ->get()
            ->each(function ($attendance) use ($response) {
                $response->assertSeeText(Carbon::parse($attendance->date)->translatedFormat('m/d(D)'));
                $response->assertSeeText(Carbon::parse($attendance->clock_in)->format('H:i'));
                $response->assertSeeText(Carbon::parse($attendance->clock_out)->format('H:i'));
                $response->assertSeeText($attendance->formatted_total_rest);
                $response->assertSeeText($attendance->formatted_total_work);
            });
    }

    public function test_can_navigate_to_attendance_detail(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSee('/attendance/' . $attendance->id);

        $detailResponse = $this->get('/attendance/' . $attendance->id);
        $detailResponse->assertStatus(200);
    }
}
