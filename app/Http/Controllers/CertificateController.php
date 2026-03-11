<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = \App\Models\Certificate::with(['user','course'])->orderBy('created_at','desc')->get();
        return view('certificates.index', compact('certificates'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        $cert = \App\Models\Certificate::create([
            'user_id' => $data['user_id'],
            'course_id' => $data['course_id'],
            'certificate_data' => [
                'recipient' => optional(\App\Models\User::find($data['user_id']))->fullname,
                'course' => optional(\App\Models\Course::find($data['course_id']))->name,
                'issued_at' => now()->toDateString(),
                'certificate_id' => strtoupper(\Illuminate\Support\Str::random(10)),
            ],
        ]);

        return redirect()->route('certificates.edit', $cert->id)->with('status','Certificate generated.');
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
}
