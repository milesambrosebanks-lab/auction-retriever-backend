<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Models\Menu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class MenuController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'menu');
    }

    public function index(Request $request)
    {
        $menus = Menu::query()->latest()->get();
        $groupedMenus = $menus->groupBy('parent_id');
        return view("backend.layouts.menu.index", compact('menus', 'groupedMenus'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'             => 'required|max:250',
            'parent_id'        => 'nullable|exists:menus,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $menu = new Menu();

            $menu->slug = Helper::makeSlug(Menu::class, $data['name']);

            $menu->name = $data['name'];
            $menu->parent_id = $data['parent_id'];
            $menu->save();

            session()->put('t-success', 'Created Successfully.');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.menu.index')->with('t-success', 'Created Successfully.');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|max:50',
            'parent_id'    => 'nullable|exists:menus,id|not_in:' . $id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $menu = Menu::findOrFail($id);

            $menu->name = $data['name'];
            $menu->parent_id = $data['parent_id'] ?? null;
            $menu->save();

            session()->put('t-success', 'updated successfully');

        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->back()->with('t-success', 'updated successfully');
    }

    public function destroy(string $id)
    {
        try {
            $menu = Menu::findOrFail($id);
            $menu->delete();
            session()->put('t-success', 'Menu deleted successfully');
        } catch (Exception $e) {
            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.menu.index');
    }

    public function status(int $id): JsonResponse
    {
        $data = Menu::findOrFail($id);
        if (!$data) {
            return response()->json([
                'status' => 't-error',
                'message' => 'Item not found.',
            ]);
        }
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();
        return response()->json([
            'status' => 't-success',
            'message' => 'Your action was successful!',
        ]);
    }

}
