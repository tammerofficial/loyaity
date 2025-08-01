<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class LogoController extends Controller
{
    /**
     * عرض قائمة الشعارات
     */
    public function index()
    {
        $logos = Logo::orderBy('is_active', 'desc')
                    ->orderBy('is_default', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        return view('admin.logos.index', compact('logos'));
    }

    /**
     * عرض صفحة إنشاء شعار جديد
     */
    public function create()
    {
        return view('admin.logos.create');
    }

    /**
     * حفظ شعار جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo_file' => 'required|image|mimes:jpeg,jpg,png,gif,svg,webp|max:2048',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'is_default' => 'boolean'
        ]);

        try {
            $file = $request->file('logo_file');
            
            // إنشاء اسم فريد للملف
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $filePath = 'logos/' . $fileName;
            
            // رفع الملف
            $path = Storage::putFileAs('public/logos', $file, $fileName);
            
            // الحصول على أبعاد الصورة
            $width = null;
            $height = null;
            
            if (in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                $imageInfo = getimagesize($file->getPathname());
                if ($imageInfo) {
                    $width = $imageInfo[0];
                    $height = $imageInfo[1];
                }
            }
            
            // إنشاء سجل الشعار
            $logo = Logo::create([
                'name' => $request->name,
                'file_path' => $filePath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'width' => $width,
                'height' => $height,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
                'is_default' => $request->has('is_default')
            ]);
            
            // إذا كان نشط، إلغاء تفعيل الآخرين
            if ($logo->is_active) {
                $logo->activate();
            }
            
            // إذا كان افتراضي، إلغاء الافتراضية من الآخرين
            if ($logo->is_default) {
                $logo->makeDefault();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'تم رفع الشعار بنجاح!',
                'logo' => $logo->load(''),
                'redirect' => route('admin.logos.index')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء رفع الشعار: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض تفاصيل شعار معين
     */
    public function show(Logo $logo)
    {
        return view('admin.logos.show', compact('logo'));
    }

    /**
     * عرض صفحة تحرير شعار
     */
    public function edit(Logo $logo)
    {
        return view('admin.logos.edit', compact('logo'));
    }

    /**
     * تحديث شعار موجود
     */
    public function update(Request $request, Logo $logo)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo_file' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp|max:2048',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'is_default' => 'boolean'
        ]);

        try {
            $updateData = [
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
                'is_default' => $request->has('is_default')
            ];
            
            // إذا تم رفع ملف جديد
            if ($request->hasFile('logo_file')) {
                $file = $request->file('logo_file');
                
                // حذف الملف القديم
                if (Storage::exists($logo->file_path)) {
                    Storage::delete($logo->file_path);
                }
                
                // رفع الملف الجديد
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $filePath = 'logos/' . $fileName;
                Storage::putFileAs('public/logos', $file, $fileName);
                
                // الحصول على أبعاد الصورة الجديدة
                $width = null;
                $height = null;
                
                if (in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                    $imageInfo = getimagesize($file->getPathname());
                    if ($imageInfo) {
                        $width = $imageInfo[0];
                        $height = $imageInfo[1];
                    }
                }
                
                $updateData = array_merge($updateData, [
                    'file_path' => $filePath,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'width' => $width,
                    'height' => $height
                ]);
            }
            
            $logo->update($updateData);
            
            // إذا كان نشط، إلغاء تفعيل الآخرين
            if ($logo->is_active) {
                $logo->activate();
            }
            
            // إذا كان افتراضي، إلغاء الافتراضية من الآخرين
            if ($logo->is_default) {
                $logo->makeDefault();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الشعار بنجاح!',
                'logo' => $logo->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الشعار: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف شعار
     */
    public function destroy(Logo $logo)
    {
        try {
            // منع حذف الشعار النشط أو الافتراضي
            if ($logo->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف الشعار النشط. يرجى تفعيل شعار آخر أولاً.'
                ], 422);
            }
            
            if ($logo->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف الشعار الافتراضي. يرجى تعيين شعار افتراضي آخر أولاً.'
                ], 422);
            }
            
            $logo->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'تم حذف الشعار بنجاح!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الشعار: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تفعيل شعار
     */
    public function activate(Logo $logo)
    {
        try {
            $logo->activate();
            
            return response()->json([
                'success' => true,
                'message' => 'تم تفعيل الشعار بنجاح!',
                'logo' => $logo->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تفعيل الشعار: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جعل شعار افتراضي
     */
    public function makeDefault(Logo $logo)
    {
        try {
            $logo->makeDefault();
            
            return response()->json([
                'success' => true,
                'message' => 'تم تعيين الشعار كافتراضي بنجاح!',
                'logo' => $logo->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تعيين الشعار الافتراضي: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API للحصول على الشعار النشط (للاستخدام في المعاينة المباشرة)
     */
    public function getActiveLogo()
    {
        $logo = Logo::getActiveLogo();
        
        return response()->json([
            'success' => true,
            'logo' => $logo ? [
                'id' => $logo->id,
                'name' => $logo->name,
                'url' => $logo->url,
                'width' => $logo->width,
                'height' => $logo->height
            ] : null
        ]);
    }
}