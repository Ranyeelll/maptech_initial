<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'App')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-slate-50">
    <div class="min-h-screen flex">
        <aside class="w-56 bg-slate-900 text-white flex flex-col fixed inset-y-0">
            <div>
                <div class="p-6 bg-slate-950 flex items-center space-x-3 border-b border-slate-800">
                    <img src="/assets/Maptech-Official-Logo.png" alt="Maptech" class="h-10 w-auto">
                    <div class="text-sm font-semibold">Maptech Information<br/>Solutions Inc.</div>
                <nav class="mt-6 px-2">
                    <ul class="space-y-1">
                        <li>
                            <a href="/admin" class="flex items-center gap-3 px-4 py-3 text-sm font-medium {{ Request::is('admin') ? 'bg-green-600 text-white rounded-l-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6"/></svg>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="/admin/departments" class="flex items-center gap-3 px-4 py-3 text-sm font-medium {{ Request::is('admin/departments*') ? 'bg-green-600 text-white rounded-l-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                                <span>Departments</span>
                            </a>
                        </li>
                        <li>
                            <a href="/admin/users" class="flex items-center gap-3 px-4 py-3 text-sm font-medium {{ Request::is('admin/users*') ? 'bg-green-600 text-white rounded-l-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M8 7a4 4 0 100-8 4 4 0 000 8z"/></svg>
                                <span>User Management</span>
                            </a>
                        </li>
                        <li>
                            <a href="/admin/courses" class="flex items-center gap-3 px-4 py-3 text-sm font-medium {{ Request::is('admin/courses*') ? 'bg-green-600 text-white rounded-l-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20l9-5-9-5-9 5 9 5z"/></svg>
                                <span>Courses and Content</span>
                            </a>
                        </li>
                        <li>
                            <a href="/admin/qna" class="flex items-center gap-3 px-4 py-3 text-sm font-medium {{ Request::is('admin/qna*') ? 'bg-green-600 text-white rounded-l-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v6a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span>Q&A Discussion</span>
                            </a>
                        </li>
                        <li>
                            <a href="/admin/enrollments" class="flex items-center gap-3 px-4 py-3 text-sm font-medium {{ Request::is('admin/enrollments*') ? 'bg-green-600 text-white rounded-l-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                                <span>Enrollments</span>
                            </a>
                        </li>
                        <li>
                            <a href="/admin/reports" class="flex items-center gap-3 px-4 py-3 text-sm font-medium {{ Request::is('admin/reports*') ? 'bg-green-600 text-white rounded-l-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17a4 4 0 100-8 4 4 0 000 8zm-7 4v-2a4 4 0 014-4h6"/></svg>
                                <span>Reports & Analytics</span>
                            </a>
                        </li>
                        <li>
                            <a href="/admin/notifications" class="flex items-center gap-3 px-4 py-3 text-sm font-medium {{ Request::is('admin/notifications*') ? 'bg-green-600 text-white rounded-l-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h11z"/></svg>
                                <span>Notifications</span>
                            </a>
                        </li>
                        <li>
                            <a href="/admin/certificates" class="flex items-center gap-3 px-4 py-3 text-sm font-medium {{ Request::is('admin/certificates*') ? 'bg-green-600 text-white rounded-l-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.567-3 3.5S10.343 15 12 15s3-1.567 3-3.5S13.657 8 12 8z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12c0 4.97-4.03 9-9 9S3 16.97 3 12 7.03 3 12 3s9 4.03 9 9z"/></svg>
                                <span>Certificates</span>
                            </a>
                        </li>
                        <li>
                            <a href="/admin/settings" class="flex items-center gap-3 px-4 py-3 text-sm font-medium {{ Request::is('admin/settings*') ? 'bg-green-600 text-white rounded-l-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0a1.72 1.72 0 002.6 1.02 1.72 1.72 0 012.3.271c.68.65.58 1.75-.196 2.207a1.72 1.72 0 00-.5 2.148c.35.66 1.19.947 1.847.65.92-.422 2.01.063 2.01 1.11v1.39c0 1.047-1.09 1.532-2.01 1.11a1.72 1.72 0 00-1.847.65 1.72 1.72 0 00.5 2.148c.776.457.876 1.557.196 2.207a1.72 1.72 0 01-2.3.271 1.72 1.72 0 00-2.6 1.02c-.299.921-1.602.921-1.901 0a1.72 1.72 0 00-2.6-1.02 1.72 1.72 0 01-2.3-.271c-.68-.65-.58-1.75.196-2.207a1.72 1.72 0 00.5-2.148 1.72 1.72 0 00-1.847-.65C4.09 15.39 3 14.905 3 13.858v-1.39c0-1.047 1.09-1.532 2.01-1.11.657.297 1.497.01 1.847-.65a1.72 1.72 0 00-.5-2.148c-.776-.457-.876-1.557-.196-2.207a1.72 1.72 0 012.3-.271 1.72 1.72 0 002.6-1.02z"/></svg>
                                <span>Settings</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                </div>
            </div>
        </aside>

        <main class="flex-1 ml-56 p-8">
            @yield('content')
        </main>
    </div>

    {{-- optional scripts --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.10.3/cdn.min.js" defer></script>
</body>
</html>
