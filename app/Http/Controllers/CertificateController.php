<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = \App\Models\Certificate::with(['user','course'])->orderBy('created_at','desc')->get();

        // Also include users who have Completed enrollments but do not yet have a Certificate record.
        // We will append lightweight placeholder objects for display only (non-persistent).
        try {
            $existingPairs = $certificates->map(function($c){ return ($c->user_id ?? '') . '|' . ($c->course_id ?? ''); })->toArray();

            $completed = \App\Models\Enrollment::with(['user','course'])
                ->where('status', 'Completed')
                ->orderBy('updated_at','desc')
                ->get();

            foreach ($completed as $enr) {
                $pair = $enr->user_id.'|'.$enr->course_id;
                if (in_array($pair, $existingPairs)) continue;

                $dummy = new \stdClass();
                $dummy->id = 'enrollment-'.$enr->id;
                $dummy->user = $enr->user;
                $dummy->course = $enr->course;
                $dummy->user_id = $enr->user_id;
                $dummy->course_id = $enr->course_id;
                $dummy->certificate_data = ['issued_at' => ($enr->updated_at ? $enr->updated_at->toDateString() : now()->toDateString())];
                $dummy->created_at = $enr->updated_at ?? $enr->enrolled_at ?? now();

                $certificates->push($dummy);
            }

            // ensure newest first by created_at
            $certificates = $certificates->sortByDesc(function($c){ return $c->created_at ?? null; })->values();
        } catch (\Throwable $e) {
            \Log::debug('Failed to merge completed enrollments into certificates list: '.$e->getMessage());
        }

        // Return server-rendered admin table to ensure the admin UI shows the Generate button immediately
        return view('certificates.admin_table', compact('certificates'));
    }

    public function generate(Request $request)
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
                'course_id' => 'required|exists:courses,id',
            ]);

            $user = \App\Models\User::find($data['user_id']);
            $course = \App\Models\Course::with('instructor')->find($data['course_id']);

            $certificateData = [
                'recipient' => $user->fullname ?? null,
                'course' => $course->title ?? null,
                'issued_at' => now()->toDateString(),
                'certificate_id' => strtoupper(\Illuminate\Support\Str::random(10)),
                'instructor' => optional($course->instructor)->fullname,
                'company' => 'Maptech Information Solution Inc.',
                'company_logo' => $this->getAssetUrl('Maptech-Official-Logo'),
                'signature_president' => $this->getAssetUrl('signature_president'),
                'signature_instructor' => $this->getAssetUrl('signature_instructor'),
                'collaborated_companies' => [],
            ];

            // If a certificate template exists for this course, merge its latest saved settings
            // into the certificate data so generation always uses the most recent values.
            // First, merge global template (course_id = null) so site-wide assets are applied,
            // then merge any course-specific template which may override global values.
            try {
                // merge global
                $global = \App\Models\CertificateTemplate::whereNull('course_id')->where('name','global')->latest()->first();
                if ($global && is_array($global->template_data)) {
                    $t = $global->template_data;
                    $assetKeys = ['company_logo', 'signature_president', 'signature_instructor'];
                    $protected = ['recipient','course','issued_at','certificate_id','instructor'];
                    foreach ($t as $k => $v) {
                        if (in_array($k, $protected)) continue;
                        if (in_array($k, $assetKeys) && is_string($v)) {
                            if (preg_match('#^https?://#i', $v)) { $certificateData[$k] = $v; continue; }
                            $base = basename($v); $base = preg_replace('/\.[^.]+$/', '', $base); $base = preg_replace('#[^A-Za-z0-9_\-]#', '_', $base);
                            $certificateData[$k] = $this->getAssetUrl($base); continue;
                        }
                        $certificateData[$k] = $v;
                    }
                    if (isset($t['elements']) && is_array($t['elements'])) { $certificateData['elements'] = $t['elements']; }
                }

                // then merge course-specific template (overrides global)
                $template = \App\Models\CertificateTemplate::where('course_id', $course->id)->latest()->first();
                if ($template && is_array($template->template_data)) {
                    $t = $template->template_data;

                    // Known asset keys that should be resolved to actual URLs
                    $assetKeys = ['company_logo', 'signature_president', 'signature_instructor'];

                    // Merge template top-level keys into certificate data, but do not overwrite
                    // dynamic fields that must come from the current user/course context.
                    $protected = ['recipient','course','issued_at','certificate_id','instructor'];

                    foreach ($t as $k => $v) {
                        if (in_array($k, $protected)) continue;

                        // Resolve known asset base names to current asset URLs.
                        // Template values might be a bare basename (e.g. "Maptech-Official-Logo"),
                        // a filename ("Maptech-Official-Logo.png"), or a path ("/assets/Maptech-Official-Logo.png").
                        if (in_array($k, $assetKeys) && is_string($v)) {
                            // If it's an absolute URL, prefer it directly (rare), otherwise
                            // canonicalize the value to a basename and resolve via getAssetUrl()
                            if (preg_match('#^https?://#i', $v)) {
                                $certificateData[$k] = $v;
                                continue;
                            }

                            // Strip any path and extension to get base name
                            $base = basename($v);
                            $base = preg_replace('/\.[^.]+$/', '', $base);
                            // sanitize to allowed characters
                            $base = preg_replace('#[^A-Za-z0-9_\-]#', '_', $base);
                            $certificateData[$k] = $this->getAssetUrl($base);
                            continue;
                        }

                        // For other keys (e.g., signatory names, titles, collaborated companies, elements)
                        $certificateData[$k] = $v;
                    }

                    // If template contains elements array, ensure it's present on the certificate data
                    if (isset($t['elements']) && is_array($t['elements'])) {
                        $certificateData['elements'] = $t['elements'];
                    }
                }
            } catch (\Throwable $e) {
                // Log but continue with defaults — generation should not fail because template lookup failed
                \Log::debug('Certificate template merge failed: '.$e->getMessage());
            }

            try {
                $cert = \App\Models\Certificate::create([
                    'user_id' => $data['user_id'],
                    'course_id' => $data['course_id'],
                    'certificate_data' => $certificateData,
                ]);

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['certificate' => $cert], 201);
                }

                return redirect()->route('certificates.edit', $cert->id)->with('status','Certificate generated.');
            } catch (\Illuminate\Database\QueryException $qe) {
                // If DB schema does not support certificate_data, fallback to generating a standalone HTML certificate file
                \Log::warning('Certificate DB create failed, falling back to file generation: '.$qe->getMessage());

                // Build a lightweight certificate-like object for the view
                $dummy = new \stdClass();
                $dummy->id = 'file-'.time();
                $dummy->certificate_data = $certificateData;
                $dummy->user = $user;
                $dummy->course = $course;
                $dummy->created_at = now();

                // Render the certificate HTML using existing view
                $html = view('certificates.show', ['certificate' => $dummy])->render();

                // Ensure public/certificates exists
                $dir = public_path('certificates');
                if (!\file_exists($dir)) {
                    @mkdir($dir, 0755, true);
                }

                $filename = 'certificate_'.$dummy->id.'.html';
                $path = $dir.DIRECTORY_SEPARATOR.$filename;
                @file_put_contents($path, $html);

                $url = url('certificates/'.$filename);

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['certificate' => $certificateData, 'download_url' => $url], 201);
                }

                return redirect($url);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            // Log and return JSON-friendly error for AJAX callers
            \Log::error('Certificate generation failed: '.$e->getMessage(), ['exception' => $e]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Failed to generate certificate: '.$e->getMessage()], 500);
            }
            // For non-AJAX, rethrow so the exception handler shows the error page
            throw $e;
        }
    }

    public function edit($id)
    {
        $certificate = \App\Models\Certificate::with(['user','course'])->findOrFail($id);
        return view('certificates.edit', compact('certificate'));
    }

    public function update(Request $request, $id)
    {
        $certificate = \App\Models\Certificate::findOrFail($id);
        $data = $request->validate([
            'certificate_data.recipient' => 'required|string',
            'certificate_data.course' => 'required|string',
            'certificate_data.issued_at' => 'required|date',
        ]);

        $certificate->certificate_data = array_merge($certificate->certificate_data ?? [], $request->input('certificate_data'));
        $certificate->save();

        return back()->with('status','Certificate updated.');
    }

    public function view($id)
    {
        $certificate = \App\Models\Certificate::with(['user','course'])->findOrFail($id);
        return view('certificates.show', compact('certificate'));
    }

    public function editorPartial($id)
    {
        $certificate = \App\Models\Certificate::with(['user','course'])->findOrFail($id);
        return view('certificates.partial_editor', compact('certificate'));
    }

    public function download($id)
    {
        $certificate = \App\Models\Certificate::with(['user','course'])->findOrFail($id);
        // For now return the HTML view as downloadable file
        $html = view('certificates.show', compact('certificate'))->render();
        $filename = 'certificate_'. $certificate->id .'.html';
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    /**
     * Admin-only certificate page (read-only listing/preview).
     * This method only reads data and does not modify or delete any records.
     */
    public function page()
    {
        $certificates = \App\Models\Certificate::with(['user','course'])->orderBy('created_at','desc')->get();
        return view('certificates.admin_page', compact('certificates'));
    }

    /**
     * Public certificate generator page (read-only). Shows employees and auto-fills certificate fields.
     */
    public function publicPage()
    {
        $employees = \App\Models\User::where('role', 'employee')
            ->orderBy('fullname')
            ->get();

        return view('certificate', compact('employees'));
    }

    /**
     * Read-only endpoint to fetch employee details and latest completed course.
     */
    public function employeeData($id)
    {
        $user = \App\Models\User::with('subdepartment')->find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Latest completed enrollment (if any)
        $enrollment = \App\Models\Enrollment::where('user_id', $user->id)
            ->where('status', 'Completed')
            ->with(['course.instructor'])
            ->orderBy('updated_at', 'desc')
            ->first();

        $courseTitle = $enrollment ? optional($enrollment->course)->title : null;
        $instructorName = $enrollment && $enrollment->course && $enrollment->course->instructor ? $enrollment->course->instructor->fullname : null;

        return response()->json([
            'id' => $user->id,
            'fullname' => $user->fullname,
            'department' => $user->department,
            'subdepartment' => optional($user->subdepartment)->name,
            'completed_course' => $courseTitle,
            'instructor' => $instructorName,
            'company' => 'Maptech Information Solution Inc.',
            'company_logo' => $this->getAssetUrl('Maptech-Official-Logo')
        ]);
    }

    /**
     * Find an asset in public/assets by base name (returns URL) or fallback to the base png.
     */
    private function getAssetUrl($baseName)
    {
        $dir = public_path('assets');
        if (!file_exists($dir)) return url('/assets/'.$baseName.'.png');
        $files = glob($dir.DIRECTORY_SEPARATOR.$baseName.'.*');
        if (!$files || count($files) === 0) return url('/assets/'.$baseName.'.png');
        $file = basename($files[0]);
        $full = $dir.DIRECTORY_SEPARATOR.$file;
        $url = url('/assets/'.$file);
        if (file_exists($full)) {
            $v = filemtime($full);
            return $url.'?v='.$v;
        }
        return $url;
    }

    /**
     * Upload or replace an asset (logo or e-signature). Admin-only.
     */
    public function uploadAsset(Request $request)
    {

        $data = $request->validate([
            'type' => 'required|string',
            'asset' => 'required|image|mimes:png,jpg,jpeg|max:5120',
            'other_name' => 'sometimes|string'
        ]);

        $file = $request->file('asset');
        $ext = $file->getClientOriginalExtension();
        $map = [
            'logo' => 'Maptech-Official-Logo',
            'signature_president' => 'signature_president',
            'signature_instructor' => 'signature_instructor',
        ];

        $type = $data['type'];
        if ($type === 'other') {
            $other = $data['other_name'] ?? null;
            // sanitize base name: allow letters, numbers, underscore
            $base = preg_replace('/[^A-Za-z0-9_\-]/', '_', ($other ?: 'asset'));
        } else {
            if (!isset($map[$type])) {
                return back()->with('status', 'Unknown asset type');
            }
            $base = $map[$type];
        }

        $dir = public_path('assets');
        if (!file_exists($dir)) @mkdir($dir, 0755, true);

        // remove existing variants for this base
        foreach (glob($dir.DIRECTORY_SEPARATOR.$base.'.*') as $existing) {
            @unlink($existing);
        }

        $filename = $base.'.'.$ext;
        $file->move($dir, $filename);

        // Persist the chosen base name into a global certificate template row so generation
        // and previews always read the same source of truth. We store under a global
        // CertificateTemplate (course_id = null) named 'global'. This is non-destructive.
        try {
            $global = \App\Models\CertificateTemplate::firstOrCreate([
                'course_id' => null,
                'name' => 'global'
            ], ['template_data' => ['elements' => []]]);

            $td = $global->template_data ?? ['elements' => []];
            // map the upload type to template key names
            $map = [
                'logo' => 'company_logo',
                'signature_president' => 'signature_president',
                'signature_instructor' => 'signature_instructor',
            ];
            $templateKey = $map[$type] ?? null;
            if ($templateKey) {
                $td[$templateKey] = $base; // store base name (no extension)
                $global->template_data = $td;
                $global->save();
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to persist global certificate asset reference: '.$e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'ok', 'file' => '/assets/'.$filename], 201);
        }

        return back()->with('status', 'Asset uploaded.');
    }

    /**
     * Return current certificate asset URLs for admin preview.
     */
    public function assets()
    {
        // Prefer values stored in global template if present
        $company = $this->getAssetUrl('Maptech-Official-Logo');
        $pres = $this->getAssetUrl('signature_president');
        $instr = $this->getAssetUrl('signature_instructor');
        try {
            $global = \App\Models\CertificateTemplate::whereNull('course_id')->where('name','global')->first();
            if ($global && is_array($global->template_data)) {
                $td = $global->template_data;
                if (!empty($td['company_logo'])) $company = $this->getAssetUrl($td['company_logo']);
                if (!empty($td['signature_president'])) $pres = $this->getAssetUrl($td['signature_president']);
                if (!empty($td['signature_instructor'])) $instr = $this->getAssetUrl($td['signature_instructor']);
            }
        } catch (\Throwable $e) {
            \Log::debug('Failed to read global certificate assets: '.$e->getMessage());
        }

        return response()->json([
            'company_logo' => $company,
            'signature_president' => $pres,
            'signature_instructor' => $instr,
        ]);
    }
}
