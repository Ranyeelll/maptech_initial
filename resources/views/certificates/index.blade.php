@extends('layout')

@section('title','Certificates')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Certificate Templates</h1>
            <p class="text-sm text-slate-500">Manage certificate templates and assign one per course.</p>
        </div>
        <div class="flex items-center space-x-4">
            <a href="{{ route('certificate_templates.create') }}" class="bg-green-600 text-white px-4 py-2 rounded">Create Certificate Template</a>
        </div>
    </div>

    @php
        $templates = \App\Models\CertificateTemplate::with('course')->orderBy('created_at','desc')->get();
    @endphp

    <div class="bg-white rounded shadow overflow-hidden">
        <div class="p-4 border-b flex items-center justify-between">
            <div class="text-sm text-slate-600">Showing {{ $templates->count() }} templates</div>
            <div>
                <input type="search" placeholder="Search by template or course" class="border rounded px-2 py-1 text-sm">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Template Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Template ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($templates as $t)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $t->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ optional($t->course)->title ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $t->created_at->toDateString() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $t->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('certificate_templates.preview', $t->id) }}" class="text-blue-600 hover:underline mr-2">Preview</a>
                            <a href="{{ route('certificate_templates.edit', $t->id) }}" class="text-gray-600 hover:underline mr-2">Edit</a>
                            <form action="{{ route('certificate_templates.destroy', $t->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Delete this template?');">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
