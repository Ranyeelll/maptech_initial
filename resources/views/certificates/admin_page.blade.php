@extends('layout')

@section('title','Admin Certificates')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Certificates (Admin)</h1>
        <p class="text-sm text-slate-500">Read-only listing and preview for administrators. This page does not modify data.</p>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <div class="p-4 border-b flex items-center justify-between">
            <div class="text-sm text-slate-600">Admin view — {{ $certificates->count() }} items</div>
            <div>
                <input type="search" placeholder="Search certificates" class="border rounded px-2 py-1 text-sm">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issued</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Certificate ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($certificates as $cert)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ optional($cert->user)->fullname }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ optional($cert->course)->title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ optional($cert->certificate_data)['issued_at'] ?? $cert->created_at->toDateString() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ optional($cert->certificate_data)['certificate_id'] ?? $cert->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('certificates.view',$cert->id) }}" class="text-blue-600 hover:underline mr-2">View</a>
                            <a href="{{ route('certificates.download',$cert->id) }}" class="text-green-600 hover:underline mr-2">Download</a>
                            <a href="{{ route('certificates.edit',$cert->id) }}" class="text-gray-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
