<?php

namespace App\Services;

use App\Http\Requests\RegisterRequest;
use App\Models\User;

class UserService
{
    public function index(){
        return User::all();
    }

    public function show(string $id){
        return User::findOrFail($id);
    }

    public function update(array $request, string $id){
        return User::findOrFail($id)->update($request);
    }

    public function destroy(string $id){
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé avec succès'], 200);
    }

    public function getAvailableUsersForProject($projectId){
        return User::whereDoesntHave('projects', function ($query) use ($projectId) {
            $query->where('project_id', $projectId);
        })
            ->where('role', '!=', 'projectManager')
            ->where('role', '!=', 'productOwner')
            ->where('role', '!=', 'admin')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getAvailableUsers(){
        return User::whereDoesntHave('projects')
            ->where('role', '!=', 'projectManager')
            ->where('role', '!=', 'productOwner')
            ->orderBy('name', 'asc')
            ->get();
    }

}
