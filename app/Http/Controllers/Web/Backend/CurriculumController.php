<?php
namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class CurriculumController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'curriculum');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'title'     => 'required|max:250',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $curriculum = new Curriculum();

            $curriculum->course_id   = $data['course_id'];
            $curriculum->title       = $data['title'];
            $curriculum->save();

            session()->put('t-success', 'Created Successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->back()->with('t-success', 'Created Successfully');
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'course_id'   => 'required|exists:courses,id',
            'title'       => 'required|max:250'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $curriculum = Curriculum::findOrFail($id);

            $curriculum->course_id   = $data['course_id'];
            $curriculum->title       = $data['title'];
            $curriculum->save();

            session()->put('t-success', 'Updated Successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->back()->with('t-success', 'Updated Successfully');
    }

    public function destroy(string $id)
    {
        $data = Curriculum::findOrFail($id);
        $data->delete();
        return redirect()->back()->with('t-success', 'Delete Successfully');
    }

}