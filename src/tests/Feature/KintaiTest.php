<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Request as AttendanceRequest;
use App\Models\RestTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Tests\TestCase;

class KintaiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(VerifyEmailViewResponse::class, function () {
            return new class implements VerifyEmailViewResponse
            {
                public function toResponse($request)
                {
                    return response()->view('auth.verify-email');
                }
            };
        });
    }

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
        $startOfJuly = Carbon::create(2025, 7, 1);
        Carbon::setTestNow($startOfJuly);
        Carbon::setLocale('ja');

        $user = User::factory()->create();

        $startOfJune = Carbon::create(2025, 6, 1);

        Attendance::factory()->count(5)->sequence(
            fn ($sequence) => ['date' => $startOfJune->copy()->addDays($sequence->index)]
        )->create([
            'user_id' => $user->id,
        ]);

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
        $startOfJuly = Carbon::create(2025, 7, 1);
        Carbon::setTestNow($startOfJuly);
        Carbon::setLocale('ja');

        $user = User::factory()->create();

        Attendance::factory()->count(5)->sequence(
            fn ($sequence) => ['date' => $startOfJuly->copy()->addDays($sequence->index)]
        )->create([
            'user_id' => $user->id,
        ]);

        $startOfAugust = $startOfJuly->copy()->addMonth()->startOfMonth();

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

        $response->assertSee('/attendance/'.$attendance->id);
        $response->assertSeeText('詳細');

        $detailResponse = $this->get('/attendance/'.$attendance->id);
        $detailResponse->assertStatus(200);
    }

    public function test_show_login_user_name(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance/'.$attendance->id);
        $response->assertStatus(200);
        $response->assertSee('<td class="py-4p px-4p">'.$user->name.'</td>', false);
    }

    public function test_show_select_date(): void
    {
        Carbon::setLocale('ja');
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance/'.$attendance->id);
        $response->assertStatus(200);
        $response->assertSeeText(Carbon::parse($attendance->date)->format('Y年'));
        $response->assertSeeText(Carbon::parse($attendance->date)->translatedFormat('n月j日'));
        $response->assertSee('value="09:00"', false);
        $response->assertSee('value="18:00"', false);
    }

    public function test_show_clock_in_out(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance/'.$attendance->id);
        $response->assertStatus(200);
        $response->assertSee('name="clock_in"', false);
        $response->assertSee('value="09:00"', false);
        $response->assertSee('name="clock_out"', false);
        $response->assertSee('value="18:00"', false);
    }

    public function test_show_rest_time(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        RestTime::factory()->create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance/'.$attendance->id);
        $response->assertStatus(200);
        $response->assertSee('name="rest[0][start_time]"', false);
        $response->assertSee('value="12:00"', false);
        $response->assertSee('name="rest[0][end_time]"', false);
        $response->assertSee('value="13:00"', false);
    }

    public function test_validation_clock_in(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance/'.$attendance->id);
        $response->assertStatus(200);

        $response = $this->post('/stamp_correction_request/list', [
            'attendance_id' => $attendance->id,
            'clock_in' => '12:00',
            'clock_out' => '08:00',
            'reason' => 'テスト申請',
        ]);
        $response->assertSessionHasErrors('clock_in');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            '出勤時間もしくは退勤時間が不適切な値です',
            $errors->get('clock_in')[0]
        );
    }

    public function test_validation_rest_start_time(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance/'.$attendance->id);
        $response->assertStatus(200);

        $response = $this->post('/stamp_correction_request/list', [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest' => [
                ['start_time' => '19:00', 'end_time' => '12:00'],
            ],
            'reason' => 'テスト申請',
        ]);

        $response->assertSessionHasErrors('rest.0.start_time');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            '休憩時間が不適切な値です',
            $errors->get('rest.0.start_time')[0]
        );
    }

    public function test_validation_rest_end_time(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance/'.$attendance->id);
        $response->assertStatus(200);

        $response = $this->post('/stamp_correction_request/list', [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest' => [
                ['start_time' => '12:00', 'end_time' => '19:00'],
            ],
            'reason' => 'テスト申請',
        ]);
        $response->assertSessionHasErrors('rest.0.end_time');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            '休憩時間もしくは退勤時間が不適切な値です',
            $errors->get('rest.0.end_time')[0]
        );
    }

    public function test_validation_reason(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance/'.$attendance->id);
        $response->assertStatus(200);

        $response = $this->post('/stamp_correction_request/list', [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest' => [
                ['start_time' => '12:00', 'end_time' => '13:00'],
            ],
            'reason' => '',
        ]);
        $response->assertSessionHasErrors('reason');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            '備考を記入してください',
            $errors->get('reason')[0]
        );
    }

    public function test_request_success(): void
    {
        Carbon::setLocale('ja');
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance/'.$attendance->id);
        $response->assertStatus(200);

        $response = $this->post('/stamp_correction_request/list', [
            'attendance_id' => $attendance->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'rest' => [
                ['start_time' => '12:00', 'end_time' => '13:00'],
            ],
            'reason' => 'test',
        ]);

        $attendanceRequest = AttendanceRequest::with('attendance', 'requestRests')->latest()->first();

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('request.approve', ['attendance_correct_request' => $attendanceRequest->id]));
        $response->assertStatus(200);
        $response->assertSeeText($user->name);
        $response->assertSeeText(Carbon::parse($attendanceRequest->attendance->date)->format('Y年'));
        $response->assertSeeText(Carbon::parse($attendanceRequest->attendance->date)->translatedFormat('n月j日'));
        $response->assertSeeText(Carbon::parse($attendanceRequest->clock_in)->format('H:i'));
        $response->assertSeeText(Carbon::parse($attendanceRequest->clock_out)->format('H:i'));
        $response->assertSeeText(Carbon::parse($attendanceRequest->requestRests[0]->start_time)->format('H:i'));
        $response->assertSeeText(Carbon::parse($attendanceRequest->requestRests[0]->end_time)->format('H:i'));
        $response->assertSeeText($attendanceRequest->reason);

        $response = $this->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);
        $response->assertSeeText('承認待ち');
        $response->assertSeeText($user->name);
        $response->assertSeeText(Carbon::parse($attendanceRequest->attendance->date)->format('Y/m/d'));
        $response->assertSeeText($attendanceRequest->reason);
        $response->assertSeeText($attendanceRequest->created_at->format('Y/m/d'));
    }

    public function test_pending_request(): void
    {
        $user = User::factory()->create();
        $fixedDate = Carbon::create(2025, 7, 1);
        Carbon::setTestNow($fixedDate);

        $attendances = Attendance::factory()->count(5)->sequence(
            fn ($sequence) => ['date' => $fixedDate->copy()->addDays($sequence->index)->format('Y-m-d')]
        )->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $attendanceRequests = collect();
        foreach ($attendances as $attendance) {
            $attendanceRequests->push(
                AttendanceRequest::factory()->create([
                    'attendance_id' => $attendance->id,
                    'clock_in' => '10:00',
                    'clock_out' => '19:00',
                    'reason' => "テスト申請{$attendance->id}",
                ])
            );
        }

        $this->actingAs($user);
        $response = $this->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);
        foreach ($attendanceRequests as $attendanceRequest) {
            $response->assertSeeText($user->name);
            $response->assertSeeText(Carbon::parse($attendanceRequest->attendance->date)->format('Y/m/d'));
            $response->assertSeeText($attendanceRequest->reason);
        }
    }

    public function test_approved_request(): void
    {
        $user = User::factory()->create();
        $fixedDate = Carbon::create(2025, 7, 1);
        Carbon::setTestNow($fixedDate);

        $attendances = Attendance::factory()->count(5)->sequence(
            fn ($sequence) => ['date' => $fixedDate->copy()->addDays($sequence->index)->format('Y-m-d')]
        )->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $attendanceRequests = collect();
        foreach ($attendances as $attendance) {
            $attendanceRequests->push(
                AttendanceRequest::factory()->create([
                    'attendance_id' => $attendance->id,
                    'clock_in' => '10:00',
                    'clock_out' => '19:00',
                    'reason' => "テスト申請{$attendance->id}",
                    'approved' => true,
                ])
            );
        }

        $this->actingAs($user);
        $response = $this->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);
        foreach ($attendanceRequests as $attendanceRequest) {
            $response->assertSeeText($user->name);
            $response->assertSeeText(Carbon::parse($attendanceRequest->attendance->date)->format('Y/m/d'));
            $response->assertSeeText($attendanceRequest->reason);
        }
    }

    public function test_navigate_to_attendance_detail_on_request_list(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $attendanceRequest = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'reason' => 'テスト申請',
        ]);

        $this->actingAs($user);
        $response = $this->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);

        $response->assertSee('/attendance/'.$attendanceRequest->attendance->id);
        $response->assertSeeText('詳細');

        $response = $this->get('/attendance/'.$attendanceRequest->attendance->id);
        $response->assertStatus(200);
    }

    public function test_admin_show_attendance_list(): void
    {
        $users = User::factory()->count(5)->create();
        $admin = Admin::factory()->create();
        $fixedDate = Carbon::create(2025, 7, 1);
        Carbon::setTestNow($fixedDate);
        Carbon::setLocale('ja');

        foreach ($users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $fixedDate->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
            ]);
        }

        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);

        foreach ($users as $user) {
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $fixedDate->format('Y-m-d'))
                ->first();
            $response->assertSeeText($user->name);
            $response->assertSeeText(Carbon::parse($attendance->clock_in)->format('H:i'));
            $response->assertSeeText(Carbon::parse($attendance->clock_out)->format('H:i'));
            $response->assertSeeText($attendance->formatted_total_rest);
            $response->assertSeeText($attendance->formatted_total_work);
        }
    }

    public function test_admin_show_attendance_list_with_now_date(): void
    {
        $users = User::factory()->count(5)->create();
        $admin = Admin::factory()->create();
        $fixedDate = Carbon::create(2025, 7, 1);
        Carbon::setTestNow($fixedDate);
        Carbon::setLocale('ja');

        foreach ($users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $fixedDate->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
            ]);
        }

        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);

        $response->assertSeeText('2025/07/01');
    }

    public function test_admin_show_previous_day_attendance(): void
    {
        $fixedDate = Carbon::create(2025, 7, 1);
        Carbon::setTestNow($fixedDate);
        Carbon::setLocale('ja');

        $users = User::factory()->count(5)->create();
        $admin = Admin::factory()->create();
        $previousDate = $fixedDate->copy()->subDay();

        foreach ($users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $previousDate->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
            ]);
        }

        $this->actingAs($admin, 'admin');
        $url = route('admin-index', ['year' => 2025, 'month' => 6, 'day' => 30]);
        $response = $this->get($url);
        $response->assertStatus(200);

        $response->assertSeeText('2025/06/30');

        foreach ($users as $user) {
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $previousDate->format('Y-m-d'))
                ->first();
            $response->assertSeeText($user->name);
            $response->assertSeeText(Carbon::parse($attendance->clock_in)->format('H:i'));
            $response->assertSeeText(Carbon::parse($attendance->clock_out)->format('H:i'));
            $response->assertSeeText($attendance->formatted_total_rest);
            $response->assertSeeText($attendance->formatted_total_work);
        }
    }

    public function test_admin_show_next_day_attendance(): void
    {
        $fixedDate = Carbon::create(2025, 7, 1);
        Carbon::setTestNow($fixedDate);
        Carbon::setLocale('ja');

        $users = User::factory()->count(5)->create();
        $admin = Admin::factory()->create();
        $nextDate = $fixedDate->copy()->addDay();

        foreach ($users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $nextDate->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
            ]);
        }

        $this->actingAs($admin, 'admin');
        $url = route('admin-index', ['year' => 2025, 'month' => 7, 'day' => 2]);
        $response = $this->get($url);
        $response->assertStatus(200);

        $response->assertSeeText('2025/07/02');

        foreach ($users as $user) {
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $nextDate->format('Y-m-d'))
                ->first();
            $response->assertSeeText($user->name);
            $response->assertSeeText(Carbon::parse($attendance->clock_in)->format('H:i'));
            $response->assertSeeText(Carbon::parse($attendance->clock_out)->format('H:i'));
            $response->assertSeeText($attendance->formatted_total_rest);
            $response->assertSeeText($attendance->formatted_total_work);
        }
    }

    public function test_admin_show_select_date(): void
    {
        $fixedDate = Carbon::create(2025, 7, 1);
        Carbon::setTestNow($fixedDate);
        Carbon::setLocale('ja');

        $user = User::factory()->create();
        $admin = Admin::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $fixedDate->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get('/attendance/'.$attendance->id);
        $response->assertStatus(200);
        $response->assertSeeText($user->name);
        $response->assertSeeText(Carbon::parse($attendance->date)->format('Y年'));
        $response->assertSeeText(Carbon::parse($attendance->date)->translatedFormat('n月j日'));
        $response->assertSee('value="09:00"', false);
        $response->assertSee('value="18:00"', false);
    }

    public function test_admin_validation_clock_in(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get('/attendance/'.$attendance->id);
        $response->assertStatus(200);

        $response = $this->patch('/admin/attendance/list', [
            'attendance_id' => $attendance->id,
            'clock_in' => '12:00',
            'clock_out' => '08:00',
            'reason' => 'テスト申請',
        ]);
        $response->assertSessionHasErrors('clock_in');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            '出勤時間もしくは退勤時間が不適切な値です',
            $errors->get('clock_in')[0]
        );
    }

    public function test_admin_validation_rest_start_time(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get('/attendance/'.$attendance->id);
        $response->assertStatus(200);

        $response = $this->patch('/admin/attendance/list', [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest' => [
                ['start_time' => '19:00', 'end_time' => '12:00'],
            ],
            'reason' => 'テスト申請',
        ]);

        $response->assertSessionHasErrors('rest.0.start_time');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            '休憩時間が不適切な値です',
            $errors->get('rest.0.start_time')[0]
        );
    }

    public function test_admin_validation_rest_end_time(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get('/attendance/'.$attendance->id);
        $response->assertStatus(200);

        $response = $this->patch('/admin/attendance/list', [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest' => [
                ['start_time' => '12:00', 'end_time' => '19:00'],
            ],
            'reason' => 'テスト申請',
        ]);
        $response->assertSessionHasErrors('rest.0.end_time');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            '休憩時間もしくは退勤時間が不適切な値です',
            $errors->get('rest.0.end_time')[0]
        );
    }

    public function test_admin_validation_reason(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get('/attendance/'.$attendance->id);
        $response->assertStatus(200);

        $response = $this->patch('/admin/attendance/list', [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rest' => [
                ['start_time' => '12:00', 'end_time' => '13:00'],
            ],
            'reason' => '',
        ]);
        $response->assertSessionHasErrors('reason');
        $errors = $response->getSession()->get('errors');
        $this->assertEquals(
            '備考を記入してください',
            $errors->get('reason')[0]
        );
    }

    public function test_admin_show_staffs(): void
    {
        $admin = Admin::factory()->create();
        $users = User::factory()->count(5)->create();

        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/staff/list');
        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSeeText($user->name);
            $response->assertSeeText($user->email);
        }
    }

    public function test_show_select_staff_attendance_list(): void
    {
        $admin = Admin::factory()->create();
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

        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/attendance/staff/'.$user->id);
        $response->assertStatus(200);

        Attendance::where('user_id', $user->id)->get()->each(function ($attendance) use ($response) {
            $response->assertSeeText($attendance->user->name);
            $response->assertSeeText(Carbon::parse($attendance->date)->translatedFormat('m/d(D)'));
            $response->assertSeeText(Carbon::parse($attendance->clock_in)->format('H:i'));
            $response->assertSeeText(Carbon::parse($attendance->clock_out)->format('H:i'));
            $response->assertSeeText($attendance->formatted_total_rest);
            $response->assertSeeText($attendance->formatted_total_work);
        });
    }

    public function test_admin_show_previous_month_staff_attendance(): void
    {
        Carbon::setLocale('ja');

        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $startOfJune = Carbon::create(2025, 6, 1);

        Attendance::factory()->count(5)->sequence(
            fn ($sequence) => ['date' => $startOfJune->copy()->addDays($sequence->index)]
        )->create([
            'user_id' => $user->id,
        ]);

        $startOfJuly = $startOfJune->copy()->addMonth()->startOfMonth();

        Attendance::factory()->count(5)->sequence(
            fn ($sequence) => ['date' => $startOfJuly->copy()->addDays($sequence->index)]
        )->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($admin, 'admin');
        $url = route('staff-attendance.show', ['id' => $user->id, 'year' => 2025, 'month' => 6]);
        $response = $this->get($url);
        $response->assertStatus(200);

        $response->assertSeeText('2025/06');

        Attendance::where('user_id', $user->id)
            ->whereMonth('date', 6)
            ->get()
            ->each(function ($attendance) use ($response) {
                $response->assertSeeText($attendance->user->name);
                $response->assertSeeText(Carbon::parse($attendance->date)->translatedFormat('m/d(D)'));
                $response->assertSeeText(Carbon::parse($attendance->clock_in)->format('H:i'));
                $response->assertSeeText(Carbon::parse($attendance->clock_out)->format('H:i'));
                $response->assertSeeText($attendance->formatted_total_rest);
                $response->assertSeeText($attendance->formatted_total_work);
            });
    }

    public function test_admin_show_next_month_staff_attendance(): void
    {
        Carbon::setLocale('ja');

        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $startOfJuly = Carbon::create(2025, 7, 1);

        Attendance::factory()->count(5)->sequence(
            fn ($sequence) => ['date' => $startOfJuly->copy()->addDays($sequence->index)]
        )->create([
            'user_id' => $user->id,
        ]);

        $startOfAugust = $startOfJuly->copy()->addMonth()->startOfMonth();

        Attendance::factory()->count(5)->sequence(
            fn ($sequence) => ['date' => $startOfAugust->copy()->addDays($sequence->index)]
        )->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($admin, 'admin');
        $url = route('staff-attendance.show', ['id' => $user->id, 'year' => 2025, 'month' => 8]);
        $response = $this->get($url);
        $response->assertStatus(200);

        $response->assertSeeText('2025/08');

        Attendance::where('user_id', $user->id)
            ->whereMonth('date', 8)
            ->get()
            ->each(function ($attendance) use ($response) {
                $response->assertSeeText($attendance->user->name);
                $response->assertSeeText(Carbon::parse($attendance->date)->translatedFormat('m/d(D)'));
                $response->assertSeeText(Carbon::parse($attendance->clock_in)->format('H:i'));
                $response->assertSeeText(Carbon::parse($attendance->clock_out)->format('H:i'));
                $response->assertSeeText($attendance->formatted_total_rest);
                $response->assertSeeText($attendance->formatted_total_work);
            });
    }

    public function test_can_navigate_to_attendance_detail_on_staff_attendance(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/attendance/staff/'.$user->id);
        $response->assertStatus(200);

        $response->assertSee('/attendance/'.$attendance->id);
        $response->assertSeeText('詳細');

        $detailResponse = $this->get('/attendance/'.$attendance->id);
        $detailResponse->assertStatus(200);
    }

    public function test_show_pending_request(): void
    {
        $admin = Admin::factory()->create();
        $users = User::factory()->count(5)->create();

        $attendances = collect();
        foreach ($users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => now()->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
            ]);
            $attendances->push($attendance);
        }

        $attendanceRequests = collect();
        foreach ($attendances as $attendance) {
            $attendanceRequest = AttendanceRequest::factory()->create([
                'attendance_id' => $attendance->id,
                'clock_in' => '08:00',
                'clock_out' => '17:00',
                'reason' => 'テスト',
                'approved' => false,
            ]);
            $attendanceRequests->push($attendanceRequest);
        }

        AttendanceRequest::factory()->create([
            'attendance_id' => $attendances->first()->id,
            'clock_in' => '07:00',
            'clock_out' => '16:00',
            'reason' => '承認済み申請',
            'approved' => true,
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);

        foreach ($attendanceRequests as $attendanceRequest) {
            $response->assertSeeInOrder(['承認待ち', '承認待ち', '承認待ち', '承認待ち', '承認待ち']);
            $response->assertSeeText($attendanceRequest->attendance->user->name);
            $response->assertSeeText(Carbon::parse($attendanceRequest->attendance->date)->format('Y/m/d'));
            $response->assertSeeText($attendanceRequest->reason);
            $response->assertSeeText($attendanceRequest->created_at->format('Y/m/d'));
        }

        $response->assertDontSeeText('承認済み申請');
    }

    public function test_show_approved_request(): void
    {
        $admin = Admin::factory()->create();
        $users = User::factory()->count(5)->create();

        $attendances = collect();
        foreach ($users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => now()->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
            ]);
            $attendances->push($attendance);
        }

        $attendanceRequests = collect();
        foreach ($attendances as $attendance) {
            $attendanceRequest = AttendanceRequest::factory()->create([
                'attendance_id' => $attendance->id,
                'clock_in' => '08:00',
                'clock_out' => '17:00',
                'reason' => 'テスト',
                'approved' => true,
            ]);
            $attendanceRequests->push($attendanceRequest);
        }

        AttendanceRequest::factory()->create([
            'attendance_id' => $attendances->first()->id,
            'clock_in' => '07:00',
            'clock_out' => '16:00',
            'reason' => '承認待ち申請',
            'approved' => false,
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);

        foreach ($attendanceRequests as $attendanceRequest) {
            $response->assertSeeInOrder(['承認済み', '承認済み', '承認済み', '承認済み', '承認済み']);
            $response->assertSeeText($attendanceRequest->attendance->user->name);
            $response->assertSeeText(Carbon::parse($attendanceRequest->attendance->date)->format('Y/m/d'));
            $response->assertSeeText($attendanceRequest->reason);
            $response->assertSeeText($attendanceRequest->created_at->format('Y/m/d'));
        }

        $response->assertDontSeeText('承認待ち申請');
    }

    public function test_show_request(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $attendance->restTimes()->create([
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);

        $attendanceRequest = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => '08:00',
            'clock_out' => '17:00',
            'reason' => 'テスト',
            'approved' => false,
        ]);

        $requestRest = $attendanceRequest->requestRests()->create([
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get(route('request.approve', ['attendance_correct_request' => $attendanceRequest->id]));
        $response->assertStatus(200);

        $response->assertSeeText($user->name);
        $response->assertSeeText(Carbon::parse($attendanceRequest->attendance->date)->format('Y年'));
        $response->assertSeeText(Carbon::parse($attendanceRequest->attendance->date)->translatedFormat('n月j日'));
        $response->assertSeeText(Carbon::parse($attendanceRequest->clock_in)->format('H:i'));
        $response->assertSeeText(Carbon::parse($attendanceRequest->clock_out)->format('H:i'));
        $response->assertSeeText(Carbon::parse($requestRest->start_time)->format('H:i'));
        $response->assertSeeText(Carbon::parse($requestRest->end_time)->format('H:i'));
        $response->assertSeeText($attendanceRequest->reason);
    }

    public function test_approve_request(): void
    {
        Carbon::setLocale('ja');
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $attendance->restTimes()->create([
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);

        $attendanceRequest = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => '08:00',
            'clock_out' => '17:00',
            'reason' => 'テスト',
            'approved' => false,
        ]);

        $attendanceRequest->requestRests()->create([
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get(route('request.approve', ['attendance_correct_request' => $attendanceRequest->id]));
        $response->assertStatus(200);

        $response = $this->patch(route('request.approve.patch', $attendanceRequest));
        $response->assertStatus(302);

        $this->assertDatabaseHas('requests', [
            'id' => $attendanceRequest->id,
            'approved' => true,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '08:00',
            'clock_out' => '17:00',
        ]);

        $this->assertDatabaseHas('rest_times', [
            'attendance_id' => $attendance->id,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $this->assertDatabaseMissing('rest_times', [
            'attendance_id' => $attendance->id,
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);

        $response = $this->get('admin/attendance/list');
        $response->assertStatus(200);

        $response->assertSeeText($user->name);
        $response->assertSeeText('08:00');
        $response->assertSeeText('17:00');
        $response->assertSeeText($attendance->fresh()->formatted_total_rest);
        $response->assertSeeText($attendance->fresh()->formatted_total_work);
    }

    public function test_user_receives_verification_email()
    {
        Notification::fake();

        $this->post('/register', [
            'name' => '田中太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_show_verification_screen(): void
    {
        Http::fake([
            'http://localhost:8025/' => Http::response('mailhog mock response', 200),
        ]);
        $user = User::factory()->unverified()->create();

        $this->actingAs($user, 'web');
        $response = $this->get('/email/verify');
        $response->assertStatus(200);

        $response->assertSeeText('認証はこちらから');
        $response->assertSee('http://localhost:8025/');

        $mailhogResponse = Http::get('http://localhost:8025/');
        $this->assertEquals(200, $mailhogResponse->status());
    }

    public function test_user_is_redirected_to_attendance_after_verification(): void
    {
        Event::fake();
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);
        $response->assertRedirect('/attendance');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        Event::assertDispatched(Verified::class);
    }
}
