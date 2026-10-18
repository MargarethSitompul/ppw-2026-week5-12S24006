<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class CourseController extends Controller {
    public function index(Request $request) {
        if ($request->filled('search')) {
            session(['last_krs_search' => $request->search]);
        }

        $courses = Course::withCount('enrollments')
            ->when($request->search, fn($q, $s) => $q->where('code', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%"))
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total_courses' => DB::table('courses')->count(),
            'total_quota_left' => DB::table('courses')->sum('quota'),
            'total_enrollments' => DB::table('enrollments')->count(),
            'total_sks_taken' => DB::table('enrollments')
                ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                ->sum('courses.credits'),
        ];

        return view('courses.index', compact('courses', 'stats'));
    }

    public function enroll(Request $request, int $id) {
        $validated = $request->validate([
            'student_nim' => 'required|string|min:6|max:15',
            'student_name' => 'required|string|min:3|max:150',
        ]);

        try {
            DB::transaction(function () use ($validated, $id) {
                $course = Course::where('id', $id)->lockForUpdate()->firstOrFail();

                if ($course->quota < 1) {
                    throw new \RuntimeException("Kuota kelas {$course->code} telah penuh!");
                }

                $course->decrement('quota');
                $course->enrollments()->create([
                    'student_nim' => strtoupper(trim($validated['student_nim'])),
                    'student_name' => $validated['student_name'],
                    'academic_year' => '2025/2026-Genap',
                ]);
            }, 3);

            return back()->with('success', 'Berhasil mengambil KRS secara atomik!');
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION]: ' . $e->getMessage());
            return back()->withInput()->with('error', 'NIM sudah terdaftar pada mata kuliah ini.');
        }
    }
}