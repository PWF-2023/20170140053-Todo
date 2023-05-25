<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TodoController extends Controller
{
    public function index()
    {
        $todos = Todo::where('user_id', auth()->user()->id)
            ->orderBy('is_complete', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // dd($todos);
        $todosCompleted = Todo::where('user_id', auth()->user()->id)
            ->where('is_complete', true)
            ->count();
        return view('todo.index', compact('todos', 'todosCompleted'));
    }

    public function create()
    {
        $categories = Category::where('user_id', auth()->user()->id)->get();
        return view('todo.create', compact('categories'));
    }

    public function edit(Todo $todo)
    {
        $categories = Category::where('user_id', auth()->user()->id)->get();
        if (auth()->user()->id == $todo->user_id) {
            // dd($todo);

            return view('todo.edit', compact(['todo', 'categories']));
        } else {
            // abort(403);
            // abort(403, 'Not authorized');
            return redirect()->route('todo.index')->with('danger', 'You are not authorized to edit this todo!');
        }
    }

    public function update(Request $request, Todo $todo)
    {
        $request->validate([
            'title' => 'required|max:255',
            'category_id' => 'nullable',
        ]);
        // Practical
        // $todo->title = $request->title;
        // $todo->save();

        // Eloquent Way - Readable
     /*    $todo->update([
            'title' => ucfirst($request->title),
        ]); */
        $todo->update([
            'title' => ucfirst($request->title),
            'category_id' => $request->category_id,
            //'user_id' => auth()->user()->id,
        ]);
        if (!empty($request->category_id)) {
            $todo['category_id'] = $request->category_id;
        }
        $todo->save();
        return redirect()->route('todo.index')->with('success', 'Todo updated successfully!');
    }

    public function complete(Todo $todo)
    {
        if (auth()->user()->id == $todo->user_id) {
            $todo->update([
                'is_complete' => true,
            ]);
            return redirect()->route('todo.index')->with('success', 'Todo completed successfully!');
        } else {
            return redirect()->route('todo.index')->with('danger', 'You are not authorized to complete this todo!');
        }
    }

    public function uncomplete(Todo $todo)
    {
        if (auth()->user()->id == $todo->user_id) {
            $todo->update([
                'is_complete' => false,
            ]);
            return redirect()->route('todo.index')->with('success', 'Todo uncompleted successfully!');
        } else {
            return redirect()->route('todo.index')->with('danger', 'You are not authorized to uncomplete this todo!');
        }
    }

    public function store(Request $request, Todo $todo,  Category $category)
    {
        $request->validate([
            'title' => 'required|max:255',
            'category_id' => 'nullable',
        ]);
        // $todo = Todo::create([
        //     'title' => ucfirst($request->title),
        //     // 'category_id' => ucfirst($request->category_id),
        //     'user_id' => auth()->user()->id,
        // ]);
        $todo = [
            'title' => ucfirst($request->title),
            'user_id' => auth()->user()->id,
        ];

        if (!empty($request->category_id)) {
            $todo['category_id'] = $request->category_id;
        }

        $todo = Todo::create($todo);
        return redirect()->route('todo.index')->with('success', 'Todo created successfully!');
    }

    public function destroy(Todo $todo)
    {
        if (auth()->user()->id == $todo->user_id) {
            $todo->delete();
            return redirect()->route('todo.index')->with('success', 'Todo deleted successfully!');
        } else {
            return redirect()->route('todo.index')->with('danger', 'You are not authorized to delete this todo!');
        }
    }

    public function destroyCompleted()
    {
        $todoCompleted = Todo::where('user_id', auth()->user()->id)
            ->where('is_complete', true)
            ->get();
        foreach ($todoCompleted as $todo) {
            $todo->delete();
        }
        return redirect()->route('todo.index')->with('success', 'All completed todos deleted successfully!');
    }
}
