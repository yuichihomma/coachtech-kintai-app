<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminStampCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private function admin()
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function staff()
    {
        return User::factory()->create(['role' => 'staff']);
    }

    /** ① 承認待ち一覧表示 */
    public function test_承認待ち申請が管理者に表示される()
    {
        $admin = $this->admin();
        $staff = $this->staff();

        $attendance = Attendance::factory()->create(['user_id' => $staff->id]);

        StampCorrectionRequest::create([
            'user_id'       => $staff->id,
            'attendance_id' => $attendance->id,
            'request_type'  => 'update',
            'before_value'  => '{}',
            'after_value'   => '{}',
            'reason'        => '修正理由',
            'status'        => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.stamp_correction_request.list'))
            ->assertSee('修正理由');
    }

    /** ② 承認済み一覧表示 */
    public function test_承認済み申請が管理者に表示される()
    {
        $admin = $this->admin();
        $staff = $this->staff();

        $attendance = Attendance::factory()->create(['user_id' => $staff->id]);

        StampCorrectionRequest::create([
            'user_id'       => $staff->id,
            'attendance_id' => $attendance->id,
            'request_type'  => 'update',
            'before_value'  => '{}',
            'after_value'   => '{}',
            'reason'        => '承認済理由',
            'status'        => 'approved',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.stamp_correction_request.list', ['status' => 'approved']))
            ->assertSee('承認済理由');
    }

    /** ③ 修正申請の詳細表示 */
    public function test_管理者は申請詳細を表示できる()
    {
        $admin = $this->admin();
        $staff = $this->staff();

        $attendance = Attendance::factory()->create(['user_id' => $staff->id]);

        $request = StampCorrectionRequest::create([
            'user_id'       => $staff->id,
            'attendance_id' => $attendance->id,
            'request_type'  => 'update',
            'before_value'  => '{}',
            'after_value'   => '{"clock_in":"09:00"}',
            'reason'        => '詳細確認',
            'status'        => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.stamp_correction_request.show', $request->id))
            ->assertStatus(200)
            ->assertSee('詳細確認');
    }

    /** ④ 承認処理で勤怠更新 */
    public function test_申請承認時に勤怠が更新される()
    {
        $admin = $this->admin();
        $staff = $this->staff();

        $attendance = Attendance::factory()->create([
            'user_id'   => $staff->id,
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $request = StampCorrectionRequest::create([
            'user_id'       => $staff->id,
            'attendance_id' => $attendance->id,
            'request_type'  => 'update',
            'before_value'  => '{}',
            'after_value'   => json_encode([
                'clock_in'  => '10:00',
                'clock_out' => '19:00',
            ]),
            'reason'        => '時間修正',
            'status'        => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.stamp_correction_request.approve', $request->id));

        $this->assertDatabaseHas('attendances', [
            'id'        => $attendance->id,
            'clock_in'  => $attendance->work_date->format('Y-m-d') . ' 10:00:00',
            'clock_out' => $attendance->work_date->format('Y-m-d') . ' 19:00:00',
        ]);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id'     => $request->id,
            'status' => 'approved',
        ]);
    }
}
