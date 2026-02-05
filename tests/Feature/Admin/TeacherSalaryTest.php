<?php

namespace Tests\Feature\Admin;

use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Teacher;
use App\Models\TeacherPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherSalaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_fixed_salary_calculation(): void
    {
        $teacher = Teacher::factory()->fixed(3000000)->create();
        $period = now()->format('Y-m');

        $earnings = $teacher->calculateMonthlyEarnings($period);

        $this->assertEquals(3000000, $earnings);
    }

    public function test_percent_salary_calculation(): void
    {
        $teacher = Teacher::factory()->percent(50)->create();
        $group = Group::factory()->for($teacher)->create();
        $enrollment = Enrollment::factory()->for($group)->create();
        $period = now()->format('Y-m');

        // Create payment - teacher_share should be calculated automatically
        Payment::factory()->for($enrollment)->create([
            'amount' => 1000000,
            'period' => $period,
        ]);

        $earnings = $teacher->calculateMonthlyEarnings($period);

        $this->assertEquals(500000, $earnings); // 50% of 1000000
    }

    public function test_hybrid_salary_calculation(): void
    {
        $teacher = Teacher::factory()->hybrid(2000000, 30)->create();
        $group = Group::factory()->for($teacher)->create();
        $enrollment = Enrollment::factory()->for($group)->create();
        $period = now()->format('Y-m');

        // Create payment
        Payment::factory()->for($enrollment)->create([
            'amount' => 1000000,
            'period' => $period,
        ]);

        $earnings = $teacher->calculateMonthlyEarnings($period);

        // 2000000 fixed + 300000 (30% of 1000000) = 2300000
        $this->assertEquals(2300000, $earnings);
    }

    public function test_debt_calculation(): void
    {
        $teacher = Teacher::factory()->fixed(3000000)->create();
        $period = now()->format('Y-m');

        // Pay only 1000000
        TeacherPayment::factory()->for($teacher)->create([
            'amount' => 1000000,
            'period' => $period,
        ]);

        $debt = $teacher->getDebt($period);

        $this->assertEquals(2000000, $debt); // 3000000 - 1000000
    }

    public function test_no_debt_when_fully_paid(): void
    {
        $teacher = Teacher::factory()->fixed(3000000)->create();
        $period = now()->format('Y-m');

        // Pay full amount
        TeacherPayment::factory()->for($teacher)->create([
            'amount' => 3000000,
            'period' => $period,
        ]);

        $debt = $teacher->getDebt($period);

        $this->assertEquals(0, $debt);
    }

    public function test_fixed_salary_teacher_has_zero_teacher_share_in_payments(): void
    {
        $teacher = Teacher::factory()->fixed(3000000)->create();
        $group = Group::factory()->for($teacher)->create();
        $enrollment = Enrollment::factory()->for($group)->create();

        $payment = Payment::factory()->for($enrollment)->create([
            'amount' => 1000000,
        ]);

        $this->assertEquals(0, $payment->teacher_share);
        $this->assertEquals(1000000, $payment->school_share);
    }

    public function test_percent_salary_teacher_has_calculated_shares(): void
    {
        $teacher = Teacher::factory()->percent(40)->create();
        $group = Group::factory()->for($teacher)->create();
        $enrollment = Enrollment::factory()->for($group)->create();

        $payment = Payment::factory()->for($enrollment)->create([
            'amount' => 1000000,
        ]);

        $this->assertEquals(400000, $payment->teacher_share);
        $this->assertEquals(600000, $payment->school_share);
    }

    public function test_paid_amount_calculation(): void
    {
        $teacher = Teacher::factory()->create();
        $period = now()->format('Y-m');

        // Multiple payments in same period
        TeacherPayment::factory()->for($teacher)->create([
            'amount' => 500000,
            'period' => $period,
        ]);
        TeacherPayment::factory()->for($teacher)->create([
            'amount' => 700000,
            'period' => $period,
        ]);

        // Payment in different period should not be counted
        TeacherPayment::factory()->for($teacher)->create([
            'amount' => 1000000,
            'period' => now()->subMonth()->format('Y-m'),
        ]);

        $paid = $teacher->getPaidAmount($period);

        $this->assertEquals(1200000, $paid);
    }

    public function test_teacher_payments_relationship(): void
    {
        $teacher = Teacher::factory()->create();
        TeacherPayment::factory()->count(3)->for($teacher)->create();

        $this->assertCount(3, $teacher->teacherPayments);
    }

    public function test_salary_types_array(): void
    {
        $types = Teacher::salaryTypes();

        $this->assertArrayHasKey('fixed', $types);
        $this->assertArrayHasKey('percent', $types);
        $this->assertArrayHasKey('hybrid', $types);
    }

    public function test_payment_methods_array(): void
    {
        $methods = TeacherPayment::methods();

        $this->assertArrayHasKey('cash', $methods);
        $this->assertArrayHasKey('card', $methods);
        $this->assertArrayHasKey('transfer', $methods);
    }
}
