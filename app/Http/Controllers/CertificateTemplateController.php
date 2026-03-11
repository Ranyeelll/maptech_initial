<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateTemplate;
use App\Models\Course;

class CertificateTemplateController extends Controller
{
    // List templates
    public function index()
    {
        $templates = CertificateTemplate::with('course')->orderBy('created_at','desc')->get();
        return view('certificate_templates.index', compact('templates'));
    }

    // Show create form
    public function create()
    {
        $courses = Course::orderBy('title')->get();
        return view('certificate_templates.create', compact('courses'));
    }

    // Store new template
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'course_id' => 'nullable|exists:courses,id|unique:certificate_templates,course_id',
        ]);

        $template = CertificateTemplate::create([
            'name' => $data['name'],
            'course_id' => $data['course_id'] ?? null,
            'template_data' => ['elements'=>[]]
        ]);

        return redirect()->route('certificate_templates.edit', $template->id)->with('status','Template created.');
    }

    // Edit (open editor)
    public function edit($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        // create a certificate-like object for the existing editor partial
        $certificate = new \stdClass();
        $certificate->id = 'template-'.$template->id;
        $certificate->certificate_data = $template->template_data ?? ['elements'=>[]];
        // ensure fields exist
        $certificate->certificate_data['recipient'] = $certificate->certificate_data['recipient'] ?? '';
        $certificate->certificate_data['course'] = $certificate->certificate_data['course'] ?? optional($template->course)->title;
        $certificate->certificate_data['issued_at'] = $certificate->certificate_data['issued_at'] ?? now()->toDateString();

        // pass both template and certificate to the view; the partial will detect template
        return view('certificates.edit', compact('certificate'))->with('template', $template);
    }

    // Update template (from editor form)
    public function update(Request $request, $id)
    {
        $template = CertificateTemplate::findOrFail($id);
        $data = $request->validate([
            'certificate_data' => 'required|array'
        ]);
        // only update template_data
        $template->template_data = $data['certificate_data'];
        $template->save();
        return back()->with('status','Template updated.');
    }

    public function destroy($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        $template->delete();
        return redirect()->route('certificate_templates.index')->with('status','Template deleted.');
    }

    public function preview($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        $certificate = new \stdClass();
        $certificate->id = 'template-'.$template->id;
        $certificate->certificate_data = $template->template_data ?? ['elements'=>[]];
        return view('certificates.show', compact('certificate'));
    }
}
