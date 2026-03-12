@extends('layout')
@section('title', 'Certificate')
@section('content')
    <div class="bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Automatic Certificate Generator</h1>
        <p class="text-sm text-slate-500 mb-4">Select an employee to auto-fill certificate fields (read-only).</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Employee</label>
                <select id="employeeSelect" class="mt-1 block w-full border rounded px-2 py-1">
                    <option value="">-- Select employee --</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}">{{ $e->fullname }} ({{ $e->email }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Company</label>
                <div class="mt-1 flex items-center space-x-3">
                    <img id="companyLogo" src="/assets/Maptech-Official-Logo.png" alt="Maptech logo" class="h-12" />
                    <input id="company" type="text" readonly class="flex-1 border rounded px-2 py-1" value="Maptech Information Solution Inc.">
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Employee Name</label>
                <input id="recipient" type="text" readonly class="mt-1 block w-full border rounded px-2 py-1">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Completed Course</label>
                <input id="course" type="text" readonly class="mt-1 block w-full border rounded px-2 py-1">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Department</label>
                <input id="department" type="text" readonly class="mt-1 block w-full border rounded px-2 py-1">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Subdepartment</label>
                <input id="subdepartment" type="text" readonly class="mt-1 block w-full border rounded px-2 py-1">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Instructor</label>
                <input id="instructor" type="text" readonly class="mt-1 block w-full border rounded px-2 py-1">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Issued Date</label>
                <input id="issued_at" type="text" readonly class="mt-1 block w-full border rounded px-2 py-1" value="{{ now()->toDateString() }}">
            </div>
        </div>

        <p class="mt-4 text-sm text-slate-500">Note: This page only reads data and fills the certificate preview fields automatically. It does not create, edit, or delete any records.</p>
    </div>

    <script>
        document.getElementById('employeeSelect').addEventListener('change', async function(e){
            const id = e.target.value;
            // clear fields
            ['recipient','course','department','subdepartment','instructor'].forEach(i=>document.getElementById(i).value='');
            if (!id) return;
            try{
                const res = await fetch('/api/certificate/employee/'+id);
                if (!res.ok) throw new Error('Failed to load');
                const d = await res.json();
                document.getElementById('recipient').value = d.fullname || '';
                document.getElementById('course').value = d.completed_course || '';
                document.getElementById('department').value = d.department || '';
                document.getElementById('subdepartment').value = d.subdepartment || '';
                document.getElementById('instructor').value = d.instructor || '';
                document.getElementById('company').value = d.company || 'Maptech Information Solution Inc.';
                // update logo if provided
                const logoEl = document.getElementById('companyLogo');
                if (logoEl && d.company_logo) {
                    logoEl.src = d.company_logo;
                }
            }catch(err){
                console.error(err);
            }
        });
    </script>
@endsection
