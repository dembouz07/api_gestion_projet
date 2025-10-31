<?php

namespace App\Services;

use App\Models\Sprint;
use App\Models\User;
use Carbon\Carbon;

class SprintService
{
    public function index()
    {
        return Sprint::all()->map(function ($sprint) {
            return $this->formatSprintData($sprint);
        });
    }

    public function show(string $id){
        $sprint = Sprint::findOrFail($id);
        return $this->formatSprintData($sprint);
    }

    public function store(array $request){
        $sprint = Sprint::create($request);
        return $this->formatSprintData($sprint);
    }

    public function update(array $request, string $id){
        $sprint = Sprint::findOrFail($id);
        $sprint->update($request);
        return $this->formatSprintData($sprint);
    }

    public function destroy(string $id){
        $sprint = Sprint::findOrFail($id);
        $sprint->delete();
        return response()->json(['message' => 'Sprint supprimé avec succès'], 200);
    }

    private function formatSprintData(Sprint $sprint)
    {
        $start = Carbon::parse($sprint->start_date);
        $end = Carbon::parse($sprint->deadline);
        $today = Carbon::now();

        $durationDays = $start->diffInDays($end) + 1;
        $daysLeft = $today->lessThanOrEqualTo($end) ? $today->diffInDays($end) : 0;

        return [
            'id' => $sprint->id,
            'number' => $sprint->number,
            'objective' => $sprint->objective,
            'project_id' => $sprint->project_id,
            'start_date' => $sprint->start_date,
            'deadline' => $sprint->deadline,
            'status' => $sprint->status,
            'duration_days' => $durationDays,
            'days_left' => $daysLeft,
        ];
    }
}
