<?php

namespace App\Http\Controllers;

use App\Models\CertificateAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CertificateAssetController extends Controller
{
    protected $allowedKeys = [
        'company_logo',
        'partner_logo',
        'signature_president',
        'signature_instructor',
        'signature_collaborator',
    ];

    public function index()
    {
        $assets = CertificateAsset::whereIn('key', $this->allowedKeys)
            ->get()
            ->pluck('path', 'key')
            ->toArray();

        $urls = [];
        foreach ($this->allowedKeys as $k) {
            if (!empty($assets[$k])) {
                $urls[$k] = Storage::disk('public')->url($assets[$k]);
            } else {
                $urls[$k] = null;
            }
        }

        return response()->json($urls);
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => ['required', Rule::in($this->allowedKeys)],
            'image' => ['required_without:file', 'nullable', 'string'],
            'file' => ['required_without:image', 'nullable', 'image', 'mimes:png,jpg,jpeg,webp'],
            'display_name' => ['sometimes','nullable','string','max:255'],
        ]);

        $key = $request->input('key');
        $type = Str::contains($key, 'signature') ? 'signature' : 'logo';

        if ($request->filled('image')) {
            $data = $request->input('image');
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $typeMatch)) {
                $data = substr($data, strpos($data, ',') + 1);
                $ext = strtolower($typeMatch[1]) === 'png' ? 'png' : 'png';
                $decoded = base64_decode($data);
                if ($decoded === false) {
                    return response()->json(['error' => 'Invalid image data'], 422);
                }
                $filename = 'certificates/' . ($type === 'signature' ? 'signatures' : 'logos') . '/' . now()->format('Ymd_His_') . Str::random(8) . '.' . $ext;
                Storage::disk('public')->put($filename, $decoded);
                $path = $filename;
                $source = 'draw';
            } else {
                return response()->json(['error' => 'Invalid data URI'], 422);
            }
        } else {
            $file = $request->file('file');
            $dir = 'certificates/' . ($type === 'signature' ? 'uploads/signatures' : 'uploads/logos');
            $path = $file->store($dir, 'public');
            $source = 'upload';
        }

        $update = [
            'path' => $path,
            'type' => $type,
            'source' => $source,
            'updated_by' => Auth::id(),
            'status' => 'active',
        ];
        if ($request->filled('display_name')) {
            $update['display_name'] = $request->input('display_name');
        }

        $asset = CertificateAsset::updateOrCreate(
            ['key' => $key],
            $update
        );

        return response()->json([
            'message' => 'Saved',
            'asset' => $asset,
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}
