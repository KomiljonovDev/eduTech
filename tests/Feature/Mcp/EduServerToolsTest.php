<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\EduServer;
use App\Mcp\Tools\AttendanceReportTool;
use App\Mcp\Tools\CourseListTool;
use App\Mcp\Tools\DashboardStatsTool;
use App\Mcp\Tools\GroupScheduleTool;
use App\Mcp\Tools\LeadManagementTool;
use App\Mcp\Tools\PaymentReportTool;
use App\Mcp\Tools\StudentSearchTool;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lead;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EduServerToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_stats_tool_returns_statistics(): void
    {
        // Create some test data
        Student::factory()->count(5)->create(['is_active' => true]);
        Course::factory()->count(3)->create(['is_active' => true]);
        Teacher::factory()->count(2)->create(['is_active' => true]);

        $response = EduServer::tool(DashboardStatsTool::class, [
            'period' => 'all',
        ]);

        $response->assertOk();
        $response->assertSee("O'QUV MARKAZ STATISTIKASI");
        $response->assertSee('Faol talabalar: 5');
        $response->assertSee('Faol kurslar: 3');
    }

    public function test_student_search_tool_finds_students(): void
    {
        Student::factory()->create([
            'name' => 'Test Talaba',
            'phone' => '+998901234567',
            'is_active' => true,
        ]);

        $response = EduServer::tool(StudentSearchTool::class, [
            'query' => 'Test',
            'active_only' => true,
        ]);

        $response->assertOk();
        $response->assertSee('Test Talaba');
        $response->assertSee('+998901234567');
    }

    public function test_student_search_tool_returns_not_found(): void
    {
        $response = EduServer::tool(StudentSearchTool::class, [
            'query' => 'NonExistent',
        ]);

        $response->assertOk();
        $response->assertSee('Talaba topilmadi');
    }

    public function test_course_list_tool_returns_courses(): void
    {
        Course::factory()->create([
            'name' => 'Python Dasturlash',
            'code' => 'PY101',
            'monthly_price' => 500000,
            'is_active' => true,
        ]);

        $response = EduServer::tool(CourseListTool::class, [
            'active_only' => true,
        ]);

        $response->assertOk();
        $response->assertSee('Python Dasturlash');
        $response->assertSee('PY101');
    }

    public function test_group_schedule_tool_returns_groups(): void
    {
        $course = Course::factory()->create();
        $teacher = Teacher::factory()->create();

        Group::factory()->create([
            'name' => 'Python-1',
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'days' => 'odd',
            'status' => 'active',
        ]);

        $response = EduServer::tool(GroupScheduleTool::class, [
            'status' => 'active',
        ]);

        $response->assertOk();
        $response->assertSee('Python-1');
        $response->assertSee('DU-CHOR-JUM');
    }

    public function test_lead_management_tool_returns_leads(): void
    {
        $course = Course::factory()->create();

        Lead::factory()->create([
            'name' => 'Yangi Lid',
            'phone' => '+998901111111',
            'course_id' => $course->id,
            'status' => 'new',
            'source' => 'instagram',
        ]);

        $response = EduServer::tool(LeadManagementTool::class, [
            'status' => 'new',
        ]);

        $response->assertOk();
        $response->assertSee('Yangi Lid');
        $response->assertSee('Instagram');
    }

    public function test_attendance_report_tool_returns_overall_report(): void
    {
        $response = EduServer::tool(AttendanceReportTool::class, []);

        $response->assertOk();
        $response->assertSee('UMUMIY DAVOMAT HISOBOTI');
    }

    public function test_payment_report_tool_returns_financial_report(): void
    {
        $response = EduServer::tool(PaymentReportTool::class, [
            'period' => 'month',
        ]);

        $response->assertOk();
        $response->assertSee('MOLIYAVIY HISOBOT');
        $response->assertSee('Shu oy');
    }
}
