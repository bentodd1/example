<?php

use App\models\Task;
use App\models\SimulatedBet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Display All Tasks
 */
Route::get('/', function () {
    $tasks = Task::orderBy('created_at', 'asc')->get();
    $simulatedBets = SimulatedBet::orderBy('created_at', 'asc')->get();

    return view('tasks', [
        'tasks' => $tasks,
        'simulatedBets' => $simulatedBets
    ]);
});

/**
 * Add A New Task
 */
Route::post('/task', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'name' => 'required|max:255',
    ]);

    if ($validator->fails()) {
        return redirect('/')
            ->withInput()
            ->withErrors($validator);
    }

    $task = new Task;
    $task->name = $request->name;
    $task->save();

    return redirect('/');
});

/**
 * Delete An Existing Task
 */
Route::delete('/task/{id}', function ($id) {
    Task::findOrFail($id)->delete();

    return redirect('/');
});
