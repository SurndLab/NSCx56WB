<?php

namespace App\Http\Controllers;

use App\Models\Publisher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PublisherAdminController extends Controller
{
    public function create(Publisher $publisher)
    {
        return view('publisher-admins.form', compact('publisher'));
    }

    public function store(Request $request, Publisher $publisher)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:64', 'unique:users,username'],
            'password' => ['required', 'string', 'min:4'],
            'display_name' => ['required', 'string', 'max:255'],
        ]);

        $publisher->admins()->create([
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'display_name' => $data['display_name'],
            'role' => User::ROLE_PUBLISHER_ADMIN,
            'is_active' => true,
        ]);

        return redirect()->route('publishers.show', $publisher->id)->with('success', '管理員已建立');
    }

    public function edit(Publisher $publisher, User $admin)
    {
        if ((int) $admin->publisher_id !== (int) $publisher->id) {
            abort(404);
        }
        return view('publisher-admins.form', compact('publisher', 'admin'));
    }

    public function update(Request $request, Publisher $publisher, User $admin)
    {
        if ((int) $admin->publisher_id !== (int) $publisher->id) {
            abort(404);
        }

        $data = $request->validate([
            'username' => ['required', 'string', 'max:64', 'unique:users,username,' . $admin->id],
            'password' => ['nullable', 'string', 'min:4'],
            'display_name' => ['required', 'string', 'max:255'],
        ]);

        $admin->username = $data['username'];
        $admin->display_name = $data['display_name'];
        if (!empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }
        $admin->save();

        return redirect()->route('publishers.show', $publisher->id)->with('success', '管理員已更新');
    }

    public function destroy(Publisher $publisher, User $admin)
    {
        if ((int) $admin->publisher_id !== (int) $publisher->id) {
            abort(404);
        }

        $admin->delete();

        return redirect()->route('publishers.show', $publisher->id)->with('success', '管理員已刪除');
    }
}
