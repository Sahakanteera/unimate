<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', ['categories' => Category::withCount('activities')->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($request->validate(['name' => ['required', 'string', 'max:100', 'unique:categories,name']]));

        return back()->with('success', 'เพิ่มหมวดหมู่แล้ว');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        // ยกเว้นรายการปัจจุบันในการตรวจชื่อซ้ำ เพื่อให้บันทึกชื่อเดิมของตัวเองได้
        $category->update($request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('categories')->ignore($category)]]));

        return back()->with('success', 'แก้ไขหมวดหมู่แล้ว');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // รวมกิจกรรมที่ยกเลิกแล้วด้วย เพราะยังต้องแสดงหมวดหมู่ในประวัติโพสต์
        // Foreign key restrictOnDelete ใน migration ป้องกันซ้ำที่ระดับฐานข้อมูล
        if ($category->activities()->exists()) {
            return back()->with('error', 'ลบไม่ได้: หมวดหมู่นี้มีกิจกรรมใช้งานอยู่');
        }
        $category->delete();

        return back()->with('success', 'ลบหมวดหมู่แล้ว');
    }
}
