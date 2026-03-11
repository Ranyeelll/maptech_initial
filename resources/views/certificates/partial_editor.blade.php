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

    <div class="grid gap-6" style="grid-template-columns:220px minmax(0,1fr) 320px;">
        <!-- Left toolbar -->
        <div>
            <div class="bg-white p-4 rounded shadow">
                <h3 class="font-semibold mb-3">Tools</h3>
                <button id="addText" class="w-full mb-2 bg-blue-600 text-white py-2 rounded">Add Text</button>
                <button id="insertNameFieldLeft" class="w-full mb-2 bg-indigo-600 text-white py-2 rounded">Insert Name</button>
                <button id="insertCourseFieldLeft" class="w-full mb-2 bg-indigo-600 text-white py-2 rounded">Insert Course</button>
                <button id="insertDateFieldLeft" class="w-full mb-2 bg-indigo-600 text-white py-2 rounded">Insert Date</button>
                <label class="w-full mb-2 block">
                    <span class="sr-only">Upload Logo</span>
                    <input id="uploadLogoLeft" name="uploadLogoLeft" type="file" accept="image/*" class="hidden">
                    <button type="button" id="chooseLogoLeft" class="w-full bg-gray-200 py-2 rounded">Upload Logo</button>
                </label>
                <button id="resetCanvasLeft" class="w-full mt-2 bg-red-500 text-white py-2 rounded">Reset Layout</button>
            </div>
        </div>

        <!-- Canvas area (dominant) -->
        <div style="min-width:0;">
            <div class="bg-white p-6 rounded shadow">
                <div class="border bg-gray-50 flex flex-col items-center p-6">
                    <div class="w-full flex items-center mb-3 gap-3">
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium">Template</label>
                            <select id="templateSelect" class="border rounded px-3 py-1 text-sm">
                                <option value="classic">Classic Elegant</option>
                                <option value="modern">Modern Minimal</option>
                                <option value="professional">Professional Training</option>
                                <option value="classic_golden">Classic Golden (Ornate)</option>
                                <option value="cisco">Cisco Style (Detailed)</option>
                            </select>
                            <div id="templateGallery" class="mt-2 flex gap-2">
                                <button class="template-thumb border rounded p-1" data-template="classic" title="Classic Elegant" style="width:72px;height:54px">
                                    <div style="width:100%;height:100%;background:linear-gradient(#fff,#f7f5f0);border:3px solid #d4af37;box-sizing:border-box"></div>
                                </button>
                                <button class="template-thumb border rounded p-1" data-template="modern" title="Modern Minimal" style="width:72px;height:54px">
                                    <div style="width:100%;height:100%;background:linear-gradient(#fff,#fafafa);border-top:6px solid #111827;box-sizing:border-box"></div>
                                </button>
                                <button class="template-thumb border rounded p-1" data-template="professional" title="Professional" style="width:72px;height:54px">
                                    <div style="width:100%;height:100%;background:linear-gradient(#fff,#f3f6fb);border:6px solid #0b63a7;box-sizing:border-box"></div>
                                </button>
                                <button class="template-thumb border rounded p-1" data-template="classic_golden" title="Classic Golden" style="width:72px;height:54px">
                                    <div style="width:100%;height:100%;background:linear-gradient(#fff,#fbfaf8);border:3px solid #d4af37;box-sizing:border-box;position:relative;">
                                        <div style="position:absolute;right:-6px;top:-6px;width:26px;height:26px;background:#d4af37;border-radius:50%;"></div>
                                    </div>
                                </button>
                                <button class="template-thumb border rounded p-1" data-template="cisco" title="Cisco Style" style="width:72px;height:54px">
                                    <div style="width:100%;height:100%;background:linear-gradient(#fff,#f6fbff);box-sizing:border-box;position:relative;">
                                        <div style="position:absolute;right:6px;top:6px;width:18px;height:14px;background:#2db2a8;border-radius:6px;transform:rotate(-18deg)"></div>
                                        <div style="position:absolute;right:18px;top:20px;width:30px;height:18px;background:#2b9be3;border-radius:9px;transform:rotate(-18deg)"></div>
                                        <div style="position:absolute;right:28px;top:36px;width:40px;height:22px;background:#16a34a;border-radius:11px;transform:rotate(-18deg)"></div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div class="ml-auto flex items-center space-x-2">
                            <div class="text-sm text-slate-600">Export:</div>
                            <button id="exportPng" class="bg-green-600 text-white px-3 py-1 rounded text-sm">PNG</button>
                            <button id="exportPdf" class="bg-gray-800 text-white px-3 py-1 rounded text-sm">PDF</button>
                        </div>
                    </div>

                    <div style="width:100%;height:auto;border:1px solid #e5e7eb; background:white; display:flex; justify-content:center; align-items:center; position:relative; padding:20px;" id="canvasContainer">
                        <div id="canvasInner" style="width:100%;max-width:1000px;background:white;display:flex;justify-content:center;align-items:center;">
                            <canvas id="fabricCanvas" style="display:block;width:100%;height:auto;"></canvas>
                        </div>
                        <div class="absolute right-2 top-2 flex space-x-2">
                            <button id="toggleProperties" class="bg-slate-600 text-white px-3 py-1 rounded text-sm">Hide Properties</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right properties panel -->
        <div class="relative z-0 properties-wrapper" id="propertiesPanel">
            <div class="bg-white p-4 rounded shadow sticky top-24 properties-panel mt-6">
                <h3 class="font-semibold mb-3">Properties</h3>
                @php
                    // allow editing templates as well - if $template is present, use template routes
                @endphp
                <form id="editorForm" action="{{ isset($template) ? route('certificate_templates.update', $template->id) : route('certificates.update', $certificate->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="certificate_data[certificate_id]" id="certificate_id" value="{{ $certificate->certificate_data['certificate_id'] ?? $certificate->id }}">
                    <div class="mb-3">
                        <label class="block text-sm font-medium">Recipient</label>
                        <input type="text" name="certificate_data[recipient]" id="recipient" value="{{ $certificate->certificate_data['recipient'] ?? optional($certificate->user)->fullname }}" class="mt-1 block w-full border rounded px-2 py-1">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium">Course</label>
                        <input type="text" name="certificate_data[course]" id="courseName" value="{{ $certificate->certificate_data['course'] ?? optional($certificate->course)->title }}" class="mt-1 block w-full border rounded px-2 py-1">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium">Date Issued</label>
                        <input type="date" name="certificate_data[issued_at]" id="issuedAt" value="{{ $certificate->certificate_data['issued_at'] ?? now()->toDateString() }}" class="mt-1 block w-full border rounded px-2 py-1">
                    </div>

                    <hr class="my-3">
                    <div id="elementProps" style="display:none;">
                        <div class="mb-2">
                            <label class="block text-sm font-medium">Text</label>
                            <input type="text" id="propText" class="mt-1 block w-full border rounded px-2 py-1">
                        </div>
                        <div class="mb-2">
                            <label class="block text-sm font-medium">Font Size</label>
                            <input type="number" id="propFontSize" class="mt-1 block w-full border rounded px-2 py-1" min="8" max="200">
                        </div>
                        <div class="mb-2">
                            <label class="block text-sm font-medium">Color</label>
                            <input type="color" id="propColor" class="mt-1 block w-full border rounded px-2 py-1">
                        </div>
                        <div class="mb-2">
                            <button type="button" id="removeElement" class="w-full bg-red-500 text-white py-2 rounded">Delete Element</button>
                        </div>
                    </div>

                    <input type="hidden" name="certificate_data[elements]" id="elementsInput">
                    <div class="mt-4">
                        <button type="submit" id="saveBtn" class="w-full bg-green-600 text-white py-2 rounded">Save Certificate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* keep small editor helpers */
    #certificateCanvas .editable-text{ padding:4px }
</style>
