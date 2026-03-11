@extends('layout')

@section('title','Certificate Editor')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Certificate Editor</h1>
            <p class="text-sm text-slate-500">Edit layout and metadata for this certificate.</p>
        </div>
        <div>
            <a href="{{ route('certificates.view', $certificate->id) }}" class="text-sm text-slate-600 hover:underline">Preview</a>
        </div>
    </div>

    @include('certificates.partial_editor')

    {{-- pass existing elements to standalone JS when loaded via SPA --}}
    <script>
        window.__CERT_EXISTING_ELEMENTS = {!! json_encode($certificate->certificate_data['elements'] ?? []) !!};
    </script>
    <script src="/js/certificate-editor.js"></script>

@endsection
