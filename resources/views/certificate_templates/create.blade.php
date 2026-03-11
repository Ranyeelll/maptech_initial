@extends('layout')

@section('title','Create Certificate Template')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Create Certificate Template</h1>
    <div class="bg-white p-6 rounded shadow">
        <form action="{{ route('certificate_templates.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="block text-sm font-medium">Template Name</label>
                <input type="text" name="name" class="mt-1 block w-full border rounded px-2 py-1" required>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium">Assign to Course (optional)</label>
                <select name="course_id" class="mt-1 block w-full border rounded px-2 py-1">
                    <option value="">-- No course assigned --</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-2">
                <button class="bg-green-600 text-white px-4 py-2 rounded">Create and Edit</button>
                <a href="{{ route('certificate_templates.index') }}" class="px-4 py-2 rounded border">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
